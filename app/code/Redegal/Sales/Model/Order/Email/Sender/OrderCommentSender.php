<?php

namespace Redegal\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;

/**
 * Class OrderCommentSender
 */
class OrderCommentSender extends \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
{
    /**
     * Send email to customer
     *
     * @param Order $order
     * @param bool $notify
     * @param string $comment
     * @return bool
     */
    public function send(Order $order, $notify = true, $comment = '')
    {
        $this->identityContainer->setStore($order->getStore());

        $transport = [
            'order' => $order,
            'comment' => $comment,
            'billing' => $order->getBillingAddress(),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'order_data' => [
                'customer_name' => $order->getCustomerName(),
                'customer_firstname' => $order->getCustomerFirstname(),
                'frontend_status_label' => $order->getFrontendStatusLabel()
            ]
        ];

        if ($order->getFrontendStatusLabel() == 'Cancelado') {
            $transport['is_canceled_status'] = true;
            $transport['dynamic_subject'] = 'Pedido cancelado';
        }

        if ($order->getFrontendStatusLabel() == 'Entregado') {
            $transport['is_complete_status'] = true;
            $transport['dynamic_subject'] = 'Llegó el Bien-Estar';
        }

        if ($order->getFrontendStatusLabel() == 'En camino') {
            $transport['is_en_camino'] = true;
            $transport['dynamic_subject'] = 'El Bien- Estar está listo';
        }

        if ($order->getFrontendStatusLabel() == 'Recolectado') {
            $transport['is_recolectado'] = true;
            $transport['dynamic_subject'] = 'El Bien- Estar está en camino';
        }

        if ($order->getFrontendStatusLabel() != 'Entregado' && $order->getFrontendStatusLabel() != 'Cancelado' && $order->getFrontendStatusLabel() != 'En camino' && $order->getFrontendStatusLabel() != 'Recolectado') {
            $transport['is_other_status'] = true;
            $transport['dynamic_subject'] = 'Actualizado el estado de su pedido';
        }

        $transportObject = new DataObject($transport);

        /**
         * Event argument `transport` is @deprecated. Use `transportObject` instead.
         */
        $this->eventManager->dispatch(
            'email_order_comment_set_template_vars_before',
            ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
        );

        $this->templateContainer->setTemplateVars($transportObject->getData());

        return $this->checkAndSend($order, $notify);
    }
}
