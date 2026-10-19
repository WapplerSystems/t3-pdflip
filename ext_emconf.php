<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'PDF Flip Book',
    'description' => 'PDF flip books: embedded, as gallery with lightbox or as link target. Based on the open-source pdflip library (pdf.js, no jQuery).',
    'category' => 'fe',
    'author' => 'Sven Wappler',
    'author_email' => 'typo3@wappler.systems',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'version' => '14.3.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-14.99.99',
            'filelist' => '13.4.0-14.99.99',
            'fluid_styled_content' => '13.4.0-14.99.99',
        ],
    ],
];
