<?php

use App\Support\AdminAccess;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:set-pin {pin : The panel PIN (min 4 characters)}', function (string $pin): int {
    if (strlen($pin) < 4) {
        $this->error('PIN must be at least 4 characters.');

        return 1;
    }

    AdminAccess::setPanelPin($pin);
    $this->info('Admin panel PIN saved. You can sign in at /admin and change it under Settings → Security.');

    return 0;
})->purpose('Set or reset the admin panel PIN (stored on server, not in .env)');
