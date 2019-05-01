<?php
/**
 * @author    Reign <hello@reign.com.au>
 * @version   1.1.0
 * @copyright Copyright (c) 2019 Reign. All rights reserved.
 * @copyright Copyright (c) 2019 Bambora. All rights reserved.
 * @license   Proprietary/Closed Source
 * By viewing, using, or actively developing this application in any way, you are
 * henceforth bound the license agreement, and all of its changes, set forth by
 * Reign and Bambora. The license can be found, in its entirety, at this address:
 * http://www.reign.com.au/magento-licence
 */

namespace Bambora\Apacapi\Model\Source;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'sandbox' => 'Sandbox',
            'live' => 'Live',
        ];
    }
}