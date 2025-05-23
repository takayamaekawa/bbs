# bbs

## Description
This is a project to revive a bulletin board system, originally created in PHP.

## Requirements

* PHP (version 8.0 or higher recommended)
* PDO SQLite extension (`php-sqlite` or `php_pdo_sqlite`)
* PDO MySQL extension (`php-mysql` or `php_pdo_mysql`)
* Composer (for managing dependencies, if any are added later)
* GNU Make (for using the `Makefile` commands like `make dev`)

## Setup and Configuration

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd bbs
    ```

2.  **Install Composer dependencies (if any):**
    If a `composer.json` file exists and specifies dependencies:
    ```bash
    composer install
    ```

3.  **Application Configuration (`config.json`):**
    This project requires a `config.json` file in the `config/` directory for database settings, API keys, and other application settings.
    * Copy the example configuration file:
        ```bash
        cp config/config.json.example config/config.json
        ```
    * Edit `config/config.json` with your specific settings:
        * Set `database.type` to either `"sqlite"` or `"mysql"`.
        * If using SQLite, ensure the `database.path` (e.g., `"database/app.sqlite"`) points to your desired SQLite file location (relative to the project root). The `database` directory will be created if it doesn't exist, and the SQLite file will be created automatically upon first connection if it doesn't exist, provided PHP has write permissions.
        * If using MySQL, configure `database.host`, `database.name`, `database.user`, and `database.password`.
        * Update `app_settings` and `footer` sections as needed.

4.  **PHP Configuration (`project-php.ini` or global `php.ini`):**
    The application requires specific PHP extensions to be enabled (e.g., `pdo_sqlite`, `pdo_mysql`). The `make dev` command is configured to use a project-specific `project-php.ini` file located in the project root.

    * **Method 1: Using `project-php.ini` (Recommended for `make dev`)**
        1.  Copy the example PHP INI file:
            ```bash
            cp project-php.ini.example project-php.ini
            ```
        2.  **Edit `project-php.ini`:**
            * **Crucially, you must set the `extension_dir` directive.** This tells PHP where to find the extension `.dll` (Windows) or `.so` (Linux/macOS) files.
                * Find your PHP installation's extension directory. You can often find this by looking at your main `php.ini` (`php --ini` will show its location) or by knowing your PHP installation path (e.g., `C:/path/to/php/ext` on Windows, or `/usr/lib/php/modules` on Linux).
                * Update the `extension_dir` line in `project-php.ini`. For example:
                    * Windows: `extension_dir = "C:/Users/your_username/php-8.x.x/ext"`
                    * Linux: `extension_dir = "/usr/lib/php/20230831"` (the exact path depends on your PHP version and distribution)
            * The example file already includes `extension=pdo_sqlite` and `extension=pdo_mysql`. Ensure these are uncommented.

    * **Method 2: Modifying your global `php.ini` (Alternative)**
        As an alternative to using `project-php.ini`, you can enable the required extensions directly in your system's main `php.ini` file.
        1.  Find your `php.ini` file:
            ```bash
            php --ini
            ```
            Look for the "Loaded Configuration File" line.
        2.  Edit this `php.ini` file.
        3.  Ensure the `extension_dir` directive is correctly set.
        4.  Uncomment or add the lines for the required extensions:
            ```ini
            extension=pdo_sqlite
            extension=pdo_mysql
            ```
        5.  Save the `php.ini` file. If you are using a web server like Apache or Nginx with PHP-FPM, you will need to restart it for the changes to take effect. For the command-line PHP (used by `make dev`), changes are typically picked up on the next execution.

    **Note:** The `make dev` command is specifically configured to load `project-php.ini` using the `-c` flag. If you choose to modify your global `php.ini` and do not want to use `project-php.ini`, you can remove or comment out the `-c $(PROJECT_PHP_INI_ABS)` part from the `DEV_COMMAND` in the `Makefile`. However, using `project-php.ini` can be helpful for project-specific PHP configurations without altering your global PHP setup.

5.  **Initialize the database and tables:**
    The `bootstrap.php` script, when run (e.g., via `make dev` and accessing the site), will attempt to:
    * Create the SQLite database file if it doesn't exist (at the path specified in `config.json`).
    * Create necessary tables (e.g., `members`) if they don't exist.
    Ensure the directory for the SQLite database (e.g., `database/`) is writable by the PHP process.

## Running the Development Server

To start the PHP development server:
```bash
make dev
```
This will typically start the server on `http://127.0.0.1:8000` (configurable in the `Makefile`).
The server will use settings from `project-php.ini` if it exists at the project root.

## Logging
* Bootstrap process logs: `logs/bootstrap.log`
* PHP runtime errors: `logs/php_errors.log`

Ensure the `logs/` directory is writable by the PHP process.

## License
[MIT](LICENSE)
