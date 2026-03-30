# API Reference - AverbePorto

## Classe Principal

### `AverbePorto`

Classe principal para interação com a API da Porto Seguro.

---

## Constantes

### Endpoints

```php
AverbePorto::ENDPOINT = 'https://apis.averbeporto.com.br/php/conn.php'
AverbePorto::COMP = 5
AverbePorto::PATH_GUARD = 'eguarda/php/'
AverbePorto::PATH_PROT = 'atwe/php/'
AverbePorto::USER_AGENT = 'Mozilla/5.0 AverbePorto-PHP/2.0'
```

### Tipos de Recipient

```php
AverbePorto::RECIPIENT_AUTO        = ''   // Automático (recomendado)
AverbePorto::RECIPIENT_EMBARCADOR  = 'E'
AverbePorto::RECIPIENT_FORNECEDOR  = 'F'
AverbePorto::RECIPIENT_TRANSPORTADOR = 'T'
AverbePorto::RECIPIENT_DUPLO_RAMO  = 'D'
```

---

## Métodos Públicos

### `__construct()`

Cria uma nova instância de AverbePorto.

**Parâmetros:**

| Nome | Tipo | Obrigatório | Descrição |
|------|------|-------------|-----------|
| `$user` | string | Sim | Usuário de API (60 caracteres) |
| `$pass` | string | Sim | Senha de API (64 caracteres) |
| `$endpoint` | string | Não | URL da API (padrão: ENDPOINT) |
| `$comp` | int | Não | Código da empresa (padrão: 5) |
| `$timeout` | int | Não | Timeout em segundos (padrão: 60) |

**Exemplo:**

```php
$ap = new AverbePorto('user_60chars', 'pass_64chars');
```

---

### `upload()`

Envia conteúdo XML ou ZIP para averbação.

**Assinatura:**

```php
public function upload(
    string  $content,
    string  $recipient = self::RECIPIENT_AUTO,
    ?string $filename = null
): array
```

**Parâmetros:**

| Nome | Tipo | Descrição |
|------|------|-----------|
| `$content` | string | Conteúdo bruto do XML ou ZIP |
| `$recipient` | string | Tipo de remetente (use constantes RECIPIENT_*) |
| `$filename` | string\|null | Nome do arquivo no multipart |

**Retorna:**

Array com resposta decodificada da API:

```php
[
    'success' => 1,
    'S' => [
        'P' => 1,  // Processado
        'D' => 0,  // Duplicado
        'R' => 0,  // Rejeitado
        'N' => 0   // Negado
    ],
    'prot' => '1234567890123456789012345678901234567890'
]
```

**Lança:**

- `RuntimeException` - Em caso de erro cURL, JSON inválido ou falha de conexão

**Exemplo:**

```php
$xml = file_get_contents('documento.xml');
$response = $ap->upload($xml, AverbePorto::RECIPIENT_AUTO, 'doc.xml');
```

---

### `uploadArquivo()`

Envia um arquivo XML ou ZIP do disco.

**Assinatura:**

```php
public function uploadArquivo(
    string $filePath,
    string $recipient = self::RECIPIENT_AUTO
): array
```

**Parâmetros:**

| Nome | Tipo | Descrição |
|------|------|-----------|
| `$filePath` | string | Caminho absoluto do arquivo |
| `$recipient` | string | Tipo de remetente |

**Retorna:**

Array com resposta da API (mesmo formato de `upload()`)

**Lança:**

- `RuntimeException` - Se arquivo não existir ou houver erro de conexão

**Exemplo:**

```php
$response = $ap->uploadArquivo('/caminho/absoluto/documento.xml');

if (AverbePorto::uploadOk($response)) {
    echo "Protocolo: " . AverbePorto::extrairProtocolo($response);
}
```

---

### `consultarChave()`

Consulta o protocolo ANTT a partir de chaves de acesso.

**Assinatura:**

```php
public function consultarChave(
    array  $chaves,
    string $out = 'json',
    int    $download = 0,
    string $delim = ','
): array
```

**Parâmetros:**

| Nome | Tipo | Descrição |
|------|------|-----------|
| `$chaves` | array | Array de chaves (44 dígitos cada) |
| `$out` | string | Formato: 'json', 'xml' ou 'csv' |
| `$download` | int | 0=display, 1=download |
| `$delim` | string | Delimitador para CSV |

**Retorna:**

```php
[
    'success' => 1,
    'S' => [
        ['chave' => '4401...', 'protocolo' => '1234...'],
        ['chave' => '3505...', 'protocolo' => '5678...']
    ]
]
```

**Exemplo:**

```php
$chaves = ['4401234567890123456789012345678901234567'];
$response = $ap->consultarChave($chaves);

foreach ($response['S'] as $item) {
    echo $item['chave'] . ' → ' . $item['protocolo'];
}
```

---

### `consultarProtocolo()`

Consulta inversa: obtém chave a partir do protocolo ANTT.

**Assinatura:**

