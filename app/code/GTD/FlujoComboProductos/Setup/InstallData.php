<?php

namespace Empresas\FlujoComboProductos\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Api\SearchCriteriaBuilder;


class InstallData implements InstallDataInterface
{
    private $attributeSetFactory;
    private $categorySetupFactory;
    private $eavSetupFactory;
    private $attributeGroupRepository;
    private $attributeGroupFactory;
    private $collectionAttributeSet;
    private $searchCriteriaBuilder;

    public function __construct(
        AttributeSetFactory               $attributeSetFactory,
        CategorySetupFactory              $categorySetupFactory,
        EavSetupFactory                   $eavSetupFactory,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        AttributeGroupInterfaceFactory    $attributeGroupFactory,
        CollectionFactory                 $collectionAttributeSet,
        SearchCriteriaBuilder             $searchCriteriaBuilder
    )
    {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->collectionAttributeSet = $collectionAttributeSet;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $attributeSetName = 'Bundle Empresas';
        $attributeGroupName = 'Atributos empresas';
        $attributes = [
            'velocidad_nacional' => [
                'type' => 'varchar',
                'label' => 'Velocidad Nacional (X) Mbps Simétrica',
                'input' => 'text',
                'required' => false,
                'visible_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'velocidad_internacional' => [
                'type' => 'varchar',
                'label' => 'Velocidad Internacional (x) Mbps Simétrica',
                'input' => 'text',
                'required' => false,
                'visible_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'conexion_exclusiva' => [
                'type' => 'varchar',
                'label' => 'Conexión Exclusiva',
                'input' => 'text',
                'required' => false,
                'visible_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'ancho_de_banda' => [
                'type' => 'varchar',
                'label' => 'Ancho Banda Máximo (x) Mbps',
                'input' => 'text',
                'required' => false,
                'visible_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'watchguard' => [
                'type' => 'varchar',
                'label' => 'Watchguard (x)',
                'input' => 'text',
                'required' => false,
                'visible_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'usuarios_recomendados' => [
                'type' => 'varchar',
                'label' => '(x) usuarios recomendados',
                'input' => 'text',
                'required' => false,
                'visible_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'rendimiento_utm' => [
                'type' => 'varchar',
                'label' => 'Rendimiento UTM (x) Mbps',
                'input' => 'text',
                'required' => false,
                'visible_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'pool_ip_disponible' => [
                'type' => 'varchar',
                'label' => 'Pool de IP disponibles',
                'input' => 'text',
                'required' => false,
                'visible_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
        ];

        $this->createAttributeSets($setup, $attributeSetName);
        $this->createAttributeGroup([$attributeSetName], $attributeGroupName);
        $this->addAttributesToGroup($setup, $attributeSetName, $attributeGroupName, $attributes);

        $setup->endSetup();
    }

    private function createAttributeSets($setup, $attributeSetName)
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);

        $attributeSetCollection = $this->collectionAttributeSet->create()
            ->addFieldToFilter('attribute_set_name', $attributeSetName)
            ->addFieldToFilter('entity_type_id', $entityTypeId);

        if ($attributeSetCollection->getSize() > 0) {
            return;
        }

        $attributeSet = $this->attributeSetFactory->create();
        $defaultAttributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

        $data = [
            'attribute_set_name' => $attributeSetName,
            'entity_type_id' => $entityTypeId,
            'sort_order' => 50,
        ];

        $attributeSet->setData($data);
        $attributeSet->validate();
        $attributeSet->save();
        $attributeSet->initFromSkeleton($defaultAttributeSetId)->save();
    }

    private function createAttributeGroup(array $attrSetNames, string $groupName)
    {
        foreach ($attrSetNames as $attrSetName) {
            $attributeSetId = $this->getAttrSetId($attrSetName);

            if (!$attributeSetId) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Attribute set '%1' does not exist.", $attrSetName)
                );
            }

            // Verificar si el grupo ya existe en el conjunto de atributos
            $existingGroupId = $this->getGroupIdIfExists($attributeSetId, $groupName);
            if ($existingGroupId) {
                // Si ya existe, no intentar crearlo
                continue;
            }

            $attributeGroup = $this->attributeGroupFactory->create();
            $attributeGroup->setAttributeSetId($attributeSetId);
            $attributeGroup->setAttributeGroupName($groupName);
            $attributeGroup->setSortOrder(50); // Asegúrate de establecer un valor de orden

            try {
                $this->attributeGroupRepository->save($attributeGroup);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The attribute group '%1' could not be saved: %2", $groupName, $e->getMessage())
                );
            }
        }
    }

    private function getGroupIdIfExists(int $attributeSetId, string $groupName): ?int
    {

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('attribute_set_id', $attributeSetId, 'eq')
            ->create();


        $attributeGroups = $this->attributeGroupRepository->getList($searchCriteria)->getItems();
        foreach ($attributeGroups as $group) {
            if ($group->getAttributeGroupName() === $groupName) {
                return $group->getAttributeGroupId();
            }
        }
        return null; // Si no existe, retornar nulo
    }


    private function addAttributesToGroup($setup, string $attrSetName, string $groupName, array $attributes)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $attributeSetId = $this->getAttrSetId($attrSetName);
        $groupId = $this->getGroupId($attributeSetId, $groupName);

        foreach ($attributes as $attributeCode => $options) {
            $eavSetup->addAttribute(Product::ENTITY, $attributeCode, $options);

            // Assign attribute to group
            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                $attributeSetId,
                $groupId,
                $attributeCode
            );
        }
    }

    private function getAttrSetId(string $attrSetName)
    {
        $attributeSet = $this->collectionAttributeSet->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('attribute_set_name', $attrSetName)
            ->getFirstItem();

        if (!$attributeSet->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Attribute set '%1' not found.", $attrSetName)
            );
        }

        return $attributeSet->getId();
    }


    private function getGroupId(int $attributeSetId, string $groupName)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('attribute_set_id', $attributeSetId, 'eq')
            ->create();

        $attributeGroupCollection = $this->attributeGroupRepository->getList($searchCriteria)->getItems();

        foreach ($attributeGroupCollection as $group) {
            if ($group->getAttributeGroupName() === $groupName) {
                return $group->getAttributeGroupId();
            }
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __("Attribute group '%1' not found in set ID '%2'", $groupName, $attributeSetId)
        );
    }

}
