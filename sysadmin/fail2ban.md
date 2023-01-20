
### Useful commands

##### Restart services after config change
* fail2ban service
`sudo service fail2ban restart`
* logger daemon
`sudo service rsyslog restart`

##### See the list of active jails
`sudo fail2ban-client status`

##### See detailed status of a jail
`sudo fail2ban-client status wordpress`

##### See log file of fail2ban daemon  (to check startup errors, if any)
`journalctl -ru fail2ban`
or
`cat /var/log/fail2ban.log`

##### See full list of existing iptables 
`sudo iptables -L`

##### See only DOCKER-USER chain
`iptables -L DOCKER-USER`

##### Check a log file against a specific filter:
`fail2ban-regex /var/log/nginx/access.log wp-login`


##### Manually un-ban a host:
`fail2ban-client set [jail_name] unbanip [ip_address]`




### References:
https://www.e-tinkers.com/2017/01/secure-nginx-and-wordpress-with-fail2ban/
https://github.com/crazy-max/docker-fail2ban
https://www.the-lazy-dev.com/fr/installez-fail2ban-avec-docker/






