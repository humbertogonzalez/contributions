<?php
/**
 * @author Humberto González <humberto.gonzalez@balloon-group.com>
 */
declare(strict_types=1);

namespace BalloonGroup\Register\Controller\Index;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use BalloonGroup\Register\Model\MailSender;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem\DirectoryList;

class Post extends Action
{
    /**
     * Post constructor
     *
     * @param Context $context
     * @param MailSender $mail
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     * @param SessionManager $sessionManager
     * @param UploaderFactory $uploadFactory
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Context $context,
        private MailSender $mail,
        private DataPersistorInterface $dataPersistor,
        private LoggerInterface $logger,
        private SessionManager $sessionManager,
        private UploaderFactory $uploadFactory,
        private DirectoryList $directoryList
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost() && $this->getRequest()->getFiles('attachment')) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            $this->validatedParams();
            $file = $this->uploadFile();
            $this->sendEmail($this->getRequest()->getParams(), $file);
            $this->messageManager->addSuccessMessage(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->dataPersistor->clear('register_company');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('register_company', $this->getRequest()->getParams());

            return $this->resultRedirectFactory->create()->setPath('register/index');
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->dataPersistor->set('register_company', $this->getRequest()->getParams());

            return $this->resultRedirectFactory->create()->setPath('register/index');
        }

        $this->sessionManager->setFormSubmitted(true);
        return $this->resultRedirectFactory->create()->setPath('register/index/success');
    }


    /**
     * @param $post
     * @param $file
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    private function sendEmail($post, $file): void
    {
        $this->mail->send($post['email'], $post, "","", $file);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function validatedParams(): void
    {
        $request = $this->getRequest();

        if (trim($request->getParam('razon_social', '')) === '') {
            throw new LocalizedException(__('Enter the Razón Social and try again.'));
        }
        if (\strpos($request->getParam('email', ''), '@') === false) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
        if (trim($request->getParam('telephone', '')) === '') {
            throw new LocalizedException(__('Enter the Telephone and try again.'));
        }
        if (trim($request->getParam('provincia', '')) === '') {
            throw new LocalizedException(__('Enter the Provincia and try again.'));
        }
        if (trim($request->getParam('localidad', '')) === '') {
            throw new LocalizedException(__('Enter the Localidad and try again.'));
        }
        if (trim($request->getParam('domicilio_comercial', '')) === '') {
            throw new LocalizedException(__('Enter the Domicilio Comercial and try again.'));
        }
        if (trim($request->getParam('postcode', '')) === '') {
            throw new LocalizedException(__('Enter the Código Postal and try again.'));
        }
        if (trim($request->getParam('contacto', '')) === '') {
            throw new LocalizedException(__('Enter the Contacto and try again.'));
        }
        if (trim($request->getParam('provincia_entrega', '')) === '') {
            throw new LocalizedException(__('Enter the Provincia de Entrega and try again.'));
        }
        if (trim($request->getParam('domicilio_entrega', '')) === '') {
            throw new LocalizedException(__('Enter the Domicilio de Entrega and try again.'));
        }
        if (trim($request->getParam('localidad_entrega', '')) === '') {
            throw new LocalizedException(__('Enter the Localidad de Entrega and try again.'));
        }
        if (trim($request->getParam('postcode_entrega', '')) === '') {
            throw new LocalizedException(__('Enter the Código Postal de Entrega and try again.'));
        }
        if (!$this->getRequest()->getFiles('attachment')) {
            throw new LocalizedException(__('Enter the constancia de inscripción vigente a la AFIP and try again.'));
        }

    }

    /**
     * @throws FileSystemException
     * @throws Exception
     */
    private function uploadFile()
    {
        // Handle file upload
        $uploader = $this->uploadFactory->create(['fileId' => 'attachment']);
        $uploader->setAllowedExtensions(['pdf']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        $path = $this->directoryList->getPath('pub');
        $uploadDir = $path . '/constancias/';
        $uploader->save($uploadDir);

        return $uploader->getUploadedFileName();
    }
}
