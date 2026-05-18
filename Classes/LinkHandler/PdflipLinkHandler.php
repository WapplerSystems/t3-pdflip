<?php

declare(strict_types=1);

namespace WapplerSystems\Pdflip\LinkHandler;


use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Controller\AbstractLinkBrowserController;
use TYPO3\CMS\Backend\LinkHandler\AbstractLinkHandler;
use TYPO3\CMS\Backend\LinkHandler\LinkHandlerInterface;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\RecordList\ElementBrowserRecordList;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ComponentFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\FolderUtilityRenderer;
use TYPO3\CMS\Backend\View\RecordSearchBoxComponent;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Resource\Search\FileSearchDemand;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Filelist\LinkHandler\AbstractResourceLinkHandler;
use TYPO3\CMS\Filelist\Matcher\Matcher;
use TYPO3\CMS\Filelist\Matcher\ResourceFileExtensionMatcher;
use TYPO3\CMS\Filelist\Matcher\ResourceFileTypeMatcher;
use TYPO3\CMS\Filelist\Matcher\ResourceFolderTypeMatcher;
use TYPO3\CMS\Filelist\Matcher\ResourceMatcher;
use TYPO3\CMS\Filelist\Type\LinkType;
use TYPO3\CMS\Filelist\Type\Mode;


#[Autoconfigure(public: true, shared: false)]
class PdflipLinkHandler extends AbstractResourceLinkHandler
{

    protected LinkType $type = LinkType::FILE;

    public function __construct(
        IconFactory $iconFactory,
        ResourceFactory $resourceFactory,
        PageRenderer $pageRenderer,
        UriBuilder $uriBuilder,
        TcaSchemaFactory $tcaSchemaFactory,
        LanguageServiceFactory $languageServiceFactory,
        ComponentFactory $componentFactory,
        private readonly ViewFactoryInterface $viewFactory,
        private readonly LinkService $linkService,
    ) {
        parent::__construct($iconFactory, $resourceFactory, $pageRenderer, $uriBuilder, $tcaSchemaFactory, $languageServiceFactory, $componentFactory);
    }


    public function getLinkAttributes(): array
    {
        return ['target', 'title', 'class'];
    }


    public function initializeVariables(ServerRequestInterface $request): void
    {
        parent::initializeVariables($request);
        $this->pageRenderer->loadJavaScriptModule('@pdflip/pdflip-link-handler.js');

        $this->resourceDisplayMatcher = GeneralUtility::makeInstance(Matcher::class);
        $this->resourceDisplayMatcher->addMatcher(GeneralUtility::makeInstance(ResourceFolderTypeMatcher::class));

        // @todo Deprecate "allowedExtensions", see LinkPopup for further information
        $allowedExtensions = GeneralUtility::trimExplode(',', (string)($this->linkBrowser->getParameters()['params']['allowedExtensions'] ?? ''), true);
        $allowedFileExtensions = GeneralUtility::trimExplode(',', (string)($this->linkBrowser->getParameters()['params']['allowedFileExtensions'] ?? ''), true);
        $allowedFileExtensions = array_unique(array_merge($allowedExtensions, $allowedFileExtensions));
        if (count($allowedFileExtensions) >= 1) {
            $fileExtensionMatcher = GeneralUtility::makeInstance(ResourceFileExtensionMatcher::class);
            $fileExtensionMatcher->setExtensions($allowedFileExtensions);
        } else {
            $fileExtensionMatcher = GeneralUtility::makeInstance(ResourceFileTypeMatcher::class);
        }
        $this->resourceDisplayMatcher->addMatcher($fileExtensionMatcher);
    }



    /**
     * Format the current link for HTML output
     *
     * @return string
     */
    public function formatCurrentUrl(): string
    {
        return $this->linkParts['url']['pdflip'];
    }


    public function canHandleLink(array $linkParts): bool
    {
        if ($linkParts['type'] !== 'pdflip') {
            return false;
        }
        $this->linkParts = $linkParts['url'] ?? [];
        return true;
    }


