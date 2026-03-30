# Manual de Integração - AverbePorto

## Índice

1. [Introdução](#introdução)
2. [Autenticação](#autenticação)
3. [Upload de Documentos](#upload-de-documentos)
4. [Tipos de Documentos](#tipos-de-documentos)
5. [Consultas](#consultas)
6. [Tratamento de Erros](#tratamento-de-erros)
7. [Boas Práticas](#boas-práticas)

## Introdução

A biblioteca **AverbePorto** facilita a integração com a API REST da Porto Seguro para automatizar o envio de documentos fiscais para averbação.

### Documentos Suportados

- **NF-e** (55): Nota Fiscal Eletrônica
- **NFC-e** (65): Nota Fiscal do Consumidor Eletrônica
- **CT-e** (57): Conhecimento de Transporte Eletrônico
- **MDF-e** (58): Manifesto de Documento Fiscal Eletrônico
- **Minuta de CT-e** (94)
- **Cancelamentos**: Eventos de cancelamento dos documentos acima

### Métodos de Envio

1. **Upload Direto** (via biblioteca) - Recomendado
2. **Upload em Lote** (ZIP com múltiplos XMLs)
3. **Consulta de Protocolos** (chave → protocolo)
4. **Consulta Inversa** (protocolo → chave)

---

## Autenticação

### Gerar Credenciais de API

⚠️ **IMPORTANTE**: Use credenciais específicas de API, não a senha web!

1. Acesse [https://wws.averbeporto.com.br](https://wws.averbeporto.com.br) ou [https://www.averbeporto.com.br](https://www.averbeporto.com.br)
2. Faça login com suas credenciais web
3. Vá em **Cadastro do Usuário**
4. Clique no **X** para gerar novas credenciais
5. Anote:
   - **Usuário**: 60 caracteres
   - **Senha**: 64 caracteres

### Inicializar a Conexão

```php
use AverbePorto\AverbePorto;

$usuario = 'seu_usuario_60_caracteres';
$senha   = 'sua_senha_64_caracteres';

$ap = new AverbePorto($usuario, $senha);
```

### Opções Avançadas

```php
$ap = new AverbePorto(
    $usuario,
    $senha,
    'https://apis.averbeporto.com.br/php/conn.php',  // Endpoint (não alterar)
    5,    // Código da empresa (padrão)
    120   // Timeout em segundos
);
```

---

## Upload de Documentos

### Upload Simples (Arquivo)

```php
try {
    $response = $ap->uploadArquivo('/caminho/para/arquivo.xml');
    
    if (AverbePorto::uploadOk($response)) {
        $protocolo = AverbePorto::extrairProtocolo($response);
        echo "Protocolo ANTT: $protocolo";
    } else {
        echo "Status: " . AverbePorto::interpretarStatus($response);
    }
} catch (RuntimeException $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Upload por Conteúdo

```php
$xmlContent = file_get_contents('/arquivo.xml');

$response = $ap->upload(
    $xmlContent,
    AverbePorto::RECIPIENT_AUTO,  // Tipo de remetente
    'meu-arquivo.xml'              // Nome do arquivo
);
```

### Upload em Lote (ZIP)

```php
$zipContent = file_get_contents('/lote.zip');

$response = $ap->upload(
    $zipContent,
    AverbePorto::RECIPIENT_AUTO,
    'lote.zip'
);

// Retorna estatísticas
echo "Processados: " . $response['S']['P'];
echo "Duplicados:  " . $response['S']['D'];
echo "Rejeitados:  " . $response['S']['R'];
echo "Negados:     " . $response['S']['N'];
```

### Especificar Tipo de Remetente

```php
// Automático (recomendado)
->upload($content, AverbePorto::RECIPIENT_AUTO);

// Embarcador
->upload($content, AverbePorto::RECIPIENT_EMBARCADOR);

// Fornecedor
->upload($content, AverbePorto::RECIPIENT_FORNECEDOR);

// Transportador
->upload($content, AverbePorto::RECIPIENT_TRANSPORTADOR);

// Duplo Ramo
->upload($content, AverbePorto::RECIPIENT_DUPLO_RAMO);
```

---

## Tipos de Documentos

### Validação de Formato

Os XMLs devem estar em um dos formatos MIME válidos:

- `application/xml`
- `text/xml`

ZIPs devem ser:
- `application/zip`
- **Máximo 400 XMLs** por ZIP

### Estrutura Esperada

Cada XML deve ter a estrutura conforme especificação SPED RFB:

- **NF-e**: Raiz `NFe` ou `nfeProc`
- **NFC-e**: Raiz `NFe` ou `nfcProc`
- **CT-e**: Raiz `CTe` ou `cteProc`
- **MDF-e**: Raiz `MDFe` ou `mdfProc`

---

## Consultas

### Consultar Protocolo por Chave

Obtém o protocolo ANTT através da chave de acesso (44 dígitos):

```php
$chaves = [
    '4401234567890123456789012345678901234567',
    '3505890123456789012345678901234567890123'
];

$response = $ap->consultarChave($chaves);

foreach ($response['S'] as $item) {
    echo "{$item['chave']} → {$item['protocolo']}\n";
}
```

### Opções de Formato

```php
// JSON (padrão)
$ap->consultarChave($chaves, 'json');

// XML
$ap->consultarChave($chaves, 'xml');

// CSV
$ap->consultarChave($chaves, 'csv', 0, ',');
```

### Consulta Inversa (Protocolo → Chave)

```php
$protocolos = [
    '1234567890123456789012345678901234567890',
    '9876543210987654321098765432109876543210'
];

$response = $ap->consultarProtocolo($protocolos);

foreach ($response['S'] as $item) {
    echo "{$item['protocolo']} → {$item['chave']}\n";
}
```

---

## Tratamento de Erros

### Tipos de Erro

```php
try {
    $response = $ap->uploadArquivo('arquivo.xml');
} catch (RuntimeException $e) {
    $msg = $e->getMessage();
    
    // Arquivo não encontrado
    if (strpos($msg, 'arquivo não encontrado') !== false) {
        echo "Arquivo não localizado";
    }
    
    // Credenciais inválidas
    if (strpos($msg, 'login falhou') !== false) {
        echo "Usuário ou senha incorretos";
    }
    
    // Captcha requerido
    if (strpos($msg, 'captcha exigido') !== false) {
        echo "Acesse o portal web para resolver captcha";
    }
    
    // Acesso bloqueado
    if (strpos($msg, 'acesso bloqueado') !== false) {
        echo "Verifique User-Agent e subdomínio";
    }
    
    // Erro de conexão
    if (strpos($msg, 'erro cURL') !== false) {
        echo "Problema de conexão com a API";
    }
}
```

### Interpretações de Resposta

```php
// Verificar se processado com sucesso
if (AverbePorto::uploadOk($response)) {
    // XML foi guardado
}

// Obter mensagem legível
$status = AverbePorto::interpretarStatus($response);
// Retorna: "Processado", "Duplicado", "Rejeitado", "Negado", etc.

// Extrair protocolo
$protocolo = AverbePorto::extrairProtocolo($response);
```

### Campos da Resposta

Campo **S** (Status do Upload):

| Campo | Significado |
|-------|-------------|
| P | Processado (XML guardado com sucesso) |
| D | Duplicado (XML já existe) |
| R | Rejeitado (XML não é do tipo correto) |
| N | Negado (não é XML ou ZIP válido) |

---

## Boas Práticas

### 1. Gerenciamento de Sessão

A sessão é válida por **1 semana** com credenciais de API:

```php
// Verificar se está logado
if ($ap->estaLogado()) {
    echo "Sessão ativa";
}

// Forçar novo login (se expirar)
$ap->relogin();
```

### 2. Logging e Auditoria

```php
$response = $ap->uploadArquivo($arquivo);

// Registrar em BD ou arquivo de log
$log = [
    'timestamp' => date('Y-m-d H:i:s'),
    'arquivo' => basename($arquivo),
    'status' => AverbePorto::interpretarStatus($response),
    'protocolo' => AverbePorto::extrairProtocolo($response) ?? 'N/A',
    'resposta_completa' => json_encode($response)
];

file_put_contents('averbacoes.log', json_encode($log) . "\n", FILE_APPEND);
```

### 3. Retry com Exponential Backoff

```php
function enviarComRetry($ap, $arquivo, $maxTentativas = 3) {
    for ($i = 1; $i <= $maxTentativas; $i++) {
        try {
            return $ap->uploadArquivo($arquivo);
        } catch (RuntimeException $e) {
            if ($i === $maxTentativas) {
                throw $e;
            }
            
            // Esperar: 1s, 2s, 4s...
            $delay = pow(2, $i - 1);
            echo "Tentativa $i falhou. Aguardando {$delay}s...\n";
            sleep($delay);
        }
    }
}
```

### 4. Tratamento de Duplicatas

```php
$response = $ap->uploadArquivo($arquivo);

if (!empty($response['S']['D'])) {
    // XML é duplicado
    echo "Documento já foi averbado anteriormente";
    
    // Consultar protocolo existente
    $chave = extrairChaveDoXML($arquivo);
    $consulta = $ap->consultarChave([$chave]);
    $protocolo = $consulta['S'][0]['protocolo'];
    echo "Protocolo: $protocolo";
}
```

### 5. Validação de XML Pré-envio

```php
function validarXML($caminhoArquivo) {
    $xml = simplexml_load_file($caminhoArquivo);
    
    if ($xml === false) {
        throw new RuntimeException("XML inválido: " . libxml_get_last_error());
    }
    
    // Validar tamanho
    $tamanho = filesize($caminhoArquivo);
    if ($tamanho > 10 * 1024 * 1024) { // 10MB
        throw new RuntimeException("Arquivo muito grande: {$tamanho} bytes");
    }
    
    return true;
}
```

### 6. Rate Limiting

A API só permite até **10 uploads por segundo**. Acima disso, aplicará delay progressivo:

```php
function enviarComThrottle($ap, $arquivos, $delayMs = 200) {
    foreach ($arquivos as $arquivo) {
        try {
            $response = $ap->uploadArquivo($arquivo);
            echo "✓ " . basename($arquivo) . "\n";
        } catch (RuntimeException $e) {
            echo "✗ " . basename($arquivo) . " - " . $e->getMessage() . "\n";
        }
        
        usleep($delayMs * 1000); // Aguardar entre requisições
    }
}
```

---

## Suporte

Para mais informações:

- Portal: https://www.averbeporto.com.br
- Manual Oficial: https://www.averbeporto.com.br/proxy/manual.php?format=pdf
- Issues: https://github.com/seu-usuario/averbporto/issues
