
#!/bin/bash

# Variables
PROJECT_NAME="drupal-elasticsearch"
DRUPAL_PROFILE="standard"
DRUPAL_VERSION="10"
CONFIG_SYNC_DIR="config/sync"
DB_NAME="drupal10"
DB_USER="drupal10"
DB_PASS="drupal10"
DB_HOST="database"
ADMIN_USER="admin"
ADMIN_PASS="admin"
ELASTICSEARCH_VERSION="7.10.2"

# Function to check if a command exists
command_exists () {
    command -v "$1" >/dev/null 2>&1 ;
}

# Check if Lando is installed
if ! command_exists lando; then
  echo "Lando could not be found, please install it first."
  exit 1
fi

# Check if Composer is installed
if ! command_exists composer; then
  echo "Composer could not be found, please install it first."
  exit 1
fi

# Create a new Drupal project with Composer
composer create-project drupal/recommended-project:$DRUPAL_VERSION $PROJECT_NAME

# Navigate to the project directory
cd $PROJECT_NAME || { echo "Failed to navigate to project directory: $PROJECT_NAME"; exit 1; }

# Modify composer.json to set minimum-stability to alpha
jq '. + { "minimum-stability": "alpha", "prefer-stable": true }' composer.json > temp.json && mv temp.json composer.json

# Initialize Lando
lando init --recipe drupal10 --webroot web --name $PROJECT_NAME

# Add Elasticsearch service to Lando configuration
cat <<EOL >> .lando.yml
services:
  elasticsearch:
    type: elasticsearch
    version: $ELASTICSEARCH_VERSION
    portforward: 9200

tooling:
  elasticsearch-index:
    service: appserver
    cmd: drush es:reindex

EOL

# Start Lando
lando start

# Wait for the database service to be fully up and running
echo "Waiting for the database service to be ready..."
lando ssh -s database -c "while ! mysqladmin ping --host=$DB_HOST --user=$DB_USER --password=$DB_PASS --silent; do echo waiting for mysql; sleep 2; done"

# Install Drush within the Lando appserver service
lando composer require drush/drush
lando composer require drupal/admin_toolbar
# Install Drupal with Drush
lando drush site:install $DRUPAL_PROFILE --account-name=$ADMIN_USER --account-pass=$ADMIN_PASS --db-url=mysql://$DB_USER:$DB_PASS@$DB_HOST/$DB_NAME --yes

# Install and enable Elasticsearch modules with stability
lando composer require "drupal/elasticsearch_connector:8.0.0-alpha1" "drupal/search_api:^1.0" --with-all-dependencies

# Enable necessary modules
lando drush en admin_toolbar admin_toolbar_tools -y || { echo "Failed to enable admin_toolbar modules."; exit 1; }
lando drush en elasticsearch_connector search_api -y || { echo "Failed to enable Elasticsearch modules."; exit 1; }

# Get the Elasticsearch service URL from Lando
ELASTICSEARCH_URL=$(lando info | jq -r '.services.elasticsearch.urls[0]')
if [ -z "$ELASTICSEARCH_URL" ]; then
  echo "Failed to retrieve Elasticsearch URL from Lando."
  exit 1
fi

# Log the retrieved Elasticsearch URL
echo "Elasticsearch URL: $ELASTICSEARCH_URL"

# Wait for Elasticsearch service to be ready
echo "Waiting for the Elasticsearch service to be ready..."
while ! curl -s $ELASTICSEARCH_URL > /dev/null; do echo "waiting for elasticsearch at $ELASTICSEARCH_URL"; sleep 2; done

# Configure Elasticsearch settings
lando drush php:eval "
  \$config = \Drupal::service('config.factory')->getEditable('elasticsearch_connector.cluster.default');
  \$config->set('url', '$ELASTICSEARCH_URL');
  \$config->save();
"

# Create the config sync directory if it doesn't exist
mkdir -p $CONFIG_SYNC_DIR

# Set the configuration sync directory in settings.php
echo "\$settings['config_sync_directory'] = '$CONFIG_SYNC_DIR';" >> web/sites/default/settings.php

# Export the configuration to the sync directory
lando drush config:export --destination=$CONFIG_SYNC_DIR -y

# Print the Drush ULI URL for login
echo "Generating Drush ULI URL..."
lando drush uli

# Print finishing message
echo "Drupal and Elasticsearch installation and setup completed!"
