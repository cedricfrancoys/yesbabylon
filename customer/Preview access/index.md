# Access to Development and Preview instances

To speed up the development process and set things in motion from the very beginning of each project, the domain name specified for the application (e.g. myapp.mydomain.com) is used all along the development phase. However, as the global DNS registry is left untouched during that phase, it is necessary to explicitly tell to each computer how to access the targeted application given its domain name.   

This is achieved by manually editing your system’s hosts file.



## Adapt your hosts file

All operating systems have a `hosts` file that contains a list of DNS rules. Each line consists of an IP address and its hostname mapping, telling the system how the domain name has to be translated into an IP address. A mapping can be de-activated by starting the line with the # symbol. 

Example:

```
127.0.0.1 localhost
123.45.67.89 www.example.com
#98.76.54.32 www.inactive-example.com
```

What you need to do, in order to access your preview instance, is to append your new mappings underneath the default ones. You can navigate the file using the arrow keys.   

```
123.45.67.89 domain.com 
```

- Replace `123.45.67.89` with the server IP provided to you. 
- Replace domain.com with your actual domain name. 
- Additional domains, subdomains or addon domains (such as www.domain.com) can be added at the end of the line, separated by spaces.  



See detailed instructions for making these changes to your desired operating system:

* [Mac OS X](mac-os-x.md)
* [Windows Vista or 7](windows-7.md)
* [Windows 8 or 10](windows-10.md)
* [Linux / Unix](linux-unix.md)



## Clear browser DNS cache

Additionnaly, you might require to [clear your browser DNS cache](browser-cache.md#empty-dns-cache).

