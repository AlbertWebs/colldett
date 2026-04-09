<?php

namespace Database\Seeders;

use App\Models\ContactDetail;
use Illuminate\Database\Seeder;

class ContactDetailSeeder extends Seeder
{
    public function run(): void
    {
        if (ContactDetail::query()->exists()) {
            return;
        }

        $c = config('colldett.company', []);

        ContactDetail::query()->create([
            'phone' => $c['phone'] ?? null,
            'email' => $c['email'] ?? null,
            'address' => $c['address'] ?? null,
            'map_embed_url' => $c['map_embed_url'] ?? null,
        ]);
    }
}
