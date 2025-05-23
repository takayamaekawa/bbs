# Makefile

PHP = php
HOST = 127.0.0.1
# HOST = 0.0.0.0 # For LAN access
PORT = 8000
DOCROOT = public

# Project-specific PHP configuration file (located in the project root)
# This file should contain directives to enable extensions like pdo_sqlite or pdo_mysql.
PROJECT_PHP_INI_REL = ./project-php.ini
# Get the absolute path to pass to PHP's -c option
PROJECT_PHP_INI_ABS = $(abspath $(PROJECT_PHP_INI_REL))

default: help

# Using the -c option to specify a single ini file should work cross-platform
DEV_COMMAND = $(PHP) -S $(HOST):$(PORT) -t $(DOCROOT) -c $(PROJECT_PHP_INI_ABS)

dev:
	@echo "Starting PHP development server..."
	@echo "Document root: $(DOCROOT)"
	@echo "Additional INI file (absolute path): $(PROJECT_PHP_INI_ABS)"
	@echo "Access URL: http://$(HOST):$(PORT)"
	@echo "Command to be executed (for debugging): $(DEV_COMMAND)"
	@echo "Press Ctrl+C to stop the server."
	$(DEV_COMMAND)

help:
	@echo ""
	@echo "Available commands:"
	@echo "  make dev    - Start the PHP development server (http://$(HOST):$(PORT))"
	@echo "              It will also load settings from $(PROJECT_PHP_INI_ABS)."
	@echo "  make help   - Display this help message"
	@echo ""

.PHONY: default dev help
