## SSH access

Par défaut, on utilise Pageant pour charger la clé SSH spécifiée pour l'instance dans OVH. Il faut faire une première connexion avec l'utilisateur "ubuntu".

S'il s'agit d'une instance Public Cloud, il est nécessaire de modifier le fichier de configuration du service SSH pour permettre l'identification par mot de passe (clé privée par défaut) et les connexions pour l'utilisateur `root`:

```
sudo vi /etc/ssh/sshd_config
```

1. Update line `PasswordAuthentication no` to `PasswordAuthentication yes`

2. A few lines below  `# Authentication:`, add (or uncomment): 

```
PermitRootLogin yes
```

Note Ubuntu 22.10 : Check `/etc/ssh/sshd_config.d` folder to make sure that no additional conf file overrides the global config.

3. Restart SSH service:

```
sudo service ssh restart
```

4. Create a password for the `root` user : 

```
sudo passwd root
```





## Install b2 

Each customer is assigned to a fully dedicated VPS. Resources of this VPS are then shared amongst customer's services.

In order to  setup the whole environment and related dependencies for taking advantage of a containerized strategy, we use a collection of scripts (intended for Ubuntu distribution) called B2 (**bîtû** is the akkadian word for "home").

1. Make sure to be logged as `root`

```
sudo -i
```

2. Check the Git version (required 2.17+) and, if necessary, update to the minimum requirement : 

``` bash
git --version
```


``` bash
sudo apt-get update
sudo apt-get install git
```

3. Download and install the B2 scripts collection : 

``` bash
git clone https://github.com/yesbabylon/b2.git
cd b2
./install.sh
```

4. At the end of the installation, a folder `/home/docker` should have been created.  Open the `/home/docker/accounts/.env` file to edit variables for account creation.

Required variables are:

* USERNAME: FQDN of the Web App that will be hosted by the VPS
* PASSWORD: a password that will provide SSH and FTP external access
* TEMPLATE: one of the frameworks supported by b2 (check up-to-date list [here](https://github.com/yesbabylon/b2/tree/master/docker/templates)). Note: If the required framework is missing, just use the one that is the closest (same language, same services).

Upon saving and closing, the install process resumes, creates a docker-compose file for the given account, and launches the required services (web services, security, monitoring, ...).

Note : check that fail2ban service is running `fail2ban-client status`.  If getting following message : 
```
Failed to access socket path: /var/run/fail2ban/fail2ban.sock. Is fail2ban running?
```
force start with:

`chmod -x /var/run/`
`systemctl start fail2ban`


## Configuration IP fail-over

### Ajout d'une interface réseau

#### Configuration sous Ubuntu 18.04, 20.04, 22.04

Configuration à appliquer sur un couple de serveurs configurés en miroir.

> Pour voir les interfaces réseau : `$ ip address show`

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

> Dans l'exemple `178.32.40.18` est l'IP failover

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

Note : en cas d'erreur de redémarrage du service, un reboot de l'instance peut être nécessaire (ubuntu 22.10)

A ce stade l'instance doit répondre aux PING depuis l'extérieur vers l'IP failover.

### Limitation d'accès en HTTP/HTTPS

Afin de limiter les accès INPUT au protocole HTTP(S) **sur l'IP failover**, qui est une IP publique (seule référencée par la configuration DNS externe), utiliser les IP tables pour bloquer le trafic en fonction de l'adresse IP de destination : 

```
iptables -I INPUT -d 178.32.40.18 -j DROP
iptables -A INPUT -d 178.32.40.18 -p tcp --dport 80 -j ACCEPT
iptables -A INPUT -d 178.32.40.18 -p tcp --dport 443 -j ACCEPT
```

>  Pour voir les IPTABLES actives : `sudo iptables -S`



Pour limiter les accès OUTPUT : 

* Si une installation n'a pas besoins d'accès internet sortant, une ligne DNS peut être ajoutée pour éviter les connexions nécessitant une résolution DNS : 
```
     networks:
       - proxynet
     dns: 0.0.0.0
```
(ceci ne bloque pas les connexions IP directes)

* Bloquer (temporairement) des attaques à partir d'un site vers une IP (il faut bien sur identifier l'attaque et la corriger):

Exemple :
`iptables -A OUTPUT -d 195.128.100.92 -j DROP`

Pour affiner il est possible :
a) de crééer un serveur DNS sur l'hote et de l'utiliser pour sélectionner les résolutions DNS valides ou non 
b) bloquer toutes connexions sortantes, sauf celles d'une whitelist (en fonction de l'App et des APIs auxquelles elle accède)