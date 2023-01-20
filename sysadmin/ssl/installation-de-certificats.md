# Reverse proxy et certificats SSL
Ce document décrit la généralisation de l'utilisation de nginx comme reverse-proxy ainsi que la mise en place de certificats SSL sur des instances VM.


## Génération de certificats temporaires
A des fins de test, on peut générer des certificats auto-signés en utilisant la commande openssl. Pour accéder au site, il faut alors ajouter une exception (car les certificat auto-signés sont considérés comme une menace potentielle).

Exemple:
```
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/fqdn.key -out /etc/ssl/private/fqdn.crt
```


## Stratégie pour la validation de certificats
Les certificats sont enregistrés pour les domaines racines (e.g. `example.com`).
Les éventuels sous-domaines sont soit gérés de manière identique (par exemple comme redirection) soit traités indépendamment lorsqu'il s'agit d'une application distincte (e.g. `eshop.example.com`).

La stratégie est donc d'intercepter les redirections faites de HTTP vers HTTPS et rediriger les requêtes contenant la chaîne "pki-validation" vers le dossier public nginx.


### Ajouter le fichier de validation
Créer l'arborescence et copier le fichier dans le dossier public de nginx (`./usr/share/nginx/html/`)
e.g.: `/usr/share/nginx/html/.well-known/pki-validation/835C241CB99E1743A432EA58B7C6F121.txt`


### Éditer la  config
Éditer le fichier config nginx visé (e.g. `/etc/nginx/sites-enabled/antipodes`).
Dans la configuration on ajoute une interception pour ne rediriger vers l'instance concernée que si la chaîne "pki-validation" est absente de l'URI. Dans le cas contraire (chaïne "pki-validation" présente), le serveur sert les fichiers hébergés par nginx (et non par le service vers lequel pointe les requêtes HTTPS).

Exemple simplifié:
```
server {
    listen  10.10.2.253:80;
    server_name antipodesvoyages.travel;
    if ($uri !~ "^(.*)/(pki-validation)(.*)"){
        # redirection vers sous le domaine www.
        return 301 http://www.antipodesvoyages.travel$request_uri;
    }
}
server {
    listen  10.10.2.253:80;
    server_name www.antipodesvoyages.travel;
    location / {
        # forward to instance
		# [...]
    }   
}
```
Note: le nom d'hôte est à adapter en fonction de la stratégie DNS pour les sous-domaines (redirection de www vers la racine ou redirection de la racine vers www).

### Vérifier la syntaxe de configuration nginx
```
/usr/sbin/nginx -t
```

### Recharger la configuration nginx
```
service nginx reload
```

## Interception HTTP 

L'idée est de pouvoir intercepter les requêtes HTTP et les rediriger vers le protocole HTTPS, tout en pouvant gérer des exceptions (nécessaire par exemple pour la validation de la propriété d'un domaine).  


Voici un exemple simplifié de configuration nginx utilisatn cette approche avec interception des requêtes HTTP vers la version HTTPS:

```
server {
    listen  10.10.2.253:80;
    server_name antipodesvoyages.travel www.antipodesvoyages.travel;
    return 301 https://antipodesvoyages.travel$request_uri;
}
server {
    listen  10.10.2.253:443;
    server_name antipodesvoyages.travel;
    location / {
        # [...] (forward to instance)
    }   
}
```

## Configuration SSL nginx

### Installation de certificats SSL
https://raymii.org/s/tutorials/Strong_SSL_Security_On_nginx.html

* <username>.key : private key (received along with CSR)
* <username>.cert : single SSL certificate 
* <username>.pem : chained certificates down to CA (`.ca-bundle` file recevied for ComodoCA certificates)

### Configuration nginx typique 

La configuration suivante permet d'obtenir un ranking **A+** sur Qualis SSL labs.

Create a **`dhparam.pem`** (once per server) (note: runs approx. 30 minutes)

```
    openssl dhparam -out dhparam.pem 4096
```

Contenu du fichier de configuration SSL global  **`/etc/nginx/ssl.conf`**
``` 
    ssl on;

    ssl_stapling on;
    ssl_stapling_verify on;

    ssl_session_timeout 5m;

    #ssl_protocols SSLv3 TLSv1;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;

    ssl_ciphers 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH';

    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;

    ssl_dhparam /etc/ssl/certs/dhparam.pem;
    add_header Strict-Transport-Security max-age=15768000; # six months
```

### Configuration finale client 

Typiquement, une configuration reverse-proxy nginx avec SSL ressemble à ceci : 

```
server {
    # interception des requêtes HTTP (port 80)...
    listen  10.10.2.253:80;
    # ... sur le domaine racine et sur le sous-domaine www
    server_name www.newconcepts.lu newconcepts.lu;
    # si la chaine "pki-validation" est absente de l'URI
    if ($uri !~ "^(.*)/(pki-validation)(.*)") {
        # redirection permanente vers le domaine racine en HTTPS
        return 301 https://newconcepts.lu$request_uri;
    }
    # else : exception 
    # pas de redirection (requête servie depuis /usr/share/nginx/html)
}

server {
	# gérer les requêtes HTTPS (port 443)...
    listen 10.10.2.253:443;
    # ... sur le domaine racine et sur le sous-domaine www
    server_name www.newconcepts.lu newconcepts.lu;

	# gérer les requêtes de type https://www.
    if ($host = 'www.newconcepts.lu' ) {
         rewrite  ^/(.*)$  https://newconcepts.lu/$1  permanent;
    }

    location / {
       # redirection vers un service specifique de l'instance visée
       # (e.g. odoo)
       proxy_pass http://10.22.0.253:8069;
       include /etc/nginx/proxy.conf;
    }
    # inclure la configuration SSL globale 
    include ssl.conf;
    
    # certificat spécifique au nom de domaine
    ssl_certificate /etc/ssl/private/antipodes.crt;
    
    # clé privée liée au certificat
    ssl_certificate_key /etc/ssl/private/antipodes.key;
    
    # chaîne complète des certificats (CA)
    ssl_trusted_certificate /etc/ssl/private/antipodes.pem;
}
```

### Vérifier la syntaxe de configuration nginx
```
/usr/sbin/nginx -t
```

### Recharger la configuration nginx
```
service nginx reload
```

## Vérification d'une installation de certifcat

### Quick check
https://www.digicert.com/help/

### Installation safety evaluation

Qualys SSL Labs

https://www.ssllabs.com/ssltest/

