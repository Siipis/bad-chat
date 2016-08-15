<?php

/**
 * This file is part of the CMS package.
 *
 */

/**
 * Configuration options for CMS.
 */
return [
    'path' => [
        /*
        |--------------------------------------------------------------------------
        | Include paths
        |--------------------------------------------------------------------------
        |
        | Paths for CMS content partials.
        |
        */

        'layouts'   => 'layout',
        'pages'     => 'pages',
        'partials'  => 'partials',
        'menus'     => 'menus',

        /*
        |--------------------------------------------------------------------------
        | Class paths
        |--------------------------------------------------------------------------
        |
        | Paths for class dependencies
        |
        */
        'data'     => app_path('CMS/Data'),

    ],

    'parsers' => [
        /*
        |--------------------------------------------------------------------------
        | Optional parsers
        |--------------------------------------------------------------------------
        |
        | Source parsers used when loading the template.
        | NOTE: The parsers are ran in order of declaration.
        |
        */

        Siipis\CMS\Parser\SyntaxParser::class,
        Siipis\CMS\Parser\DataParser::class,
    ],

];
