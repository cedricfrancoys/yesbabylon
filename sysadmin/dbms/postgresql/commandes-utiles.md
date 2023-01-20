Lancer la ligne de commande postgreSQL

```
sudo -u postgres psql 
```

Lancer la ligne de commande postgreSQL sous un utilisateur spécifique:

```
sudo -u USERNAME psql DBNAME
```



Pour les instances Docker avec odoo : 

Lancer la console de l'instance `sql.<FQDN>` depuis Portainer

ou se connecter en ligne de commande : 

```
docker exec -ti <CTID> /bin/bash
```

```
psql -U odoo
```





To list existing databases:

- `\list` or `\l`: list all databases
- `\dt`: list all tables in the current database

You will never see tables in other databases, these tables aren't visible. You have to connect to the correct database to see its tables (and other objects).

To switch databases:

```sql
\connect database_name
```



Voir la liste des tables: 

```sql
SELECT * FROM pg_catalog.pg_tables;
```



Voir la liste des colonnes : 

```sql
SELECT *
FROM information_schema.columns
WHERE table_schema = 'your_schema'
  AND table_name   = 'your_table'
```



**Créer une nouvelle database**
```
create database <db_name>;
grant all privileges on database production to odoo;
alter database production owner to odoo;
```
**Exporter un backup de db**

```
sudo -u postgres pg_dump -c <db_name> > dump.sql
```



**Importer une db à partie d'un fichier .sql**

En ligne de commande : 

```
sudo -u postgres psql <database> < file.sql
```


Depuis la console pgadmin:

```
\i /path/to/file/dump.sql
```

**Mettre à jour le mot de passe d'un utilisateur**

```
ALTER USER user_name WITH PASSWORD 'new_password';
```



**Restauration des permissions dossier postgresql**
```
chmod 700 -R /var/lib/postgresql
chown -Rf postgres:postgres /var/lib/postgresql
```