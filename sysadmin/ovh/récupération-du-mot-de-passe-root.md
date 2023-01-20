# Récupération du mot de passe root sur un serveur dédié

Ce document présente les étapes pour la modification du mot de passe root sur un serveur dédié OVH (identique aux serveurs Kimsufi et SoYouStart).

Complément d'infos sur:  
https://www.tech2tech.fr/serveur-kimsufi-mot-de-passe-root-perdu/

## Créer un trousseau SSH
Utiliser puttygen : créer une clé / sauvegarder clés publique (.rsa) et privée (.ppk)

Dans le manager (menu <nom d'utilisateur>):
* Ajouter la clé publique dans <user> / clés SSH
* Marquer la clé publique comme clé par défaut (celle à charger pour le mode rescue)

Le format attendu est de type:

```
ssh-rsa AAAAB3NzaC1yc2EAAAABJQAAAgEAmlt5vQZPn6dNuRqyPqZlMFwYTqEzhNbPK3ZrcBXrk8X3Q7+GTM2S7rg0+6qtnLPBZom/wWLhQJo2TiIGvNDRZ3R9qNne9FDbTUyUxP7nDozdOHY0Dn6bqfL5n0UsZE78HcSQZSZAcHp+oeLeZjIwCMeU2vmk46hcNI5UeY+9Uz45HvwoVAYoaR7D/NPSLh4aJmP4ILaI17ReWtvvEvKNP0MFIMgnRSVY1R0d+2aW+lOuQ/kv/koVPTyTHU2q5lNG1+6kQej/vBzfU1DrUVWKZmPcXto1YytVhyEEw/0XYf1tmzRrwVN/x5zFaKne9puA5NX+ryXQ6L1bhDHNMOCS2XS+/iiCh1S0+eIelJsXOip1ZqtBAvVBBhZUBPRe+2uP1lnftfVBCZl6SWJz3DIHAJGKDg2Uj17rVn9DkuaOrRoJ3bQVBFMqMAQpcn+OxSmjmQpaF+jPHLmlEKmv4yxL5Okv+kXuoHgAIloCiaeV9K5eKOY0iuO5F3MFo0uVyWp2dabXT86sEKXOgKnKuqQsytVf7UM9ycwXX2gWMtrilYJSJ4QhBbnsyNWLKo/L3zpRYBgYm5UP4NSd+1FUTU3wYmWfLERNOh7qSNSMeAgdc4OFsVLQPMzMUqnZnCCLXB7hzX+jq5kBUXGA+KiXzoUxChqr73FAAxwqPkbRNPrEPp0= rsa-key-20180831
```

## Réinitialiser le mot de passe root

### Configurer le mode de reboot

* Aller dans `manager OVH > [serveur] > NetBoot > Rescue`  
option "rescue64-pro" (customer rescue system)

* Lancer le redémarrage

### Monter le FS
Lister les disques disponibles
```
    fdisk -l
```

#### FS direct (ext)
```
    mount /dev/sda1 /mnt/
```

#### FS en RAID

1. Vérifier le statut des arrays
```
    cat /proc/mdstat
```

(les disques commençant par md* sont des RAID)

**Si l'array n'est pas définie:**
* récupérer le UUID
```
    mdadm --examine /dev/sda1
```

* assembler les disques
```
    mdadm --assemble --uuid 81d950aa:214137d8:a4d2adc2:26fd5302 /dev/md1
```

2. monter le RAID /
```
    mount /dev/md1 /mnt/
```

3. monter le RAID /home

**S'il s'agit d'un volume logique:**
    * vgscan 
    * lvm vgchange -a y
    * fdisk -l
    * mount /dev/mapper/pve-data /mnt/home

```
    mount /dev/md5 /mnt/home
```

### Mode écriture
``` 
    chroot /mnt/
```

### Mettre à jour le mot de passe root
``` 
    passwd
```

### Vérifier le port SSH
Découvrir sur quel port le service SSH est configuré (directive `Port`)
```
    /etc/ssh/sshd_config
```

### Configuer le mode de reboot

* Aller dans `NetBoot > Disque dur`

* Lancer le redémarrage


## SSH keys removal
https://docs.ovh.com/fr/public-cloud/changer-sa-cle-ssh-en-cas-de-perte/	  

https://pve.proxmox.com/wiki/Duplicate_Virtual_Machines#How_to_change_the_SSH_keys

