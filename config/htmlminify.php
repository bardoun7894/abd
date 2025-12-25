<?php
return [
    'default' => env('HTML_MINIFY', true),
    // exclude route name for exclude from minify
    'exclude_route' => [
        'dashboard.emps.upd_role'
    ]
];
