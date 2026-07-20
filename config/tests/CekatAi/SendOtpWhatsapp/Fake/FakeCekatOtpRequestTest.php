<?php
namespace App\Tests\CekatAi\SendOtpWhatsapp\Fake;

use PHPUnit\Framework\TestCase;
use App\Library\CekatAI\ApiConfig;

/**
 * Unit test untuk FakeCekatOtpRequest
 * 
 * Memastikan fake class berfungsi dengan benar untuk testing CI/CD
 * tanpa consume API production
 */
class FakeCekatOtpRequestTest extends TestCase {

    private ?FakeCekatOtpRequest $fake = null;
    private $mockDb;
    private $mockApiConfig;

    protected function setUp(): void {
        $this->mockDb = $this->createMock(\mysqli::class);
        $this->mockApiConfig = $this->createMock(ApiConfig::class);
        
        $this->mockApiConfig->apiBaseUrl = 'https://api.cekat.com';
        $this->mockApiConfig->apiOtpUrl = 'https://api.cekat.com/otp/send';
        $this->mockApiConfig->apiKey = 'test-api-key-fake';
        
        $this->fake = new FakeCekatOtpRequest($this->mockApiConfig, $this->mockDb);
    }

    protected function tearDown(): void {
        $this->fake = null;
        $this->mockDb = null;
        $this->mockApiConfig = null;
    }

    // ==================== Mock Response Tests ====================

    /**
     * Test default mock response
     */
    public function testDefaultMockResponse(): void {
        $result = $this->fake->sendOtp('123456', 'Test User', '628123456789', '62');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('messsage', $result);
        $this->assertEquals('Workflow was started', $result['messsage']);
    }

    /**
     * Test custom mock response can be set
     */
    public function testSetCustomMockResponse(): void {
        $customResponse = '{"status":"success","code":200}';
        $this->fake->setMockResponse($customResponse);
        
        $result = $this->fake->sendOtp('123456', 'Test User', '628123456789', '62');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals(200, $result['code']);
    }

