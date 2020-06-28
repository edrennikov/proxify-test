# proxify-test
Test assignment for Proxify

Author: Ivan Edrennikov

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