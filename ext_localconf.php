<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['pdflip'] = \WapplerSystems\Pdflip\LinkHandling\PdflipLinkHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['pdflip'] = \WapplerSystems\Pdflip\LinkHandler\PdflipLinkBuilder::class;

// TYPO3 v12 has no site sets, which provide the page TSconfig in v13+
if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 13) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        "@import 'EXT:ws_pdflip/Configuration/Sets/Pdflip/page.tsconfig'\n"
        . "@import 'EXT:ws_pdflip/Configuration/TsConfig/NewContentElementWizardV12.tsconfig'"
    );
}
