<?php

namespace Balloon\RestClientMapErrors\Controller\Adminhtml\Save;

use Balloon\RestClientMapErrors\Api\Data\MapErrorInterface;
use Balloon\RestClientMapErrors\Api\Data\TypeMapErrorInterface;
use Balloon\RestClientMapErrors\Model\MapErrorFactory;
use Balloon\RestClientMapErrors\Model\ResourceModel\MapError;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements HttpPostActionInterface
{
    public function __construct(
        protected readonly MapError $mapErrorResource,
        protected readonly MapErrorFactory $mapErrorFactory,
        protected readonly ResultFactory $resultFactory,
        protected readonly RequestInterface $request
    )
    {
    }

    public function execute(): ResultInterface
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $dataResponse = [
            'success' => false,
            'body' => null
        ];
        try {
            $errorType = (int)$this->request->getParam('errorType');
            $error = $this->request->getParam('error');
            if (
                !in_array($errorType, array_keys(TypeMapErrorInterface::MAP)) ||
                !is_numeric($error[MapErrorInterface::CODE_RESPONSE]) ||
                !$error[MapErrorInterface::BLOCK_CODE]
            ) {
                $response->setHttpResponseCode(400);
                $dataResponse['body'] = 'Parametro inválido';
            } else {
                if ($newError = $this->createOrUpdate($error, $errorType)) {
                    $dataResponse['success'] = true;
                    $dataResponse['body'] = $newError->getData();
                } else {
                    $dataResponse['body'] = 'Ocurrio un error inesperado, intente mas tarde';
                }
            }
        } catch (\Exception $e) {
            $response->setHttpResponseCode(500);
            $dataResponse['body'] = $e->getMessage();
        }
        $response->setData($dataResponse);
        return $response;
    }

    protected function createOrUpdate(array $errorData, int $errorType): MapErrorInterface {
        $error = $this->mapErrorFactory->create();
        if (isset($errorData[MapErrorInterface::ENTITY_ID])) {
            $this->mapErrorResource->load($error, $errorData[MapErrorInterface::ENTITY_ID], MapErrorInterface::ENTITY_ID);
        } else {
            $error->setData(MapErrorInterface::TYPE_MAP, $errorType);
        }
        $error->addData([
            MapErrorInterface::BLOCK_CODE => $errorData[MapErrorInterface::BLOCK_CODE],
            MapErrorInterface::CODE_RESPONSE => (int)$errorData[MapErrorInterface::CODE_RESPONSE]
        ]);
        $this->mapErrorResource->save($error);
        return $error;
    }
}
