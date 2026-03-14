<?php
namespace WapplerSystems\Pdflip\DataProcessing;

use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Http\ServerRequest;

class FilePublicUrlDataProcessor implements DataProcessorInterface
{
    /**
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj
     * @param array $contentObjectConfiguration
     * @param array $processorConfiguration
     * @param array $processedData
     * @return array
     */
    public function process(
        \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {

        $request = $cObj->getRequest();
        $fileUid = null;
        if ($request instanceof ServerRequest) {
            $fileUid = $request->getQueryParams()['file'] ?? null;
        }
        $publicUrl = null;
        if ($fileUid) {
            /** @var ResourceFactory $resourceFactory */
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            try {
                $file = $resourceFactory->getFileObject((int)$fileUid);
                $publicUrl = $file->getPublicUrl();
            } catch (\Exception $e) {
                $publicUrl = null;
            }
        }
        $processedData['filePublicUrl'] = $publicUrl;
        return $processedData;
    }
}
