# Mode maintenance
Pour passer un site sous un mode de maintenance (rediriger toutes les pages vers une page statique annoncant l'indisponibilité temporaire du site), on intervient au niveau de la résolution revers-proxy, avec la stratégie suivante:


Afin d'éviter une indexation incorrecte et d'impacter négativement le SEO, les pages renvoient un status code  [503: Service Unavailable](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.5.4).  

Un fichier statique standard `maintenance.html` est placé dans le dossier web de nginx (par défaut `/usr/share/nginx/html/`).

Dans le fichier de configuraiton nginx du site concerné, on ajoute les lignes suivantes :

```
server {
    ...
    error_page 503 @maintenance;
    location @maintenance {
        rewrite ^(.*)$ /maintenance.html break;
    }
    ...
}
```

Dans la partie `location` principale, on ajoute un condition pour vérifier la précence d'un fichier sémaphore optionnel:
```
server {
    ...
    location / {
        if (-f $document_root/imoov.solutions/maintenance) {
            return 503;
        }
    }
    ...
}
```

Pour chaque site, on crée un dossier correspondant à son FQDN comme sous-dossier du répertoire web nginx (Par défaut `/usr/share/nginx/html`). 

Exemple: `/usr/share/nginx/html/imoov.solutions/`

Pour alterner le mode d'un site, il suffit respectivement de créer ou de supprimer le fichier `maintenance`associé.

Exemples:
```
# put site under maintenance
echo 1 > /usr/share/nginx/html/imoov.solutions/maintenance
```

```
# put site back into production
rm /usr/share/nginx/html/imoov.solutions/maintenance
```