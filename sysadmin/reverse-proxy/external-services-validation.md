Certains services (comme Google) nécessitent de pouvoir valider la prorpiété d'un nom de domaine en permettant d'accéder à un fichier statique sur l'adresse principale.

Ceci peut être géré en ajoutant une première directive location avec une condition suffisamment ciblée pour intervepter les requêtes concernées.

Exemple avec Google : 

```
server {
    # gestion des requêtes HTTPS
    listen  10.10.2.253:443;
    ...

    location ~* /google(.*)\.html$ {
        # do not redirect and fallback to nginx static files
        # force a specific root ti serve the request
        root /var/www/html;
    }
    
    ...
    
```

Les fichiers statiques correspondants, doivent être placés dans le dossier renseigné par la directive root (défini par défaut dans `/etc/nginx/sites-enabled/default`)

```
    root /var/www/html;
```

