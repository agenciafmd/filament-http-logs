<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Database\Seeders;

use Agenciafmd\HttpLogs\Database\Factories\HttpLogFactory;
use Agenciafmd\HttpLogs\Models\HttpLog;
use Illuminate\Database\Seeder;

final class HttpLogSeeder extends Seeder
{
    public function run(): void
    {
        HttpLog::query()
            ->truncate();

        HttpLogFactory::new()
            ->count(500)
            ->create();
    }
}
