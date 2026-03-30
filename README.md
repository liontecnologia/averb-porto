# AverbePorto - Biblioteca PHP para API da Porto Seguro

[![Latest Version](https://img.shields.io/badge/version-2.0-blue.svg)](https://packagist.org/packages/liontecnologia/averbporto)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892be.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Biblioteca PHP completa para integração com a API da Porto Seguro (AverbePorto). Automatize o envio de documentos fiscais (XML, ZIP) para averbação e consulte protocolos ANTT.

## 📋 Recursos

- ✅ Autenticação segura com sessão via cookie
- ✅ Upload de XML e ZIP para averbação
- ✅ Suporte a documentos (NF-e, CT-e, MDF-e, NFC-e e eventos de cancelamento)
- ✅ Consulta de protocolo ANTT por chave de acesso
- ✅ Consulta inversa (protocolo → chave)
- ✅ Tratamento robusto de erros
- ✅ Sessão persistente (validade de 1 semana com credenciais de API)

## 🚀 Instalação

### Via Composer

```bash
composer require liontecnologia/averbporto
```

### Manualmente

1. Baixe ou clone o repositório:
```bash
git clone https://github.com/liontecnologia/averbporto.git
```

2. No seu projeto, inclua a classe:
```php
require_once 'path/to/AverbePorto.php';
```

## 📖 Uso Rápido

### 1. Inicializar a Biblioteca

```php
use AverbePorto\AverbePorto;

// Credenciais geradas no módulo "Cadastro do Usuário" da API
$usuario = 'SEU_USUARIO_API_60_CARACTERES';
$senha   = 'SUA_SENHA_API_64_CARACTERES';

$ap = new AverbePorto($usuario, $senha);
```

### 2. Upload de XML

```php
// Enviar conteúdo XML direto
$xmlContent = file_get_contents('/caminho/para/arquivo.xml');
$response = $ap->upload($xmlContent);

if (AverbePorto::uploadOk($response)) {
    $protocolo = AverbePorto::extrairProtocolo($response);
    echo "Sucesso! Protocolo ANTT: $protocolo";
} else {
    echo "Erro: " . AverbePorto::interpretarStatus($response);
}
```

### 3. Upload de Arquivo

```php
try {
    $response = $ap->uploadArquivo('/caminho/para/arquivo.xml');
    
    if (AverbePorto::uploadOk($response)) {
        echo "Arquivo averbado com sucesso!";
        echo "Protocolo: " . AverbePorto::extrairProtocolo($response);
    }
} catch (RuntimeException $e) {
    echo "Erro: " . $e->getMessage();
}
```

### 4. Upload em ZIP

```php
// Se tiver múltiplos XMLs em um ZIP
$zipContent = file_get_contents('/caminho/para/arquivo.zip');
$response = $ap->upload($zipContent, AverbePorto::RECIPIENT_AUTO, 'arquivos.zip');

// Para ZIPs, o protocolo retorna um array
$protocolos = AverbePorto::extrairProtocolo($response);
print_r($protocolos);
```

### 5. Consultar Protocolo ANTT por Chave

```php
$chaves = [
    '4401234567890123456789012345678901234567',
    '4402345678901234567890123456789012345678'
];

$response = $ap->consultarChave($chaves);

if (!empty($response['S'])) {
    foreach ($response['S'] as $item) {
        echo "Chave: {$item['chave']} → Protocolo: {$item['protocolo']}\n";
    }
}
```

### 6. Consulta Inversa (Protocolo → Chave)

```php
$protocolos = [
    '1234567890123456789012345678901234567890',
    '9876543210987654321098765432109876543210'
];

$response = $ap->consultarProtocolo($protocolos);

if (!empty($response['S'])) {
    foreach ($response['S'] as $item) {
        echo "Protocolo: {$item['protocolo']} → Chave: {$item['chave']}\n";
    }
}
```

## 🔐 Tipos de Remetente (Recipient)

Especifique o tipo do documento quando necessário:

```php
// Automático (recomendado)
$ap->upload($content, AverbePorto::RECIPIENT_AUTO);

// Embarcador/Emitente
$ap->upload($content, AverbePorto::RECIPIENT_EMBARCADOR);

// Fornecedor
$ap->upload($content, AverbePorto::RECIPIENT_FORNECEDOR);

// Transportador
$ap->upload($content, AverbePorto::RECIPIENT_TRANSPORTADOR);

// Duplo Ramo
$ap->upload($content, AverbePorto::RECIPIENT_DUPLO_RAMO);
```

## ⚙️ Configuração Avançada

### Alterar Timeout (padrão: 60 segundos)

```php
$ap = new AverbePorto($usuario, $senha, AverbePorto::ENDPOINT, AverbePorto::COMP, 120);
```

### Usar Endpoint Customizado

```php
$ap = new AverbePorto(
    $usuario,
    $senha,
    'https://apis.averbeporto.com.br/php/conn.php',  // seu endpoint
    5,   // código da empresa
    60   // timeout
);
```

### Forçar Relogin

```php
// Útil se a sessão expirar
$ap->relogin();
```

### Verificar Status da Sessão

```php
if ($ap->estaLogado()) {
    echo "Sessão ativa";
} else {
    echo "Sem sessão ativa";
}
```

## 🛠️ Tratamento de Erros

```php
try {
    $response = $ap->uploadArquivo('/arquivo.xml');
} catch (RuntimeException $e) {
    $mensagem = $e->getMessage();
    
    if (strpos($mensagem, 'login falhou') !== false) {
        echo "Credenciais inválidas ou sessão expirada";
    } elseif (strpos($mensagem, 'captcha exigido') !== false) {
        echo "Acesse o sistema web para resolver o captcha";
    } elseif (strpos($mensagem, 'acesso bloqueado') !== false) {
        echo "Verificar User-Agent ou subdomínio";
    } else {
        echo "Erro genérico: " . $mensagem;
    }
}
```

## 📚 Interpretação de Respostas

### Status do Upload

```php
$response = $ap->upload($xmlContent);

// Interpreta o status
echo AverbePorto::interpretarStatus($response);

// Verifica se processado com sucesso
if (AverbePorto::uploadOk($response)) {
    // OK
}

// Extrai protocolo
$protocolo = AverbePorto::extrairProtocolo($response);
```

### Campos da Resposta (campo S)

| Campo | Significado |
|-------|-------------|
| `P` | Processado (guardado com sucesso) |
| `D` | Duplicado (XML já existente) |
| `R` | Rejeitado (XML não é do tipo correto) |
| `N` | Negado (não é XML ou ZIP) |

## 📝 Exemplos Práticos

Veja a pasta [examples/](./examples/) para exemplos de:
- Upload simples
- Upload em lote (batch)
- Tratamento de erros
- Consultas de protocolo
- Integração com banco de dados

## 🔐 Importantes Notas de Segurança

### Credenciais de API

⚠️ **NÃO use** credenciais web no acesso à API. Gere credenciais específicas:

1. Acesse https://wws.averbeporto.com.br ou https://www.averbeporto.com.br
2. Vá no módulo **Cadastro do Usuário**
3. Clique no **X** para gerar novas credenciais de API
4. Use os valores gerados (60 caracteres de usuário, 64 de senha)

### User-Agent

A API requer um User-Agent válido. Padrão da biblioteca:
```
Mozilla/5.0 AverbePorto-PHP/2.0
```

Se ajustar, evite nomes proibidos conforme: https://hub-data.crowdsec.net/web/bad_user_agents.regex.txt

### Endpoint

⚠️ Use sempre: `https://apis.averbeporto.com.br/php/conn.php`

❌ **NÃO** use subdomínios como `www`, `wws` ou `api`

## 📋 Requisitos

- **PHP**: 7.4 ou superior
- **Extensões**: cURL
- **Certificado SSL**: Para HTTPS

## 📄 Documentação Completa

Consulte a documentação detalhada em [docs/](./docs/):
- [Manual de Integração](./docs/MANUAL.md)
- [API Reference](./docs/API.md)
- [Troubleshooting](./docs/TROUBLESHOOTING.md)

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor:

1. Faça fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/algo-incrível`)
3. Commit suas mudanças (`git commit -m 'Adiciona algo incrível'`)
4. Push para a branch (`git push origin feature/algo-incrível`)
5. Abra um Pull Request

## 📜 Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

## 🙋 Suporte

- 📧 Envie um email: seu.email@exemplo.com
- 🐛 Abra uma issue: [Github Issues](https://github.com/liontecnologia/averbporto/issues)
- 📚 Wiki: [Github Wiki](https://github.com/liontecnologia/averbporto/wiki)

## 🔗 Recursos Externos

- [Portal AverbePorto](https://www.averbeporto.com.br)
- [API Manual Oficial](https://www.averbeporto.com.br/proxy/manual.php?format=pdf)
- [Especificação NF-e](http://sped.rfb.gov.br/pagina/show/1328)
- [Especificação CT-e](http://sped.rfb.gov.br/pagina/show/1126)
- [Especificação MDF-e](http://sped.rfb.gov.br/pagina/show/1515)

---

**Desenvolvido com ❤️ para a comunidade PHP brasileira**
