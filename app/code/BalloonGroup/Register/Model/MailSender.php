<?php
/**
 * @author Humberto González <humberto.gonzalez@balloon-group.com>
 */
declare(strict_types=1);

namespace BalloonGroup\Register\Model;

use Magento\Contact\Model\MailInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Amasty\CompanyAccount\Model\ConfigProvider;
use Magento\Framework\Filesystem\DirectoryList;
use BalloonGroup\Register\Helper\Config;

class MailSender implements MailInterface
{
    /** @var string */
    public const FOLDER_LOCATION = 'contactattachment';
    private UploaderFactory $fileUploaderFactory;
    private Filesystem $fileSystem;
    private File $file;
    private TransportBuilder $transportBuilder;
    private StateInterface $inlineTranslation;
    private StoreManagerInterface $storeManager;

    /**
     * Initialize dependencies.
     *
     * @param UploaderFactory $fileUploaderFactory
     * @param Filesystem $fileSystem
     * @param File $file
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ConfigProvider $configProvider
     * @param DirectoryList $directoryList
     * @param Config $config
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        UploaderFactory $fileUploaderFactory,
        Filesystem $fileSystem,
        File $file,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        private ConfigProvider $configProvider,
        private DirectoryList $directoryList,
        private Config $config,
        StoreManagerInterface $storeManager = null
    ) {
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->fileSystem = $fileSystem;
        $this->file = $file;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @param $replyTo
     * @param array $templateVars
     * @param $file
     * @param null $template
     * @param null $mails
     * @return void
     * @throws NoSuchEntityException
     */
    public function send($replyTo, array $templateVars, $template = null, $mails = null, $file = "")
    {
        $path = $this->directoryList->getPath('pub');
        $filePath = $path . '/constancias/' . $file;
        $this->inlineTranslation->suspend();

        if (!empty($filePath) && $this->file->fileExists($filePath)) {
            $mimeType = mime_content_type($filePath);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier(
                    $this->configProvider->getEmailTemplate(ConfigProvider::ADMIN_NOTIF_NEW_COMPANY_REQUEST)
                )
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setAttachment($this->file->read($filePath), $file, $mimeType)
                ->setTemplateVars($templateVars)
                ->setFrom($this->configProvider->getAdminSender())
                ->addTo($this->config->getRegisterFormReceiver())
                ->addCc($replyTo)
                ->getTransport();
            $transport->sendMessage();
        }

        $this->inlineTranslation->resume();
    }
}
