# Configurer nginx avec HTTP Basic Authentication

L'objectif de cette configuration est (par exemple) d'empêcher les robots des moteurs de recherche (qui ignoreraient le fichier `robots.txt`) d'indexer un site en développement ou en preview.

### Installer Apache2 utilities

```
apt-get update
apt-get install apache2-utils
```

### Créer un utilisateur 'admin'
```
sudo htpasswd -c /etc/nginx/htpasswd admin
```
La sécurité n'est pas la fonction de cette identification (celle-ci est gérée par la couche suivante, par exemple odoo) donc, par convention, on renseigne 'admin' comme login et mot de passe.


### Vérifier la configuration htpasswd
```
cat /etc/nginx/htpasswd
```
La sortie doit ressembler à quelque chose comme ceci: 
```
admin:$apr1$/woC1jnP$KAh0SsVn5qeSMjTtn0E9Q0
```

### Dans la configuration nginx

Modifier la configuraiton du site concerné (typiquement dans `/etc/nginx/sites-enabled`) et ajouter la configuration auth.
```
server {
    ...
    auth_basic           "restricted area";
    auth_basic_user_file /etc/nginx/htpasswd;
    ...
}
```

### Redémarrer nginx
```
sudo service nginx restart
```