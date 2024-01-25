FROM webdevops/php-nginx:8.0-alpine

COPY . /var/www/html/api

WORKDIR /var/www/html

COPY vhost.conf /opt/docker/etc/nginx/vhost.conf

RUN chmod -R 777 /var/www/html/api/storage

EXPOSE 80

#docker build -t laravel .
#docker run --name laravel  -p 8083:80 -d laravel
#docker exec -it laravel /bin/bash
#docker stop laravel && docker rm laravel && docker rmi laravel
#webdevops/php:7.4
#webdevops/php:8.0
#webdevops/php:8.1
#webdevops/php:8.2
#webdevops/php-dev:7.4
#webdevops/php-dev:8.0
#webdevops/php-dev:8.1
#webdevops/php-dev:8.2
#webdevops/php-apache:7.4
#webdevops/php-apache:8.0
#webdevops/php-apache:8.1
#webdevops/php-apache:8.2
#webdevops/php-apache-dev:7.4
#webdevops/php-apache-dev:8.0
#webdevops/php-apache-dev:8.1
#webdevops/php-apache-dev:8.2
#webdevops/php-nginx:7.4
#webdevops/php-nginx:8.0
#webdevops/php-nginx:8.1
#webdevops/php-nginx:8.2
#webdevops/php-nginx-dev:7.4
#webdevops/php-nginx-dev:8.0
#webdevops/php-nginx-dev:8.1
#webdevops/php-nginx-dev:8.2

#webdevops/php:7.4-alpine
#webdevops/php:8.0-alpine
#webdevops/php:8.1-alpine
#webdevops/php:8.2-alpine
#webdevops/php-dev:7.4-alpine
#webdevops/php-dev:8.0-alpine
#webdevops/php-dev:8.1-alpine
#webdevops/php-dev:8.2-alpine
#webdevops/php-apache:7.4-alpine
#webdevops/php-apache:8.0-alpine
#webdevops/php-apache:8.1-alpine
#webdevops/php-apache:8.2-alpine
#webdevops/php-apache-dev:7.4-alpine
#webdevops/php-apache-dev:8.0-alpine
#webdevops/php-apache-dev:8.1-alpine
#webdevops/php-apache-dev:8.2-alpine
#webdevops/php-nginx:7.4-alpine
#webdevops/php-nginx:8.0-alpine
#webdevops/php-nginx:8.1-alpine
#webdevops/php-nginx:8.2-alpine
#webdevops/php-nginx-dev:7.4-alpine
#webdevops/php-nginx-dev:8.0-alpine
#webdevops/php-nginx-dev:8.1-alpine
#webdevops/php-nginx-dev:8.2-alpine
