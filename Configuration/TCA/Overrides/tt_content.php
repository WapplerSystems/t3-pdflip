<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use WapplerSystems\WsSlider\Hooks\ItemsProcFunc;

defined('TYPO3') || die();


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addRecordType(
    [
        'label' => 'LLL:EXT:pdflip/Resources/Private/Language/Backend.xlf:pdflip-gallery',
        'description' => 'LLL:EXT:pdflip/Resources/Private/Language/Backend.xlf:pdflip-gallery.description',
        'value' => 'pdflip-gallery',
        'icon' => 'mimetypes-x-content-text-media',
    ],
    '
        --palette--;;headers,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:media,
        assets,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
        --palette--;;frames,
        --palette--;;appearanceLinks,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
        categories',
    [
        'columnsOverrides' => [
            'assets' => [
                'config' => [
                    'allowed' => 'pdf',
                ],
            ],
        ],
    ]
);





/*  Embedded */


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addRecordType(
    [
        'label' => 'LLL:EXT:pdflip/Resources/Private/Language/Backend.xlf:pdflip-embedded',
        'description' => 'LLL:EXT:pdflip/Resources/Private/Language/Backend.xlf:pdflip-embedded.description',
        'value' => 'pdflip-embedded',
        'icon' => 'mimetypes-x-content-text-media',
    ],
    '
        --palette--;;headers,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:media,
        assets,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
        --palette--;;frames,
        --palette--;;appearanceLinks,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
        categories',
    [
        'columnsOverrides' => [
            'assets' => [
                'config' => [
                    'allowed' => 'pdf',
                    'maxitems' => 1,
                ],
            ],
        ],
    ]
);


