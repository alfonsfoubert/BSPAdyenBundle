# BSPAdyenBundle

This bundle provides an integration with Adyen for Symfony2. 

## Installation

Installation is a 3 step process:

1. Download BSPAdyenBundle using composer
2. Enable the Bundle
3. Configure the bundle

### Step 1: Download BSPAdyenBundle using composer

``` js
{
	"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/D3r3ck/BSPAdyenBundle"
        }
    ],
    "require": {
        "d3r3ck/bsp-adyen-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update d3r3ck/bsp-adyen-bundle
```

Composer will install the bundle to your project's `vendor/d3r3ck/bsp-adyen-bundle` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new BSP\AdyenBundle\BSPAdyenBundle(),
    );
}
```

### Step 3: Configure the bundle

Add the following lines to your config.yml

``` yaml
# app/config/config.yml

bsp_adyen:
    platform: test # Options are 'live' or 'test'
    merchant_account: MyAppAccount
    skin: 6bci2GpJ
    shared_secret: testing-key
    currency: EUR # Default value is EUR
	payment_methods: [ 'visa', 'mastercard' ] # Look for available payment methods in the Adyen documentation
    webservice_username: username
    webservice_password: password
```

### Warning

Username and Password for the webservice are not enabled by default. 
If you need recurring payments you'll have to ask for it to Adyen.

And you are done!

## Basic Usage

This bundle integrates the Adyen functionalities into a Symfony2 project by triggering events into your system. All processes are called by the `bsp.adyen.service`

``` php
$adyen = $this->get('bsp.adyen.service');
```

### Creating a recurring account

Creating an account is a 2 step process:

1. Setup the account
2. Listen to notification

#### 1. Setup the account

``` php
$adyen->setup( 'account-unique-name', 'account-email@mailinator.com', '100', 'EUR', 'http://localhost/myReturnUrl' );
```

#### 2. Listen to notification

``` yaml
# Acme/DemoBundle/Resources/config/services.yml

acme_demo.adyen_listener:
    class: Acme\DemoBundle\EventListener\MyListener
    tags:
        - { name: kernel.event_listener, event: bsp.adyen.setup, method: onSetup }
```

``` php
// File: Acme/DemoBundle/EventListener/MyListener.php

class MyListener
{
    public function onSetup( BSP\AdyenBundle\Event\SetupEvent $event )
    {
        $parameters = $event->getParameters();
        $trackId    = $parameters['merchantReference'];
        $extraData  = array( 'store' => $parameters['shopperReference'] );
        // ... do your stuff
    }
}
```

### Charging money to a recurring amount

``` php
$adyen->charge( 'account-unique-name', 'account-email@mailinator.com', '2500', 'EUR' ); // returns true or false
```

You can also do it by a console command

``` bash
php app/console bsp:adyen:charge account-unique-name account-email@mailinator.com 2500 EUR
```

