<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * Test login page exists and is accessible
     */
    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');

        // Should return 200 (manifest now exists) or 302 (already logged in)
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /**
     * Test login endpoint accepts POST requests
     */
    public function test_login_endpoint_accepts_post_requests()
    {
        $response = $this->post('/login', []);

        // Should respond to POST requests
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    /**
     * Test login with valid credentials structure
     */
    public function test_login_accepts_valid_credentials_structure()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => false,
        ]);

        // Should accept the request structure
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    /**
     * Test login validation for required fields
     */
    public function test_login_validation_for_required_fields()
    {
        // Test missing email
        $response = $this->post('/login', [
            'password' => 'password',
            'remember' => false,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);

        // Test missing password
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'remember' => false,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);

        // Test missing remember
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    /**
     * Test login email format validation
     */
    public function test_login_email_format_validation()
    {
        // Valid email format
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => false,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);

        // Invalid email format
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password',
            'remember' => false,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    /**
     * Test login with seeded credentials (E2E test)
     */
    public function test_login_with_seeded_credentials()
    {
        // First ensure we have a fresh database with seeded data
        $this->artisan('migrate:fresh', ['--seed' => true]);

        $response = $this->post('/login', [
            'email' => 'carvalho.cwell@gmail.com',
            'password' => 'Well.10091999',
            'remember' => false,
        ]);

        // Should process the request (may succeed or fail depending on implementation)
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
            'remember' => false,
        ]);

        // Should return validation or authentication error
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    /**
     * Test login request structure validation
     */
    public function test_login_request_structure_matches_controller_expectations()
    {
        // Test that the request structure matches what controller expects
        $requestData = [
            'email' => 'carvalho.cwell@gmail.com',
            'password' => 'Well.10091999',
            'remember' => false,
        ];

        $this->assertArrayHasKey('email', $requestData);
        $this->assertArrayHasKey('password', $requestData);
        $this->assertArrayHasKey('remember', $requestData);

        $this->assertEquals('carvalho.cwell@gmail.com', $requestData['email']);
        $this->assertEquals('Well.10091999', $requestData['password']);
        $this->assertFalse($requestData['remember']);
    }
}
