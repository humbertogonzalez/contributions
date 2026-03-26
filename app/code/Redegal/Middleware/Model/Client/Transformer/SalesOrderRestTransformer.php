<?php

namespace Redegal\Middleware\Model\Client\Transformer;

class SalesOrderRestTransformer extends AbstractMiddlewareRestTransformer
{
    const COMPONENTS = ['removeNesting', 'renameResponseAttributes'];

    /**
     * Remove unnecessary nesting
     * @param array  &$result
     * @param array  &$attributes
     */
    public function removeNesting(array &$result, array &$attributes)
    {
        $result = $result['Header'] ?? $result;
    }

    public function renameResponseAttributes(array &$result, array &$attributes)
    {
        if (empty($result)) {
            return [];
        }
        
        $result['quote_id'] = $result['ShoppingCartID'] ?? '';
        $result['order_increment_id'] = $result['SalesOrderReference'] ?? '';
        $result['order_id'] = $result['SalesOrderID'] ?? '';
        $result['website'] = $result['FinancialPartyDescription'] ?? '';
        $result['customer_id'] = $result['CustomerID'] ?? '';
        $result['website'] = $result['FinancialPartyDescription'] ?? '';
        $result['shipping'] = $result['ShippingContact'] ?? '';
        $result['status'] = $result['Status'] ?? '';
        
        unset($result['ShoppingCartID']);
        unset($result['SalesOrderReference']);
        unset($result['SalesOrderID']);
        unset($result['FinancialPartyDescription']);
        unset($result['CustomerID']);
        unset($result['ShippingContact']);
        unset($result['Status']);
    }
}