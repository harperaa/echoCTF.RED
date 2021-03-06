# echoCTF Installation
The following steps outline the installation instructions for the applications hosted on this repository.

Keep in mind that you are advised to run `frontend` and `backend` from separate nginx instances, each with its own uid and chroot location. Similarly the same logic must be followed when/if you're using `php-fpm`.

Note: Although the interfaces are able to run on any system, the VPN server is assumed to run on OpenBSD.

Before we start make sure you have MariaDB, NGiNX/Apache + PHP, php-memcached, composer and MEMCACHED running, this guide will not deal with these.



Also make sure you have installed the following UDF https://github.com/echoCTF/memcached_functions_mysql.git

* cd /var/www
* git clone --depth 1 https://github.com/echoCTF/echoCTF.RED.git
* Create a database and import schema
```sh
mysqladmin create echoCTF
mysql echoCTF<./echoCTF.RED/schemas/echoCTF.sql
mysql echoCTF<./echoCTF.RED/schemas/echoCTF-routines.sql
mysql echoCTF<./echoCTF.RED/schemas/echoCTF-triggers.sql
mysql echoCTF<./echoCTF.RED/schemas/echoCTF-events.sql
```

* Copy the sample files and update the database name, database server, memcached and other relevant details.
```sh
cp echoCTF.RED/backend/config/cache-local.php echoCTF.RED/backend/config/cache.php
cp echoCTF.RED/backend/config/validationKey-local.php echoCTF.RED/backend/config/validationKey.php
cp echoCTF.RED/backend/config/db-sample.php echoCTF.RED/backend/config/db.php
cp echoCTF.RED/frontend/config/memcached-local.php echoCTF.RED/frontend/config/memcached.php
cp echoCTF.RED/frontend/config/validationKey-local.php echoCTF.RED/frontend/config/validationKey.php
cp echoCTF.RED/frontend/config/db-local.php echoCTF.RED/frontend/config/db.php
```

* Install required composer files
```sh
cd echoCTF.RED/backend
composer install
cd -
cd echoCTF.RED/frontend
composer install
```

* Import the initial data (countries, avatars, experience etc)
```
./echoCTF.RED/backend/yii init_data --interactive=0
```

* Install the needed migrations
```
./echoCTF.RED/backend/yii migrate --interactive=0
```

* The migrations for the live platform at https://echoCTF.RED are stored here. You don't need to run this as it will most likely fail.
```
./echoCTF.RED/backend/yii migrate-red --interactive=0
```

* Create assets folders and make sure they are writable by your webserver
```sh
mkdir -p backend/web/assets frontend/web/assets
chown www-data backend/web/assets frontend/web/assets
```

* Ensure runtime folder on both backend and fronend are also writable
```sh
chown www-data backend/runtime frontend/runtime
```

* Ensure your web server configuration for the frontend points to `echoCTF.RED/frontend/web`
* Ensure your web server configuration for the backend points to `echoCTF.RED/backend/web`
 - for apache you can create `/etc/apache2/sites-available/echoCTF.RED.conf` with the following entries
```
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    ServerName echoCTF.RED
    ServerAlias www.your_domain
    DocumentRoot /var/www/echoCTF.RED/frontend/web/
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
    DirectoryIndex index.php
</VirtualHost>
<VirtualHost *:80>
    ServerName backend.echoCTF.RED
    DocumentRoot "/var/www/echoCTF.RED/backend/web/"
    DirectoryIndex index.php
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

 - enable mod_rewrite and add the following under `backend/web/.htaccess` and `frontend/web/.htaccess`
```
RewriteEngine on
# If a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# Otherwise forward it to index.php
RewriteRule . index.php
```
