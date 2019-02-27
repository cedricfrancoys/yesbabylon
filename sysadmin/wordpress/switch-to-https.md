When it comes to activate HTTPS for a WP website, there are two possible scenarios  :

1) WP is configured behind a reverse proxy

2) WP is directly accessible through a web server



## WP behind a reverse proxy

In that case, the most important part is to tell WP that URL must be rewritten in https.

As [WP official codex](http://codex.wordpress.org/Administration_Over_SSL#Using_a_Reverse_Proxy) states:

> If WordPress is hosted behind a reverse proxy that provides SSL, but is hosted itself without SSL, these options will initially send any requests into an infinite redirect loop. 

It is hence necessary to adapt `wp-config.php`:

```
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
    $_SERVER['HTTPS']='on';    
}

```

## WP with direct access

Either we're moving an instance from one environment to another. Or we're simply activating SSL for an existing instance.

1. Backup the original database
2. If necessary, import the backup to the new environment
3. If necessary, adapt `wp-config.php` for `DB_` values
4. Run the `wp-domain_update.php` script
5. Export database to a text file (SQL dump)
6. Search/Replace {http://example.com} to {https://example.com}
7. Re-import updated SQL 
8. Adapt `wp-config.php`  to force https
```
define('FORCE_SSL_ADMIN', true);
define('WP_HOME','https://example.com');
define('WP_SITEURL','https://example.com');
```
9. Adapt any JS URL rewriting
10. Check .htaccess for URL rewriting 


Here is the detail of the `wp-domain_update.php` script :
```
<?php
require_once("wp-load.php");
 
global $wpdb;
 
$old_url = ['https://dev.example.com', 'http://dev.example.com'];
$new_url = 'https://example.com';
 
$sql = "SELECT * from {$wpdb->postmeta};";
 
$meta_posts = $wpdb->get_results($sql , ARRAY_A );
 
$result_count = count($meta_posts); 
echo "<pre>Updating {$result_count} records...".PHP_EOL;
 
foreach($meta_posts as $meta_data){
     
    $original_meta = $meta_data['meta_value'];
    $is_serialized = false;
     
    if(is_serialized($original_meta)){
        $original_meta = unserialize($original_meta);
        $is_serialized = true;
    }
     
    $new_meta =  process_meta_values($original_meta, $old_url, $new_url);
     
    if($is_serialized){
        $new_meta = serialize($new_meta);
    }
     
    $update_result = $wpdb->update($wpdb->postmeta, array("meta_value"=>$new_meta),array("meta_id"=>$meta_data['meta_id']), array("%s"), array("%d"));
 
    if($result === false){
        echo "Error Updating {$meta_data['meta_id']}".PHP_EOL;
         
        if(!empty($wpbd->last_error)){
            echo "Mysql Error: Updating {$wpbd->last_error}".PHP_EOL;
        }
    }
    else {
        echo "Updated Meta ID :  {$meta_data['meta_id']}".PHP_EOL;
    }   
}
 
 
function process_meta_values($meta_values, $old_url, $new_url){
    if(!is_array($meta_values)){
        return str_replace($old_url, $new_url, $meta_values);
    }
    foreach($meta_values as $meta_key => &$meta_value){  
        $meta_value = process_meta_values($meta_value, $old_url, $new_url);     
    }
    return $meta_values;
}
```

