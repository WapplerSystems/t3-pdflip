<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

ExtensionManagementUtility::addRecordType(
    [
        'label' => 'LLL:EXT:pdflip/Resources/Private/Language/Backend.xlf:pdflip-gallery',
        'description' => 'LLL:EXT:pdflip/Resources/Private/Language/Backend.xlf:pdflip-gallery.description',
        'value' => 'pdflip-gallery',
        'icon' => 'mimetypes-pdf',
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

ExtensionManagementUtility::addRecordType(
    [
        'label' => 'LLL:EXT:pdflip/Resources/Private/Language/Backend.xlf:pdflip-embedded',
        'description' => 'LLL:EXT:pdflip/Resources/Private/Language/Backend.xlf:pdflip-embedded.description',
        'value' => 'pdflip-embedded',
        'icon' => 'mimetypes-pdf',
    ],
    '
        --palette--;;headers,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:media,
        assets,
        pi_flexform,
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

// v14 reads the data structure from columnsOverrides (addPiFlexFormValue() is deprecated there),
// v13 from the ds array of pi_flexform
$flexForm = 'FILE:EXT:pdflip/Configuration/FlexForms/Embedded.xml';
if ((new Typo3Version())->getMajorVersion() >= 14) {
    $GLOBALS['TCA']['tt_content']['types']['pdflip-embedded']['columnsOverrides']['pi_flexform']['config']['ds'] = $flexForm;
} else {
    ExtensionManagementUtility::addPiFlexFormValue('*', $flexForm, 'pdflip-embedded');
}
