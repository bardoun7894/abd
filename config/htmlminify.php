<?php
return [
    // Default OFF. The minifier collapses <script> newlines, which turns any
    // `//` single-line comment in an inline script into "comment out the rest
    // of the script" → "Uncaught SyntaxError: Unexpected end of input" (hit on
    // dashboard/invoices/{id} and the AI widgets). Whitespace savings aren't
    // worth breaking JS; leave off unless every inline script is comment-free.
    'default' => env('HTML_MINIFY', false),
    // exclude route name for exclude from minify
    'exclude_route' => [
        'dashboard.emps.upd_role'
    ]
];
