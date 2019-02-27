Defining a custom css per user can be achieved by hooking the admin_head loop of wordpress : 

```
/* UI fixes */ 
add_action('admin_head', function () {

  $current_user = wp_get_current_user();
  if($current_user->user_login != 'root'){
    echo '<style>
    #toplevel_page_vc-welcome {
        display: none;
    } 
    #menu-users {
      display: none;
    }   
    #setting-error-tgmpa_fcvp {
      display: none;
    }
  </style>';    
  }

  echo '<style>
    .composer-switch a, .composer-switch a:visited {
        padding: 0 8px !important;
    } 
    .composer-switch .logo-icon {
      height: 12px !important;
    }
  </style>';
});
```



