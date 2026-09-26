<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Providers;

use Agenciafmd\HttpLogs\Models\HttpLog;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use SimpleXMLElement;

/* source: https://github.com/farayaz/laravel-spy/blob/main/src/LaravelSpy.php */

final class HttpClientServiceProvider extends ServiceProvider
{
    private const string MASK = '#######';

    public function boot(): void
    {
        Http::globalMiddleware(static fn (callable $handler): callable => static function (RequestInterface $request, array $options) use ($handler): mixed {
            if (! config('filament-http-logs.enabled')) {
                return $handler($request, $options);
            }

            $httpLog = self::shouldLog($request) ? self::handleRequest($request) : null;
            $promise = $handler($request, $options);

            if (! $promise instanceof PromiseInterface) {
                return $promise;
            }

            return $promise->then(
                fn (ResponseInterface $response): ResponseInterface => self::handleResponse($response, $httpLog),
                fn (Exception $e): never => self::handleException($e, $httpLog)
            );
        });
    }

    public function register(): void
    {
        //
    }

    private static function parseContent(string $context, string $content, string $contentType = ''): mixed
    {
        if ($content === '') {
            return null;
        }

        if ($contentType !== '') {
            foreach (self::configStrings('filament-http-logs.' . $context . '_body_exclude_content_types') as $excludeType) {
                if (str_contains($contentType, $excludeType)) {
                    return ['content excluded by configuration'];
                }
            }
        }

        $decodedJson = json_decode($content, true);
        if (str_contains($contentType, 'application/json') || $decodedJson !== null) {
            return $decodedJson;
        }

        if (str_contains($contentType, 'application/xml') || str_contains($contentType, 'text/xml')) {
            $xml = self::parseXml($content);
            $json = $xml === false ? false : json_encode($xml);

            return $json === false ? $content : json_decode($json, true);
        }

        if (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($content, $data);

            return $data;
        }

        if (str_contains($contentType, 'multipart/form-data')) {
            return base64_encode($content);
        }

        if ($contentType !== '' && (
            str_contains($contentType, 'image/') ||
            str_contains($contentType, 'video/') ||
            str_contains($contentType, 'application/') ||
            str_contains($contentType, 'audio/')
        )) {
            return base64_encode($content);
        }

        return $content;
    }

    /**
     * Sem os warnings do libxml, que o Laravel converteria em exceção e impediriam o registro do log.
     */
    private static function parseXml(string $content): SimpleXMLElement|false
    {
        $usedInternalErrors = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        libxml_clear_errors();
        libxml_use_internal_errors($usedInternalErrors);

        return $xml;
    }

    private static function obfuscate(mixed $data): mixed
    {
        $obfuscates = self::configStrings('filament-http-logs.hide_fields');
        $fieldMaxLength = self::configInteger('filament-http-logs.field_max_length', 10000);
        $fieldMaxRows = self::configInteger('filament-http-logs.field_max_rows', 1000);

        if (is_array($data)) {
            if ($fieldMaxRows > 0 && count($data) > $fieldMaxRows) {
                $data = Arr::take($data, $fieldMaxRows);
                $data['_spy_truncated'] = true;
            }

            foreach ($data as $k => &$v) {
                foreach ($obfuscates as $key) {
                    if (strcasecmp((string) $k, $key) === 0) {
                        if (is_array($v)) {
                            foreach ($v as &$item) {
                                $item = self::MASK;
                            }
                        } else {
                            $v = self::MASK;
                        }
                    }
                }

                if (is_array($v)) {
                    $v = self::obfuscate($v);
                } elseif (is_string($v)) {
                    $v = Str::limit($v, $fieldMaxLength);
                }
            }
        } elseif (is_string($data)) {
            $data = Str::limit(str_replace($obfuscates, self::MASK, $data), $fieldMaxLength);
        }

        return $data;
    }

    private static function obfuscateUri(UriInterface $uri): string
    {
        parse_str($uri->getQuery(), $query);
        $obfuscatedQuery = self::obfuscate($query);

        return (string) $uri->withQuery(http_build_query(is_array($obfuscatedQuery) ? $obfuscatedQuery : []));
    }

    private static function shouldLog(RequestInterface $request): bool
    {
        return ! Str::contains((string) $request->getUri(), self::configStrings('filament-http-logs.deny_hosts'));
    }

    private static function handleRequest(RequestInterface $request): ?HttpLog
    {
        $requestBody = self::parseContent(
            'request',
            $request->getBody()
                ->getContents(),
            $request->getHeaderLine('Content-Type')
        );

        try {
            return HttpLog::query()->create([
                'url' => urldecode(self::obfuscateUri($request->getUri())),
                'method' => $request->getMethod(),
                'request_headers' => self::obfuscate(self::headers($request)),
                'request_body' => self::obfuscate($requestBody),
            ]);
        } catch (Exception $exception) {
            report($exception); // silence is golden

            return null;
        }
    }

    private static function handleResponse(ResponseInterface $response, ?HttpLog $httpLog): ResponseInterface
    {
        if ($httpLog instanceof HttpLog) {
            try {
                $responseBody = self::parseContent(
                    'response',
                    $response->getBody()->getContents(),
                    $response->getHeaderLine('Content-Type')
                );
                $httpLog->update([
                    'status' => $response->getStatusCode(),
                    'response_body' => self::obfuscate($responseBody),
                    'response_headers' => self::obfuscate(self::headers($response)),
                ]);
            } catch (Exception $e) {
                report($e); // silence is golden
            }
        }

        return $response;
    }

    private static function handleException(Exception $exception, ?HttpLog $httpLog): never
    {
        if ($httpLog instanceof HttpLog) {
            try {
                $httpLog->update([
                    'status' => 0,
                    'response_body' => $exception->getMessage(),
                ]);
            } catch (Exception $e) {
                report($e); // silence is golden
            }
        }

        throw $exception;
    }

    /**
     * @return array<string> nome do cabeçalho => valores separados por vírgula
     */
    private static function headers(MessageInterface $message): array
    {
        return collect($message->getHeaders())
            ->map(static fn (array $values): string => implode(', ', $values))
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function configStrings(string $key): array
    {
        $values = config($key);

        return collect(is_array($values) ? $values : [])
            ->filter(static fn (mixed $value): bool => is_string($value))
            ->values()
            ->all();
    }

    private static function configInteger(string $key, int $default): int
    {
        $value = config($key);

        return is_numeric($value) ? (int) $value : $default;
    }
}
