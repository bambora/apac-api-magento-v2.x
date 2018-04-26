# Bambora APAC API Magento v2.x extension

## Supported Magento 2 versions
 * Supports all current versions from 2.x and up.

## Installation
These installation steps will apply until this extension is published on the Magento Marketplace.

### Install the extension via SSH terminal
1. Log in to your Linux server terminal
2. Navigate to the Magento 2 root folder
3. Modify the composer.json file with the following:

* Change "minimum-stability": "stable" to "minimum-stability": "dev"
* Add the Bambora repo to 'repositories':

        {
            "type": "vcs",
            "url": "https://github.com/bambora/apac-api-magento-v2.x"
        }

For example:

    "minimum-stability": "dev",
    "prefer-stable": true,
    "repositories": [
        {
            "type": "composer",
            "url": "https://repo.magento.com/"
        },
        {
            "type": "vcs",
            "url": "https://github.com/bambora/apac-api-magento-v2.x"
        }

    ],

4. Run the command: `composer require bambora/apacapi`
5. Run the command: `php bin/magento setup:upgrade`
6. Run the command: `php bin/magento setup:di:compile`
7. Run the command: `php bin/magento cache:flush`

If you are running your Magento 2 site in production mode:

8. Run the command: `php bin/magento setup:static-content:deploy`
9. Run the command: `php bin/magento indexer:reindex`

The extension has now been installed and the next step is to configure it.

## Configure the extension
In order to access the configuration settings:
1. Click `Stores` > `Configuration` 
2. Click `Sales` > `Payment Methods`
3. Locate `Bambora APAC Online` in the right column and expand it
4. Edit configuration (see [Mandatory Configurations](#mandatoryconfigurations) for details)
5. Click `Save Config`

![magento step 4-1](/assets/images/magento-api-step-4-1.png)
<label>Configuration of the extension: Steps 1 and 2</label>

![magento step 4-2](/assets/images/magento-api-step-4-2.png)
<label>Configuration of the extension: Steps 3 to 4</label>

<a name="mandatoryconfigurations"></a> 
### Mandatory configurations
There are a number of settings that must be completed to successfully configure the Bambora APAC Online extension. Most of the settings have been set to default values upon installation, however, the following settings are mandatory to finalise your installation:
* Mode (Sandbox or Live)
* Live API Username
* Live API Password
* Sandbox API Username
* Sandbox API Password

Ensure that the extension `Mode` is set to `Live` on your live environment and `Sandbox` on your development or staging environment. After you have completed the above mandatory configuration settings, you are now ready to offer Bambora as a payment method in your checkout.
