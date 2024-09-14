# Drupal Recipe Project Setup

This script automates the installation and configuration of a Drupal 10 project using **Lando** and **Composer**. It handles the creation of a Drupal site, database setup, module installations, role and content type creation, and configuration export.

## Prerequisites

Ensure that both [Lando](https://docs.lando.dev/basics/installation.html) and [Composer](https://getcomposer.org/download/) are installed on your system before running the script.

### Required Tools:
- **Lando**: A local development environment tool.
- **Composer**: PHP dependency manager.

## Script Breakdown

1. **Variables**:
    - `DB_URL`: URL for the SQL database file to be imported.
    - `PROJECT_NAME`: The name of the Drupal project.
    - `DRUPAL_VERSION`: Version of Drupal to be installed.
    - `CONFIG_SYNC_DIR`: Directory for configuration synchronization.
    - `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_HOST`: Database credentials and host information.
    - `ADMIN_USER`, `ADMIN_PASS`: Drupal admin credentials.

2. **Command Checks**:
    - Checks if `lando` and `composer` are installed. If not, the script exits with a message.

3. **Drupal Project Creation**:
    - Uses Composer to create a new Drupal project.
    - Initializes a new Lando environment with the Drupal 10 recipe.

4. **Database Setup**:
    - Starts the Lando environment.
    - Waits for the database service to be ready.
    - Attempts to download and import a database from a remote URL. If the download fails, it proceeds with a fresh Drupal installation using Drush.

5. **Drush and Module Setup**:
    - Installs Drush and the `admin_toolbar` module using Composer within the Lando environment.
    - If a fresh installation is performed, the script:
        - Installs Drupal using Drush.
        - Enables the `admin_toolbar` and `admin_toolbar_tools` modules.
        - Creates custom roles (`member` and `employee`).
        - Creates a custom content type (`blog`) and assigns appropriate permissions to the `member` role.

6. **Configuration Management**:
    - Creates a configuration sync directory and updates Drupal's `settings.php` to use this directory.
    - Exports the current configuration to the sync directory using Drush.

7. **Final Steps**:
    - Generates a login link using `drush uli`.
    - Prints a message indicating that the setup is complete.

## Usage

### Run the Script

Make sure all the required dependencies are installed and then run the following commands to set up your Drupal project:

1. Clone the repository and navigate to the directory:
    ```bash
    git clone https://your-repo-url.git
    cd your-project-directory
    ```

2. Make the script executable:
    ```bash
    chmod +x setup-drupal.sh
    ```

3. Run the script:
    ```bash
    ./setup-drupal.sh
    ```
