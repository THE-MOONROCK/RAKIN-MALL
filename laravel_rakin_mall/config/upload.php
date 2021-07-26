<?php

/**
  * Here you can define all the default upload options globally & access via config('upload.message') to get upload option for that module
  */

    return array(
      'upload' => array(
        'auth_required' => 1,
        'max_file_size' => env('MAX_UPLOAD_SIZE', 50),
        'allowed_file_extensions' => ['jpg','png','jpeg','pdf','doc','docx','xls','xlsx','pptx', 'ppt', 'xps', 'potx', 'pot', 'ppsx', 'pps','txt'],
        'max_no_of_files' => 10
      ),
    );