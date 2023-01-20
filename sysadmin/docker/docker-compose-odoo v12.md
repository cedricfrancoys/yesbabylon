## docker-compose
Fichier de configuration docker-compose 3.3

### Créer un utilisateur odoo

```
adduser --no-create-home --disabled-login odoo
```

## Image config

### build.sh

```
#!/bin/bash
# build a new container in current directory, using host odoo UID and GUI
docker build -t docked-odoo --build-arg odoo_gid=`cut -d: -f3 < <(getent group odoo)` --build-arg odoo_uid=`id -u odoo` .
```

### Dockerfile

```
FROM odoo:12.0
LABEL   Description="Latest version of Odoo with odoo UID and GUID injection" \
        Maintainer="Cedric Francoys <cedricfrancoys@gmail.com>" \
        License="Apache License 2.0" \
        Version="1.0"

ARG odoo_uid
ARG odoo_gid

USER root
RUN apt-get update && apt-get install -y \
    git \
    procps \
    netcat \
    vim \
    && /usr/sbin/usermod -u $odoo_uid odoo \
    && /usr/sbin/groupmod -g $odoo_gid odoo
ADD ./wait-for-postgres.sh /
RUN ["chmod", "+x", "/wait-for-postgres.sh"]
USER odoo
```



### wait-for-postgres.sh

```
#!/bin/sh
# wait-for-postgres.sh
set -e

host="$1"
shift
cmd="$@"

for count in {1..30}; do
      echo "Pinging mysql database attempt "${count}
      if  $(nc -z $host 5432) ; then
        >&2 echo "Postgres is up - executing command"
        exec $cmd
        break
      fi
      sleep 1
done

>&2 echo "Postgres is unavailable - stopping"
```

## Home directory

### docker-compose-first.yml

```
version: '3.3'

services:
  db:
    container_name: sql.${DOMAIN_NAME}
    image: postgres:10.5
    volumes:
      - db_data:/var/lib/postgresql/data
    environment:
      - POSTGRES_DB=${DB_NAME}
      - POSTGRES_PASSWORD=${DB_PASSWORD}
      - POSTGRES_USER=${DB_USER}
    networks:
      - proxynet
    restart: always

  odoo:
    container_name: ${DOMAIN_NAME}
    image: docked-odoo:latest
    depends_on:
      - db
    environment:
      - POSTGRES_PASSWORD=${DB_PASSWORD}
      - POSTGRES_USER=${DB_USER}
      - POSTGRES_DB=${DB_NAME}
      - DB_PORT_5432_TCP_ADDR=db
      - VIRTUAL_HOST=${DOMAIN_NAME}
      - VIRTUAL_PORT=8069
      - HTTPS_METHOD=noredirect
      - LETSENCRYPT_HOST=${DOMAIN_NAME}
      - LETSENCRYPT_EMAIL=${DOMAIN_CONTACT}
    expose:
      - '8069'
    networks:
      - proxynet
    ports:
      - "8069:8069"
    volumes:
      - ./www:/var/lib/odoo:rw
      - ./odoo:/etc/odoo
    command: ./wait-for-postgres.sh db odoo -i base --without-demo=all --stop-after-init --db_host=db -d ${DB_NAME} -r ${DB_USER} -w ${DB_PASSWORD}

networks:
  proxynet:
    external: true
volumes:
  db_data:
```

### docker-compose.yml
```
version: '3.3'

services:
  db:
    container_name: sql.${DOMAIN_NAME}
    image: postgres:10.5
    volumes:
      - db_data:/var/lib/postgresql/data
    environment:
      - POSTGRES_DB=${DB_NAME}
      - POSTGRES_PASSWORD=${DB_PASSWORD}
      - POSTGRES_USER=${DB_USER}
    networks:
      - proxynet
    restart: always

  odoo:
    container_name: ${DOMAIN_NAME}
    image: docked-odoo:latest
    depends_on:
      - db
    environment:
      - POSTGRES_PASSWORD=${DB_PASSWORD}
      - POSTGRES_USER=${DB_USER}
      - POSTGRES_DB=${DB_NAME}
      - DB_PORT_5432_TCP_ADDR=db
      - VIRTUAL_HOST=${DOMAIN_NAME}
      - VIRTUAL_PORT=8069
      - HTTPS_METHOD=noredirect
      - LETSENCRYPT_HOST=${DOMAIN_NAME}
      - LETSENCRYPT_EMAIL=${DOMAIN_CONTACT}
    expose:
      - '8069'
    networks:
      - proxynet
    ports:
      - "8069:8069"
    volumes:
      - ./www:/var/lib/odoo:rw
      - ./odoo:/etc/odoo
    restart: always

networks:
  proxynet:
    external: true
volumes:
  db_data:
```

### .env

Dans le même dossier, un fichier `.env` est attendu avec la syntaxe suivante :

```
DB_NAME=odoo
DB_USER=odoo
DB_PASSWORD=my_pasword
DOMAIN_NAME=example.com
DOMAIN_CONTACT=info@example.com
```

```
mkdir www
chown odoo:odoo www
```
### ./odoo/odoo.conf
```
[options]
addons_path=/etc/odoo/custom,/etc/odoo/enterprise
admin_passwd = admin123
data_dir = /var/lib/odoo
dbfilter = ^.*
xmlrpc_port = 8069
proxy_mode = True
```


## Initialisation

Attention, la version 12 de l'image docker de odoo démarre un process odoo qui n'initialise pas la base de données. Il est donc nécessaire de le faire lors du premier démarrage.

### Premier démarrage

```
docker-compose -f docker-compose-first.yml up -d
```

>  La liste des processus affiche : 
> ```
> /usr/bin/python3 /usr/bin/odoo -i base --without-demo=all --stop-after-init --db_host=db -d odoo -r odoo -w admin
> ```

Après initialisation, forcer les droits pour l'utilisateur odoo sur le dossier `www`
```
chown -R odoo:odoo www
```


### Démarrages suivants

```
docker-compose up -d
```


> Après le redémarrage, la liste des processus indique:
>
> ```
> /usr/bin/python3 /usr/bin/odoo --db_host db --db_port 5432 --db_user odoo --db_password admin
> ```






Exemple d'extraits des logs après le redémarrage : 

```
2018-11-02 13:14:03,733 1 INFO ? odoo: Odoo version 12.0-20181008
2018-11-02 13:14:03,734 1 INFO ? odoo: Using configuration file at /etc/odoo/odoo.conf
2018-11-02 13:14:03,734 1 INFO ? odoo: addons paths: ['/var/lib/odoo/.local/share/Odoo/addons/12.0', '/usr/lib/python3/dist-packages/odoo/addons']
2018-11-02 13:14:03,735 1 INFO ? odoo: database: odoo@db:5432
2018-11-02 13:14:03,864 1 INFO ? odoo.addons.base.models.ir_actions_report: Will use the Wkhtmltopdf binary at /usr/local/bin/wkhtmltopdf
2018-11-02 13:14:03,992 1 INFO ? odoo.service.server: HTTP service (werkzeug) running on 267fb2aea9d8:8069
2018-11-02 13:14:18,282 1 INFO ? odoo.http: HTTP Configuring static files
2018-11-02 13:14:18,318 1 INFO odoo odoo.modules.loading: loading 1 modules...
2018-11-02 13:14:18,362 1 INFO odoo odoo.modules.loading: 1 modules loaded in 0.04s, 0 queries
2018-11-02 13:14:18,393 1 INFO odoo odoo.modules.loading: loading 26 modules...

```

