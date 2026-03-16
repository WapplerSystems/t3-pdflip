<?php
namespace WapplerSystems\Pdflip\ViewHelpers;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class FlipbookGlobalConfigViewHelper extends AbstractViewHelper
{

    public function __construct(readonly AssetCollector $assetCollector,
    )
    {

    }

    public function initializeArguments()
    {
        $this->registerArgument('as', 'string', 'Variable name for the JS code', false, 'flipbookLabelsJs');
        $this->registerArgument('useNonce', 'bool', 'Whether to use the global nonce value', false, false);

    }

    public function render()
    {
        $options = [
            'useNonce' => $this->arguments['useNonce'],
        ];
        $attributes = [];

        $labels = [
            'toggleSound',
            'toggleThumbnails',
            'toggleOutline',
            'previousPage',
            'nextPage',
            'toggleFullscreen',
            'zoomIn',
            'zoomOut',
            'toggleHelp',
            'singlePageMode',
            'doublePageMode',
            'downloadPDFFile',
            'gotoFirstPage',
            'gotoLastPage',
            'play',
            'pause',
            'share',
            'mailSubject',
            'mailBody',
            'loading',
        ];
        $jsLabels = [];

        $languageService = $this->getLanguageService();

        foreach ($labels as $label) {
            $translation = $languageService->sL('LLL:EXT:pdflip/Resources/Private/Language/de.locallang.xlf:' . $label);
            $jsLabels[$label] = $translation ?: $label;
        }
        $labels = json_encode($jsLabels, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '';

        $pathPrefix = PathUtility::getPublicResourceWebPath('EXT:pdflip/Resources/Public/');

        $content = <<<JS

    DFLIP.defaults.soundFile = "{$pathPrefix}Sounds/turn2.mp3";
    DFLIP.defaults.imagesLocation = "{$pathPrefix}Images";
    DFLIP.defaults.text = {$labels};

JS;

        $this->assetCollector->addInlineJavaScript('dflip-globals', $content, $attributes, $options);

    }


    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
