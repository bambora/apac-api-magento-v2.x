<?php

namespace Bambora\Apacapi\Model;

/**
 * Interface Constant
 * @package Bambora\Apacapi\Model
 */
interface Constant
{
    // Transaction types
    const CC_PURCHASE = 1;
    const CC_AUTH = 2;
    const CC_REFUND = 5;
    const DE_DEBIT = 7;
    const DE_CREDIT = 8;

    // Transaction type names
    const CC_PURCHASE_LABEL = 'Purchase';
    const CC_AUTH_LABEL = 'Authorise Only';

    // Web Services URLs
    const SANDBOX_ENDPOINT = 'https://demo.bambora.co.nz/interface/api/dts.asmx';
    const LIVE_ENDPOINT = 'https://www.ippayments.com.au/interface/api/dts.asmx';

    // Payment statuses
    const PAYMENTSTATUS_PROCESSED = 'bambora_processed';

    // Configuration
    const APPROVED_RESPONSE_CODE = 0;
    const API_TIMEOUT = 30;
    const LOGFILE = 'bambora.log'; // for errors and exceptions
    const DEBUG_LOGFILE = 'bambora_debug.log'; // logs all transactions, errors and exceptions

    // Error messages
    const TRANSACTION_SANDBOX_ERROR_MESSAGE = 'Payment failed';
    const TRANSACTION_LIVE_ERROR_MESSAGE = 'Unable to complete payment. Please try again or contact us for further assistance.';

    // Integrated Checkout
    const CHECKOUT_V1_PURCHASE = 'checkout_v1_purchase';
    const CHECKOUT_V1_PREAUTH = 'checkout_v1_preauth';
    // const CHECKOUT_V1_PREAUTH = 'checkout_v1_purchase';

    // const SANDBOX_INTEGRATED_CHECKOUT_URL = 'https://demo.ippayments.com.au/access/index.aspx';
    const SANDBOX_INTEGRATED_CHECKOUT_URL = 'https://demo.bambora.co.nz/access/index.aspx';
    const LIVE_INTEGRATED_CHECKOUT_URL = 'https://www.ippayments.com.au/access/index.aspx';

    const BAMBORA_ORDER_TRANSACTION_AUTHORIZATION = "authorization";
    const BAMBORA_ORDER_TRANSACTION_CAPTURE = "capture";
}