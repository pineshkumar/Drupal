Drupal Project Setup
This script automates the installation and configuration of a Drupal 10 project using Lando and Composer. It handles the creation of a Drupal site, database setup, module installations, role and content type creation, and configuration export.

Prerequisites
Lando (development environment tool)
Composer (PHP dependency manager)
Ensure that both Lando and Composer are installed on your system before running the script.

Script Breakdown
Variables:

DB_URL: URL for the SQL database file to be imported.
PROJECT_NAME: The name of the Drupal project.
DRUPAL_VERSION: Version of Drupal to be installed.
CONFIG_SYNC_DIR: Directory for configuration synchronization.
DB_NAME, DB_USER, DB_PASS, DB_HOST: Database credentials and host information.
ADMIN_USER, ADMIN_PASS: Drupal admin credentials.
Command Checks:

Checks if lando and composer are installed. If not, the script exits.
Drupal Project Creation:

Uses Composer to create a new Drupal project based on the specified version.
Initializes a new Lando environment with the Drupal 10 recipe.
Database Setup:

Starts the Lando environment.
Waits for the database service to be ready.
Attempts to download and import a database from a remote URL. If the download fails, it proceeds with a fresh Drupal installation using Drush.
Drush and Module Setup:

Installs Drush and the admin_toolbar module using Composer within the Lando environment.
If a fresh installation is performed, the script:
Installs Drupal using Drush.
Enables the admin_toolbar and admin_toolbar_tools modules.
Creates custom roles (member and employee).
Creates a custom content type (blog) and assigns appropriate permissions to the member role.
Configuration Management:

Creates a configuration sync directory and updates Drupal's settings.php to use this directory.
Exports the current configuration to the sync directory using Drush.
Final Steps:

Generates a login link using drush uli.
Prints a message indicating that the setup is complete.
Usage
Run the script in your terminal:

./setup-drupal.sh
Make sure to adjust any necessary variables (e.g., admin credentials, database details) before running the script.
