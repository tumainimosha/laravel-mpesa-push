<?php

namespace Tumainimosha\MpesaPush;

class WsClient extends \SoapClient
{
    public static function instance()
    {
        // WSDL
        $wsdl = __DIR__ . '/../files/ussd_push.wsdl';

        // Certificates
        $sslConfig = [];
        $sslConfig['cafile'] = config('mpesa-push.ca_file');
        $sslConfig['local_cert'] = config('mpesa-push.ssl_cert');
        $sslConfig['local_pk'] = config('mpesa-push.ssl_key');

        $sslPassphrase = config('mpesa-push.ssl_cert_password');
        if(!empty($sslPassphrase)) {
            $sslConfig['passphrase'] = config('mpesa-push.ssl_cert_password');
        }

        $context = stream_context_create([
            'ssl' => $sslConfig,
        ]);

        // URL
        $endpoint = config('mpesa-push.endpoint');

        $soapConfig = [
            'stream_context' => $context,
            'location' => $endpoint,
            // other options
            'exceptions' => true,
            'trace' => 1,
            'connection_timeout' => 10,
            //'cache_wsdl' => WSDL_CACHE_NONE,
        ];

        $client = new self($wsdl, $soapConfig);

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
