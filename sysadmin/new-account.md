# New account creation
Here are the steps in order to create a new "account", which consists of all config and related data for a specific App. 

## Create a new user account
An account is identified by its (fully qualified) domain name (e.g. myapp.domain.com).

### Run init.sh
A script (located under `/home/nabu/accounts/init.sh`) is dedicated to new user accounts creation.
Basically, that script:  
* creates a new user account (username and password) and adds it the www-data group
* creates a new directory under `/home` and sets the proper ownership

To use it:
* first define account-specific parameters in the `.env` file (within same directory);
    * minimal params are USERNAME (fully qualified domain name of the App); and PASSWORD (for `FTP` and `doc` access) 
* then run it by typing following command `sudo ./init.sh`

### Choose a Template
Then, based on the project requirements, it is necessary to:    
* copy a project template to targeted home directory (e.g. `/home/myapp.domain.com`)
* update `docker-compose.yml` and `.env` files with relevant values
* create ad-hoc directories and set permissions accordingly (chmod)

Remember: all services used by an App should be part of a same stack, and therefore defined in a single `docker-compose.yml` file.

Note: Most database service result in a named volume for the DB (e.g. domainname_db_data)
So, in case of config change (e.g. DB name or password), it is necessary to 
* manualy update config values OR to remove/recreate the volume.
* empty the www folder

### Documentation
If the App comes with documentation, and if that documentation is not public, it is also necessary to create a htpasswd **file** under `/srv/docker/nginx/htpasswd`, named after the App account (e.g. myapp.domain.com).
This can be quickly achieved using the `htpasswd` command (from apache2-utils)

```
htpasswd -bc /srv/docker/nginx/htpasswd/doc.myapp.domain.com {username} {password}
```

### SSL
Most templates, come SSL enabled. However, it is possible to choose whether the proxy should auto-redirect HTTP requests to HTTPS or not, by setting following vars in `docker-compose.yml` files: 
``` 
HTTPS_METHOD=noredirect 
VIRTUAL_PORT=80
```

Note: in order to be operational, a website running under SSL need to be attached to a valid DNS entry (accessible through internet, to any client). This is because the nginx auto-config (wrongly ?) sets a `return 500` when using the `/etc/nginx/certs/default.crt`  certificate.

As an alternate method, inside the nginx-proxy instance, it is possible to update the `/app/nginx.tmpl`: Under the `{{ if (and (not $is_https) (exists "/etc/nginx/certs/default.crt") (exists "/etc/nginx/certs/default.key")) }}` section, do: 

```
        # return 500;
        location / {
                proxy_pass {{ trim $proto }}://{{ trim $upstream_name }};
        }
```

(i.e.: prevent returning a HTTP 500 error, and relay the request the same way as in HTTP.)

## Create related GIT repositories
Mandatory repositories are:
* for the app itslef (having files stored undes /home/{usenrame}/www)
* for its documentation (having files stored undes /home/{usenrame}/doc)

## Populate directories
The home directory should be available though FTP, using the App account (e.g. myapp.domain.com).
Git has also to be initialised, typically by using the `git clone` command and, if necessary, `git commit` and `git push`, to store the initial content of the directories. 

## Starting the App
When the configuration is ready, the command to run the app container is: `docker-compose up -d`

If necessary, all files are created and docker config updated.

After that, all containers from the stack will be accessible through docker ps, or using Portainer (see [server administration](server-administration.md)).

## About mkdocs
When App is first started, doc folder will be populated with default files from mkdocs.
In order to synch it with the git repository it is necessary to:  
`mv ./doc/mkdocs/docs ./doc/mkdocs/old`
`git clone {git_repo} ./doc/mkdocs/docs`

chown {username}:www-data -R doc

In addition, edit the file `./doc/mkdocs/mkdocs.yml`
site_name: My Docs
theme: readthedocs


