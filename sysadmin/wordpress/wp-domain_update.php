<?php
require_once("wp-load.php");
 
global $wpdb;

$old_domain = '5k03t.hosts.cx';
$new_domain = 'alienor.eu';
$old_urls = ['http://'.$old_domain, 'https://'.$old_domain];
$new_url = 'https://'.$new_domain;

/**
* recursive function for updating value within a WP table 
*/
function update_value($value){
    global $old_domain, $new_domain, $old_urls, $new_url;
    // remember original representation
    $is_serialized = false;    
    // values stored in the meta_value field might be stored under their PHP-serialized form
    if(is_serialized($value)){
        $value = unserialize($value);
        $is_serialized = true;
    }    
    // value can be handled as a string (int, float, string, bool)
    if(is_scalar($value)){
        return str_replace($old_domain, $new_domain, str_replace($old_urls[1], $new_url, str_replace($old_urls[0], $new_url, $value)));
    }
    // value is of complex type
    else {
        // can we iterate through attributes ?
        if(is_iterable($value)){
            foreach($value as $meta_key => &$meta_value){  
                $value[$meta_key] = update_value($meta_value);     
            }
        }
        else {
            // unsupported type : leave it untouched
        }
    }
    // return updated value with original representation
    if($is_serialized){
        $value = serialize($value);
    }    
    return $value;
}
// wp_post table
// 1) update guid
// 2) update field `post_content`
$sql = "SELECT `ID`, `guid`, `post_content` from {$wpdb->posts} WHERE (`post_content` like '%{$old_domain}%' )  OR (`guid` like '%{$old_domain}%' );";
$posts = $wpdb->get_results($sql , ARRAY_A );
$result_count = count($posts);
echo "<pre>Updating {$result_count} records...".PHP_EOL;
foreach($posts as $post_data){
    $result = $wpdb->update($wpdb->posts, [
        "post_content"  =>  update_value($post_data['post_content']),
        "guid"          =>  update_value($post_data['guid'])
    ],
    ["ID"=>$post_data['ID']], array("%s"), array("%d"));
    if($result === false){
        echo "Error Updating {$post_data['ID']}".PHP_EOL;
        if(!empty($wpbd->last_error)){
            echo "Mysql Error: Updating {$wpbd->last_error}".PHP_EOL;
        }
    }
    else {
        echo "Updated Post ID :  {$post_data['ID']}".PHP_EOL;
    }
}
// wp_postmeta table
// look for values in the `meta_value` field of the wp_postmeta table, candidates to update
$sql = "SELECT * from {$wpdb->postmeta} WHERE `meta_value` like '%{$old_domain}%' ;";
$meta_posts = $wpdb->get_results($sql , ARRAY_A );
$result_count = count($meta_posts);
echo "<pre>Updating {$result_count} records...".PHP_EOL;
foreach($meta_posts as $meta_data){
    $original_meta = $meta_data['meta_value'];
    $new_meta = update_value($original_meta);
    $result = $wpdb->update($wpdb->postmeta, ["meta_value"=>$new_meta], ["meta_id"=>$meta_data['meta_id']], array("%s"), array("%d"));
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
