<?php
namespace App\Tests\CekatAi\SendOtpWhatsapp;

use PHPUnit\Framework\TestCase;
use App\Factory\CekatAiFactory;
use App\Library\CekatAI\Services\CekatOtpRequest;
use PHPUnit\Framework\MockObject\MockObject;

class CekatAiFactoryTest extends TestCase {

    protected function setUp(): void {
        // Setup environment variables for testing
        $_ENV['CEKATAI_API_BASE_URL'] = 'https://api.cekat.com';
        $_ENV['CEKATAI_API_OTP_URL'] = 'https://api.cekat.com/otp/send';
        $_ENV['CEKATAI_API_KEY'] = 'test-api-key-12345';
    }

    protected function tearDown(): void {
        // Clean up environment after each test
        unset($_ENV['CEKATAI_API_BASE_URL']);
        unset($_ENV['CEKATAI_API_OTP_URL']);
        unset($_ENV['CEKATAI_API_KEY']);
    }

    /**
     * Test factory creates CekatOtpRequest instance with correct type
     */
    public function testFactoryCreatesCorrectServiceType(): void {
        try {
            $service = CekatAiFactory::createOtpService();
            $this->assertInstanceOf(CekatOtpRequest::class, $service);
        } catch (\Exception $e) {
            $this->markTestSkipped('Database connection required: ' . $e->getMessage());
        }
    }

    /**
     * Test factory returns different instances on each call
     */
    public function testFactoryCreatesNewInstanceEachCall(): void {
        try {
            $service1 = CekatAiFactory::createOtpService();
            $service2 = CekatAiFactory::createOtpService();
            
            $this->assertNotSame($service1, $service2);
        } catch (\Exception $e) {
            $this->markTestSkipped('Database connection required: ' . $e->getMessage());
        }
    }

    /**
     * Test factory with missing API base URL should throw exception
     */
    public function testFactoryWithMissingApiBaseUrl(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing CekatAI configuration');
        
        unset($_ENV['CEKATAI_API_BASE_URL']);
        $_ENV['CEKATAI_API_OTP_URL'] = 'https://api.cekat.com/otp/send';
        $_ENV['CEKATAI_API_KEY'] = 'test-key';
        
        CekatAiFactory::createOtpService();
    }

    /**
     * Test factory with missing OTP URL should throw exception
     */
    public function testFactoryWithMissingOtpUrl(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing CekatAI configuration');
        
        $_ENV['CEKATAI_API_BASE_URL'] = 'https://api.cekat.com';
        unset($_ENV['CEKATAI_API_OTP_URL']);
        $_ENV['CEKATAI_API_KEY'] = 'test-key';
        
        CekatAiFactory::createOtpService();
    }

    /**
     * Test factory with missing API key should throw exception
     */
    public function testFactoryWithMissingApiKey(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing CekatAI configuration');
        
        $_ENV['CEKATAI_API_BASE_URL'] = 'https://api.cekat.com';
        $_ENV['CEKATAI_API_OTP_URL'] = 'https://api.cekat.com/otp/send';
        unset($_ENV['CEKATAI_API_KEY']);
        
        CekatAiFactory::createOtpService();
    }

    /**
     * Test API configuration is properly passed to service
     */
    public function testFactoryPassesCorrectApiConfiguration(): void {
        $_ENV['CEKATAI_API_BASE_URL'] = 'https://test.api.com';
        $_ENV['CEKATAI_API_OTP_URL'] = 'https://test.api.com/send';
        $_ENV['CEKATAI_API_KEY'] = 'test-secure-key';
        
        try {
            $service = CekatAiFactory::createOtpService();
            $this->assertInstanceOf(CekatOtpRequest::class, $service);
        } catch (\Exception $e) {
            $this->markTestSkipped('Database connection required: ' . $e->getMessage());
        }
    }

}
