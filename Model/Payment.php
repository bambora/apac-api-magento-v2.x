<?php
/**
 * @author    Reign <hello@reign.com.au>
 * @version   1.0.0
 * @copyright Copyright (c) 2018 Reign. All rights reserved.
 * @copyright Copyright (c) 2018 Bambora. All rights reserved.
 * @license   Proprietary/Closed Source
 * By viewing, using, or actively developing this application in any way, you are
 * henceforth bound the license agreement, and all of its changes, set forth by
 * Reign and Bambora. The license can be found, in its entirety, at this address:
 * http://www.reign.com.au/magento-licence
 */

namespace Bambora\Apacapi\Model;


class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'bambora_apacapi';

    protected $_code = self::CODE;

    protected $_isGateway                   = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = false;
    protected $_canVoid                     = true;
    protected $_canDelete                   = true;
    
    protected $_connectionType;
    protected $_isBackendOrder;

    protected $_api_username = '';
    protected $_api_password = '';    

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = array()
    ) {
        
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

    }

    /**
     * Payment capturing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        /** @var \Magento\Sales\Model\Order\Address $billing */
        $billing = $order->getBillingAddress();

        try {

            $paymentaction = \Bambora\Apacapi\Model\Constant::CC_AUTH;
            $amount = $amount * 100;
            $response = $this->_SubmitSinglePayment($payment, $amount, $paymentaction);
            
            $responseCode = isset($response->ResponseCode) ? $response->ResponseCode : '';
            if ($responseCode == 0) {
                $payment
                    ->setTransactionId((string) $response->Receipt)
                    ->setIsTransactionClosed(0);
                
            }else{
                throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
            }
            
        } catch (\Exception $e) {
            $this->debugData(['request' => $requestData, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment capturing error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
        }


        if ($this->getConfigData('debug') == "1"){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $logger = $objectManager->get('\Psr\Log\LoggerInterface');
        
            $timestamp = (isset($response->Timestamp)) ? $response->Timestamp : '';      
            $declinedCode = (isset($response->DeclinedCode)) ? $response->DeclinedCode : '';
            $declinedMsg = (isset($response->DeclinedMessage)) ? $response->DeclinedMessage : '';
            $currencyCode = $payment->getOrder()->getOrderCurrencyCode();
            $orderId = $payment->getOrder()->getIncrementId(); 
            $receiptNo = (isset($response->Receipt)) ? $response->Receipt : '';
            $cardNo = "XXXX-" . substr($payment->getCcNumber(), -4); // last 4 digits only
            $cardExp = $payment->getCcExpMonth() . "/" . $payment->getCcExpYear();
            $cardholdername = $payment->getCcOwner();
            $paymentApiMode = $this->getConfigData('mode');
            $AccountNumber = $this->getConfigData('account_number');            
            
            $message  = "Timestamp: " . $timestamp . "\n";                        
            $message .= " Response Code: " . $responseCode  . "\n";
            $message .= " Declined Code: " . $declinedCode  . "\n";  
            $message .= " Declined Message: " . $declinedMsg  . "\n";
            $message .= " Currency: " . $currencyCode  . "\n";
            $message .= " Payment Action: " . $paymentaction  . "\n";  
            $message .= " Amount: " . $amount  . "\n";
            $message .= " Receipt #: " . $receiptNo  . "\n";
            $message .= " Card Number: " . $cardNo  . "\n";
            $message .= " Expiry: " . $cardExp  . "\n";
            $message .= " Card Holder Name: " . $cardholdername  . "\n";
            $message .= " Magento Order #: " . $orderId  . "\n";
            $message .= " Account Number: " . $AccountNumber  . "\n";
            $message .= " Payment API Mode: " . $paymentApiMode  . "\n";          

            $logger->debug($message);            
        }
                
        return $this;
    }

    
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $receiptNumber = $payment->getLastTransId();
        
        if ($receiptNumber) {
            // Preauthorisation exists so capture it
            $paymentaction = \Bambora\Apacapi\Model\Constant::BAMBORA_ORDER_TRANSACTION_CAPTURE;
            $response = $this->_SingleCaptureRequest($receiptNumber, $amount);
        } else {
            // No preauthorisation so submit the purchase
            $paymentaction = \Bambora\Apacapi\Model\Constant::CC_PURCHASE_LABEL;
            $amount = $amount * 100;
            $response = $this->_SubmitSinglePayment($payment, $amount, \Bambora\Apacapi\Model\Constant::CC_PURCHASE);
        }

        $responseCode = isset($response->ResponseCode) ? $response->ResponseCode : '';

        if ($responseCode == 0) {
            $payment->setTransactionId($response->Receipt)->setIsTransactionClosed(0);
        }else{
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
        }
        
        if ($this->getConfigData('debug') == "1") {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $logger = $objectManager->get('\Psr\Log\LoggerInterface');

            $timestamp = (isset($response->Timestamp)) ? $response->Timestamp : '';      
            $declinedCode = (isset($response->DeclinedCode)) ? $response->DeclinedCode : '';
            $declinedMsg = (isset($response->DeclinedMessage)) ? $response->DeclinedMessage : '';
            $currencyCode = $payment->getOrder()->getOrderCurrencyCode();
            $orderId = $payment->getOrder()->getIncrementId(); 
            $receiptNo = (isset($response->Receipt)) ? $response->Receipt : '';
            $cardNo = "XXXX-" . substr($payment->getCcNumber(), -4); // last 4 digits only
            $cardExp = $payment->getCcExpMonth() . "/" . $payment->getCcExpYear();
            $cardholdername = $payment->getCcOwner();
            $paymentApiMode = $this->getConfigData('mode');
            $AccountNumber = $this->getConfigData('account_number');            
            
            $message  = "Timestamp: " . $timestamp . "\n";                        
            $message .= " Response Code: " . $responseCode  . "\n";
            $message .= " Declined Code: " . $declinedCode  . "\n";  
            $message .= " Declined Message: " . $declinedMsg  . "\n";
            $message .= " Currency: " . $currencyCode  . "\n";
            $message .= " Payment Action: " . $paymentaction  . "\n"; 
            $message .= " Amount: " . $amount  . "\n";
            $message .= " Receipt #: " . $receiptNo  . "\n";
            $message .= " Card Number: " . $cardNo  . "\n";
            $message .= " Expiry: " . $cardExp  . "\n";
            $message .= " Card Holder Name: " . $cardholdername  . "\n";
            $message .= " Magento Order #: " . $orderId  . "\n";
            $message .= " Account Number: " . $AccountNumber  . "\n";            
            $message .= " Payment API Mode: " . $paymentApiMode  . "\n";

            $logger->debug($message);
        }
        
        
        
        return $this;
    }


    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        
        $this->_getAPICred();

        $username = $this->_api_username;
        $password = $this->_api_password;
        
        $receiptNumber = $payment->getLastTransId();
        
        
        $amountcents = $payment->getOrder()->getGrandTotal();

        $response = $this->_SingleCaptureRequest($receiptNumber,$amountcents);
        $payment->setTransactionId((string) $response->Receipt)->setIsTransactionClosed(0); 

        $soaprequest  = ' <dts:SubmitSingleVoid>';
        $soaprequest .= '     <!--Optional:-->';
        $soaprequest .= '     <dts:trnXML>';
        $soaprequest .= '     <![CDATA[';
        $soaprequest .= '     <Void>';
        $soaprequest .= '         <Receipt>'.$response->Receipt  .'</Receipt>';
        $soaprequest .= '         <Amount>' . $amountcents . '</Amount>';
        $soaprequest .= '         <Security>';
        $soaprequest .= '             <UserName>'. $username . '</UserName>';
        $soaprequest .= '             <Password>'. $password . '</Password>';
        $soaprequest .= '          </Security> ';
        $soaprequest .= '     </Void>';
        $soaprequest .= '     ]]>';
        $soaprequest .= '     </dts:trnXML>';
        $soaprequest .= ' </dts:SubmitSingleVoid>';

        $xml = $this->_doAPI($soaprequest);
        
        $xmlarray = (array) $xml->SubmitSingleVoidResponse->SubmitSingleVoidResult;
        $response = isset($xmlarray[0]) ? simplexml_load_string($xmlarray[0]) : null; 

        if ($this->getConfigData('debug') == "1") {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $logger = $objectManager->get('\Psr\Log\LoggerInterface');
 
            $message  = "ResponseCode: " . $response->ResponseCode . "\n";
            $message .= "Timestamp: " . $response->Timestamp . "\n";
            $message .= "Receipt: " . $response->Receipt . "\n";  
            $message .= "SettlementDate: " . $response->SettlementDate . "\n";
            $message .= "DeclinedCode" . $response->DeclinedCode . "\n";
            $message .= "DeclinedMessage: " . $response->DeclinedMessage . "\n"; 
 
            $logger->debug($message);
        }
        
        return $this;
 
    }    


    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->void($payment);
    }


    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        
        $this->_getAPICred();
        
        $amountcents = $amount * 100;
        $username = $this->_api_username;
        $password = $this->_api_password;
        
        $receiptNumber = $payment->getLastTransId();

        $soaprequest  = ' <dts:SubmitSingleRefund>';
        $soaprequest .= '     <!--Optional:-->';
        $soaprequest .= '     <dts:trnXML>';
        $soaprequest .= '     <![CDATA[';
        $soaprequest .= '     <Refund>';
        $soaprequest .= '         <Receipt>'.$receiptNumber .'</Receipt>';
        $soaprequest .= '         <Amount>' . $amountcents . '</Amount>';
        $soaprequest .= '         <Security>';
        $soaprequest .= '             <UserName>'. $username . '</UserName>';
        $soaprequest .= '             <Password>'. $password . '</Password>';
        $soaprequest .= '          </Security> ';
        $soaprequest .= '     </Refund>';
        $soaprequest .= '     ]]>';
        $soaprequest .= '     </dts:trnXML>';
        $soaprequest .= ' </dts:SubmitSingleRefund>';
                   
        $xml = $this->_doAPI($soaprequest);
        
        $xmlarray = (array) $xml->SubmitSingleRefundResponse->SubmitSingleRefundResult;
        $response = isset($xmlarray[0]) ? simplexml_load_string($xmlarray[0]) : null; 
        
        $payment->setTransactionId((string) $response->Receipt)->setIsTransactionClosed(0); 
        
        if ($this->getConfigData('debug') == "1"){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $logger = $objectManager->get('\Psr\Log\LoggerInterface');
 
            $message  = "ResponseCode: " . $response->ResponseCode . "\n";
            $message .= "Timestamp: " . $response->Timestamp  . "\n";
            $message .= "Receipt: " . $response->Receipt  . "\n";  
            $message .= "SettlementDate: " . $response->SettlementDate  . "\n";
            $message .= "DeclinedCode" . $response->DeclinedCode  . "\n";
            $message .= "DeclinedMessage: " . $response->DeclinedMessage  . "\n"; 
 
            $logger->debug($message);
        }

        return $this;
        
    }
    
    protected function _SubmitSinglePayment($payment, $amount, $paymentaction)
    {
        
        $orderId = $payment->getOrder()->getIncrementId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if($customerSession->isLoggedIn()) {
            $CustNumber = $customerSession->getCustomer()->getId();
        }else{
            $CustNumber = "";
        }

        $this->_getAPICred();
        
        $username = $this->_api_username;
        $password = $this->_api_password;
    
        $custref = $orderId;
        $amountcents = $amount;
        $accountnumber = $this->getConfigData("account_number"); //Mage::getStoreConfig('payment/bambora/account_number');
        $cardnumber = $payment->getCcNumber();
        $expm = sprintf('%02d',$payment->getCcExpMonth());
        $expy = $payment->getCcExpYear();
        $CVN = $payment->getCcCid();
        $cardholdername = $payment->getCcOwner();
        $trntype = $paymentaction;         


        // CC sample info parameters
        /*[amount] => 5000
        [card] => Array
        (
        [number] => 4111111111111111
        [exp_month] => 02
        [exp_year] => 2019
        [cvc] => 123
        [name] => mitch villanueva
        )*/

        $soaprequest  = '  <dts:SubmitSinglePayment>';
        $soaprequest .= '     <!--Optional:-->';
        $soaprequest .= '     <dts:trnXML>';
        $soaprequest .= '     <![CDATA[';
        $soaprequest .= '      <Transaction>';
        $soaprequest .= '        <CustNumber>' . $CustNumber . '</CustNumber>';
        $soaprequest .= '        <CustRef>' . $custref . '</CustRef>';
        $soaprequest .= '        <Amount>' . $amountcents . '</Amount>';
        $soaprequest .= '        <TrnType>' . $trntype . '</TrnType>';
        $soaprequest .= '        <AccountNumber>' . $accountnumber . '</AccountNumber>';
        $soaprequest .= '        <CreditCard Registered="False">';
        $soaprequest .= '                <CardNumber>' . $cardnumber . '</CardNumber>';
        $soaprequest .= '                <ExpM>' . $expm . '</ExpM>';
        $soaprequest .= '                <ExpY>' . $expy . '</ExpY>';
        $soaprequest .= '                <CVN>' . $CVN . '</CVN>';
        $soaprequest .= '                <CardHolderName>' . $cardholdername . '</CardHolderName>';
        $soaprequest .= '        </CreditCard>';
        $soaprequest .= '        <Security>';
        $soaprequest .= '                <UserName>' . $username . '</UserName>';
        $soaprequest .= '                <Password>' . $password . '</Password>';
        $soaprequest .= '        </Security>';
        $soaprequest .= '        </Transaction>';
        $soaprequest .= '      ]]>';
        $soaprequest .= '     </dts:trnXML>';
        $soaprequest .= '  </dts:SubmitSinglePayment>';
        
        $xml = $this->_doAPI($soaprequest);
        
        $xmlarray = (array) $xml->SubmitSinglePaymentResponse->SubmitSinglePaymentResult;
        $response = isset($xmlarray[0]) ? simplexml_load_string($xmlarray[0]) : null;         

        return $response;        
    }

    protected function _doAPI($request)
    {        
        if ($this->getConfigData('mode') == "sandbox") {
            $url = \Bambora\Apacapi\Model\Constant::SANDBOX_ENDPOINT;;
        } else {
            $url = \Bambora\Apacapi\Model\Constant::LIVE_ENDPOINT;
        }

        $soaprequest  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:dts="http://www.ippayments.com.au/interface/api/dts">';
        $soaprequest .= '<soapenv:Header/>';
        $soaprequest .= '<soapenv:Body>';
        $soaprequest .= $request;
        $soaprequest .= '</soapenv:Body>';
        $soaprequest .= '</soapenv:Envelope>';
        
        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: ".strlen($soaprequest),
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soaprequest);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,1);    


        $responsecurl =  curl_exec($ch);
        $rt = str_replace("<soap:Body>", "", $responsecurl);
        $rx = str_replace("</soap:Body>", "", $rt); 
        $xml = simplexml_load_string($rx);
        curl_close($ch);        
        return $xml;
    }

    /**
     * Submit single capture request (i.e. complete a preauthorisation) to gateway 
     * and return response
     */     
    protected function _SingleCaptureRequest($receiptNumber, $amount)
    {
        $this->_getAPICred();
        
        $amountcents = $amount * 100;
        $username = $this->_api_username;
        $password = $this->_api_password;
        
        $soaprequest  = '<dts:SubmitSingleCapture>';
        $soaprequest .= '<dts:trnXML>';
        $soaprequest .= '<![CDATA[';
        $soaprequest .= '<Capture>';
        $soaprequest .= '<Receipt>' . $receiptNumber . '</Receipt>';
        $soaprequest .= '<Amount>' . $amountcents . '</Amount>';
        $soaprequest .= '<Security>';
        $soaprequest .= '<UserName>' . $username . '</UserName>';
        $soaprequest .= '<Password>' . $password . '</Password>';
        $soaprequest .= '</Security>';
        $soaprequest .= '</Capture>';
        $soaprequest .= ']]>';    
        $soaprequest .= '</dts:trnXML>';
        $soaprequest .= '</dts:SubmitSingleCapture>';
        
        $xml = $this->_doAPI($soaprequest);
        
        $xmlarray = (array) $xml->SubmitSingleCaptureResponse->SubmitSingleCaptureResult;
        $response = isset($xmlarray[0]) ? simplexml_load_string($xmlarray[0]) : null; 

        return $response; 
    }
    
    protected function _getAPICred()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $encryp = $objectManager->get('\Magento\Framework\Encryption\EncryptorInterface');
        
        if ($this->getConfigData('mode') == "sandbox") {
            $this->_api_username = $encryp->decrypt($this->getConfigData('sandbox_api_username'));
            $this->_api_password = $encryp->decrypt($this->getConfigData('sandbox_api_password'));
        } else {
            $this->_api_username = $encryp->decrypt($this->getConfigData('live_api_username'));
            $this->_api_password = $encryp->decrypt($this->getConfigData('live_api_password'));
        }
    }
    

}