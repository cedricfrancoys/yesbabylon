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