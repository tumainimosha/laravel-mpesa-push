<?php

namespace Tumainimosha\MpesaPush\Tests;

use PHPUnit\Framework\TestCase;
use Tumainimosha\MpesaPush\Exceptions\AuthException;
use Tumainimosha\MpesaPush\MpesaPush;

/**
 * sessionIdFrom() only reads the decoded SOAP response, so no Laravel bootstrap is needed.
 */
class SessionIdTest extends TestCase
{
    private function response($dataItem): \stdClass
    {
        return (object) ['response' => (object) ['dataItem' => $dataItem]];
    }

    private function item(string $name, ?string $value = null): \stdClass
    {
        $item = (object) ['name' => $name, 'type' => 'String'];

        // SoapClient omits the property entirely when <value> is absent.
        if ($value !== null) {
            $item->value = $value;
        }

        return $item;
    }

    public function test_returns_the_session_id(): void
    {
        $this->assertSame('abc123', MpesaPush::sessionIdFrom($this->response($this->item('SessionId', 'abc123'))));
    }

    public function test_picks_the_session_id_item_out_of_several(): void
    {
        $response = $this->response([
            $this->item('Other', 'x'),
            $this->item('SessionId', 'abc123'),
        ]);

        $this->assertSame('abc123', MpesaPush::sessionIdFrom($response));
    }

    public function test_a_session_id_without_a_value_is_a_failed_login(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('no SessionId');

        MpesaPush::sessionIdFrom($this->response($this->item('SessionId')));
    }

    public function test_an_empty_value_is_a_failed_login(): void
    {
        $this->expectException(AuthException::class);

        MpesaPush::sessionIdFrom($this->response($this->item('SessionId', '  ')));
    }

    public function test_a_response_with_no_data_items_is_a_failed_login(): void
    {
        $this->expectException(AuthException::class);

        MpesaPush::sessionIdFrom((object) ['response' => (object) []]);
    }

    public function test_invalid_credentials_is_still_reported_as_such(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Invalid Credentials');

        MpesaPush::sessionIdFrom($this->response($this->item('SessionId', 'Invalid Credentials')));
    }
}
