# Obojobo
Obojobo is a Learning Module Management System

# License
Obojobo is not open source. If you have access to this repository, your license are goverend by the agreement you signed with UCF.

# Requirements

* PHP 5.6
 * gd
 * mbstring
 * mycrypt
 * mysqlnd
 * opcache
 * pdo
 * pecl-memcache
 * pecl-oauth
 * xml
* Memcached
* Nginx (we prefer Nginx, but Apache will work)
* MySQL 5.6 database
* Unique Domain or sub domain (obojobo.yourschool.edu)

# Install

1. Install all the required yum/apt-get packages listed above
2. Clone the main Obojobo repository to a web directory `git clone git@github.com:ucfcdl/Obojobo.git /var/www/obojobo`
3. Install composer (locally or globally) with instructions from https://getcomposer.org/download
4. In the root app directory run `composer install` (w/ global composer) or `php composer.phar install` (w/ local composer)
5. Make sure these directories are writable by the webserver's user (in our case, the nginx user)
 * internal/logs
 * internal/media
 * internal/templates/compiled
6. Set up php ini
 * `date.timezone`
 * `short_open_tag` - we used to need this but I believe it's no longer required
 * `session.save_handler` to 'memcache'
 * `session.save_path` to speed up sessions, use memcache by setting a value to something like: `tcp://localhost:11211?persistent=1&weight=1&timeout=1&retry_interval=15`
 * `security.limit_extensions` to `.php` especially if your using nginx
7. Set a few php-fpm options
 * user = nginx
 * group = nginx
8. Set up your Nginx config. See `internal/config/nginx.sample.conf`
9. Create your obojobo database, user, and tables defined in `internal/config/tables.sql`
10. Add a cron to run every 15 minutes `php /var/www/obojobo/internal/includes/cron15minute.php >> /var/log/cron.log 2>&1`
11. Copy `/internal/config/cfgLocal.default.php` to `/internal/config/cfgLocal.php`
12. Customize your cfgLocal.php settings

# Administration
TBD
