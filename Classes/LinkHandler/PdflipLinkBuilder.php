<?php

declare(strict_types=1);

namespace WapplerSystems\Pdflip\LinkHandler;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Typolink\AbstractTypolinkBuilder;
use TYPO3\CMS\Frontend\Typolink\LinkResult;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;
use TYPO3\CMS\Frontend\Typolink\PageLinkBuilder;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

class PdflipLinkBuilder extends PageLinkBuilder
{


    public function build(array &$linkDetails, string $linkText, string $target, array $conf): LinkResultInterface
    {

        // TODO: verschiedene Arten der Einbindung unterstützen,

        $file = $linkDetails['file'] ?? false;
        // check if the file exists or if a / is contained (same check as in detectLinkType)
        if (!($file instanceof FileInterface) && !($file instanceof Folder)) {
            throw new UnableToLinkException(
                'File "' . $linkDetails['typoLinkParameter'] . '" did not exist, so "' . $linkText . '" was not linked.',
                1490989449,
                null,
                $linkText
            );
        }
        debug($linkDetails);

        $fragment = $this->calculateUrlFragment($conf, $linkDetails);
        $queryParameters = $this->calculateQueryParameters($conf, $linkDetails);

        $linkDetails['type'] = LinkService::TYPE_PAGE;


        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $request = $this->contentObjectRenderer->getRequest();

        $pageId = $request->getAttribute('frontend.page.information')?->getId();
        if ($pageId === null) {
            $site = $request->getAttribute('site');
            if ($site !== null) {
                $pageId = $site->getRootPageId();
            } else {
                // @todo: We can usually expect a site to be always set. This fallback here may only be
                //        required due to incomplete setup in transform.html VH functional test?!
                $allSites = $siteFinder->getAllSites();
                $firstSite = reset($allSites);
                $pageId = $firstSite->getRootPageId();
            }
        }
        $linkDetails['pageuid'] = $pageId;

        $queryParameters['type'] = 68724;
        $queryParameters['file'] = $file->getUid();


        $page = $this->resolvePage($linkDetails, $conf, true);

        if (empty($page)) {
            throw new UnableToLinkException('Page id "' . $linkDetails['pageuid'] . '" was not found, so "' . $linkText . '" was not linked.', 1490987336, null, $linkText);
        }

        try {
            $siteOfTargetPage = $siteFinder->getSiteByPageId((int)$page['uid'], null, $queryParameters['MP'] ?? '');
            $currentSite = $this->getCurrentSite();
        } catch (SiteNotFoundException $e) {
            // Usually happens in tests, as sites with configuration should be available everywhere.
            $siteOfTargetPage = null;
            $currentSite = null;
        }
        if ($siteOfTargetPage === null) {
            throw new UnableToLinkException('Could not link to page with ID: ' . $page['uid'], 1546887172, null, $linkText);
        }


        $url = $this->generateUrlForPageWithSiteConfiguration($page, $siteOfTargetPage, $queryParameters, $fragment, $conf);
        // no scheme => always not external
        if (!$url->getScheme() || !$url->getHost()) {
            $treatAsExternalLink = false;
        } else {
            // URL has a scheme, possibly because someone requested a full URL. So now lets check if the URL
            // is on the same site pagetree. If this is the case, we'll treat it as internal
            // @todo: currently this does not check if the target page is a mounted page in a different site,
            // so it is treating this as an absolute URL, which is wrong
            if ($currentSite && $currentSite->getRootPageId() === $siteOfTargetPage->getRootPageId()) {
                $treatAsExternalLink = false;
            }
        }
        $url = (string)$url;



        $linkLocation = $file->getPublicUrl();
        if ($linkLocation === null) {
            // set the linkLocation to an empty string if null,
            // so it does not collide with the various string functions
            $linkLocation = '';
        }
        // Setting title if blank value to link
        $linkText = $this->encodeFallbackLinkTextIfLinkTextIsEmpty($linkText, rawurldecode($linkLocation));
        debug($linkDetails);
        return (new LinkResult($linkDetails['type'], $this->forceAbsoluteUrl($url, $conf)))
            ->withLinkConfiguration($conf)
            ->withTarget($target ?: $this->resolveTargetAttribute($conf, 'fileTarget'))
            ->withLinkText($linkText);
    }

}
