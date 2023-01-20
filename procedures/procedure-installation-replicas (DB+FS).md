## Configuration VLAN


La création d'un VLAN implique l'activation d'un VRACK (https://docs.ovh.com/fr/public-cloud/public-cloud-vrack).

La création du VLAN se fait via l'interface OpenStack de OVH et doit être défini en fonction de sites (il faut choisir des sites dans lesquels se trouvent les instances).

Une configuration plus fine est possible via l'interface Horizon: https://horizon.cloud.ovh.net/project/instances/

Lorsqu'un réseau est crée, il faut attacher les instances à celui-ci .

Il est possible d'attacher une interface avec n'importe quelle adresse IP fixe qui correspond au masque réseau (ex. 10.0.0.0/16)

```
sudo apt install vlan
sudo modprobe 8021q
sudo su
echo "8021q" >> /etc/modules
exit
```

Voir les interfaces disponibles

```
ip link show
```

sudo vi /etc/netplan/60-private.yaml

```
network:
        version: 2
        renderer: networkd
        ethernets:
                ens4:
                        dhcp4: no
                        dhcp6: no
                        addresses: [10.2.3.211/16]
```

`ens4` a été attribué lorsque l'instance a été jointe au VLAN.



Vérifier la configuration 

```
netplan --debug generate 
```

Générer la nouvelle configuration

```
netplan generate
```

Appliquer la nouvelle configuration

```
netplan apply
```

## IPtables
Pour les instances qui ne doivent être accessibles que via le VLAN, il est nécessaire de bloquer les accès TCP (hors port 22) sur l'IP publique.
Docker utilise des chaines spécifiques qu'il faut également mettre à jour.

```
iptables -I INPUT -p tcp --dport 22 -j ACCEPT
iptables -A INPUT -j DROP
iptables -I DOCKER -d 172.18.0.2/32 -p tcp -m tcp --dport 443 -j DROP
iptables -I DOCKER -d 172.18.0.2/32 -p tcp -m tcp --dport 80 -j DROP
```

Note : pour la mainteance et autoriser l'accès à certains services (ex. phpmyadmin) :

```
iptables -D DOCKER -d 172.18.0.2/32 -p tcp -m tcp --dport 443 -j DROP
iptables -D DOCKER -d 172.18.0.2/32 -p tcp -m tcp --dport 80 -j DROP
```



## Réplication DB

La stratégie est d'utiliser une DB "master" en RW et une DB "slave" en RO (il faut que le framework le permette ou soit adapté : les requêtes en écriture doivent être répétées sur l'hote slave).

Du point de vue des instances locales, l'adresse IP du réseau est considérée comme un gateway.
On crée donc un réseau virtuel pour le masque réseau avec cette IP comme passerelle.

Création du réseau "priv_net", rattachée à l'interface `ens4`
```
docker network create -d macvlan --subnet=10.2.0.0/16 --gateway=10.2.3.211 -o parent=ens4 priv_net
```


Et on assigne une IP fixe virtuelle (correspondant au masque) au serveur SQL: 

```
services:
   db_7b48a:
     container_name: sql1.test.com
     image: mysql:5.7
     # allow packets up to 512 MB (for imports)
     # commented - should be included in the `mysql.cnf` below
     # command: --max_allowed_packet=536870912
     volumes:
       - db_data:/var/lib/mysql
       - ./mysql.cnf:/etc/mysql/conf.d/custom.cnf
     restart: always
     networks:
       priv_net:
         # assign a VLAN IP adress to SQL server (distinct from interface)
         ipv4_address: 10.2.3.212
       proxynet: {}
     ports:
       - "3306:3306"
     environment:
       - MYSQL_ROOT_PASSWORD=wordpress
       - MYSQL_DATABASE=wordpress
       - MYSQL_USER=wordpress
       - MYSQL_PASSWORD=wordpress
# To expose the services, we use the 'proxynet' which contains a nginx reverse proxy.
# Only services having a VIRTUAL_HOST environment variable set will be accessible.
networks:
  proxynet:
    external: true
  priv_net:
    external: true
volumes:
  db_data:
```

* proxynet permet les accès externes (WAN) via nginx
* priv_net permet les accès internes (VLAN) via le VPN






Sur la machine "master", les deux DB peuvent être utilisées (master en RW et salve en RO): 
```
   wordpress:
     container_name: ${DOMAIN_NAME}
     image: docked-wp:latest
     volumes:
       - /home/${DOMAIN_NAME}/www:/var/www/html
       # map local file for custom config (e.g. upload_max_filesize)
       - ./php.ini:/usr/local/etc/php/conf.d/docker-custom.ini
     restart: always
     networks:
       - proxynet
     environment:
       - WORDPRESS_DB_NAME=${MYSQL_DATABASE}
       - WORDPRESS_DB_USER=${MYSQL_USER}
       - WORDPRESS_DB_PASSWORD=${MYSQL_PASSWORD}
       # les deux lignes suivantes sont interchangeables
       # use local DB
       - WORDPRESS_DB_HOST=db_7b48a:3306
       # use master DB
       - WORDPRESS_DB_HOST=10.2.3.212:3306
```



## Création de Filestore NFS

Permettre la synchronisation permanente entre deux dossiers, accessibles uniquement par les instances concernées (via la VLAN).


```
sudo apt update
sudo apt install nfs-kernel-server
sudo apt install nfs-common
```

On the server  (10.2.0.1):
```
sudo mkdir /var/nfs/main -p
sudo chown nobody:nogroup /var/nfs/main
sudo vi /etc/exports
```
```
/var/nfs/main    10.2.0.0/16(rw,sync,no_root_squash,no_subtree_check)
```
```
sudo systemctl restart nfs-kernel-server
```


on the client  (10.2.0.2):

```
sudo mkdir -p /mnt/filestore
sudo mount 10.2.3.211:/var/nfs/main /mnt/filestore
```


mounting at boot:
```
sudo vi/etc/fstab
```

```bash
10.2.0.1:/var/nfs/main    /mnt/filestore   nfs auto,nofail,noatime,nolock,intr,tcp,actimeo=1800 0 0
```




## Synchronisation de Filestores

L'idée est que les dossiers contenant les données soient des liens vers les fichiers synchronisés. On veut que le l'instance slave continue de fonctionner en cas de défaillance de l'instance master.

Les opérations de montage et de synchronisation sont donc distinctes.



1) Sur hôte hébergeant l'instance "master", synchroniser le dossier partagé depuis les données de l'app:

```
$ rsync -av --delete /home/app/www /var/nfs/main
```

2) Sur hôte hébergeant l'instance "slave", synchroniser les données de l'app depuis le dossier partagé

```
$ rsync -av --delete /mnt/filestore /home/app/www
```