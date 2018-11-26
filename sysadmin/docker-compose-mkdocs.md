# MkDocs docker configuration 

Tested under 

* Ubuntu 16.04, Docker 17.12.1-ce
* Ubuntu 18.04, Docker 17.12.1-ce



## Image config

### build.sh
```
#!/bin/bash
# build a new container in current directory, using host mysql and apache UID and GUI
docker build -t docked-mkdocs --build-arg apache_gid=`cut -d: -f3 < <(getent group www-data)` --build-arg apache_uid=`id -u www-data` .
```

### Dockerfile
```
FROM polinux/mkdocs
LABEL Description="Latest version of MkDocs with Apache UID and GUID injection" \
        Maintainer="Cedric Francoys <cedricfrancoys@gmail.com>" \
        License="Apache License 2.0" \
        Version="1.0"

ARG apache_uid
ARG apache_gid
```
## Home directory

### .env
```
DOMAIN_NAME=doc.example.com
DOMAIN_CONTACT=contact@example.com
```

### docker-compose.yml
```
version: '3.3'

# This file expects some .env file (within same dir) defining the following vars:
#   DOMAIN_NAME
#   DOMAIN_CONTACT

# We define all services and dependencies under the 'services' section, so that all related containers run within the same stack
services:
   docs:
     container_name: ${DOMAIN_NAME}
     image: docked-mkdocs
     restart: always
     networks:
       - proxynet
     ports:
       # it is necessary to use distinct ports if we run several mkdocs instances
       - "8001:8000"
     volumes:
       - /home/${DOMAIN_NAME}/doc:/workdir
     environment:
       - VIRTUAL_HOST=${DOMAIN_NAME}
       - HTTPS_METHOD=noredirect
       - LETSENCRYPT_HOST=${DOMAIN_NAME}
       - LETSENCRYPT_EMAIL=${DOMAIN_CONTACT}

# To expose the services, we use the 'proxynet' which contains a nginx reverse proxy.
# Only services having a VIRTUAL_HOST environment variable set will be accessible.
networks:
  proxynet:
    external: true
```



### Home directory structure

./
```
drwxr-xr-x (755)    doc.example.com:www-data     doc
```

./doc
```
lrwxrwxrwx (777) root:root    docs -> ./mkdocs/docs/
drwxr-xr-x (755) doc.yesbabylon.com:www-data    mkdocs
lrwxrwxrwx (777) root:root    mkdocs.yml -> ./mkdocs/mkdocs.yml
```

```
ln -s ./mkdocs/docs docs
```
```
ln -s ./mkdocs/mkdocs.yml mkdocs.yml
```

### mkdocs.yml

```
site_name: Example Docs
site_url: https://doc.example.com/
docs_dir: docs
dev_addr: doc.example.com
theme: readthedocs

```

### git init
```
rm -rf docs
git clone https://github.com/<username>/<repository> docs
```