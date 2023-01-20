# Configuration IP fail-over



## Ajout d'une interface réseau

### Configuration sous Ubuntu 18.04 et 20.04


Configuration à appliquer sur un couple de serveurs configurés en miroir.

Dans le dossier `/etc/netplan/`, créer un fichier `51-failover.yaml`

```
network:
  version: 2
  vlans:
    veth0:
      id: 0
      link: ens3
      dhcp4: no
      addresses: [178.32.40.18/24]
```

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



### Configuration sous Ubuntu 16.04

Dans le dossier `/etc/network/interfaces.d`, créer un nouveau fichier `51-failover.cfg` :

```
auto ens3:1
iface ens3:1 inet static
    address 178.32.40.18
    netmask 255.255.255.255
```
Redémarrer le service réseau : 
```
/etc/init.d/networking restart
```



## Limitation d'accès en HTTP/HTTPS

Afin de limiter les accès au protocole HTTP(S) sur l'IP failover, qui est une IP publique (seule référencée par la configuration DNS externe), utiliser les IP tables pour bloquer le trafic en fonction de l'adresse IP de destination : 
```
iptables -I INPUT -d 178.32.40.18 -j DROP
iptables -A INPUT -d 178.32.40.18 -p tcp --dport 80 -j ACCEPT
iptables -A INPUT -d 178.32.40.18 -p tcp --dport 443 -j ACCEPT
```

(pour voir les IPTABLES actives : `sudo iptables -S`)
