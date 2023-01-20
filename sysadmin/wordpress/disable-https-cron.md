Lorsqu'une instance Wordpress est executée dans un container, le virtualhost externe est géré par nginx et peut utiliser un certificat SSL. De sorte que l'adresse finale du site est en HTTPS mais le service apache local peut être configuré uniquement en HTTP (i.e. le container ne gère pas de certificat SSL et est accessible uniquement via l'hote qui l'héberge).

Le mécanisme de CRON Wordpress utilise l'adresse finale du site (via la fonction `site_url()`) et, dans ce cas cURL peut être incapable d'accéder au script cron (ex. `https://www.example.com/wp-cron.php`), et retourner une erreur du type : 
`cURL error 7: Failed connect to example.com:443; Connection refused`

Ce problème peut être corrigé en forcant les requêtes émises par le CRON en HTTP, quelle que soit l'adresse finale du site en ajoutant un filtre dans un plugin dédié.

Exemple:

```
add_filter('cron_request', function ($request) {
	$request['url'] = str_replace('https://', 'http://', $request['url']);
	return $request;
});
```



