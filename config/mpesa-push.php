<?php

return [

    /**
     * Request URL.
     */
    'endpoint' => 'https://broker2.ipg.tz.vodafone.com:30010/iPG/b2c/ussd_push',

    /**
     * Path to Vodacom Certificate Authority (CA) Root certificate.
     * Certificate used to validate Mpesa server's HTTPS certificate.
     */
    'ca_file' => storage_path('certificates/mpesa/root.pem'),

    /**
     * Path to your SSL client certificate
     * Certificate used to authenticate ourselves to Mpesa servers.
     */
    'ssl_cert' => storage_path('certificates/mpesa/project.vodacom.co.tz.pem'),

    /**
     * Path to your SSL client private key
     * Private key used to authenticate ourselves to Mpesa servers.
     */
    'ssl_key' => storage_path('certificates/mpesa/project.vodacom.co.tz.key'),

    /**
     * SSL client certificate passphrase
     * Password used to decrypt ssl certificate.
     */
    'ssl_cert_password' => env('TZ_MPESA_PUSH_SSL_CERT_PASSWORD'),


    /**
     * Control WSDL_CACHE option of SoapClient. During development you may
     * disable caching to debug certain errors. However, in production
     * it is advisable to cache the wsdl file for performance.
     */
    'cache_wsdl' => env('TZ_MPESA_PUSH_CACHE_WSDL', true),


    /**
     * Default channel options
     */
    'default' => [
        'username' => env('TZ_MPESA_PUSH_USERNAME'),
        'password' => env('TZ_MPESA_PUSH_PASSWORD'),

        'businessName' => env('TZ_MPESA_PUSH_BUSINESS_NAME'),
        'businessNumber' => env('TZ_MPESA_PUSH_BUSINESS_NUMBER'),

        'currency' => 'TSH',
        'command' => 'customerPayBill',
        'callbackChannel' => '1',
        'callbackUrl' => env('TZ_MPESA_PUSH_CALLBACK_URL'),
    ],
];
