# Server administration

## Specific tasks

### Start docker service
```
systemctl start docker
```

### Start nginx-proxy and SSL companion
```
cd /home/nabu/docker/nginx-proxy/
sudo docker-compose up -d
```
### Launch console (portainer)
```
sudo /home/nabu/docker/console_start.sh
```

## Check containers status

## Stop or Restart an App 

## Create a new Template

## Access nginx config

Some config files and directories are maped and accessible from `/srv/docker/nginx/` (SSL certificates and htpasswd files)