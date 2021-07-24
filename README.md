# Nagad (Bangladesh) payment gateway for Laravel 6.x+, 7.x+, 8.x+

[Nagad](https://nagad.com.bd) is one of the Financial Services in Bangladesh. This package is built for Nagad Payment Gateway for Laravel 6.x, 7.x and 8.x+ 

## Contents

- [Installation](#installation)
	- [Setting up your configuration](#setting-up-your-configuration)
- [Usage](#usage)
- [License](#license)

## Installation

You can install the package via composer:

``` bash
composer require anovob/laravel-nagad-api
```

### Setting up your configuration
Extract the nagad config files:

```bash
php artisan vendor:publish --tag=nagad-config
```

- This will publish and config file in ``config_path()`` of your application. Eg. `config/nagad.php`

- Configure the configurations for the nagad merchant account. Use `sandbox = true` for development stage.

- Be sure to set the **timezone** of you application to `Asia/Dhaka` in order to work with Nagad PGW. To do this:
go to `config/app.php` and set `'timezone' => 'Asia/Dhaka'`

## Usage

NagadPGW uses three stages of payment process, and two of theme are simultaneous. To get started, first you have to setup 
a callback route (`GET`) for the Nagad Callback and name the route in the nagad config file.

``` php
    // in routes/web.php
    Route::get('/nagad/callback', 'NagadController@callback')->name('nagad.callback');

    //in config/nagad.php
    'callback' => 'nagad.callback' // or use env variable to store
```

To Start payment, in your NagadController:
```php    
    use NagadAPI\Nagad;    
    use Illuminate\Http\Request;
    
    public function createPayment() 
    {
    	$nagad = new Nagad($this->nagadConfig());    
        /**
         * Method 1: Quickest
         * This will automatically redirect you to the Nagad PG Page
         * */

        return $nagad->setOrderID('ORDERID123')
            ->setAmount('540')
            ->checkout()
            ->redirect();
        
        /**
         * Method 2: Manual Redirection
         * This will return only the redirect URL and manually redirect to the url
         * */

        $url = $nagad->setOrderID('ORDERID123')
            ->setAmount('540')
            ->checkout()
            ->getRedirectUrl();

        return ['url' => $url];


        /**
         * Method 3: Advanced 
         * You set additional params which will be return at the callback
         * */

        return $nagad->setOrderID('ORDERID123')
            ->setAmount('540')
            ->setAddionalInfo(['pid' => 9, 'myName' => 'DG'])
            ->checkout()
            ->redirect();


        /**
         * Method 4: Advanced Custom Callabck
         * You can set/override callback url while creating payment
         * */

        return $nagad->setOrderID('ORDERID123')
            ->setAmount('540')
            ->setAddionalInfo(['pid' => 9, 'myName' => 'DG'])
            ->setCallbackUrl("https://manual-callback.url/callback")
            ->checkout()
            ->redirect();
    }
    

	//To receive the callback response use this method: 

    /**
     * This is the routed callback method
     * which receives a GET request.
     * 
     * */

    public function callback(Request $request)
    {
    	$nagad = new Nagad($this->nagadConfig());
        $verified = $nagad->callback($request)->verify();
        if($verified->success()) {

            // Get Additional Data
            dd($verified->getAdditionalData());
            
            // Get Full Response
            dd($verified->getVerifiedResponse());
        } else {
            dd($verified->getErrors());
        }
    }
    
    public function nagadConfig() {        
        $config = [
            'NAGAD_METHOD' => 'sandbox',  // 'sandbox' or 'live'
            'NAGAD_APP_ACCOUNT' => '01817535192', //'nagad_merchant_phone'
            'NAGAD_APP_MERCHANTID' => '683002007104225', //nagad_merchant_id'
            'NAGAD_APP_MERCHANT_PRIVATE_KEY' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCJakyLqojWTDAVUdNJLvuXhROV+LXymqnukBrmiWwTYnJYm9r5cKHj1hYQRhU5eiy6NmFVJqJtwpxyyDSCWSoSmIQMoO2KjYyB5cDajRF45v1GmSeyiIn0hl55qM8ohJGjXQVPfXiqEB5c5REJ8Toy83gzGE3ApmLipoegnwMkewsTNDbe5xZdxN1qfKiRiCL720FtQfIwPDp9ZqbG2OQbdyZUB8I08irKJ0x/psM4SjXasglHBK5G1DX7BmwcB/PRbC0cHYy3pXDmLI8pZl1NehLzbav0Y4fP4MdnpQnfzZJdpaGVE0oI15lq+KZ0tbllNcS+/4MSwW+afvOw9bazAgMBAAECggEAIkenUsw3GKam9BqWh9I1p0Xmbeo+kYftznqai1pK4McVWW9//+wOJsU4edTR5KXK1KVOQKzDpnf/CU9SchYGPd9YScI3n/HR1HHZW2wHqM6O7na0hYA0UhDXLqhjDWuM3WEOOxdE67/bozbtujo4V4+PM8fjVaTsVDhQ60vfv9CnJJ7dLnhqcoovidOwZTHwG+pQtAwbX0ICgKSrc0elv8ZtfwlEvgIrtSiLAO1/CAf+uReUXyBCZhS4Xl7LroKZGiZ80/JE5mc67V/yImVKHBe0aZwgDHgtHh63/50/cAyuUfKyreAH0VLEwy54UCGramPQqYlIReMEbi6U4GC5AQKBgQDfDnHCH1rBvBWfkxPivl/yNKmENBkVikGWBwHNA3wVQ+xZ1Oqmjw3zuHY0xOH0GtK8l3Jy5dRL4DYlwB1qgd/Cxh0mmOv7/C3SviRk7W6FKqdpJLyaE/bqI9AmRCZBpX2PMje6Mm8QHp6+1QpPnN/SenOvoQg/WWYM1DNXUJsfMwKBgQCdtddE7A5IBvgZX2o9vTLZY/3KVuHgJm9dQNbfvtXw+IQfwssPqjrvoU6hPBWHbCZl6FCl2tRh/QfYR/N7H2PvRFfbbeWHw9+xwFP1pdgMug4cTAt4rkRJRLjEnZCNvSMVHrri+fAgpv296nOhwmY/qw5Smi9rMkRY6BoNCiEKgQKBgAaRnFQFLF0MNu7OHAXPaW/ukRdtmVeDDM9oQWtSMPNHXsx+crKY/+YvhnujWKwhphcbtqkfj5L0dWPDNpqOXJKV1wHt+vUexhKwus2mGF0flnKIPG2lLN5UU6rs0tuYDgyLhAyds5ub6zzfdUBG9Gh0ZrfDXETRUyoJjcGChC71AoGAfmSciL0SWQFU1qjUcXRvCzCK1h25WrYS7E6pppm/xia1ZOrtaLmKEEBbzvZjXqv7PhLoh3OQYJO0NM69QMCQi9JfAxnZKWx+m2tDHozyUIjQBDehve8UBRBRcCnDDwU015lQN9YNb23Fz+3VDB/LaF1D1kmBlUys3//r2OV0Q4ECgYBnpo6ZFmrHvV9IMIGjP7XIlVa1uiMCt41FVyINB9SJnamGGauW/pyENvEVh+ueuthSg37e/l0Xu0nm/XGqyKCqkAfBbL2Uj/j5FyDFrpF27PkANDo99CdqL5A4NQzZ69QRlCQ4wnNCq6GsYy2WEJyU2D+K8EBSQcwLsrI7QL7fvQ==', //'nagad_key_private'
            'NAGAD_APP_MERCHANT_PG_PUBLIC_KEY' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjBH1pFNSSRKPuMcNxmU5jZ1x8K9LPFM4XSu11m7uCfLUSE4SEjL30w3ockFvwAcuJffCUwtSpbjr34cSTD7EFG1Jqk9Gg0fQCKvPaU54jjMJoP2toR9fGmQV7y9fz31UVxSk97AqWZZLJBT2lmv76AgpVV0k0xtb/0VIv8pd/j6TIz9SFfsTQOugHkhyRzzhvZisiKzOAAWNX8RMpG+iqQi4p9W9VrmmiCfFDmLFnMrwhncnMsvlXB8QSJCq2irrx3HG0SJJCbS5+atz+E1iqO8QaPJ05snxv82Mf4NlZ4gZK0Pq/VvJ20lSkR+0nk+s/v3BgIyle78wjZP1vWLU4wIDAQAB', //'nagad_key_public'
            'NAGAD_APP_TIMEZONE' => 'Asia/Dhaka',
            'NAGAD_CALL_BACK_URL' => 'nagad.callback'
        ];
        return $config;
    }
```

To receive error response use this in App/Exceptions/Handler.php:

Upto Laravel 7
```php    
use NagadAPI\Exceptions\NagadException;
public function render($request, Exception $exception)
{
    if($exception instanceof NagadException) {
    //return custom error page when custom exception is thrown
        return response()->view('errors.nagad', compact('exception'));
    }

    return parent::render($request, $exception);
}
```
Laravel 8
```php    
use NagadAPI\Exceptions\NagadException;
public function render($request, Throwable $exception)
{
    if($exception instanceof NagadException) {
    //return custom error page when custom exception is thrown
        return response()->view('errors.nagad', compact('exception'));
    }

    return parent::render($request, $exception);
}
```

## Available Methods  
### For Checking-out  
- `setOrderID(string $orderID)` : ``$orderID`` to be any unique AlphaNumeric String
- `setAmount(string $amount)` : ``$amount`` to be any valid currency numeric String
- `setAddionalInfo(array $array)` : ``$array`` to be any array to be returned at callback
- `setCallbackUrl(string $url)` : ``$url`` to be any url string to be overidden the defualt callback url set in config
- `checkout()` : to initiate checkout process.
- `redirect()` : to direct redirect to the NagadPG Web Page.
- `getRedirectUrl()` : instead of redirecting, getting the redirect url manually.

### For Callback 
- `callback($request)` : ``$request`` to be ```Illuminate\Http\Request``` instance
- `verify()` : to verify the response.
- `success()` : to check if transaction is succeed.
- `getErrors()` : to get the error and errorCode if fails transactions | <kbd>returns</kbd> `array[]`
- `getVerifiedResponse()` : to get the full verified response | <kbd>returns</kbd> `array[]`
- `getAdditionalData(bool $object)` : to get the additional info passed during checkout. `$object` is to set return object or array.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
