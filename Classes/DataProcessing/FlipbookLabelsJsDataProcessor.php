<?php
namespace WapplerSystems\Pdflip\DataProcessing;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FlipbookLabelsJsDataProcessor implements DataProcessorInterface
{
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        $languageFile = 'LLL:EXT:pdflip/Resources/Private/Language/de.locallang.xlf:';
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
        $localizationUtility = LocalizationUtility::class;
        $jsLabels = [];
        foreach ($labels as $label) {
            $translation = $localizationUtility::translate($languageFile.$label, 'pdflip');
            $jsLabels[$label] = $translation ?: $label;
        }
        $processedData['flipbookLabelsJs'] = 'const flipbookLabels = ' . json_encode($jsLabels, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';';


        return $processedData;
    }
}

