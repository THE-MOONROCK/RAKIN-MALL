<?php

/**
  * Here you can define all the default system variables globally & access via config('system.default_role') to get default option
  */

return array(
    'default_role' => array(                // Currently system supports two default role; one for admin & another for user
        'super' => 'super',
        'admin' => 'admin',
        'head' => 'head',
        'assistant' => 'assistant',
        'user' => 'user',
        'administrator' => 'administrator'
    ),
    'hidden_field' => 'xxxxxxxxxx',         // Hidden fields are system variables that is used to hide private configuration variables as listed below
    'private_config_variables' => array('smtp_username','smtp_password','mailgun_username','mailgun_password','mailgun_secret','mandrill_secret','nexmo_api_key','nexmo_api_secret','facebook_client','facebook_secret','twitter_client','twitter_secret','github_client','github_secret'),
    'social_login_providers' => array('facebook','twitter','github'),   // You can add more social login providers
);