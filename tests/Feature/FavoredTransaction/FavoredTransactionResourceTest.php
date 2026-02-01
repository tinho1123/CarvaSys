<?php

namespace Tests\Feature\FavoredTransaction;

use App\Models\Client;
use App\Models\Company;
use App\Models\FavoredTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FavoredTransactionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
        $this->client = Client::factory()->create(['company_id' => $this->company->id]);

        // Associate user with company
        $this->user->companies()->attach($this->company->id);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_render_favored_transactions_list_page()
    {
        $response = $this->get("/admin/{$this->company->id}/favored-transactions");

        $response->assertOk();
    }

    /** @test */
    public function it_can_render_favored_transactions_create_page()
    {
        $response = $this->get("/admin/{$this->company->id}/favored-transactions/create");

        $response->assertOk();
    }

    /** @test */
    public function it_can_render_favored_transactions_edit_page()
    {
        $transaction = FavoredTransaction::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
        ]);

        $response = $this->get("/admin/{$this->company->id}/favored-transactions/{$transaction->uuid}/edit");

        $response->assertOk();
    }

    /** @test */
    public function it_displays_client_relationship_in_table()
    {
        $transaction = FavoredTransaction::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'name' => 'Test Transaction',
            'favored_total' => 100.00,
        ]);

        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\ListFavoredTransactions::class)
            ->assertTableColumnExists('client.name')
            ->assertTableColumnFormattedStateSet('client.name', $this->client->name, $transaction);
    }

    /** @test */
    public function it_displays_remaining_balance_in_table()
    {
        $transaction = FavoredTransaction::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'favored_total' => 100.00,
            'favored_paid_amount' => 30.00,
        ]);

        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\ListFavoredTransactions::class)
            ->assertTableColumnExists('remaining_balance')
            ->assertTableColumnFormattedStateSet('remaining_balance', 70.00, $transaction);
    }

    /** @test */
    public function it_can_create_favored_transaction_via_form()
    {
        $transactionData = [
            'client_id' => $this->client->id,
            'name' => 'New Test Transaction',
            'description' => 'Test description',
            'favored_total' => 150.50,
            'favored_paid_amount' => 0,
            'quantity' => 2,
            'active' => true,
        ];

        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\CreateFavoredTransaction::class)
            ->fillForm($transactionData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('favored_transactions', [
            'name' => 'New Test Transaction',
            'client_id' => $this->client->id,
            'company_id' => $this->company->id,
            'favored_total' => 150.50,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\CreateFavoredTransaction::class)
            ->call('create')
            ->assertHasFormErrors([
                'client_id',
                'name',
                'favored_total',
                'quantity',
            ]);
    }

    /** @test */
    public function it_can_edit_favored_transaction_via_form()
    {
        $transaction = FavoredTransaction::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'name' => 'Original Name',
            'favored_total' => 100.00,
        ]);

        $editData = [
            'client_id' => $this->client->id,
            'name' => 'Updated Name',
            'favored_total' => 200.00,
            'favored_paid_amount' => 50.00,
        ];

        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\EditFavoredTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->fillForm($editData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('favored_transactions', [
            'id' => $transaction->id,
            'name' => 'Updated Name',
            'favored_total' => 200.00,
            'favored_paid_amount' => 50.00,
        ]);
    }

    /** @test */
    public function it_can_delete_favored_transaction()
    {
        $transaction = FavoredTransaction::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\ListFavoredTransactions::class)
            ->callTableAction('delete', $transaction);

        $this->assertModelMissing($transaction);
    }

    /** @test */
    public function it_filters_transactions_by_company()
    {
        // Create transaction for our company
        $companyTransaction = FavoredTransaction::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Company Transaction',
        ]);

        // Create transaction for another company
        $otherCompany = Company::factory()->create();
        $otherTransaction = FavoredTransaction::factory()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Other Company Transaction',
        ]);

        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\ListFavoredTransactions::class)
            ->assertCanSeeTableRecords([$companyTransaction])
            ->assertCannotSeeTableRecords([$otherTransaction]);
    }

    /** @test */
    public function it_shows_money_columns_with_brl_formatting()
    {
        $transaction = FavoredTransaction::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'favored_total' => 1234.56,
            'favored_paid_amount' => 789.10,
        ]);

        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\ListFavoredTransactions::class)
            ->assertTableColumnFormattedStateSet('favored_total', 'R$1.234,56', $transaction)
            ->assertTableColumnFormattedStateSet('favored_paid_amount', 'R$789,10', $transaction);
    }

    /** @test */
    public function it_searches_by_client_name()
    {
        $client1 = Client::factory()->create(['company_id' => $this->company->id, 'name' => 'João Silva']);
        $client2 = Client::factory()->create(['company_id' => $this->company->id, 'name' => 'Maria Souza']);

        $transaction1 = FavoredTransaction::factory()->create(['client_id' => $client1->id]);
        $transaction2 = FavoredTransaction::factory()->create(['client_id' => $client2->id]);

        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\ListFavoredTransactions::class)
            ->searchTable('João')
            ->assertCanSeeTableRecords([$transaction1])
            ->assertCannotSeeTableRecords([$transaction2]);
    }

    /** @test */
    public function it_searches_by_transaction_name()
    {
        $transaction1 = FavoredTransaction::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Venda de Produto A',
        ]);

        $transaction2 = FavoredTransaction::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Venda de Produto B',
        ]);

        Livewire::test(\App\Filament\Resources\FavoredTransactionResource\Pages\ListFavoredTransactions::class)
            ->searchTable('Produto A')
            ->assertCanSeeTableRecords([$transaction1])
            ->assertCannotSeeTableRecords([$transaction2]);
    }
}
