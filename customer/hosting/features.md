# Hosting features



## Anti-DDoS

DDoS threat protection
Your infrastructure is protected against distributed denial of service attacks. 



## Disk Mirroring

Redundant Array of Independent Disks
All data is replicated live multiple times to ensure no data is lost in case of hardware failure. 



##  SSL certificate

Let's encrypt SSL certificate
Configuration & installation of a free SSL certificate for an HTTPS ready App. 



## Backups

Backups on a distinct location server
Every day your production database and filestore are backed up and kept up to 4 months. 


## Monitoring

Two kind of monitoring are involved : 

* Host monitoring is done using [netdata](https://docs.netdata.cloud/) and allows to see real-time resources consumption : an interactive status page is available with current servers' status. 
* Online presence is ensured using [updown.io](https://updown.io/) : an analysis of response time and status is performed every hour. In case something goes wrong, an alert is immediately sent to our services.




## Preview mode

This feature allows to easily add and remove an authentication request when accessing to the application. It uses a native HTTP basic-auth through an auto-generated .htaccess file. 

This feature is especially useful during development/updates phases, in order to prevent search engines from accessing content that is not ready yet, which would result indexing inappropriate content or URL and in decreasing SEO score. 



## Maintenance mode

Easily put an instance on hold
Set website under maintenance and prevent unwanted changes during a transition phase. 



## Mirroring hot swap

Instant Disaster Recovery
Real-time replication of production host on a distinct infrastructure. Use of a unique fail-over IP address. 
