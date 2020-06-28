# proxify-test
Test assignment for Proxify

Author: Ivan Edrennikov
# Requirements
PHP 7.3 or higher

MySQL as database (not tested with others, but PostgreSQL will work I suppose).
If using MySQL 8 then set default password plugin to mysql_native_password.
# Installation
### Database and Timeout Setup
Database and timeout configuration is in file `config/worker.php`

### Production
To run in production:

`> composer install --no-dev`<br>
`> php start-worker.php`

### Development and Testing
To run on devel and for testing:

`> composer install`<br>
`> php start-worker.php`

This will install PHPUnit package.

To run tests:

`php vendor/bin/phpunit tests`