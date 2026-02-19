<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* https://github.com/farayaz/laravel-spy/blob/main/database/migrations/create_spy_http_logs_table.php.stub */
        Schema::create('http_logs', static function (Blueprint $table) {
            $table->id();
            $table->text('url');
            $table->string('method', 6)
                ->index();
            $table->json('request_headers')
                ->nullable();
            $table->json('request_body')
                ->nullable();
            $table->unsignedSmallInteger('status')
                ->nullable()
                ->index();
            $table->json('response_headers')
                ->nullable();
            $table->json('response_body')
                ->nullable();
            $table->timestamps();
        });
    }
};
