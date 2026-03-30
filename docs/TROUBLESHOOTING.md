# Troubleshooting - AverbePorto

## Problemas Comuns e Soluções

---

## 🔴 Erro: "HTTP 403 Forbidden"

**Mensagem Completa:**
```
AverbePorto: acesso bloqueado (HTTP 403). Verifique o User-Agent ou o subdomínio utilizado.
```

### Causas

1. Subdomínio inválido
2. User-Agent proibido ou ausente
3. Rate limiting /abuso detectado

### Soluções

**1. Verificar Subdomínio**

❌ **Errados:**
- `https://www.averbeporto.com.br/php/conn.php`
- `https://wws.averbeporto.com.br/php/conn.php`
- `https://api.averbeporto.com.br/php/conn.php`

✅ **Correto:**
```php
const ENDPOINT = 'https://apis.averbeporto.com.br/php/conn.php';
```

**2. Verificar User-Agent**

```php
// Padrão correto
const USER_AGENT = 'Mozilla/5.0 AverbePorto-PHP/2.0';

// Evitar nomes proibidos:
// https://hub-data.crowdsec.net/web/bad_user_agents.regex.txt

// NÃO use:
// - bot, crawler, spider
// - curl, wget, python
// - gsa-crawler
```

**3. Aplicar Throttle**

Se enviar muitos XMLs, aguarde entre requisições:

```php
function enviarComThrottle($ap, $arquivos) {
    foreach ($arquivos as $arquivo) {
        $ap->uploadArquivo($arquivo);
        sleep(1); // Aguardar 1 segundo entre requisições
    }
}
```

---

## 🔴 Erro: "login falhou — usuário ou senha inválidos"

### Causas

1. Credenciais de API incorretas
2. Usando credenciais web em vez de API
3. Sessão expirada (> 1 semana)

### Soluções

**1. Gerar Novas Credenciais de API**

❌ **NÃO use:**
```php
$ap = new AverbePorto('seu_usuario_web', 'sua_senha_web');
```

✅ **Fazer:**
1. Acesse https://www.averbeporto.com.br
2. Faça login com seu usuário/senha web
3. Vá em **Cadastro do Usuário**
4. Clique no **X** para gerar credenciais de API
5. Use os 60 + 64 caracteres gerados

**2. Verificar Comprimento**

```php
$usuario = 'sua_credencial';
$senha = 'sua_credencial';

if (strlen($usuario) !== 60) {
    throw new RuntimeException("Usuário deve ter 60 caracteres, tem " . strlen($usuario));
}

if (strlen($senha) !== 64) {
    throw new RuntimeException("Senha deve ter 64 caracteres, tem " . strlen($senha));
}
```

**3. Forçar Relogin**

Se a sessão expirou:

```php
$ap->relogin();
// ou
// $ap = new AverbePorto($usuario, $senha); // Nova instância
```

---

## 🔴 Erro: "captcha exigido"

**Mensagem:**
```
AverbePorto: captcha exigido. Resolva em: https://www.averbeporto.com.br
```

### Causas

Muitas tentativas de login com falha

### Soluções

**1. Acesso Manual**

1. Abra https://www.averbeporto.com.br
2. Faça login manualmente
3. Resolva o captcha
4. Após desbloqueio, tente novamente com a biblioteca (aguarde 15-30 min)

**2. Verificar Credenciais**

Antes de fazer nova tentativa:

```php
// Validar credenciais antes
echo "Usuário: " . strlen($usuario) . " chars (deve ser 60)";
echo "Senha: " . strlen($senha) . " chars (deve ser 64)";
```

**3. Implementar Retry com Delay**

```php
function enviarComRetrySeguro($usuario, $senha, $arquivo, $tentativas = 3) {
    for ($i = 1; $i <= $tentativas; $i++) {
        try {
            $ap = new AverbePorto($usuario, $senha);
            return $ap->uploadArquivo($arquivo);
        } catch (RuntimeException $e) {
            if (strpos($e->getMessage(), 'captcha') !== false) {
                echo "Captcha requerido. Aguarde e tente novamente via portal web.";
                return null;
            }
            
            if ($i < $tentativas) {
                echo "Tentativa $i falhou. Aguardando...\n";
                sleep(5); // Aguardar 5 segundos
            } else {
                throw $e;
            }
        }
    }
}
```

---

## 🔴 Erro: "resposta inválida da API"

**Mensagem:**
```
AverbePorto: resposta inválida da API (HTTP 200): ...
```

### Causas

1. API retornou HTML em vez de JSON
2. Certificado SSL inválido
3. Proxy/firewall interferindo

### Soluções

**1. Testar Conectividade**

```php
// Teste rápido
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://apis.averbeporto.com.br/php/conn.php',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpCode\n";
echo "Error: $error\n";
echo "Response: " . substr($response, 0, 200) . "\n";

curl_close($ch);
```

**2. Desabilitar Verificação SSL (Último Recurso)**

```php
// ⚠️ APENAS EM DESENVOLVIMENTO
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
```

**3. Adicionar Debug Dump**

```php
// Usar o parâmetro dump da API
$ap->upload($content, AverbePorto::RECIPIENT_AUTO);

// A resposta incluirá mais informações
// Veja: Manual de Integração - parâmetro dump
```

---

## 🔴 Erro: "arquivo não encontrado"

### Causas

Caminho do arquivo inválido

### Solução

