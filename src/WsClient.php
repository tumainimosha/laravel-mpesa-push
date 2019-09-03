<?php

namespace Tumainimosha\MpesaPush;

class WsClient extends \SoapClient
{
    public static function instance()
    {
        // WSDL
        $wsdl = __DIR__ . '/../files/ussd_push.wsdl';

        // URL
        $url = config('mpesa-push.endpoint');

        // Certificates
        $caFile = config('mpesa-push.ca_file');
        $certFile = config('mpesa-push.ssl_cert');
        $sslKeyFile = config('mpesa-push.ssl_key');
        $sslKeyPasswd = config('mpesa-push.ssl_cert_password');

        $context = stream_context_create([
            'ssl' => [
                'cafile' => $caFile,
                'local_cert' => $certFile,
                'local_pk' => $sslKeyFile,
                'passphrase' => $sslKeyPasswd,
                //'ciphers'=>'AES256-SHA'
            ], ]);

        $client = new self($wsdl, [
            'stream_context' => $context,
            'location' => $url,
            // other options
            'exceptions' => true,
            'trace' => 1,
            'connection_timeout' => 10,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);

        return $client;
    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        logger('Begin SoapClient Request =======================================');

        logger("REQUEST:\n" . $request . "\n");
        logger("LOCATION:\n" . $location . "\n");
        logger("ACTION:\n" . $action . "\n");
        logger("VERSION:\n" . $version . "\n");
        logger("ONE WAY:\n" . $one_way . "\n");

        $response = parent::__doRequest($request, $location, $action, $version, $one_way);

        logger("RESPONSE:\n" . $response . "\n");

        logger('End SoapClient Request =======================================');

        return $response;
    }

    public function execRequest(string $eventID, string $requestXml, string $token = '')
    {
        // Set EventID header
        $headers = [
            new \SoapHeader('http://www.4cgroup.co.za/soapauth', 'EventID', $eventID),
            new \SoapHeader('http://www.4cgroup.co.za/soapauth', 'Token', $token),
        ];

        $this->__setSoapHeaders($headers);

        // Add request as XML
        $request = new \SoapVar($requestXml, \XSD_ANYXML);

        // Make request
        return $this->__call('getGenericResult', [$request]);
    }
}