    /**
     * Render the link handler
     */
    public function render(ServerRequestInterface $request): string
    {



        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:filelist/Resources/Private/Templates/','EXT:pdflip/Resources/Private/Backend/Templates/'],
            partialRootPaths: ['EXT:backend/Resources/Private/Partials/','EXT:pdflip/Resources/Private/Backend/Partials/'],
            layoutRootPaths: ['EXT:backend/Resources/Private/Layouts/'],
            request: $request,
        );
        $view = $this->viewFactory->create($viewFactoryData);


        $contentHtml = '';
        if ($this->selectedFolder !== null) {

            // store the selected folder
            $backendUser = $this->getBackendUser();
            $modData = $backendUser->getModuleData('browse_links.php', 'ses');
            $modData['expandFolder'] = $this->selectedFolder->getCombinedIdentifier();
            $backendUser->pushModuleData('browse_links.php', $modData);

            // Create the filelist
            $this->filelist->start(
                $this->selectedFolder,
                MathUtility::forceIntegerInRange($this->currentPage, 1, 100000),
                $this->sortField,
                $this->sortDirection,
                Mode::BROWSE
            );
            $this->filelist->setResourceDisplayMatcher($this->resourceDisplayMatcher);
            $this->filelist->setResourceSelectableMatcher($this->resourceSelectableMatcher);

            $searchWord = trim((string)($request->getParsedBody()['searchTerm'] ?? $request->getQueryParams()['searchTerm'] ?? ''));
            $searchDemand = $searchWord !== '' ? FileSearchDemand::createForSearchTerm($searchWord)->withFolder($this->selectedFolder)->withRecursive() : null;

            $resource = $this->linkParts['url']['file'] ?? null;
            if ($resource instanceof ResourceInterface) {
                $resourceSelectedMatcher = GeneralUtility::makeInstance(Matcher::class);
                $resourceMatcher = GeneralUtility::makeInstance(ResourceMatcher::class);
                $resourceMatcher->addResource($resource);
                $resourceSelectedMatcher->addMatcher($resourceMatcher);
                $this->filelist->setResourceSelectedMatcher($resourceSelectedMatcher);
            }

            $markup = [];

            // Render the filelist search box
            $markup[] = '<div class="mb-4">';
            $markup[] = GeneralUtility::makeInstance(RecordSearchBoxComponent::class)
                ->setSearchWord($searchWord)
                ->render($request, $this->filelist->createModuleUri($this->getUrlParameters([])));
            $markup[] = '</div>';

            // Render the filelist header bar
            $markup[] = '<div class="row justify-content-between mb-2">';
            $markup[] = '    <div class="col-auto"></div>';
            $markup[] = '    <div class="col-auto">';
            $markup[] = '        ' . $this->getSortingModeButtons($request, $this->filelist->mode);
            $markup[] = '        ' . $this->getViewModeButton($request);
            $markup[] = '    </div>';
            $markup[] = '</div>';

            // Render the filelist
            $markup[] = $this->filelist->render($searchDemand, $this->view);

            // Render the file upload and folder creation form
            $folderUtilityRenderer = GeneralUtility::makeInstance(FolderUtilityRenderer::class, $this);
            //$markup[] = $folderUtilityRenderer->uploadForm($request, $this->selectedFolder);
            $markup[] = $folderUtilityRenderer->createFolder($request, $this->selectedFolder);

            $contentHtml = implode(PHP_EOL, $markup);
        }


        $this->view->assign('selectedFolder', $this->selectedFolder);
        $this->view->assign('content', $contentHtml);
        $this->view->assign('contentOnly', (bool)($request->getQueryParams()['contentOnly'] ?? false));
        $this->view->assign('treeActions', ($this->type === LinkType::FOLDER) ? ['link'] : []);
        $this->view->assign('currentIdentifier', !empty($this->linkParts) ? $this->linkParts['url']['file']->getUid() : '');

        return $this->view->render('LinkHandler/File');
    }

}
