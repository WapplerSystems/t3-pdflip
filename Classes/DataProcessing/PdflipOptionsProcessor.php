<?php

declare(strict_types=1);

namespace WapplerSystems\Pdflip\DataProcessing;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Builds the options of the pdflip library from the site settings (`settings.pdflip`) and,
 * where present, the FlexForm of the content element (`flexFormData.settings`). The result
 * is a JSON string for the `data-pdflip-options` attribute, so no inline script is needed.
 *
 * Variables: `pdflipOptions` (JSON) and `pdflipSettings` (merged array, for templates).
 *
 * It also registers the library: one script and one stylesheet per page, however many
 * books there are. pdf.js itself is loaded by the library only when a book becomes visible.
 */
final class PdflipOptionsProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly AssetCollector $assetCollector,
        private readonly Typo3Version $typo3Version,
    ) {}

    private const ENUMS = [
        'pageMode' => ['auto', 'single', 'double'],
        'flipStyle' => ['soft', 'hard', 'cover'],
        'direction' => ['ltr', 'rtl'],
        'controlsPosition' => ['top', 'bottom', 'hidden'],
        'wheel' => ['zoom', 'flip', 'none'],
        'openPanel' => ['none', 'thumbnails', 'outline'],
    ];

    private const CONTROLS = [
        'first', 'prev', 'pageNumber', 'next', 'last', 'thumbnails', 'outline', 'search', 'zoomIn',
        'zoomOut', 'autoplay', 'pageMode', 'sound', 'share', 'download', 'fullscreen',
    ];

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $settings = $this->plain($contentObjectConfiguration['settings.']['pdflip.'] ?? []);
        $flexForm = $processedData['flexFormData']['settings'] ?? [];
        foreach (is_array($flexForm) ? $flexForm : [] as $key => $value) {
            // empty FlexForm values mean "use the site setting"
            if ($value !== '' && $value !== null) {
                $settings[$key] = $value;
            }
        }

        $options = [];
        foreach (self::ENUMS as $key => $allowed) {
            if (in_array($settings[$key] ?? '', $allowed, true)) {
                $options[$key] = $settings[$key];
            }
        }
        foreach (['sound', 'autoplay', 'download', 'links', 'textLayer', 'deepLink'] as $key) {
            if (isset($settings[$key]) && $settings[$key] !== '') {
                $options[$key] = (bool)$settings[$key];
            }
        }
        if (MathUtility::canBeInterpretedAsInteger($settings['startPage'] ?? null) && (int)$settings['startPage'] > 1) {
            $options['startPage'] = (int)$settings['startPage'];
        }
        if (MathUtility::canBeInterpretedAsInteger($settings['height'] ?? null) && (int)$settings['height'] > 0) {
            $options['height'] = (int)$settings['height'];
        }
        // a CSS colour; anything that could leave the property value is dropped
        $background = trim((string)($settings['background'] ?? ''));
        if ($background !== '' && preg_match('/^[#a-z0-9(),.%\s\/-]+$/i', $background) === 1) {
            $options['background'] = $background;
        }
        $controls = array_values(array_intersect(
            GeneralUtility::trimExplode(',', (string)($settings['controls'] ?? ''), true),
            self::CONTROLS
        ));
        $hidden = GeneralUtility::trimExplode(',', (string)($settings['hideControls'] ?? ''), true);
        if ($controls !== []) {
            $options['controls'] = array_values(array_diff($controls, $hidden));
        } elseif ($hidden !== []) {
            $options['controls'] = array_values(array_diff(self::CONTROLS, $hidden));
        }

        $language = $cObj->getRequest()->getAttribute('language');
        if ($language instanceof SiteLanguage) {
            $options['locale'] = $language->getLocale()->getLanguageCode();
        }
        $uid = (int)($processedData['data']['uid'] ?? 0);
        if ($uid > 0) {
            $options['id'] = 'c' . $uid;
        }

        $this->registerAssets();

        $as = (string)($processorConfiguration['as'] ?? 'pdflipOptions');
        $processedData[$as] = json_encode($options, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $processedData['pdflipSettings'] = $settings;
        return $processedData;
    }

    private function registerAssets(): void
    {
        // the nonce option was renamed from "useNonce" (v13) to "csp" (v14)
        $csp = $this->typo3Version->getMajorVersion() >= 14 ? ['csp' => true] : ['useNonce' => true];
        $this->assetCollector->addStyleSheet('pdflip', 'EXT:ws_pdflip/Resources/Public/Pdflip/pdflip.css', [], $csp);
        $this->assetCollector->addJavaScript('pdflip', 'EXT:ws_pdflip/Resources/Public/Pdflip/pdflip.global.js', ['defer' => 'defer'], $csp);
    }

    /** TypoScript arrays without the trailing dots of sub keys. */
    private function plain(array $typoScript): array
    {
        $result = [];
        foreach ($typoScript as $key => $value) {
            if (!is_array($value)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
