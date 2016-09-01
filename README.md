# Obojobo
Obojobo is a Learning Module Management System

# License
Obojobo is not open source. If you have access to this repository, your license are goverend by the agreement you signed with UCF.

# Quick Start with Docker
For local development, testing, and as a reference for the architecture setup, we have a Docker environment to get you up and running quickly.

The docker-compose setup will automatically set up the server containers, seed the database, and get everything up and running.

1. Install [Docker for Mac/Windows/Linux](https://www.docker.com/products/docker)
2. Install PHP Composer via https://getcomposer.org/download
3. Run `composer install` - if you have git permission issues, [configure your github ssh keys](https://help.github.com/articles/generating-an-ssh-key/)
4. Run `docker-compose up`
5. After up finishes, Run `docker-compose run --rm phpfpm php internal/update_password.php obojobo_admin` to generate the obojobo_admin user's password


# Requirements
* Unique domain or sub domain (obojobo.yourschool.edu)
* libjpeg
* libpng
* Nginx (Apache can work too)
* MySQL 5.5 or 5.6 database
* Memcached
* PHP 5.6 (with the following extensions)
 * gd
 * mbstring
 * mycrypt
 * mysql
 * mysqlnd
 * oauth
 * opcache
 * pecl-memcache
 * pecl-oauth
 * xml
* Install PHP Composer via https://getcomposer.org/download


# Production Install
## PHP Setup

1. Set a few php-fpm options (typically located in /etc/php-fpm.conf or /etc/php-fpm.d/*.conf)
 * user = nginx
 * group = nginx
 * `security.limit_extensions` to `.php`
2. Configure your php.ini settings. (typically located in /etc/php5/php.ini or nearby)
 * set `date.timezone` to `America/New_York` or whatever's appropriate
 * set `session.save_handler` to 'memcache'
 * set `session.save_path` to `localhost:11211` (should be whatever your memcache server is running)

## NGINX Setup
We have a handful of url routing settings unique to Obojobo that need to be configured into the webserver.

1. Set up your Nginx config, typically located in /etc/nginx/nginx.conf or /etc/nginx/conf.d/*.conf
2. Use the rules set in `internal/docker/nginx.conf` as a reference. Note the docker example is setup so that obojobo is the only site.

## Create Databases
Keeping separate users for wordpress and obojobo tables helps to somewhat isolate data and permissions.

1. Create 2 mysql users
  * `obojobo_user`
  * `obojobo_wp_user`
2. Create 2 mysql databases
  * `obojobo`
  * `obojobo_wordpress`
1. Create tables and default data
  * `internal/docker/01_obojobo_tables.sql`
  * `internal/docker/02_obojobo_sampledata.sql`
  * `internal/docker/03_wordpress_tables.sql`
  * `internal/docker/04_wordpress_data.sql`

## Set up Obojobo
1. Git clone Obojobo `git clone git@github.com:ucfcdl/Obojobo.git`, preferably to a directory like `/var/www/obojobo`
2. Install composer libraries: In the root app directory run `composer install` or `php composer.phar install`
3. Make sure the following directories are writable by phpfpm's user.
 * internal/logs
 * internal/media
 * internal/templates/compiled
4. Copy `/internal/config/cfgLocal.default.php` to `/internal/config/cfgLocal.php` and customize

## Configure

1. Run through the options in cfgLocal.php for database connection info, paths, and hosts.




# Customization

## Wordpress Themes

## Login Modules


# Administration
TBD
