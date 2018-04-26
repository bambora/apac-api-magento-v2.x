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

namespace Bambora\Apacapi\Model\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'DC');
    }

    public function toOptionArray()
    {
        /**
        * making filter by allowed cards
        */

        $options[] = ['value' => 'VI', 'label' => 'Visa'];
        $options[] = ['value' => 'MC', 'label' => 'Master Card'];
        $options[] = ['value' => 'AE', 'label' => 'American Express'];
        $options[] = ['value' => 'DC', 'label' => 'Diners Club International'];
        
        
        return $options;
    }
}
