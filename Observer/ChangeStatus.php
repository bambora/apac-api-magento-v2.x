<?php

namespace Bambora\Apacapi\Observer;

use Magento\Sales\Model\Order as Order;

class ChangeStatus implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * ChangeStatus constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderPayment = $observer->getEvent()->getPayment();
        $method = $orderPayment->getMethodInstance();
        $methodCode = $method->getCode();

        if ($methodCode == \Bambora\Apacapi\Model\Payment::CODE) {
            $orderStatus = $this->_scopeConfig->getValue('payment/' . \Bambora\Apacapi\Model\Payment::CODE . '/order_status');
            $order = $orderPayment->getOrder();
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus($orderStatus);
            $order->save();
        }

        return $this;
    }
}