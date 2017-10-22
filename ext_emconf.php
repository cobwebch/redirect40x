<?php
/**
 * Extension Manager/Repository config file for ext "redirect40x".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => '40x redirections',
    'description' => '40x handler for TYPO3',
    'category' => 'fe',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-8.7.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Cobweb\\Redirect40x\\' => 'Classes'
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Roberto Presedo',
    'author_email' => 'cobweb@cobweb.ch',
    'author_company' => 'Cobweb Development Sarl',
    'version' => '1.0.0',
];
