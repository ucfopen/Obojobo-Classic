# Obojobo
Obojobo is a Learning Module Management System

# License
Obojobo is not open source. If you have access to this repository, your license are goverend by the agreement you signed with UCF.

# Requirements

* PHP 5.6 (with the following extensions)
 * gd
 * mbstring
 * mycrypt
 * mysqlnd
 * opcache
 * mysql
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
6. Set up php ini. See `internal/docker/php.ini`
 * `date.timezone` to `America/New_York` or whatever's appropriate for you
 * `session.save_handler` to 'memcached'
 * `session.save_path` to `localhost:11211`
7. Set a few php-fpm options
 * user = nginx
 * group = nginx
 * `security.limit_extensions` to `.php`
8. Set up your Nginx config. See `internal/docker/nginx.conf`
9. Create your obojobo database, user, and tables defined in `internal/docker/tables.sql`
10. Create your wordpress database and worpress specific mysql user.  See `internal/docker/wordpress_db.sql`
10. Add a cron to run every 15 minutes `php /var/www/obojobo/internal/includes/cron15minute.php >> /var/log/cron.log 2>&1`
11. Copy `/internal/config/cfgLocal.default.php` to `/internal/config/cfgLocal.php`
12. Test site by visiting [http://localhost/repository](http://localhost/repository)

# Setup

1. Seed Wordpress database
2. Seed Obojobo database
3. Set up an admin user

## Database
There are 2 databases, one for Wordpress, and one for Obojobo.  For enhanced security, it's worth keeping them in separate databases.

# Customization

## Wordpress Themes

## Login Modules


# Administration
TBD
