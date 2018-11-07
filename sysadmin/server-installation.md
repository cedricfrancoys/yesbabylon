# Host server setup for virtualization with Docker 


## Server config
Ubuntu 18.04 LTS (GNU/Linux 4.15.0-22-generic x86_64)  
(note: PHP 7.2 is included by default in Ubuntu’s repositories since version 18.04.)

### Create Admin account
```
adduser nabu
usermod -aG sudo nabu
```

set default password as `admin`

### Allow usernames with dot in it
Edit adduser.conf config file so we can use domains as user names:  
`sudo vi /etc/adduser.conf`  

Locate the line with:  
`NAME_REGEX=...`  

and replace it with:  
`NAME_REGEX="^[a-z][-.a-z0-9]*$"`

### Update aptitude cache and allow aptitude to use HTTPS
```
sudo apt-get update && apt-get install -y apt-transport-https
```

### Mandatory accounts
Server must have `www-data` groups and users: make sure related accounts are defined on host.

(Those are not set as system to minimize risk of conflict with existing uid and gid inside docker images, UID and GID should be above 1000)

Check www-data GID and UID:  
```
cat /etc/group | grep www-data
cat /etc/passwd | grep www-data
```

If necessary, create accounts with those names:  
```
sudo addgroup www-data  
sudo adduser --no-create-home --disabled-login www-data  
sudo adduser www-data www-data  
```


### Install apache2-utils
```
apt-get install apache2-utils
```

### Install GIT
For repositories synch, we'll need git software.
```
sudo apt-get install git
```

### Install FTP service
```
apt-get install vsftpd
mv /etc/vsftpd.conf /etc/vsftpd.conf_orig
vi /etc/vsftpd.conf
```

Locate and adpat lines:
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

Restart FTP service

```
sudo systemctl restart vsftpd
```


### Install docker

```
sudo apt install docker.io
sudo systemctl start docker
sudo systemctl enable docker
```

### Install docker-compose
see https://github.com/docker/compose/releases
```
sudo curl -L https://github.com/docker/compose/releases/download/1.21.2/docker-compose-$(uname -s)-$(uname -m) -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
```

Create the directory `/home/nabu/docker`

```
mkdir /home/nabu/docker
```



### Install nginx reverse-proxy

Create the directory `/home/nabu/docker/nginx-proxy`

```
mkdir /home/nabu/docker/nginx-proxy
```



1) create a dedicated proxy network
`sudo docker network create proxynet`

2) run listener (nginx-proxy) with SSL certificates companion
We use nginx as reverse proxy, to match HTTP requests addressed to a given domain to related virtual server.

SSL
https://tech.acseo.co/sites-https-docker-nginx-lets-encrypt/
https://tevinjeffrey.me/how-to-setup-nginx-proxy-and-lets-encrypt-with-docker/


Edit `/home/nabu/docker/nginx-proxy/docker-compose.yml`

```
version: '2.1'

services:
  nginx-proxy:
    container_name: nginx-proxy
    restart: always
    image: jwilder/nginx-proxy
    networks: 
      - proxynet
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /srv/docker/nginx/certs:/etc/nginx/certs:ro
      - /srv/docker/nginx/conf.d:/etc/nginx/conf.d:rw
      - /srv/docker/nginx/htpasswd:/etc/nginx/htpasswd
      - /srv/docker/nginx/vhost.d:/etc/nginx/vhost.d
      - /srv/docker/nginx/html:/usr/share/nginx/html
      - /var/run/docker.sock:/tmp/docker.sock:ro

  nginx-proxy-companion:
    container_name: letsencrypt-companion
    image: jrcs/letsencrypt-nginx-proxy-companion
    networks: 
      - proxynet      
    volumes:
      - /srv/docker/nginx/certs:/etc/nginx/certs:rw
      - /var/run/docker.sock:/var/run/docker.sock
    volumes_from:
      - nginx-proxy

networks:
  proxynet:
    external: true
```



Inside `/home/nabu/docker/nginx-proxy`, run reverse-proxy stack : 

```
docker-compose up -d
```



Create the directory `/srv/docker/nginx/htpasswd`
```
mkdir /srv/docker/nginx/htpasswd
```

> **Note :** once some virtualhosts have been configured, in case nginx-proxy and companion have to be restarted, it is necessary to remove `/srv/docker/nginx/conf.d/default.d`

### Running containers console

We use portainer.io as console to see current state of existing containers.

Note: Portainer does not allow to associate containers with specific configuration (it is a simple UI for docker commands), so portainer is only to be used as visualisation dashboard and for cleaning purposes.
Configuring and running containers has to be done via dedicated scripts (see below).

`docker volume create portainer_data`


The script `/home/nabu/docker/console_start.sh` allows to (re)start the portainer container:
```
#!/bin/bash
sudo docker stop portainer && sudo docker rm portainer
sudo docker run --name portainer -d -p 9000:9000 -v /var/run/docker.sock:/var/run/docker.sock -v portainer_data:/data portainer/portainer 
```
```
chmod +x /home/nabu/docker/console_start.sh
```
```
/home/nabu/docker/console_start.sh
```


http://localhost:9000/

### Configure script for creating user accounts

create a directory `/home/nabu/accounts`
```
mkdir /home/nabu/accounts
```

create a `init.sh` script

```
#!/bin/bash

if [ -f .env ]
then
    # export vars from .env file
    set -a
    . ./.env

    if [ -z "$USERNAME" ]
    then 
        echo "A file named .env is expected and should contain following vars definition:"
        echo "USERNAME={domain-name-as-user-name}"
        echo "PASSWORD={user-password}"        
    else

        # create a new user
        adduser --force-badname --disabled-password --gecos ",,," $USERNAME
        echo "$USERNAME:$PASSWORD" | sudo chpasswd
        
        # add new user to www-data group
        adduser $USERNAME www-data

        # set the home directory of the new user
        mkdir /home/$USERNAME/www
        sudo usermod -d /home/$USERNAME/www $USERNAME
        # assign ownership to user and www-data (group)
        chown $USERNAME:www-data /home/$USERNAME/www
        
        # add write permission to www-data over the www directory of the user        
        chmod g+w -R /home/$USERNAME/www

        # restart SFTP service (to enable ftp login at user home)
        sudo systemctl restart vsftpd
        
        # stop auto export
        set +a
    fi
else
    echo ".env file is missing"
fi
```



```
chmod +x /home/nabu/accounts/init.sh
```



## Create a new account

Under directory `/home/docker/accounts`, edit `.env` file with following format: 

```
USERNAME=www.example.com
PASSWORD=my_password
```

Note: remember, we use FQDN as login/identifier for the projects.






## FTP
https://www.digitalocean.com/community/tutorials/how-to-set-up-vsftpd-for-a-user-s-directory-on-ubuntu-16-04






## QA

Website response time

time wget http://blog.domain.com/ -q --output-document=/dev/null
