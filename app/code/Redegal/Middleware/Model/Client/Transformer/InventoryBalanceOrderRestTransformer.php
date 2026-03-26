<?php

namespace Redegal\Middleware\Model\Client\Transformer;

class InventoryBalanceOrderRestTransformer extends AbstractMiddlewareRestTransformer
{
    const COMPONENTS = ['removeNesting', 'renameItemAttributes'];

    /**
     * Remove unnecessary nesting
     * @param array  &$result
     * @param array  &$attributes
     */
    public function removeNesting(array &$result, array &$attributes)
    {
        $result = $result['Header']['Detail'];
    }

    public function renameItemAttributes(array &$result, array &$attributes)
    {
        foreach($result as &$product)
        {
            $product['sku'] = $product['ItemMasterID'];
            $product['availableQty'] = $product['RequiredQuantity'];
            $product['blockedQty'] = $product['BlockedQuantity'];

            unset($product['ItemMasterID']);
            unset($product['RequiredQuantity']);
            unset($product['BlockedQuantity']);
        }
    }
}
