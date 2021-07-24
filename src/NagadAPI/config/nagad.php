<?php

return [

    /**
     * Nagad Payment Gateway Sandbox Mode
     * use 'true' to enable Test Payments
     * use 'false' to enable Live Payments
     */

   'domain' => [

        /**
         * Domains for Live and Sandbox Mode
         * Do not change unless Nagad PG Updates their BaseURL
         */
        'sandbox'   => env('NAGAD_BASEURL', "http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs"),
        'live'      => env('NAGAD_BASEURL', "https://api.mynagad.com/api/dfs")
    ],
    'endpoints' => [
        /**
         * Endpoints for Live and Sandbox Mode
         * Do not change unless Nagad PG Updates their Api Endpoints
         */
        'checkout-init'         => '/check-out/initialize/',
        'checkout-complete'     => '/check-out/complete/',
        'payment-verify'        => '/verify/payment/',
    ],

];