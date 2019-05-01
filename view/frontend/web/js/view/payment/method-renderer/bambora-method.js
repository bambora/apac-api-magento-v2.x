/**
 * @author    Reign <hello@reign.com.au>
 * @version   1.1.0
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
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Bambora_Apacapi/payment/bambora-form'
            },

            getCode: function () {
                return 'bambora_apacapi';
            },

            isActive: function () {
                return true;
            },

            validate: function () {
                let $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            }
        });
    }
);