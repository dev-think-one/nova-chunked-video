<?php

return [

    'use_package_routes' => true,

    'tmp_chunks_folder' => '_chunks/',

    'validation' => [
        'chunk_size' => 10 * 1024 * 1024, // 10MB,
        'max_size'   => 3 * 1024 * 1024 * 1024, // 3GB
    ],

];
