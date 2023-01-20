

### Générer un dump sql de la base de données:
Exemple:
```
su postgres
pg_dump antipodesnineodoo > dump.sql
```
### Compresser l'archive
```
tar -zcvf dump.tar.gz dumpl.sql
```
### Transférer et décompresser l'archive
Via ftp/sftp

### Dans pgadmin
1. créer un nouvelle base de données (e.g. antipodesnineodoo)
2. créer un utilisateur odoo 
```
LoginRoles > New Login Role... 
 Role name: odoo
 Role provilege : superuser
```
3. ouvrir la console 
Sélectionner la nouvelle DB (e.g. antipodesnineodoo) 
Aller dans Plugins / PSQL console
Utiliser \i pour injecter du SQL 
Exemple:
```
\i C:/Users/User/Downloads/dump.tar/dump/dump.sql
```