<?php

namespace Empresas\FlujoComboProductos\Block\Adminhtml\System\Config;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Empresas\FlujoComboProductos\Api\CombosRepositoryInterface;
use Empresas\PaqueteArmado\Model\ResourceModel\PaqueteArmado\CollectionFactory;

class Options extends \Magento\Framework\View\Element\Html\Select
{

    protected $searchCriteriaBuilder;
    protected $combosRepository;
    protected $paqueteArmadoCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        SearchCriteriaBuilder                   $searchCriteriaBuilder,
        CombosRepositoryInterface               $combosRepository,
        CollectionFactory $paqueteArmadoCollectionFactory,
        array                                   $data = []
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->combosRepository = $combosRepository;
        $this->paqueteArmadoCollectionFactory = $paqueteArmadoCollectionFactory;
        parent::__construct($context, $data);
    }


    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {

        $combos = $this->combosRepository->getList($this->searchCriteriaBuilder->create());
        $html = '<select id="' . $this->getInputId() . '" name="' . $this->getName() . '">';
        foreach ($combos->getItems() as $combo) {
            $html .= '<option value="' . $combo->getId() . '">' . $combo->getName() . '</option>';
        }


        $paquetes = $this->paqueteArmadoCollectionFactory->create();
        if ($paquetes) {
            $html .= '<option disabled>----------------</option>';
            foreach ($paquetes as $paquete) {
                $html .= '<option value="paquete-armado-' . $paquete->getId() . '">' . $paquete->getName() . '</option>';
            }
        }


        $html .= '</select>';
        return $html;
    }
}
