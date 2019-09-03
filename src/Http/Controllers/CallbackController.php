<?php

namespace Tumainimosha\MpesaPush\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Tumainimosha\MpesaPush\CallbackService;

class CallbackController extends Controller
{
    public function __invoke(Request $request, CallbackService $wsService)
    {
        // WSDL
        $wsdl = __DIR__ . '/../../../files/ussd_push.wsdl';

        $soapServer = new \SoapServer($wsdl);
        $soapServer->setObject($wsService);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');

        ob_start();
        $soapServer->handle();
        $response->setContent(ob_get_clean());

        return $response;
    }
}
