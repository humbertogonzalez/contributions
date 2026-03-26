<?php

namespace Balloon\RestClientErrorReport\Ui\Component\Logs\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Balloon\RestClientErrorReport\Model\TypeError as DefaultValues;

class TypeError extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item)
            {
                $item[$this->getData('name')] = DefaultValues::ERRORS[$item["type_id"]] ?? DefaultValues::DEFAULT_ERROR;
            }
        }
        return $dataSource;
    }
}
