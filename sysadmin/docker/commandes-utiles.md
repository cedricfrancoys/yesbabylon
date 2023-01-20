

### delete volumes

```
docker volume ls -qf dangling=true | xargs -r docker volume rm
```



### delete networks

```
docker network proxynet
```

### delete containers

```
docker rm $(docker ps -qa)
```

### delete images

```
docker rmi $(docker images -q)
```





### Start interactive console for container specified by hash
sudo docker exec -i -t 665b4a1e17b6 /bin/bash

### List existing containers
sudo docker container ls


### Reload nginx configuration 

`docker exec nginx-proxy /etc/init.d/nginx reload`



