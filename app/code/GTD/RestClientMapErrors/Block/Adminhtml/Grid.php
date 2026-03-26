<?php

namespace Balloon\RestClientMapErrors\Block\Adminhtml;

use Balloon\RestClientMapErrors\Api\Data\DefaultErrorInterface;
use Balloon\RestClientMapErrors\Api\Data\MapErrorInterface;
use Balloon\RestClientMapErrors\Api\Data\TypeMapErrorInterface;
use Balloon\RestClientMapErrors\Model\ResourceModel\{
    DefaultError\CollectionFactory as DefaultErrorCollectionFactory,
    MapError\CollectionFactory as MapErrorCollectionFactory
};
use Magento\Backend\Block\Template;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Grid extends Template
{
    protected array $types = [];
    public function __construct(
        Template\Context $context,
        protected readonly DefaultErrorCollectionFactory $defaultErrorCollectionFactory,
        protected readonly MapErrorCollectionFactory $mapErrorCollectionFactory,
        protected readonly BlockRepositoryInterface $blockRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
        $this->types = array_keys(TypeMapErrorInterface::MAP);
    }

    public function getJsLayout(): string
    {
        $layout = json_decode(parent::getJsLayout(), true);
        $layout['components']['map_errors_content']['errors'] = $this->getAllErrors();
        $layout['components']['map_errors_content']['blocks'] = $this->getBlocksData();
        $layout['components']['map_errors_content']['url_delete'] = $this->getUrl('rest_client_map_errors/delete/index', ['_secure' => true]);
        $layout['components']['map_errors_content']['url_save'] = $this->getUrl('rest_client_map_errors/save/index', ['_secure' => true]);
        $layout['components']['map_errors_content']['url_save_default'] = $this->getUrl('rest_client_map_errors/save/defaulterror', ['_secure' => true]);
        return json_encode($layout);
    }

    protected function getBlocksData(): array {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $cmsBlocks = $this->blockRepository->getList($searchCriteria)->getItems();
        $arrResult = [];

        foreach ($cmsBlocks as $block) {
            $arrResult[] = $block->getIdentifier();//['value' => $block->getIdentifier(), 'label' => $block->getTitle()];
        }
        return $arrResult;
    }

    protected function getAllErrors():array {
        $defaultErrors = $this->getDefaultErrors();
        $errors = $this->getErrors();
        $mapErrorContent = [];
        foreach (TypeMapErrorInterface::MAP as $code => $label) {
            $mapErrorContent[$code] = [
                'label' => $label,
                'default' => $defaultErrors[$code] ?? null,
                'list' => $errors[$code]
            ];
        }
        return $mapErrorContent;
    }

    protected function getErrors(): array
    {
        $errorsCollection = $this->mapErrorCollectionFactory->create();
        $errorsCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter(MapErrorInterface::TYPE_MAP, [
                'in' => $this->types
            ]);
        $errors = [];
        foreach($this->types as $code) {
            $errors[$code] = [];
        }
        foreach($errorsCollection->getItems() as $error) {
            $errors[$error->getData(MapErrorInterface::TYPE_MAP)][] = [
                MapErrorInterface::ENTITY_ID => $error->getData(MapErrorInterface::ENTITY_ID),
                MapErrorInterface::CODE_RESPONSE => $error->getData(MapErrorInterface::CODE_RESPONSE),
                MapErrorInterface::BLOCK_CODE => $error->getData(MapErrorInterface::BLOCK_CODE)
            ];
        }
        return $errors;
    }

    protected function getDefaultErrors(): array
    {
        $defaultErrorsCollection = $this->defaultErrorCollectionFactory->create();
        $defaultErrorsCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter(DefaultErrorInterface::TYPE_MAP, [
                'in' => $this->types
            ]);
        $defaultErrors = [];
        foreach($defaultErrorsCollection->getItems() as $defaultError) {
            $defaultErrors[$defaultError->getData(DefaultErrorInterface::TYPE_MAP)] = [
                DefaultErrorInterface::ENTITY_ID => $defaultError->getData(DefaultErrorInterface::ENTITY_ID),
                MapErrorInterface::BLOCK_CODE => $defaultError->getData(MapErrorInterface::BLOCK_CODE)
            ];
        }
        return $defaultErrors;
    }
}
