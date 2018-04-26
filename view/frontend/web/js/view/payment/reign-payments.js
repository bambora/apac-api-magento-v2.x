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

/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'bambora_apacapi',
                component: 'Bambora_Apacapi/js/view/payment/method-renderer/bambora-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);