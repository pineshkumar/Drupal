#!/bin/bash

# Variables
DB_URL="https://github.com/pineshkumar/d10-database/blob/main/d10-database.sql"
DB_FILE="d10-database.sql"
DRUPAL_PROFILE="standard"
DRUPAL_VERSION="10"
PROJECT_NAME="drupal-recipe"
CONFIG_SYNC_DIR="config/sync"
DB_NAME="drupal10"
DB_USER="drupal10"
DB_PASS="drupal10"
DB_HOST="database"
ADMIN_USER="admin"
ADMIN_PASS="admin"

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

# Initialize Lando
lando init --recipe drupal10 --webroot web --name $PROJECT_NAME

# Start Lando
lando start

# Wait for the database service to be fully up and running
echo "Waiting for the database service to be ready..."
lando ssh -s database -c "while ! mysqladmin ping --host=$DB_HOST --user=$DB_USER --password=$DB_PASS --silent; do echo waiting for mysql; sleep 2; done"

# Install Drush and required modules within the Lando appserver service
lando composer require drush/drush
lando composer require drupal/admin_toolbar

# Try downloading the database SQL file
curl -f -L $DB_URL -o $DB_FILE
if [ $? -ne 0 ]; then
  echo "Database file not found at $DB_URL. Proceeding with fresh installation."
  DB_FILE=""
fi

if [ -n "$DB_FILE" ]; then
  # Import the database if the file exists
  lando db-import $DB_FILE
else
  # Install Drupal with Drush
  lando drush site:install $DRUPAL_PROFILE --account-name=$ADMIN_USER --account-pass=$ADMIN_PASS --db-url=mysql://$DB_USER:$DB_PASS@$DB_HOST/$DB_NAME --yes

  # Enable necessary modules
  lando drush en admin_toolbar admin_toolbar_tools -y

  # Create roles
  lando drush role:create "member"
  lando drush role:create "employee"

  # Create blog content type
  lando drush php:eval "
    \$type = \Drupal\node\Entity\NodeType::create([
      'type' => 'blog',
      'name' => 'Blog',
    ]);
    \$type->save();
  "

  # Give permissions for blog content type
  lando drush php:eval "
    \$role = \Drupal::service('entity_type.manager')->getStorage('user_role')->load('member');
    \$role->grantPermission('create blog content')->save();
    \$role->grantPermission('edit own blog content')->save();
    \$role->grantPermission('delete own blog content')->save();
  "
fi

# Create the config sync directory if it doesn't exist
mkdir -p $CONFIG_SYNC_DIR

# Set the configuration sync directory in settings.php
echo "\$settings['config_sync_directory'] = '$CONFIG_SYNC_DIR';" >> web/sites/default/settings.php

# Export the configuration to the sync directory
lando drush cex --destination=$CONFIG_SYNC_DIR -y

# Print the Drush ULI URL for login
echo "Generating Drush ULI URL..."
lando drush uli

# Print finishing message
echo "Drupal installation and setup completed!"
