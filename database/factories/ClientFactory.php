<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    private ?int $pendingCompanyId = null;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'document_type' => 'cpf',
            'document_number' => $this->faker->unique()->numerify('###########'),
            'phone' => $this->faker->phoneNumber(),
            'active' => true,
        ];
    }

    protected function getRawAttributes(?Model $parent)
    {
        $attributes = parent::getRawAttributes($parent);

        if (isset($attributes['company_id'])) {
            $this->pendingCompanyId = (int) $attributes['company_id'];
            unset($attributes['company_id']);
        }

        return $attributes;
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Client $client) {
            if ($this->pendingCompanyId !== null) {
                $client->companies()->attach($this->pendingCompanyId, ['is_active' => true]);
                $this->pendingCompanyId = null;
            }
        });
    }
}
