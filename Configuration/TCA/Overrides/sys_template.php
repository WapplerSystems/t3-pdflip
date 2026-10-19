<?php

defined('TYPO3') || die();

// TYPO3 v12 has no site sets: include the configuration via sys_template (after fluid_styled_content)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('ws_pdflip', 'Configuration/TypoScript/', 'PDF Flip Book');
