<?php

namespace Tumainimosha\MpesaPush;

use Illuminate\Support\Arr;
use Tumainimosha\MpesaPush\Exceptions\AuthException;
use Tumainimosha\MpesaPush\Exceptions\RemoteSystemError;
use Tumainimosha\MpesaPush\Models\MpesaPushTransaction as Transaction;

class MpesaPush
{
    /**
     * @var WsClient
     */
    protected $wsClient;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param mixed $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    protected function getOptions(): array
    {
        return $this->options;
    }

    /**
     * MpesaPush constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->wsClient = app(WsClient::class);

        $defaultOptions = config('mpesa-push.default') ?? [];
        $this->options = array_merge($defaultOptions, $options);
    }

    /**
     * @param array $options
     * @return MpesaPush
     */
    public static function instance($options = [])
    {
        return new self($options);
    }

    public function setUsername(string $username)
    {
        $this->options['username'] = $username;

        return $this;
    }

    public function setPassword(string $password)
    {
        $this->options['password'] = $password;

        return $this;
    }

    public function setBusinessName(string $businessName)
    {
        $this->options['businessName'] = $businessName;

        return $this;
    }

    public function setBusinessNumber(string $businessNumber)
    {
        $this->options['businessNumber'] = $businessNumber;

        return $this;
    }

    public function setCommand(string $command)
    {
        $this->options['command'] = $command;

        return $this;
    }

    public function setCallbackChannel(string $callbackChannel)
    {
        $this->options['callbackChannel'] = $callbackChannel;

        return $this;
    }

    public function setCallbackUrl(string $callbackUrl)
    {
        $this->options['callbackUrl'] = $callbackUrl;

        return $this;
    }

    /**
     * @return null|string
     * @throws AuthException
     * @throws RemoteSystemError
     */
    protected function login(): ?string
    {
        $client = $this->wsClient;

        $xml = $this->buildLoginRequestXml();

        // login Request
        $response = $client->execRequest('2500', $xml);

        $eventInfo = $response->eventInfo;

        if ((int) $eventInfo->code !== 3) {
            $errorMsg = 'Login failed! Request Processing failed error.';
            logger($errorMsg);

            throw new RemoteSystemError($errorMsg);
        }

        $dataItem = $response->response->dataItem;

        if ((string) $dataItem->value === 'Invalid Credentials') {
            $errorMsg = 'Login failed! Invalid Credentials for Mpesa Push.';
            logger($errorMsg);

            throw new AuthException($errorMsg);
        }

        return (string) $dataItem->value;
    }

    /**
     * @param $customerMsisdn
     * @param $amount
     * @param $thirdPartyReference
     * @return string The ResponseCode field returned in Mpesa response.
     * @throws AuthException
     * @throws RemoteSystemError
     */
    public function postRequest(string $customerMsisdn, float $amount, string $thirdPartyReference)
    {
        $xml = $this->buildTransactionRequestXml($customerMsisdn, $amount, $thirdPartyReference);

        // Get Auth token
        $token = $this->login();

        $response = $this->wsClient->execRequest('40009', $xml, $token);

        $eventInfo = $response->eventInfo;

        if ((int) $eventInfo->code === 3) {

            // Save txn
            $this->saveTransactionToDb($customerMsisdn, $amount, $thirdPartyReference);

            $dataItems = $response->response->dataItem;

            foreach ($dataItems as $dataItem) {
                if ((string) $dataItem->name === 'ResponseCode') {
                    return (string) $dataItem->value;
                }
            }

            $errorMsg = 'ResponseCode not found in response';
            logger($errorMsg);

            throw new RemoteSystemError($errorMsg);
        }

        $errorMsg = 'Transaction processing failed';
        logger($errorMsg);

        throw new RemoteSystemError($errorMsg);
    }

    /**
     * @param $customerMsisdn
     * @param $amount
     * @param $thirdPartyReference
     * @return string
     */
    protected function buildTransactionRequestXml($customerMsisdn, $amount, $thirdPartyReference): string
    {
        // Get Set options
        $options = $this->getOptions();

        $username = Arr::get($options, 'username');

        $businessName = Arr::get($options, 'businessName');
        $businessNumber = Arr::get($options, 'businessNumber');

        $currency = Arr::get($options, 'currency');
        $command = Arr::get($options, 'command');
        $callbackChannel = Arr::get($options, 'callbackChannel');
        $callbackUrl = Arr::get($options, 'callbackUrl');

        // Add Txn date
        $txnDate = now()->format('YmdH');

        $xml = <<<XML
                <Request>
                    <dataItem>
                        <name>CustomerMSISDN</name>
                        <type>String</type>
                        <value>$customerMsisdn</value>
                    </dataItem>
                    <dataItem>
                        <name>BusinessName</name>
                        <type>String</type>
                        <value>$businessName</value>
                    </dataItem>
                    <dataItem>
                        <name>BusinessNumber</name>
                        <type>String</type>
                        <value>$businessNumber</value>
                    </dataItem>
                    <dataItem>
                        <name>Currency</name>
                        <type>String</type>
                        <value>$currency</value>
                    </dataItem>
                    <dataItem>
                        <name>Date</name>
                        <type>String</type>
                        <value>$txnDate</value>
                    </dataItem>
                    <dataItem>
                        <name>Amount</name>
                        <type>String</type>
                        <value>$amount</value>
                    </dataItem>
                    <dataItem>
                        <name>ThirdPartyReference</name>
                        <type>String</type>
                        <value>$thirdPartyReference</value>
                    </dataItem>
                    <dataItem>
                        <name>Command</name>
                        <type>String</type>
                        <value>$command</value>
                    </dataItem>
                    <dataItem>
                        <name>CallBackChannel</name>
                        <type>String</type>
                        <value>$callbackChannel</value>
                    </dataItem>
                    <dataItem>
                        <name>CallbackDestination</name>
                        <type>String</type>
                        <value>$callbackUrl</value>
                    </dataItem>
                    <dataItem>
                        <name>Username</name>
                        <type>String</type>
                        <value>$username</value>
                    </dataItem>
                </Request>
XML;

        return $xml;
    }

    /**
     * @return string
     */
    protected function buildLoginRequestXml(): string
    {
        // Get Set options
        $options = $this->getOptions();

        $username = Arr::get($options, 'username');
        $password = Arr::get($options, 'password');

        $xml = <<<XML
                 <Request>
                    <dataItem>
                       <name>Username</name>
                       <type>String</type>
                       <value>$username</value>
                    </dataItem>
                    <dataItem>
                       <name>Password</name>
                       <type>String</type>
                       <value>$password</value>
                    </dataItem>
                 </Request>
XML;

        return $xml;
    }

    protected function saveTransactionToDb(string $customerMsisdn, float $amount, string $thirdPartyReference)
    {
        $txn = Transaction::query()
            ->create([
                'customer_msisdn' => $customerMsisdn,
                'amount' => $amount,
                'reference' => $thirdPartyReference,
                'business_number' => $this->options['businessNumber'],
                //'mpesa_transaction_id',
            ]);
    }
}
