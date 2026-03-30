# 📦 Guia de Publicação no Packagist

Este guia explica como publicar a biblioteca **AverbePorto** no Packagist.

## ✅ Pré-requisitos Concluídos

Todos os arquivos necessários já foram criados:

- ✅ `composer.json` - Configuração do Composer
- ✅ `src/AverbePorto.php` - Código principal com namespace
- ✅ `README.md` - Documentação principal
- ✅ `docs/` - Documentação completa
- ✅ `examples/` - Exemplos funciona is
- ✅ `LICENSE` - Licença MIT
- ✅ `CHANGELOG.md` - Histórico de versões
- ✅ `.gitignore` - Arquivos ignorados
- ✅ `phpcs.xml` - Configuração de code style
- ✅ `.github/workflows/` - CI/CD automático

---

## 🚀 Passos para Publicação

### 1. Preparação do Repositório Git

```bash
cd c:\xampp\htdocs\averb-porto

# Inicializar git (se ainda não fez)
git init

# Adicionar todos os arquivos
git add .

# Commit inicial
git commit -m "Initial release - AverbePorto v2.0"

# Tag de versão
git tag v2.0.0
```

### 2. Criar Repositório no GitHub

1. Acesse [github.com/new](https://github.com/new)
2. Nome do repositório: `averbporto`
3. Descrição: "Biblioteca PHP para integração com a API da Porto Seguro - AverbePorto"
4. Escolha públicoUm
5. Clique em **Create repository**

### 3. Adicionar Remote e Push

```bash
# Adicionar remote (substitua YOUR_USERNAME)
git remote add origin https://github.com/seu-usuario/averbporto.git

# Push da branch main
git branch -M main
git push -u origin main

# Push da tag
git push origin v2.0.0
```

### 4. Registrar no Packagist

1. Acesse [packagist.org](https://packagist.org)
2. Clique em **Submit** no canto superior
3. Cole a URL do repositório: `https://github.com/seu-usuario/averbporto`
4. Clique em **Check**

Se aparecer um erro sobre webhook, faça o seguinte:

```bash
# No GitHub, vá em:
# Seu Repositório → Settings → Webhooks → Add webhook

# Configure:
- Payload URL: https://packagist.org/api/update-webhook?username=YOUR_USERNAME&apiToken=YOUR_API_TOKEN
- Content type: application/json
- Events: Push events
```

Para gerar seu API Token:
1. Acesse [packagist.org/profile](https://packagist.org/profile/)
2. Clique em "Show API Token"
3. Copie o valor

### 5. Atualizar `composer.json`

Altere os valores de exemplo:

```json
{
    "name": "seu-usuario/averbporto",
    "homepage": "https://github.com/seu-usuario/averbporto",
    "support": {
        "issues": "https://github.com/seu-usuario/averbporto/issues",
        "wiki": "https://github.com/seu-usuario/averbporto/wiki",
        "source": "https://github.com/seu-usuario/averbporto"
    },
    "authors": [
        {
            "name": "Seu Nome",
            "email": "seu.email@exemplo.com"
        }
    ]
}
```

### 6. Atualizar Documentação

Altere **TODOS** os links e emails:

**Arquivos para revisar:**
- [ ] `README.md`
- [ ] `docs/MANUAL.md`
- [ ] `docs/TROUBLESHOOTING.md`
- [ ] `examples/README.md`
- [ ] `CONTRIBUTING.md`

Busque e substitua:
- `seu-usuario` → Seu usuário GitHub
- `seu.email@exemplo.com` → Seu email real
- `seu-site.com.br` → Seu site (opcional)

### 7. Testar Instalação Local

```bash
# Criar um diretório de teste
mkdir test-install
cd test-install

# Criar composer.json
echo '{"require": {"seu-usuario/averbporto": "^2.0"}}' > composer.json

# Testar instalação (aponta para local primeiro)
composer install
```

### 8. Versionar e Publicar

```bash
# Voltar à pasta do projeto
cd ../averb-porto

# Editar CHANGELOG.md com versão final

# Commit final
git add .
git commit -m "Release v2.0.0"

# Criar tag
git tag v2.0.0

# Push
git push origin main
git push origin v2.0.0
```

### 9. Validar no Packagist

1. Acesse https://packagist.org/packages/seu-usuario/averbporto
2. Deve mostrar a versão **v2.0.0**
3. Aguarde 2-3 minutos para sincronização

---

## ✨ Pronto!

A biblioteca agora está disponível via:

```bash
composer require seu-usuario/averbporto
```

---

## 🔄 Futuras Atualizações

Para lançar novas versões:

```bash
# 1. Fazer mudanças
# 2. Atualizar CHANGELOG.md
# 3. Editar versão em composer.json (opcional)
# 4. Commit
git add .
git commit -m "v2.0.1 - Bug fixes"

# 5. Tag
git tag v2.0.1

# 6. Push
git push origin main
git push origin v2.0.1

# 7. Atualizar no Packagist (automático após webhook)
```

---

## 📋 Checklist Final

Antes de publicar, verifique:

- [ ] `composer.json` está correto
- [ ] `namespace AverbePorto` em `src/AverbePorto.php`
- [ ] `composer.json` aponta para pasta correta (`"psr-4": {"AverbePorto\\": "src/"}`)
- [ ] `README.md` tem links corretos
- [ ] `LICENSE` está presente
- [ ] `CHANGELOG.md` atualizado
- [ ] Todos os emails/URLs personalizados
- [ ] Git repository criado
- [ ] GitHub Actions configurado
- [ ] Webhook do Packagist funcionando

---

## 🐛 Troubleshooting

### "Arquivo não encontrado" no Packagist

Verifique:
1. URL do repositório está correta?
2. Nome do repositório é `averbporto` (sem hífens)?
3. Arquivo `composer.json` na raiz?

### Webhook não funciona

1. Vá em: Repositório → Settings → Webhooks
2. Clique em "Recent Deliveries"
3. Verifique o status e a mensagem de erro

### Packagist não atualiza

Vá em: https://packagist.org/packages/seu-usuario/averbporto e clique em "Update" manualmente

---

## 📚 Recursos

- [Packagist Docs](https://packagist.org/about)
- [Composer Docs](https://getcomposer.org/doc/)
- [GitHub - Publishing to Packagist](https://docs.github.com/en/rest)

---

## 🎉 Próximos Passos

Depois de publicar:

1. Compartilhe nas comunidades PHP brasileiras
2. Submeta em agregadores de notícias (Planeta PHP, etc)
3. Melz documentação a partir de feedback
4. Considere adicionar testes

---

**Parabéns! 🚀 Sua biblioteca agora está disponível para o mundo!**
