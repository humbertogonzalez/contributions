<?php

declare(strict_types=1);

namespace BalloonGroup\RequestQuote\Model\Pdf;

use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\Pdf\PdfProvider;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryList as AppDirectoryList;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class CreatePdf
{
    /**
     * CreatePdf constructor
     *
     * @param PdfProvider $pdfProvider
     * @param File $fileDriver
     * @param DirectoryList $directoryList
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly PdfProvider $pdfProvider,
        private readonly File $fileDriver,
        private readonly DirectoryList $directoryList,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Get new pdf path
     *
     * @param Quote $quote
     * @return string
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function createQuotePdf(Quote $quote): string
    {
        $rawPdf = $this->pdfProvider->generatePdfText();

        $fileName = 'quote_' . $quote->getIncrementId() . '.pdf';

        $mediaPath = $this->directoryList->getPath(AppDirectoryList::MEDIA) . '/quotes';
        $filePath = $mediaPath . '/' . $fileName;

        if (!$this->fileDriver->isExists($mediaPath)) {
            $this->fileDriver->createDirectory($mediaPath, 0755);
        }

        $this->fileDriver->filePutContents($filePath, $rawPdf);

        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'quotes/' . $fileName;
    }
}
