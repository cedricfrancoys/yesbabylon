# Empty Browser cache

To make sure not to be displaying content from the browser cache, a good practice is to use a dedicated browser in 'Private'/'Incognito' mode.

However, it might sometimes be necessary to force the browser emptying its cache storage.



## Empty filestore cache



## Empty DNS cache



### Firefox

Navigate to : about:config

Update the preference : "network.dnsCacheExpiration" , set it to `0`.

> You might need to create this entry if it does not exist.

### Chrome 

Navigate to : chrome://net-internals/#dns

"Clear host cache"

> Additionnaly, if a previous connection is still pending, you might need to go to chrome://net-internals/#sockets and "Flush socket pools"