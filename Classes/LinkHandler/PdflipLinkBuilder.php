<?php

declare(strict_types=1);

namespace WapplerSystems\Pdflip\LinkHandler;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\LinkResult;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

/**
 * Builds the frontend URL of a `t3://pdflip?uid=…` link: the current page with the page
 * type 68724, which renders nothing but the flip book of that file.
 *
 * It deliberately extends no core link builder: their constructors and entry points differ
 * between TYPO3 v13 (`build()`, constructed with the content object) and v14 (`_build()` /
 * `buildLink()`), so this class offers all of them and lets the core build the page URL.
 */
final class PdflipLinkBuilder
{
    public const PAGE_TYPE = 68724;

    public function __construct(
        private ?ContentObjectRenderer $contentObjectRenderer = null,
        mixed $typoScriptFrontendController = null
    ) {}

    /** TYPO3 v13 */
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): LinkResultInterface
    {
        return $this->createLink($linkDetails, $linkText, $target, $conf, $this->contentObjectRenderer);
    }

    /** TYPO3 v14, called for builders that are no TypolinkBuilderInterface */
    public function _build(
        array &$linkDetails,
        string $linkText,
        string $target,
        array $conf,
        ServerRequestInterface $request,
        ContentObjectRenderer $contentObjectRenderer
    ): LinkResultInterface {
        return $this->createLink($linkDetails, $linkText, $target, $conf, $contentObjectRenderer);
    }

    /** Signature of TYPO3\CMS\Frontend\Typolink\TypolinkBuilderInterface (v14), present so that v14 does not warn */
    public function buildLink(array $linkDetails, array $configuration, ServerRequestInterface $request, string $linkText = ''): LinkResultInterface
    {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');
        return $this->createLink(
            $linkDetails,
            $linkText,
            (string)($linkDetails['target'] ?? ''),
            $configuration,
            $contentObjectRenderer instanceof ContentObjectRenderer ? $contentObjectRenderer : null
        );
    }

    private function createLink(array $linkDetails, string $linkText, string $target, array $conf, ?ContentObjectRenderer $contentObjectRenderer): LinkResultInterface
    {
        $file = $linkDetails['file'] ?? null;
        if (!$file instanceof FileInterface || $contentObjectRenderer === null) {
            throw new UnableToLinkException(
                'File "' . ($linkDetails['typoLinkParameter'] ?? '') . '" does not exist, so "' . $linkText . '" was not linked.',
                1791750001,
                null,
                $linkText
            );
        }
        $request = $contentObjectRenderer->getRequest();
        $pageId = $request->getAttribute('frontend.page.information')?->getId()
            ?? $request->getAttribute('routing')?->getPageId()
            ?? $request->getAttribute('site')?->getRootPageId();
        $url = $contentObjectRenderer->createUrl([
            'parameter' => (int)$pageId,
            'additionalParams' => '&type=' . self::PAGE_TYPE . '&file=' . $file->getUid(),
            'forceAbsoluteUrl' => $conf['forceAbsoluteUrl'] ?? 0,
        ]);
        if ($url === '') {
            throw new UnableToLinkException('No URL for the flip book of file ' . $file->getUid(), 1791750002, null, $linkText);
        }
        if ($linkText === '') {
            $linkText = $file->getName();
        }
        return (new LinkResult('pdflip', $url))
            ->withTarget($target)
            ->withLinkConfiguration($conf)
            ->withLinkText($linkText);
    }
}
