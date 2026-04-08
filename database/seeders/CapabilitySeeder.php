<?php

namespace Database\Seeders;

use App\Models\Capability;
use Illuminate\Database\Seeder;

class CapabilitySeeder extends Seeder
{
    public function run(): void
    {
        $services = collect(config('colldett.services'))->values();

        foreach ($services as $index => $service) {
            Capability::query()->updateOrCreate(
                ['slug' => $service['slug']],
                [
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'details' => null,
                    'featured' => ! empty($service['featured']),
                    'coming_soon' => ! empty($service['coming_soon']),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
