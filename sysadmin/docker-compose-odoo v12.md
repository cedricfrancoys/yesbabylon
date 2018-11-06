## docker-compose
Fichier de configuration docker-compose 3.3



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
    image: odoo:12.0
    depends_on:
      - db
    environment:
      - POSTGRES_PASSWORD=${DB_PASSWORD}
      - POSTGRES_USER=${DB_USER}
      - POSTGRES_DB=${DB_NAME}
      - DB_PORT_5432_TCP_ADDR=db
      - VIRTUAL_HOST=${DOMAIN_NAME}
      - VIRTUAL_PORT=8069
      # - HTTPS_METHOD=noredirect
      - LETSENCRYPT_HOST=${DOMAIN_NAME}
      - LETSENCRYPT_EMAIL=${DOMAIN_CONTACT}
    expose:
      - '8069'
    networks:
      - proxynet
    ports:
      - "8069:8069"
    volumes:
      - odoo_data:/var/lib/odoo
      - ./addons:/mnt/extra-addons
      - ./config:/etc/odoo
    # following line must be uncommented after first start
    # restart: always
    # following line must be commented after first start
    command: odoo -i base --without-demo=all --stop-after-init --db_host=db -d ${DB_NAME} -r ${DB_USER} -w ${DB_PASSWORD}

networks:
  proxynet:
    external: true
volumes:
  db_data:
  odoo_data:
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


## Initialisation

Attenation, la version 12 de l'image docker de odoo n'initialise pas la base de données. Il est donc nécessaire de le faire "manuellement" (en deux temps).



* Il peut être nécessaire de lancer plusieurs fois la commande `docker-compose up -d`, car l'instance postgresql peut n'être pas immédiatement disponible: vérifier le statut de l'instance via `docker ps` et relancer `docker-compose` si nécessaire.

* Au premier démarrage via `docker-compose up -d`, la liste des processes affiche : 

`/usr/bin/python3 /usr/bin/odoo -i base --without-demo=all --stop-after-init --db_host=db -d odoo -r odoo -w admin`

Une fois initialisée, l'instance est stoppée automatiquement.



* Editer le fichier `docker-compose.yml` et commenter l'instruction `command:`
* Redémarrer via `docker-compose up -d`

Après le redémarrage, la liste des processus indique:

`/usr/bin/python3 /usr/bin/odoo --db_host db --db_port 5432 --db_user odoo --db_password admin`

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

