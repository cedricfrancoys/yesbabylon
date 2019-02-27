

### Rewrite using .htaccess

This only works from http to https (since the other way around the connection cannot be established if the certificate isn't right)

``` 
RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Rewrite URLs using a javascript 

Add a custom.js script to the `header.php` file of the current theme:
```
<script src="/wp-content/themes/{theme_name}/js/custom.js"></script>
```

Place a `custom.js` under `/wp-content/themes/{theme_name}/js/`:

```
jQuery(document).ready(function(){
	jQuery("a").each(function() {
	    jQuery(this).attr("href", jQuery(this).attr("href").replace("https://", "http://"));
	});
});
```