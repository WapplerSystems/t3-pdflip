<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['pdflip'] = \WapplerSystems\Pdflip\LinkHandling\PdflipLinkHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['pdflip'] = \WapplerSystems\Pdflip\LinkHandler\PdflipLinkBuilder::class;
