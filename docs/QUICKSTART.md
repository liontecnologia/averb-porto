# Documentação Rápida

Bem-vindo ao **AverbePorto**! Aqui está um guia de inicialização rápida.

## 🚀 Instalação Rápida

### Via Composer

```bash
composer require seu-usuario/averbporto
```

### Uso Imediato

```php
<?php

require_once 'vendor/autoload.php';

use AverbePorto\AverbePorto;

// 1. Conectar
$ap = new AverbePorto('usuario_60_chars', 'senha_64_chars');

// 2. Enviar XML
$response = $ap->uploadArquivo('/arquivo.xml');

// 3. Verificar resultado
if (AverbePorto::uploadOk($response)) {
    echo "Protocolo: " . AverbePorto::extrairProtocolo($response);
} else {
    echo "Erro: " . AverbePorto::interpretarStatus($response);
}
```

## 📚 Documentação

| Recurso | Descrição |
|---------|-----------|
| [README.md](README.md) | Visão geral e quickstart |
| [docs/MANUAL.md](docs/MANUAL.md) | Manual completo em português |
| [docs/API.md](docs/API.md) | Referência de API |
| [docs/TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | Guia de erros |
| [examples/](examples/) | 7 exemplos práticos |

## 🧪 Testes Rápidos

```bash
# Teste básico
php tests/BasicTest.php

# Executar um exemplo
php examples/1-upload-simples.php
```

## 🔑 Obter Credenciais

1. Acesse https://www.averbeporto.com.br
2. Login com seu usuário web
3. Vá em **Cadastro do Usuário**
4. Gere credenciais de API

## ⚡ Status de Lançamento

- ✅ **Versão 2.0** pronta para publicação
- ✅ Documentação completa
- ✅ 7 exemplos funcionais
- ✅ Estrutura Packagist-ready

## 🔗 Links Úteis

- [Portal AverbePorto](https://www.averbeporto.com.br)
- [Manual Oficial PDF](https://www.averbeporto.com.br/proxy/manual.php?format=pdf)
- [GitHub Repository](https://github.com/seu-usuario/averbporto)
- [Packagist](https://packagist.org/packages/seu-usuario/averbporto)

---

**Precisa de ajuda?** Consulte a [documentação completa](docs/MANUAL.md) ou abra uma [issue](https://github.com/seu-usuario/averbporto/issues).