```php
$arquivo = '/caminho/arquivo.xml';

// Verificar antes de enviar
if (!file_exists($arquivo)) {
    echo "Arquivo não existe: $arquivo";
    echo "Diretório atual: " . getcwd();
    exit(1);
}

// Verificar permissões
if (!is_readable($arquivo)) {
    echo "Arquivo não é legível";
    exit(1);
}

// Enviar
$response = $ap->uploadArquivo($arquivo);
```

---

## 🟡 XML Rejeitado (R > 0)

**Resposta:**
```json
{
    "success": 1,
    "S": {
        "P": 0,
        "D": 0,
        "R": 1,
        "N": 0
    }
}
```

### Causas

1. XML mal formado
2. XML de tipo não suportado
3. XML não é da Porto Seguro
4. Estrutura incorreta

### Soluções

**1. Validar XML**

```php
function validarXML($arquivo) {
    // Verificar se é um XML válido
    $xml = simplexml_load_file($arquivo);
    
    if ($xml === false) {
        echo "XML inválido:\n";
        foreach (libxml_get_errors() as $error) {
            echo "  - " . $error->message . "\n";
        }
        return false;
    }
    
    // Verificar raiz
    $root = $xml->getName();
    $validas = ['NFe', 'nfeProc', 'CTe', 'cteProc', 'MDFe', 'mdfProc', 'nfcProc'];
    
    if (!in_array($root, $validas)) {
        echo "Raiz inválida: $root\n";
        return false;
    }
    
    return true;
}
```

**2. Verificar Tipo de Documento**

```php
// Porta Seguro aceita:
// - NF-e (raiz: NFe ou nfeProc)
// - NFC-e (raiz: nfcProc)
// - CT-e (raiz: CTe ou cteProc)
// - MDF-e (raiz: MDFe ou mdfProc)

// Seu XML pode ser de outro tipo
// Verifique a raiz e o schemalocation
```

**3. Verificar MIME Type**

```php
$arquivo = '/arquivo.xml';
$mime = mime_content_type($arquivo);

// Deve ser:
// - application/xml
// - text/xml

if ($mime !== 'application/xml' && $mime !== 'text/xml') {
    echo "MIME type inválido: $mime";
}
```

---

## 🟡 XML Duplicado (D > 0)

**Causa:** XML já foi averbado anteriormente

### Solução

```php
$response = $ap->uploadArquivo($arquivo);

if (!empty($response['S']['D'])) {
    echo "XML duplicado. Obtendo protocolo original...\n";
    
    // Extrair chave do XML
    $xml = simplexml_load_file($arquivo);
    $chave = (string)$xml->xpath('//infNFe/@Id')[0]; // NF-e
    
    // Consultar protocolo existente
    $consulta = $ap->consultarChave([$chave]);
    $protocolo = $consulta['S'][0]['protocolo'];
    
    echo "Protocolo original: $protocolo\n";
}
```

---

## 🟡 XML Negado (N > 0)

**Causa:** Não é um arquivo XML ou ZIP válido

### Solução

```php
function validarArquivo($arquivo) {
    $tamanho = filesize($arquivo);
    
    // Arquivo vazio?
    if ($tamanho === 0) {
        echo "Arquivo vazio";
        return false;
    }
    
    // Arquivo muito grande?
    if ($tamanho > 50 * 1024 * 1024) { // 50MB
        echo "Arquivo muito grande: " . ($tamanho / 1024 / 1024) . "MB";
        return false;
    }
    
    // É XML?
    $primeiros = file_get_contents($arquivo, false, null, 0, 100);
    
    if (strpos($primeiros, '<?xml') === false && 
        strpos($primeiros, 'version=') === false) {
        echo "Não parece ser um XML válido";
        return false;
    }
    
    // É ZIP?
    $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
    if ($ext === 'zip') {
        $zip = new ZipArchive();
        if (!$zip->open($arquivo)) {
            echo "ZIP corrompido";
            return false;
        }
        
        if ($zip->numFiles > 400) {
            echo "ZIP com mais de 400 arquivos (limite da API)";
            return false;
        }
        
        $zip->close();
    }
    
    return true;
}
```

---

## 🟠 Timeout na Conexão

**Sintomas:**
- Requisição lenta/congelada
- Após 60+ segundos: erro timeout

### Soluções

**1. Aumentar Timeout**

```php
$ap = new AverbePorto($usuario, $senha, AverbePorto::ENDPOINT, 5, 300); // 5 minutos
```

**2. Verificar Conectividade**

```bash
# Windows
ping apis.averbeporto.com.br
curl -I https://apis.averbeporto.com.br/php/conn.php

# Linux/Mac
curl -v https://apis.averbeporto.com.br/php/conn.php
```

**3. Verificar Firewall**

- Porta 443 (HTTPS) aberta?
- Proxy interferindo?
- VPN conectada?

---

## 📋 Checklist de Diagnóstico

Quando tiver problemas, verifique:

- [ ] Credenciais de API têm 60 + 64 caracteres?
- [ ] Usando HTTPS com `apis.averbeporto.com.br`?
- [ ] User-Agent está definido?
- [ ] XML é válido (bem formado)?
- [ ] XML é do tipo suportado (NF-e, CT-e, etc)?
- [ ] Arquivo existe e é legível?
- [ ] Internet conectada?
- [ ] Porta 443 aberta?
- [ ] Firewall/proxy interferindo?
- [ ] Tentou relogin?
- [ ] Aguardou entre requisições?

---

## 📞 Obter Mais Ajuda

1. Consulte o [Manual Completo](./MANUAL.md)
2. Veja [Exemplos](../examples/)
3. Abra uma [Issue](https://github.com/seu-usuario/averbporto/issues)
4. Contato: seu.email@exemplo.com

---

**Última atualização:** 2024-12-01
