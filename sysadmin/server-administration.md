# Server administration

## System tasks

### Check active connexions
`sudo netstat -plutn`

### Check listening ports
`sudo netstat -vatn`

## Docker tasks

### Start docker service
```
systemctl start docker
```
### Restart docker service
```
service docker restart 
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


## Run a command within a Container

Examples : 
```
docker exec api.wharn.com /bin/bash -c "php run.php --do=private:wharn_pin_status-check"
docker exec api.wharn.com /bin/bash -c "php run.php --do=private:wharn_spool_send"
```

## Create a new Template

## Access nginx config

Some config files and directories are maped and accessible from `/srv/docker/nginx/` (SSL certificates and htpasswd files)



## Common errors and resolution

### 504 Gateway Time-out
This message is related to nginx config
Timeout can be increased by adding following lines to  file `/srv/docker/nginx/conf.d/custom.conf`:
```
proxy_connect_timeout       600;
proxy_send_timeout          600;
proxy_read_timeout          600;
send_timeout                600;
```