```php
public function consultarProtocolo(
    array  $protocolos,
    string $out = 'json',
    int    $download = 0,
    string $delim = ','
): array
```

**Parâmetros:**

| Nome | Tipo | Descrição |
|------|------|-----------|
| `$protocolos` | array | Array de protocolos ANTT |
| `$out` | string | Formato de saída |
| `$download` | int | 0=display, 1=download |
| `$delim` | string | Delimitador para CSV |

**Retorna:**

Mesmo formato de `consultarChave()`

**Exemplo:**

```php
$protocolos = ['1234567890123456789012345678901234567890'];
$response = $ap->consultarProtocolo($protocolos);
```

---

### `relogin()`

Força um novo login, descartando a sessão atual.

**Assinatura:**

```php
public function relogin(): array
```

**Retorna:**

Array com resposta do login

**Exemplo:**

```php
// Se a sessão expirou
$ap->relogin();
```

---

### `estaLogado()`

Verifica se há uma sessão ativa.

**Assinatura:**

```php
public function estaLogado(): bool
```

**Retorna:**

`true` se há sessão ativa, `false` caso contrário

**Exemplo:**

```php
if ($ap->estaLogado()) {
    echo "Conectado";
}
```

---

## Métodos Estáticos

### `interpretarStatus()`

Interpreta a resposta e retorna uma mensagem legível.

**Assinatura:**

```php
public static function interpretarStatus(array $response): string
```

**Parâmetros:**

| Nome | Tipo | Descrição |
|------|------|-----------|
| `$response` | array | Resposta da API |

**Retorna:**

String com descrição do status

**Exemplo:**

```php
echo AverbePorto::interpretarStatus($response);
// Output: "Processado: XML guardado com sucesso"
```

---

### `uploadOk()`

Verifica se o upload foi processado com sucesso.

**Assinatura:**

```php
public static function uploadOk(array $response): bool
```

**Parâmetros:**

| Nome | Tipo | Descrição |
|------|------|-----------|
| `$response` | array | Resposta da API |

**Retorna:**

`true` se P=1 e protocolo está presente

**Exemplo:**

```php
if (AverbePorto::uploadOk($response)) {
    // Sucesso
}
```

---

### `extrairProtocolo()`

Extrai o protocolo ANTT da resposta.

**Assinatura:**

```php
public static function extrairProtocolo(array $response)
```

**Parâmetros:**

| Nome | Tipo | Descrição |
|------|------|-----------|
| `$response` | array | Resposta da API |

**Retorna:**

- `string` - Para arquivo único
- `array` - Para ZIP com múltiplos protocolos
- `null` - Se não houver protocolo

**Exemplo:**

```php
$protocolo = AverbePorto::extrairProtocolo($response);

if (is_array($protocolo)) {
    foreach ($protocolo as $proto) {
        echo $proto . "\n";
    }
} else {
    echo $protocolo;
}
```

---

## Estrutura de Respostas

### Resposta de Upload Bem-Sucedida

```json
{
    "success": 1,
    "C": {
        "id": "00",
        "userName": "USUARIO",
        "name": "Usuario",
        "email": "usuario@dominio.com",
        "portal_groups_id": "00",
        "type": "U"
    },
    "S": {
        "P": 1,
        "D": 0,
        "R": 0,
        "N": 0
    },
    "prot": "1234567890123456789012345678901234567890"
}
```

### Resposta de Erro - Login Inválido

```json
{
    "success": 1,
    "logout": 1
}
```

### Resposta de Erro - Falta de Autenticação

```json
{
    "success": 1,
    "error": {
        "code": "01",
        "msg": "No login."
    }
}
```

### Resposta de Captcha

```json
{
    "success": 0,
    "error": {
        "code": "02",
        "msg": "Captcha required"
    },
    "captcha_url": "https://www.averbeporto.com.br",
    "captcha_html": "..."
}
```

---

## Códigos de Erro

| Código | Mensagem | Ação |
|--------|----------|------|
| 01 | No login | Fazer login novamente |
| 02 | Captcha required | Resolver captcha no portal |
| 403 | Forbidden | Verificar User-Agent e subdomínio |

---

## Exemplo Completo

```php
<?php

use AverbePorto\AverbePorto;

try {
    // 1. Conectar
    $ap = new AverbePorto('usuario_60_chars', 'senha_64_chars');
    
    // 2. Enviar
    $response = $ap->uploadArquivo('/arquivo.xml');
    
    // 3. Verificar
    if (AverbePorto::uploadOk($response)) {
        $protocolo = AverbePorto::extrairProtocolo($response);
        echo "✓ Sucesso! Protocolo: $protocolo\n";
        
        // 4. Salvar protocolo
        file_put_contents('protocolo.txt', $protocolo);
        
    } else {
        echo "✗ Falha: " . AverbePorto::interpretarStatus($response) . "\n";
    }
    
} catch (RuntimeException $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
```

---

Para mais informações, consulte o [Manual Completo](./MANUAL.md).
