Daas l'image, le fichier `/app/nginx.tmpl`ser t de template à la création des hotes virtuels.



des modifications pour la gestion des certificats et du comportement HTTPS_NOREDIRECT


limitations au niveau de la fréquence des requêtes accesptées : 
voir : https://www.nginx.com/blog/rate-limiting-nginx/

```
# define request limit zones

limit_req_zone $binary_remote_addr zone=by_ip:10m rate=12r/s;
limit_req_zone $request_uri zone=by_uri:10m rate=1r/s;

```

```

# apply rate limiting

# queue up to 100 requests by IP by second (enough for most pages)
limit_req zone=by_ip burst=100;
# queue up to 5 requests by URI by second (5 concurrent visitors)
limit_req zone=by_uri burst=5;

```

