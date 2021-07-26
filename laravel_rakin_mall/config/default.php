<?php

 /**
  * Here you can define all the default options globally & access via config('default.color_theme') to get default option
  */

return array(
    'color_theme' => 'blue',                        // default color theme
    'direction' => 'ltr',                           // default direction; can be "ltr" or "rtl"
    'locale' => 'en',                               // default language
    'timezone' => 'Asia/Kolkata',                   // default timezone; can be anytime zone from config/timezone.php
    'notification_position' => 'toast-bottom-right',    // default position for notification; can be "top-right","bottom-right","top-left","bottom-left"
    'date_format' => 'DD-MM-YYYY',                   // default date format
    'time_format' => 'H:mm',                        // default time format
    'week_start_day' => 1,
    'week_end_day' => 0,
    'driver' => 'log',                              // default mail driver
    'from_address' => 'hello@example.com',          // default mail sender email
    'from_name' => 'Hello',                         // default mail sender name
    'token_lifetime' => 120,                        // default lifetime of authentication token
    'reset_password_token_lifetime' => 30,          // default lifetime for password reset token
    'activity_log' => 1,                            // Activity log enabled by default, put 0 to disable
    'email_log' => 1,                               // Email log enabled by default, put 0 to disable
    'reset_password' => 1,                          // Reset Password enabled by default, put 0 to disable
    'registration' => 1,                            // Registration/Account Creation enabled by default, put 0 to disable
    'mode' => 1,                                    // Put 1 for live mode & 0 for test mode
    'designation_subordinate_level' => 1,
    'multilingual' => 1,
    'calendar' => 1,
    'ip_filter' => 1,
    'email_template' => 1,
    'todo' => 1,
    'message' => 1,
    'backup' => 1,
    'show_department_menu' => 1,
    'show_designation_menu' => 1,
    'report_server' => 'http://localhost:8080',
    'report_server_key' => '',
    'allow_docs_file_extensions' => 'pdf,doc,docx,xls,xlsx,pptx,ppt,xps,potx,pot,ppsx,pps,txt',
    'allow_image_file_extensions' => 'jpg,png,jpeg',
    'max_upload_filesize' => 30,
);