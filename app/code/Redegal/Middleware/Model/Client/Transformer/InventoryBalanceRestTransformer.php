<?php

namespace Redegal\Middleware\Model\Client\Transformer;

class InventoryBalanceRestTransformer extends AbstractMiddlewareRestTransformer
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
            $product['status'] = $product['Status'];
            $product['qty'] = $product['Quantity'];

            unset($product['ItemMasterID']);
            unset($product['Status']);
            unset($product['Quantity']);
        }
    }
}
