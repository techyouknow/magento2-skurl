
# SKuRL For Magento 2
Help Magento 2 store owners to simplify access to the product page by SKU, Allow add to cart, and easy access to product hero image. 
The sales and marketing team would love this extension. Make things much easier to set up the campaign.

The router is configurable on the backend.

![docs](https://i.imgur.com/nS421YB.png)

## What's included?

### (a) Product sku redirect
Open the Product page for the SKU that specified in the URL.

### (b) Direct add to cart from url
Allows store owner to create a links that add product directly in cart and redirect customer to cart page immediately. 

### (c) Product Image from SKU
Display the Product image for the SKU that specified in the URL.

## Usage Instructions

TechYouKnow SKuRL Extension - Installation steps

INSTALL TechYouKnow SKuRL EXTENSION FROM ZIP FILE ON YOUR DEV INSTANCE. TEST THAT THE EXTENSION
WAS INSTALLED CORRECTLY BEFORE SHIPPING THE CODE TO PRODUCTION

### INSTALLATION

#### Composer Installation
* Go to your magento root path
* Execute command `cd /var/www/Magento` or
 `cd /var/www/html/Magento` based on your server Centos or Ubuntu.
* run composer command: `composer require techyouknow/skurl`
- To enable module execute `php bin/magento module:enable Techyouknow_Skurl`
- Execute `php bin/magento setup:upgrade`
- Optional `php bin/magento setup:static-content:deploy`
- Execute `php bin/magento setup:di:compile`
- Execute `php bin/magento cache:clean`

#### Manual Installation
* extract files from an archive.
* Execute command `cd /var/www/Magento/app/code` or
 `cd /var/www/html/Magento/app/code` based on your server Centos or Ubuntu.
* Move files into Magento2 folder `app/code/Techyouknow/Skurl`. If you downloaded zip file on github, you need to
create directory `app/code/Techyouknow/Skurl`.
- To enable module execute `php bin/magento module:enable Techyouknow_Skurl`
- Execute `php bin/magento setup:upgrade`
- Optional `php bin/magento setup:static-content:deploy`
- Execute `php bin/magento setup:di:compile`
- Execute `php bin/magento cache:clean`

## Requirements

Techyouknow SKuRL Extension For Magento2 requires
* Magento version 2.0 and above
* PHP 7.0 or greater