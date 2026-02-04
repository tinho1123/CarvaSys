<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClienteSeeder extends Seeder
{
    /**
     * Seed the application's database with clientes.
     */
    public function run(): void
    {
        // Criar empresas para demonstração
        $empresas = [
            [
                'uuid' => Str::uuid(),
                'name' => 'Empresa Demo Alpha',
                'foundation_date' => '2020-01-15',
                'active' => 'Y',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Empresa Demo Beta',
                'foundation_date' => '2021-06-20',
                'active' => 'Y',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Empresa Demo Gamma',
                'foundation_date' => '2019-11-08',
                'active' => 'Y',
            ],
        ];

        $createdEmpresas = collect();
        foreach ($empresas as $empresaData) {
            $createdEmpresas->push(Company::create($empresaData));
        }

        // Obter a empresa principal (Carvalho Soluções em TI) para o painel admin
        $empresaPrincipal = Company::where('name', 'Carvalho Soluções em TI')->first();

        // Se não existir, criar
        if (!$empresaPrincipal) {
            $empresaPrincipal = Company::create([
                'uuid' => Str::uuid(),
                'name' => 'Carvalho Soluções em TI',
                'foundation_date' => '1999-09-10',
                'active' => 'Y',
            ]);
        }

        // Criar clientes de demonstração
        $clientes = [
            [
                'uuid' => Str::uuid(),
                'company_id' => $empresaPrincipal->id, // Para painel admin
                'name' => 'João Silva',
                'surname' => 'Santos',
                'email' => 'joao.silva@email.com',
                'document_type' => 'cpf',
                'document_number' => '12345678901',
            ],
            [
                'uuid' => Str::uuid(),
                'company_id' => $empresaPrincipal->id, // Para painel admin
                'name' => 'Maria Oliveira',
                'surname' => 'Costa',
                'email' => 'maria.oliveira@email.com',
                'document_type' => 'cpf',
                'document_number' => '98765432100',
            ],
            [
                'uuid' => Str::uuid(),
                'company_id' => $empresaPrincipal->id, // Para painel admin
                'name' => 'Carlos Alberto',
                'surname' => 'Pereira',
                'email' => 'carlos.pereira@email.com',
                'document_type' => 'cpf',
                'document_number' => '45678912345',
            ],
            [
                'uuid' => Str::uuid(),
                'company_id' => $empresaPrincipal->id, // Para painel admin
                'name' => 'Wellington Carvalho',
                'surname' => 'da Cunha Filho',
                'email' => 'wellington.carvalho@email.com',
                'document_type' => 'cpf',
                'document_number' => '13530365700',
            ],
        ];

        $createdClientes = collect();
        foreach ($clientes as $clienteData) {
            $createdClientes->push(Client::create($clienteData));
        }

        // Criar usuários dos clientes
        $usuarios = [
            [
                'uuid' => Str::uuid(),
                'client_id' => $createdClientes[0]->id,
                'email' => 'joao.silva@email.com',
                'password' => Hash::make('senha123'),
                'document_type' => 'cpf',
                'document_number' => '12345678901',
            ],
            [
                'uuid' => Str::uuid(),
                'client_id' => $createdClientes[1]->id,
                'email' => 'maria.oliveira@email.com',
                'password' => Hash::make('senha123'),
                'document_type' => 'cpf',
                'document_number' => '98765432100',
            ],
            [
                'uuid' => Str::uuid(),
                'client_id' => $createdClientes[2]->id,
                'email' => 'carlos.pereira@email.com',
                'password' => Hash::make('senha123'),
                'document_type' => 'cpf',
                'document_number' => '45678912345',
            ],
            [
                'uuid' => Str::uuid(),
                'client_id' => $createdClientes[3]->id,
                'email' => 'wellington.carvalho@email.com',
                'password' => Hash::make('Well.10091999'),
                'document_type' => 'cpf',
                'document_number' => '13530365700',
            ],
        ];

        $createdUsuarios = collect();
        foreach ($usuarios as $usuarioData) {
            $createdUsuarios->push(ClientUser::create($usuarioData));
        }

        // Vincular clientes às empresas
        // João Silva tem acesso à Empresa Alpha e Beta (multi-tenant)
        $createdEmpresas[0]->clients()->attach($createdClientes[0]->id, ['is_active' => true]);
        $createdEmpresas[1]->clients()->attach($createdClientes[0]->id, ['is_active' => true]);

        // Maria Oliveira tem acesso apenas à Empresa Beta
        $createdEmpresas[1]->clients()->attach($createdClientes[1]->id, ['is_active' => true]);

        // Carlos Alberto tem acesso às três empresas (multi-tenant completo)
        $createdEmpresas[0]->clients()->attach($createdClientes[2]->id, ['is_active' => true]);
        $createdEmpresas[1]->clients()->attach($createdClientes[2]->id, ['is_active' => true]);
        $createdEmpresas[2]->clients()->attach($createdClientes[2]->id, ['is_active' => true]);

        // Wellington Carvalho tem acesso às três empresas demo
        $createdEmpresas[0]->clients()->attach($createdClientes[3]->id, ['is_active' => true]);
        $createdEmpresas[1]->clients()->attach($createdClientes[3]->id, ['is_active' => true]);
        $createdEmpresas[2]->clients()->attach($createdClientes[3]->id, ['is_active' => true]);

        $this->command->info('✅ Clientes e empresas criados com sucesso!');
        $this->command->info('📋 Logins de teste:');
        $this->command->info('   João Silva: CPF 123.456.789-01 / senha: senha123 (2 empresas)');
        $this->command->info('   Maria Oliveira: CPF 987.654.321-00 / senha: senha123 (1 empresa)');
        $this->command->info('   Carlos Alberto: CPF 456.789.123-45 / senha: senha123 (3 empresas)');
        $this->command->info('   Wellington Carvalho: CPF 135.303.657-00 / senha: Well.10091999 (3 empresas)');
    }
}
