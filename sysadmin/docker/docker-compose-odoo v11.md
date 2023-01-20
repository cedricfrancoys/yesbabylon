
Fichier de configuration docker-compose 3.3 : `docker-compose.yml`

```
version: '3.3'

services:
  odoo:
    container_name: ${DOMAIN_NAME}
    image: odoo:11.0
    depends_on:
      - odoo_db
    environment:
      - DB_ENV_POSTGRES_PASSWORD=${DB_PASSWORD}
      - DB_ENV_POSTGRES_USER=${DB_USER}
      - DB_PORT_5432_TCP_ADDR=odoo_db
      - DB_PORT_5432_TCP_PORT=7080
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
    restart: always

  odoo_db:
    command: -p 7080
    container_name: sql.${DOMAIN_NAME}
    image: postgres:10.5
    volumes:
      - db_data:/var/lib/postgresql/data
    environment:
      - PGPORT=7080
      - POSTGRES_DB=odoo
      - POSTGRES_PASSWORD=${DB_PASSWORD}
      - POSTGRES_USER=${DB_USER}
    networks:
      - proxynet
    ports:
      - "7080:7080"
    restart: always

networks:
  proxynet:
    external: true
volumes:
  db_data:
  odoo_data:
```



Dans le même dossier, un fichier `.env` est attendu avec la syntaxe suivante :

```
DB_USER=odoo
DB_PASSWORD=my_pasword
DOMAIN_NAME=example.com
DOMAIN_CONTACT=info@example.com
```