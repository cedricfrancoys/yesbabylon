# Procédure de réservation services



Dans la plupart des cas, on utilisera l'adresse email interne assignée au client  (exemple: `betterfinance.eu@cedricfrancoys.be`)

Si un cloisonnement particulier est nécessaire pour le produit, alors on créée un compte email spécifique pour ce produit (en utilisant la procédure décrite dans la partie 'nouveau client').

Exemple:`eshop.imoov.solutions@cedricfrancoys.be`



## Réservation de services


### Hébergement

pré-requis :
* créer un compte OVH pour le client
* réserver les services

Pour un **nom de domaine**, la réservation se fait via le **compte OVH du client**.

Pour tous les autres services, la réservation se fait via **compte OVH interne** `cedricfrancoys` et le compte client est renseigné pour les contacts "administration" et "technique".

Lors de la souscription au(x) service(s),

* dans les champs  "**Contact > Propriétaire**", "**Contact > Administration**" et "**Contact > Technique**", renseigner le compte OVH du client

* dans les champs "**Contact > Facturation**", renseigner le compte OVH `cedricfrancoys`

De cette façon, il est possible à tout moment de :

1) donner l'accès à l'administration des services spécifiques au client (et à lui seul) à un opérateur tiers 
2) transférer l'intégralité de la gestion des services (propriété) au client



### Réservation espace de backup

Les espaces de backups pour les petits clients sont mutualisés par YB.





### Réservation Instance
La réservation doit se faire via le manager sous le **compte OVH interne** `yesbabylon`, dans l'interface des projets Public Cloud : 

* pour les comptes clients standard : projet YB Cloud

* Pour les gros comptes clients : 

  * menu projets > Créer un nouveau projet 
  * créditer de 10EUR avec le compte paypal@yesbabylon.com
  * Une fois le paiement effectué, modifier les identifiants **Administration** et **Technique** Public Cloud: via `Project Management > Contacts and Rights`
  
  



### New instance

#### Versionning

upon installation of git , remember to protect the `.git` directory from direct web access: 

```bash
chmod -R o-rx .git
```

