# Accounts strategy
This document is an overview of how we intend to manage accounts in a way that allows easy mapping with domain names and contracted services.

## Accounts = Apps
We host Apps.

Each App uses its own image based on a template which, in turns, relies on a standard image from docker repositories.

We regularly define our own versions of commonly used images for typical environments (LAMP generic, Wordpress, MkDocs, ...)

Custom images are stored under: `/home/nabu/docker/images`  
(These images definition consist of a `Dockerfile`, along with a `build.sh` script - which mainly maps local www-data UID/GID with the VM's one.)


For ach custom image, a project template is stored at `/home/nabu/docker/images/{image-name}/template` and consists of a `docker-compose.yml` file, and a `.env` file (for holding config specifics). Typically, this cusomisation consists of mapping one or more local UID/GID (e.g. www-data) with the machine ones.

## Templates
For each commonly use typical software stacks we define a template under `/home/nabu/docker/templates`, which is a example configuation consisting in a `docker-compose.yml`file and a `.env` file.    

Examples of templates:
* Wordpress stack (using apache, PHP, Wordpress and MySQL or MariaDB) 
* Odoo stack (using nginx, Python, Odoo and PostgreSQL)

## Directory structure
* custom images: `/home/nabu/docker/images`
* structure template: `/home/nabu/docker/images/{image-name}/template
* nightly backups (to be exported to an archive disk): `/home/nabu/backups`
* home content (`/home/domain.ext`):
    * .env
	   * docker-compose.yml
	   * backup (dir)
	   * doc (dir)
	   * www (dir)

## New acccount configuration
Steps are defined in the [New account section](new-account.md)

A typical docker-compose file will define a stack containing minium:
1) a main app container
2) a container dedicated to documentation
3) some additional services (such as mysql/mariaDB)

## Entry points (virtual hosts)
Here are the DNS entries we need to expose:

1. Full qualified domain name of the App (e.g. myapp.example.com), used for:
	* DNS resolution
	* SSL certificate
	* FTP login
	* Virtual Host resolution (reverse proxy)

2. Entry points for other typical services (non-exhaustive):
	* ftp
	* sql
	* doc

Each App account is named after a virtual host that can be resolved by the inner nginx reverse proxy.
In addition, a LETS_ENCRYPT_HOST might be defined if SSL is required.

Notes about SSL: in order to be operational, a website running under SSL need to be attached to a valid DNS entry (accessible through Internet, to any client).



## Backups
### Backup data from a mysql container
```
docker exec CONTAINER /usr/bin/mysqldump -u root --password=root DATABASE > backup.sql
```
### Restore data to a mysql container
```
cat backup.sql | docker exec -i CONTAINER /usr/bin/mysql -u root --password=root DATABASE
```