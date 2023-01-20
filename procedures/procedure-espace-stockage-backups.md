

Les backups sont réalisés en FTP depuis les instances client, vers un des serveurs backups mutualisés par YB.



Par convention, les **serveurs de backup** sont hébergés dans des datacenter européens non-français (Francfort, Varsovie) et les **instances clients** sont hébergées dans des datacenter en France (Gravelines, Strasbourg, Roubaix), afin d'éviter d'avoir une instance ET son backup dans le même data-center.



Etant donné que les blocs ne sont pas directement accessibles, ils sont montés sur des instances de backups via VLAN (non public). Les instances de backup sont uniquement accessibles en FTP.



## Configuration d'un serveur de backups



#### Utilisation d'une IP failover

(voir doc `procedure-installation-serveur.md`)


#### Réservation volume Block Storage

Le block-storage volume doit être localisé dans la même région que l'instance sur laquelle il est monté (càd régions hors-France).

https://docs.ovh.com/fr/public-cloud/creer-et-configurer-un-disque-supplementaire-sur-une-instance/

* créer un volume

* attacher le volume à une instance

* initialiser le volume
  * **Important** - utiliser des **partitions xfs** (pour pouvoir étendre facilement après augmentation de la capacité):

```
lsblk
sudo fdisk /dev/sdb
(n, p, 1, [default], [default], w)
lsblk
sudo mkfs.xfs /dev/sdb1
```

  * monter le volume

```
sudo mkdir /mnt/backups
sudo mount /dev/sdb1 /mnt/backups/
df -h
```

* update fstab (for auto-mount after reboot)

  check block ID 

  ```
  sudo blkid
  ```

  update fstab

  ```
  vim /etc/fstab
  ```
  Example
  ```
	UUID=2e4a9012-bf0e-41ef-bf9a-fbf350803ac5 /mnt/backups xfs nofail 0 0
	```



#### Augmentation du volume Block Storage

* démonter la partition: `umount /mnt/backups`
* dans l'interface Public Cloud étendre le bloc à la nouvelle taille (attendre la fin de l'opération)
* vérifier le nom du nouveau disque: `lsblk` (la nouvelle taille devrait être renseignée pour le disque, mais la taille de la partition est toujours l'ancienne)
* monter le nouveau disque: `mount /dev/sdb1 /mnt/backups`
* étendre la partition: `growpart /dev/sdb 1` (`lsblk` pour vérifier)
* allouer le nouvel espace: `xfs_growfs -d /dev/sdb1` (`df -h` pour vérifier)
* vérifier fstab

**Attention: dans certains cas le UUID du bloc peut être mis à jour. Il faut alors modifier FSTAB en conséquence !!**



#### Configuration vsftpd

```
listen=YES
listen_ipv6=NO
anonymous_enable=NO
local_enable=YES
write_enable=YES
local_umask=022
dirmessage_enable=YES
use_localtime=YES
xferlog_enable=YES
connect_from_port_20=YES
chroot_local_user=YES
secure_chroot_dir=/var/run/vsftpd/empty
pam_service_name=vsftpd
rsa_cert_file=/etc/ssl/certs/ssl-cert-snakeoil.pem
rsa_private_key_file=/etc/ssl/private/ssl-cert-snakeoil.key
ssl_enable=NO
pasv_enable=Yes
pasv_min_port=10000
pasv_max_port=10100
allow_writeable_chroot=YES
```



#### Script de création de nouveaux comptes utilisateurs

script `~/adduser.sh`

```
#!/bin/bash
  
if [ -z "$1" ]
then
    echo "Please provide an account name (Fully Qualified Domain Name)"
else
    USERNAME="$1"
    sudo adduser $USERNAME
    sudo mkdir /mnt/backups/$USERNAME
    sudo chown $USERNAME:$USERNAME /mnt/backups/$USERNAME
    sudo rm -rf /home/$USERNAME
    sudo ln -s /mnt/backups/$USERNAME /home/$USERNAME
    sudo service vsftpd restart
fi
```