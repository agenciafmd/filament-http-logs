<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Database\Factories;

use Agenciafmd\HttpLogs\Models\HttpLog;
use Illuminate\Database\Eloquent\Factories\Factory;

final class HttpLogFactory extends Factory
{
    protected $model = HttpLog::class;

    public function definition(): array
    {
        return [
            'url' => fake()->url,
            'method' => fake()->randomElement(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']),
            'request_headers' => [
                'Host' => fake()->domainName,
                'User-Agent' => fake()->userAgent,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Connection' => 'Keep-Alive',
            ],
            'request_body' => [
                'Id' => fake()->randomNumber(8),
                'Customer' => fake()->name,
                'Quantity' => fake()->randomNumber(2),
                'Price' => fake()->randomFloat(2, 10, 100),
            ],
            'status' => fake()->randomElement([100, 200, 301, 404, 500]),
            'response_headers' => [
                'Date' => fake()->dateTime->format('D, d M Y H:i:s') . ' GMT',
                'Content-Type' => 'application/json',
                'Content-Length' => fake()->randomNumber(3),
                'Connection' => 'keep-alive',
                'CF-Cache-Status' => 'DYNAMIC',
                'cf-request-id' => fake()->uuid,
                'Expect-CT' => 'max-age=604800, report-uri="https://report-uri.cloudflare.com/cdn-cgi/beacon/expect-ct"',
                'Report-To' => '{"endpoints":[{"url":"https:\/\/a.nel.cloudflare.com\/report?s=GiQaYeNzr82rB0Hw84dzi4le3fW7ZA2SC%2FgedouW%2FIvYzhvTepsDIwS7FcHJm76FKinYDt8K4klnKlsk24x8Pit%2F9xjAsbR%2FEvca"}],"group":"cf-nel","max_age":604800}',
                'NEL' => '{"report_to":"cf-nel","max_age":604800}',
                'Server' => 'cloudflare',
                'CF-RAY' => '6522f9506be00851-EWR',
                'alt-svc' => 'h3-27=":443"; ma=86400, h3-28=":443"; ma=86400, h3-29=":443"; ma=86400',
            ],
            'response_body' => [
                'success' => 'true',
            ],
        ];
    }
}
