<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

// addRecordType() exists since TYPO3 v13; v12 gets the CType select item and the type by hand
$addRecordType = static function (array $item, string $showitem, array $typeConfiguration = []): void {
    if (method_exists(ExtensionManagementUtility::class, 'addRecordType')) {
        ExtensionManagementUtility::addRecordType($item, $showitem, $typeConfiguration);
        return;
    }
    ExtensionManagementUtility::addTcaSelectItem('tt_content', 'CType', [
        'label' => $item['label'],
        'description' => $item['description'] ?? '',
        'value' => $item['value'],
        'icon' => $item['icon'] ?? '',
        'group' => $item['group'] ?? 'default',
    ]);
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$item['value']] = $item['icon'] ?? '';
    $GLOBALS['TCA']['tt_content']['types'][$item['value']] = array_replace(
        $typeConfiguration,
        ['showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,' . $showitem
            . ',--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access']
    );
};

$addRecordType(
    [
        'label' => 'LLL:EXT:ws_pdflip/Resources/Private/Language/Backend.xlf:pdflip-gallery',
        'description' => 'LLL:EXT:ws_pdflip/Resources/Private/Language/Backend.xlf:pdflip-gallery.description',
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

$addRecordType(
    [
        'label' => 'LLL:EXT:ws_pdflip/Resources/Private/Language/Backend.xlf:pdflip-embedded',
        'description' => 'LLL:EXT:ws_pdflip/Resources/Private/Language/Backend.xlf:pdflip-embedded.description',
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
$flexForm = 'FILE:EXT:ws_pdflip/Configuration/FlexForms/Embedded.xml';
if ((new Typo3Version())->getMajorVersion() >= 14) {
    $GLOBALS['TCA']['tt_content']['types']['pdflip-embedded']['columnsOverrides']['pi_flexform']['config']['ds'] = $flexForm;
} else {
    ExtensionManagementUtility::addPiFlexFormValue('*', $flexForm, 'pdflip-embedded');
}
