<?php

namespace Database\Factories;

use App\Enum\BranchType;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'branch_code' => strtoupper($this->faker->lexify('???')),
            'type' => BranchType::DESTINATION->value,
            'timezone' => 'Asia/Colombo',
            'is_primary_warehouse' => false,
        ];
    }
}
