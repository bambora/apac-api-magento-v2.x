<?php

namespace Bambora\Apacapi\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    const FILENAME = '/var/log/bambora-apac.log';

    /**
     * @var string
     */
    protected $fileName = self::FILENAME;

    /**
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;
}