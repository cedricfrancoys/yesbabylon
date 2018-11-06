# Wordpress docker configuration 

Tested under 

* Ubuntu 16.04, Docker 17.12.1-ce
* Ubuntu 18.04, Docker 17.12.1-ce



## Image config

### build.sh
```
#!/bin/bash
# build a new container in current directory, using host mysql and apache UID and GUI
docker build -t docked-wp --build-arg apache_gid=`cut -d: -f3 < <(getent group www-data)` --build-arg apache_uid=`id -u www-data` .
```

### Dockerfile
```
FROM wordpress:4.9.1
LABEL   Description="Latest version of Wordpress with Apache UID and GUID injection" \
        Maintainer="Cedric Francoys <cedricfrancoys@gmail.com>" \
        License="Apache License 2.0" \
        Version="1.0"

ARG apache_uid
ARG apache_gid

RUN /usr/sbin/usermod -u $apache_uid www-data && /usr/sbin/groupmod -g $apache_gid www-data
```
## Home directory

### .env
```
DOMAIN_NAME=example.com
DOMAIN_CONTACT=contact@example.com
MYSQL_ROOT_PASSWORD=rootdbpassword
MYSQL_DATABASE=dbname
MYSQL_USER=dbuser
MYSQL_PASSWORD=nontrivialpassword

```

### docker-compose.yml
```
version: '3.3'

# This file expects some .env file (within same dir) defining the following vars:
# DOMAIN_NAME
# DOMAIN_CONTACT
# MYSQL_ROOT_PASSWORD
# MYSQL_DATABASE
# MYSQL_USER
# MYSQL_PASSWORD


# We define all services and dependencies under the 'services' section, so that all related containers run within the same stack
services:
   db:
     container_name: sql.${DOMAIN_NAME}
     image: mysql:5.7
     volumes:
       - db_data:/var/lib/mysql
     restart: always
     networks:
       - proxynet
     environment:
       - MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
       - MYSQL_DATABASE=${MYSQL_DATABASE}
       - MYSQL_USER=${MYSQL_USER}
       - MYSQL_PASSWORD=${MYSQL_PASSWORD}
   wordpress:
     container_name: ${DOMAIN_NAME}
     depends_on:
       - db
     image: docked-wp:latest
     volumes:
       - /home/${DOMAIN_NAME}/www:/var/www/html
     restart: always
     networks:
       - proxynet
     environment:
       - WORDPRESS_DB_NAME=${MYSQL_DATABASE}
       - WORDPRESS_DB_HOST=db:3306
       - WORDPRESS_DB_USER=${MYSQL_USER}
       - WORDPRESS_DB_PASSWORD=${MYSQL_PASSWORD}
       - HTTPS_METHOD=noredirect
       - VIRTUAL_PORT=80
       - VIRTUAL_HOST=${DOMAIN_NAME}
       - LETSENCRYPT_HOST=${DOMAIN_NAME}
       - LETSENCRYPT_EMAIL=${DOMAIN_CONTACT}


# To expose the services, we use the 'proxynet' which contains a nginx reverse proxy.
# Only services having a VIRTUAL_HOST environment variable set will be accessible.
networks:
  proxynet:
    external: true

volumes:
  db_data:
```



### Home directory structure

./
```
drwxrwxr-x (775)    www-data:www-data     www
```

