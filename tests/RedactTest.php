<?php

namespace Tumainimosha\MpesaPush\Tests;

use PHPUnit\Framework\TestCase;
use Tumainimosha\MpesaPush\WsClient;

class RedactTest extends TestCase
{
    public function test_masks_credentials_in_a_login_request(): void
    {
        $xml = '<Request><dataItem><name>Username</name><type>String</type><value>user_PUSH</value></dataItem>'
            . '<dataItem><name>Password</name><type>String</type><value>s3cret</value></dataItem></Request>';

        $redacted = WsClient::redact($xml);

        $this->assertStringNotContainsString('user_PUSH', $redacted);
        $this->assertStringNotContainsString('s3cret', $redacted);
        $this->assertStringContainsString('<name>Username</name><type>String</type><value>***</value>', $redacted);
    }

    public function test_masks_multiline_items_and_the_session_id(): void
    {
        $xml = "<dataItem>\n    <name>SessionId</name>\n    <type>String</type>\n    <value>tok-999</value>\n</dataItem>";

        $this->assertStringNotContainsString('tok-999', WsClient::redact($xml));
    }

    public function test_masks_the_soap_token_header(): void
    {
        $xml = '<SOAP-ENV:Header><ns1:Token>tok-999</ns1:Token></SOAP-ENV:Header>';

        $this->assertSame('<SOAP-ENV:Header><ns1:Token>***</ns1:Token></SOAP-ENV:Header>', WsClient::redact($xml));
    }

    public function test_leaves_other_fields_alone(): void
    {
        $xml = '<dataItem><name>CustomerMSISDN</name><type>String</type><value>255700000000</value></dataItem>';

        $this->assertSame($xml, WsClient::redact($xml));
    }

    public function test_an_item_with_no_value_is_untouched(): void
    {
        $xml = '<dataItem><name>SessionId</name><type>String</type></dataItem>';

        $this->assertSame($xml, WsClient::redact($xml));
    }
}