    /**
     * Test mock response with complex nested data
     */
    public function testComplexMockResponse(): void {
        $complexResponse = json_encode([
            'status' => 'sent',
            'data' => [
                'id' => '12345',
                'phone' => '628123456789',
                'timestamp' => '2026-02-11T12:00:00Z'
            ]
        ]);
        
        $this->fake->setMockResponse($complexResponse);
        $result = $this->fake->sendOtp('654321', 'User', '08123456789', '62');
        
        $this->assertIsArray($result);
        $this->assertEquals('sent', $result['status']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('12345', $result['data']['id']);
    }

    /**
     * Test setMockResponse returns fluent interface (chainable)
     */
    public function testSetMockResponseChainable(): void {
        $response = $this->fake
            ->setMockResponse('{"test":"value"}')
            ->sendOtp('111111', 'Chain Test', '628123456789', '62');
        
        $this->assertIsArray($response);
        $this->assertEquals('value', $response['test']);
    }

    // ==================== Error Mode Tests ====================

    /**
     * Test error mode throws exception
     */
    public function testErrorModeThrowsException(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API Service Unavailable');
        
        $this->fake->setErrorMode('API Service Unavailable');
        $this->fake->sendOtp('123456', 'Test', '628123456789', '62');
    }

    /**
     * Test disable error mode
     */
    public function testDisableErrorMode(): void {
        $this->fake->setErrorMode('Should fail');
        $this->fake->disableErrorMode();
        
        // Should not throw after disabling
        $result = $this->fake->sendOtp('123456', 'Test', '628123456789', '62');
        
        $this->assertIsArray($result);
    }

    /**
     * Test error mode chainable
     */
    public function testErrorModeChainable(): void {
        $this->expectException(\Exception::class);
        
        $this->fake
            ->setErrorMode('Network timeout')
            ->sendOtp('123456', 'Test', '628123456789', '62');
    }

    /**
     * Test disable error mode chainable
     */
    public function testDisableErrorModeChainable(): void {
        $this->fake
            ->setErrorMode('Temporary error')
            ->disableErrorMode();
        
        // Should succeed
        $result = $this->fake->sendOtp('123456', 'Test', '628123456789', '62');
        $this->assertIsArray($result);
    }

    // ==================== Payload Tests ====================

    /**
     * Test last payload is captured
     */
    public function testLastPayloadCaptured(): void {
        $this->fake->sendOtp('123456', 'John Doe', '628123456789', '62');
        
        $payload = $this->fake->getLastPayload();
        
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('phone_number', $payload);
        $this->assertArrayHasKey('otp_code', $payload);
        $this->assertArrayHasKey('name', $payload);
    }

    /**
     * Test payload contains correct phone number
     */
    public function testPayloadPhoneNumber(): void {
        $this->fake->sendOtp('123456', 'Test', '628123456789', '62');
        
        $payload = $this->fake->getLastPayload();
        
        $this->assertEquals('628123456789', $payload['phone_number']);
    }

    /**
     * Test payload with phone starting with 0 converts to country code
     */
    public function testPayloadPhoneConversion(): void {
        $this->fake->sendOtp('123456', 'Test', '0812345678', '62');
        
        $payload = $this->fake->getLastPayload();
        
        // Should convert 0 to 62 (removes first 0 and adds 62)
        $this->assertEquals('62812345678', $payload['phone_number']);
    }

    /**
     * Test payload phone with special characters removed
     */
    public function testPayloadPhoneSpecialCharactersRemoved(): void {
        $this->fake->sendOtp('123456', 'Test', '62-(812)-345-6789', '62');
        
        $payload = $this->fake->getLastPayload();
        
        $this->assertEquals('628123456789', $payload['phone_number']);
    }

    /**
     * Test payload OTP code
     */
    public function testPayloadOtpCode(): void {
        $otpCode = '999888';
        $this->fake->sendOtp($otpCode, 'Test', '628123456789', '62');
        
        $payload = $this->fake->getLastPayload();
        
        $this->assertEquals($otpCode, $payload['otp_code']);
    }

    /**
     * Test payload name
     */
    public function testPayloadName(): void {
        $name = 'Budi Santoso';
        $this->fake->sendOtp('123456', $name, '628123456789', '62');
        
        $payload = $this->fake->getLastPayload();
        
        $this->assertEquals($name, $payload['name']);
    }

    /**
     * Test multiple calls update last payload
     */
    public function testMultipleCallsUpdatePayload(): void {
        // First call
        $this->fake->sendOtp('111111', 'User1', '628111111111', '62');
        $payload1 = $this->fake->getLastPayload();
        
        // Second call
        $this->fake->sendOtp('222222', 'User2', '628222222222', '62');
        $payload2 = $this->fake->getLastPayload();
        
        // Payloads should be different
        $this->assertNotEquals($payload1['otp_code'], $payload2['otp_code']);
        $this->assertNotEquals($payload1['name'], $payload2['name']);
        $this->assertEquals('222222', $payload2['otp_code']);
        $this->assertEquals('User2', $payload2['name']);
    }

    // ==================== Phone Validation Tests ====================

    /**
     * Test empty phone throws exception
     */
    public function testEmptyPhoneThrowsException(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Nomor Telepon tidak boleh kosong');
        
        $this->fake->sendOtp('123456', 'Test', '', '62');
    }

    /**
     * Test null phone throws exception
     */
    public function testNullPhoneThrowsException(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Nomor Telepon tidak boleh kosong');
        
        $this->fake->sendOtp('123456', 'Test', null, '62');
    }

    /**
     * Test short phone throws exception
     */
    public function testShortPhoneThrowsException(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Panjang Nomor Telepon tidak valid');
        
        $this->fake->sendOtp('123456', 'Test', '62123', '62');
    }

    /**
     * Test plus prefix removed
     */
    public function testPlusPrefixRemoved(): void {
        $this->fake->sendOtp('123456', 'Test', '+628123456789', '62');
        
        $payload = $this->fake->getLastPayload();
        
        // Plus should be removed
        $this->assertStringNotContainsString('+', $payload['phone_number']);
        $this->assertEquals('628123456789', $payload['phone_number']);
    }

    /**
     * Test phone with no country code gets one added
     */
    public function testCountryCodeAdded(): void {
        $this->fake->sendOtp('123456', 'Test', '8123456789', '62');
        
        $payload = $this->fake->getLastPayload();
        
        // Should have country code added
        $this->assertTrue(strpos($payload['phone_number'], '62') === 0);
    }

    // ==================== Logging Tests ====================

    /**
     * Test that log is generated after sendOtp
     */
    public function testLogIsGenerated(): void {
        $this->fake->sendOtp('123456', 'Test', '628123456789', '62');
        
        $logString = $this->fake->getLogString();
        
        $this->assertNotEmpty($logString);
        $this->assertIsString($logString);
    }

    /**
     * Test log contains validation message
     */
    public function testLogContainsValidationMessage(): void {
        $this->fake->sendOtp('123456', 'Test', '628123456789', '62');
        
        $logString = $this->fake->getLogString();
        
        $this->assertStringContainsString('Validating phone number', $logString);
    }

    /**
     * Test log contains sending message
     */
    public function testLogContainsSendingMessage(): void {
        $this->fake->sendOtp('123456', 'Test', '628123456789', '62');
        
        $logString = $this->fake->getLogString();
        
        $this->assertStringContainsString('Sending OTP', $logString);
    }

    /**
     * Test log contains error message in error mode
     */
    public function testLogContainsErrorMessage(): void {
        $this->fake->setErrorMode('Test error');
        
        try {
            $this->fake->sendOtp('123456', 'Test', '628123456789', '62');
        } catch (\Exception $e) {
            // Expected
        }
        
        $logString = $this->fake->getLogString();
        
        $this->assertStringContainsString('Error', $logString);
    }

}
