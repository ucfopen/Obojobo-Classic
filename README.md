# Obojobo
Obojobo is a Learning Module Management System

# Quick Start with Docker
For local development, testing, and as a reference for the architecture setup, we have a Docker environment to get you up and running quickly.

The docker-compose setup will automatically set up the server containers, seed the database, and get everything up and running.

1. Install [Docker for Mac/Windows/Linux](https://www.docker.com/products/docker)
2. Run `docker-compose up -d` (-d makes it run in the background eg: daemonized). this will take a while the first time.
3. After 2 finishes: Run `docker-compose run --rm phpfpm composer install` to install wordpress and all the php vendor libraries.
4. After 3 finishes:  Run `docker-compose run --rm phpfpm php internal/update_password.php obojobo_admin` VIEW output for the obojobo_admin user's password.
5. Log in either at `http://127.0.0.1/repository` or `http://127.0.0.1/wp/wp-admin`

## React Repository AND Docker

Docker isn't needed to work with storybook components, just use `yarn storybook` for that.  To allow react to talk to the server, you'll need to run webpack-dev-server and the docker servers together.

That means you need to have BOTH the docker servers running (`docker-compose up -d` shown above) AND the webpack dev server (`yarn dev`).  Using this combo requires the obojobo config for APP_URL be set to the url that webpack-dev-server creates, that value is hardcoded (due to php class const) in cfgLocal.docker.php.  The url you should use is whatever webpack-dev-server announces when it starts running, usually `https:/127.0.0.1:8080`


# Requirements
* Unique domain or sub domain (obojobo.yourschool.edu)
* libjpeg
* libpng
* Nginx & PHPFPM (or Apache & mod_php)
* MySQL 5.5 or 5.6 database
* Memcached
* PHP 5.6 (with the following extensions)
 * gd
 * mbstring
 * mycrypt
 * mysql
 * mysqlnd
 * opcache
 * pecl-memcache
 * xml
* Install PHP Composer via https://getcomposer.org/download


# Production Install
## PHP Setup
1. Configure your php.ini settings. (typically located in /etc/php5/php.ini or nearby)
 * set `date.timezone` to `America/New_York` or whatever's appropriate
 * set `session.save_handler` to 'memcache'
 * set `session.save_path` to `localhost:11211` (should be whatever your memcache server is running)

## PHP-FPM Setup (NGINX ONLY)
1. Set a few php-fpm options (typically located in /etc/php-fpm.conf or /etc/php-fpm.d/*.conf)
 * user = nginx
 * group = nginx
 * `security.limit_extensions` to `.php`

## NGINX Setup
We have a handful of url routing settings unique to Obojobo that need to be configured into the webserver.

1. Set up your Nginx config, typically located in /etc/nginx/nginx.conf or /etc/nginx/conf.d/*.conf
2. Use the rules set in `internal/docker/nginx.conf` as a reference. Note the docker example is setup so that obojobo is the only site.

## APACHE SETUP
1. Enable modrewrite `sudo a2enmod rewrite`
3. Use `internal/docker/apache-vhost.conf` to update your apache virtual hosts. Ve sure to adjust the domain matching and directories if needed
4. `sudo service apache2 restart`

## Create Databases
Keeping separate users for wordpress and obojobo tables helps to somewhat isolate data and permissions.

1. Create 2 mysql users
  * `obojobo_user`
  * `obojobo_wp_user`

2. Create 2 mysql databases
  * `obojobo`
  * `obojobo_wordpress`

3. Give each user access to each database
  * `GRANT ALL ON `obojobo`.* TO 'obojobo_user'@'%';`
  * `GRANT ALL ON `obojobo_wordpress`.* TO 'obojobo_wp_user'@'%';`
  * `FLUSH PRIVILEGES;`

4. Create tables and fill sample data
  * `mysql -uroot -p < internal/docker/01_obojobo_tables.sql`
  * `mysql -uroot -p < internal/docker/02_obojobo_sampledata.sql`
  * `mysql -uroot -p < internal/docker/04_wordpress_tables.sql`
  * `mysql -uroot -p < internal/docker/05_wordpress_data.sql`

5. Update wordpress tables to match your domain. The sample data uses `http://localhost`, use a tool like https://rudrastyh.com/tools/sql-queries-generator to change that to your own domain.

## Set up Obojobo
1. Git clone Obojobo `git clone git@github.com:ucfcdl/Obojobo.git /var/www/obojobo`
2. Install composer libraries: In the /var/www/obojobo and run `composer install` or `php composer.phar install`
3. Make sure the following directories are writable by the webserver user (usually nginx or www-user).
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
