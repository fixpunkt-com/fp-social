<?php

declare(strict_types=1);

return [
    // required import configurations of other extensions,
    // in case a module imports from another package
    'dependencies' => ['backend'],
    'imports' => [
        // recursive definiton, all *.js files in this folder are import-mapped
        // trailing slash is required per importmap-specification
        '@fixpunkt/fp-social/' => 'EXT:fp_social/Resources/Public/JavaScript/Backend/',
    ],
];
