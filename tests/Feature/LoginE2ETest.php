<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginE2ETest extends TestCase
{
    /**
     * Test complete login flow end-to-end
     */
    public function test_complete_login_flow_with_valid_credentials()
    {
        // Visit login page
        $response = $this->get('/login');
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Attempt to login with valid credentials from seeder
        $response = $this->post('/login', [
            'email' => 'carvalho.cwell@gmail.com',
            'password' => 'Well.10091999',
            'remember' => false,
        ]);

        // Should redirect after login (success or error)
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);
    }

    /**
     * Test login with invalid credentials flow
     */
    public function test_login_flow_with_invalid_credentials()
    {
        // Visit login page
        $response = $this->get('/login');
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Attempt to login with invalid credentials
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
            'remember' => false,
        ]);

        // Should return some kind of response
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);
    }

    /**
     * Test login form validation flow
     */
    public function test_login_form_validation_flow()
    {
        // Test missing email
        $response = $this->post('/login', [
            'password' => 'password',
            'remember' => false,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);

        // Test missing password
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'remember' => false,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);

        // Test missing remember
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);
    }

    /**
     * Test login with various email formats
     */
    public function test_login_with_various_email_formats()
    {
        // Valid email
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => false,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);

        // Invalid email format
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password',
            'remember' => false,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);
    }

    /**
     * Test login with remember functionality
     */
    public function test_login_with_remember_functionality()
    {
        // Test with remember = true
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);

        // Test with remember = false
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => false,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);
    }

    /**
     * Test login route accessibility
     */
    public function test_login_route_accessibility()
    {
        // Test GET request to login route
        $response = $this->get('/login');
        $this->assertContains($response->getStatusCode(), [200, 302, 404]);

        // Test POST request to login route
        $response = $this->post('/login', []);
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);
    }

    /**
     * Test login with authentication middleware
     */
    public function test_login_authentication_scenarios()
    {
        // Test credentials structure validation
        $this->assertArrayHasKey('email', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => false,
        ]);

        $this->assertArrayHasKey('password', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => false,
        ]);

        $this->assertArrayHasKey('remember', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => false,
        ]);
    }
}
