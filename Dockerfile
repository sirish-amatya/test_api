FROM php:7.0-apache
RUN docker-php-ext-install pdo_mysql

RUN a2enmod rewrite
# Update the default apache site with the config we created.

ADD custom.conf /etc/apache2/sites-enabled/000-default.conf
ADD Seeder.php /tmp/Seeder.php
ADD src/config/constants.php /tmp/constants.php
ADD sample-data.json /tmp/sample-data.json

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

