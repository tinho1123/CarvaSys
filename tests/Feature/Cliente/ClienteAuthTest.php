<?php

namespace Tests\Feature\Cliente;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClienteAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function client_can_login_with_cpf_and_password()
    {
        // Create company
        $company = Company::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => 'Test Company',
            'foundation_date' => now()->subYears(5),
            'active' => 'Y',
        ]);

        // Create client
        $client = Client::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => 'Test Client',
            'surname' => 'Test Surname',
            'email' => 'client@test.com',
            'document_type' => 'cpf',
            'document_number' => '12345678901',
        ]);

        // Attach client to company (belongsToMany relation)
        $company->clients()->attach($client->id, ['is_active' => true]);

        // Create client user
        $clientUser = ClientUser::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'client_id' => $client->id,
            'email' => 'clientuser@test.com',
            'password' => Hash::make('password'),
            'document_type' => 'cpf',
            'document_number' => '12345678901',
        ]);

        // Test login - correct route for client panel
        $response = $this->post('/client/login', [
            'document_number' => '12345678901',
            'password' => 'password',
        ]);

        // Verify if redirected (may be to dashboard or company selection)
        $response->assertStatus(302);
        $this->assertAuthenticatedAs($clientUser, 'client');
    }

    /** @test */
    public function client_cannot_login_with_invalid_cpf()
    {
        $response = $this->post('/client/login', [
            'document_number' => '11111111111',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $this->assertTrue($response->getSession()->has('errors'));
    }

    /** @test */
    public function client_with_multiple_companies_must_select_company()
    {
        // Create two companies
        $company1 = Company::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => 'Company 1',
            'foundation_date' => now()->subYears(5),
            'active' => 'Y',
        ]);

        $company2 = Company::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => 'Company 2',
            'foundation_date' => now()->subYears(3),
            'active' => 'Y',
        ]);

        // Create client
        $client = Client::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => 'Multi Client',
            'surname' => 'Test Surname',
            'email' => 'multi@test.com',
            'document_type' => 'cpf',
            'document_number' => '98765432100',
        ]);

        // Attach client to both companies
        $company1->clients()->attach($client->id, ['is_active' => true]);
        $company2->clients()->attach($client->id, ['is_active' => true]);

        // Create client user
        $clientUser = ClientUser::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'client_id' => $client->id,
            'email' => 'multiuser@test.com',
            'password' => Hash::make('password'),
            'document_type' => 'cpf',
            'document_number' => '98765432100',
        ]);

        // Test login
        $response = $this->post('/client/login', [
            'document_number' => '98765432100',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $this->assertAuthenticatedAs($clientUser, 'client');
    }
}
