# Filament – Http Logs

[![Downloads](https://img.shields.io/packagist/dt/agenciafmd/filament-http-logs.svg?style=flat-square)](https://packagist.org/packages/agenciafmd/filament-http-logs)
[![Licença](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Pacote de logs de integração para o painel administrativo (Admix). Registra automaticamente as requisições feitas pelo HTTP Client do Laravel (URL, método, cabeçalhos, corpo enviado, status e resposta) e as exibe no painel para consulta.

## Requisitos

- PHP ^8.4
- Laravel ^12.0 | ^13.0
- Filament ^5.0
- agenciafmd/filament-admix v1.x-dev | dev-master

## Instalação

1. Instale o pacote via Composer:

```bash
composer require agenciafmd/filament-http-logs
```

2. Execute as migrações:

```bash
php artisan migrate
```

3. Populando o banco com dados de testes

Adicione o seeder no `database/seeders/DatabaseSeeder.php`:

```php
use Agenciafmd\HttpLogs\Database\Seeders\HttpLogSeeder;

$this->call([
    HttpLogSeeder::class,
]);
```

Ou rode o seeder manualmente:

```bash
php artisan db:seed --class="Agenciafmd\HttpLogs\Database\Seeders\HttpLogSeeder"
```

## Ativando no painel

O pacote inclui o plugin `HttpLogsPlugin`, que registra o `HttpLogResource`. Adicione-o na config do Admix `config/filament-admix.php`:

```php
use Agenciafmd\HttpLogs\HttpLogsPlugin;

return [
    'plugins' => [
        HttpLogsPlugin::class,
    ],
];
```

Após isso, o menu **Logs de integração** aparecerá no painel, com a listagem (filtros por método, origem da URL, status e período), a visualização de cada log e a exclusão em lote.

## Configuração

Arquivo: `config/filament-http-logs.php`

```php
return [
    'name' => 'Logs de integração',
    'navigation_group' => null,
    'navigation_sort' => 1001,
    'enabled' => env('FILAMENT_HTTP_LOGS_ENABLED', true),
    'deny_hosts' => [
        'https://fonts.gstatic.com',
        'https://generativelanguage.googleapis.com',
    ],
    'hide_fields' => [
        'password',
        'token',
    ],
    'keep_days' => (int) env('FILAMENT_HTTP_LOGS_KEEP_DAYS', 120),
    'request_body_exclude_content_types' => [
        'video/',
        'audio/',
        'application/pdf',
        'application/zip',
        'application/x-zip-compressed',
        'application/octet-stream',
    ],
    'response_body_exclude_content_types' => [
        'video/',
        'audio/',
        'application/pdf',
        'application/zip',
        'application/x-zip-compressed',
        'application/octet-stream',
    ],
    'show_request_headers' => false,
    'show_response_headers' => false,
];
```

| Chave | Padrão | Descrição |
|---|---|---|
| `name` | `Logs de integração` | Nome do pacote. |
| `navigation_group` | `null` | Grupo do menu em que o Resource aparece. |
| `navigation_sort` | `1001` | Posição do item no menu. |
| `enabled` | `true` | Liga/desliga o registro dos logs. |
| `deny_hosts` | ver acima | Requisições cuja URL contém algum destes valores não são registradas. |
| `hide_fields` | `password`, `token` | Campos mascarados (`#######`) no corpo, cabeçalhos e query string. |
| `keep_days` | `120` | Dias de retenção; logs mais antigos são removidos pelo `model:prune`. |
| `request_body_exclude_content_types` | ver acima | Content types cujo corpo enviado não é gravado. |
| `response_body_exclude_content_types` | ver acima | Content types cujo corpo recebido não é gravado. |
| `show_request_headers` | `false` | Exibe os cabeçalhos de envio na visualização do log. |
| `show_response_headers` | `false` | Exibe os cabeçalhos recebidos na visualização do log. |
| `field_max_length` | `10000` | Tamanho máximo de cada valor texto gravado (não está no arquivo padrão). |
| `field_max_rows` | `1000` | Quantidade máxima de itens por array gravado (não está no arquivo padrão). |

Variáveis de ambiente:

```dotenv
FILAMENT_HTTP_LOGS_ENABLED=true
FILAMENT_HTTP_LOGS_KEEP_DAYS=120
```

O pacote não publica o arquivo de config. Para sobrescrever, crie `config/filament-http-logs.php` no projeto: ele é mesclado com o do pacote apenas no primeiro nível, então chaves com array (como `deny_hosts`) devem ser informadas por completo.

A limpeza pelo `model:prune` é agendada diariamente às 03h (os minutos vêm de `filament-admix.schedule.minutes`).

## Uso

Não é preciso chamar nada: o pacote registra um middleware global no HTTP Client do Laravel, então toda requisição feita com a facade `Http` é gravada.

```php
use Illuminate\Support\Facades\Http;

Http::post('https://api.exemplo.com/leads', [
    'name' => 'Fulano',
    'token' => 'segredo', // gravado como #######
]);
```

Corpos JSON, XML e `application/x-www-form-urlencoded` são gravados já decodificados; conteúdo binário é gravado em base64. Falhas na gravação do log são reportadas, mas não interrompem a requisição.

## Permissões

O `HttpLogResource` entra automaticamente no controle de acesso por Grupos do Admix, com as permissões padrão de visualizar, criar, editar e excluir (o painel só usa visualizar e excluir). Usuário sem grupo é administrador e tem acesso total. Não há permissões extras.

## Licença

Este pacote é software livre e está disponível nos termos da licença MIT.
