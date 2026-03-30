# Exemplos de Uso - AverbePorto

Esta pasta contém exemplos práticos de como usar a biblioteca AverbePorto.

## 📁 Arquivos

### 1️⃣ [1-upload-simples.php](./1-upload-simples.php)

Upload de um arquivo XML único.

```bash
php 1-upload-simples.php
```

**Funciona para:**
- NF-e, CT-e, MDF-e, NFC-e
- Arquivos XML individual
- Primeiro contato com a biblioteca

---

### 2️⃣ [2-upload-conteudo.php](./2-upload-conteudo.php)

Upload enviando o conteúdo XML diretamente (sem arquivo).

```bash
php 2-upload-conteudo.php
```

**Casos de uso:**
- XML gerado em memória
- XML vindo de banco de dados
- Transformações antes do envio

---

### 3️⃣ [3-upload-lote-zip.php](./3-upload-lote-zip.php)

Upload de múltiplos XMLs compactados em ZIP.

```bash
php 3-upload-lote-zip.php
```

**Funciona para:**
- Lotes de até 400 XMLs
- Processamento em massa
- Economia de requisições

---

### 4️⃣ [4-consulta-protocolo.php](./4-consulta-protocolo.php)

Obter protocolo ANTT a partir de uma chave (44 dígitos).

```bash
php 4-consulta-protocolo.php
```

**Resposta:**
```
Chave: 4401234567890123456789012345678901234567
Protocolo: 1234567890123456789012345678901234567890
```

---

### 5️⃣ [5-consulta-inversa.php](./5-consulta-inversa.php)

Consulta inversa: obter chave a partir do protocolo ANTT.

```bash
php 5-consulta-inversa.php
```

**Resposta:**
```
Protocolo: 1234567890123456789012345678901234567890
Chave: 4401234567890123456789012345678901234567
```

---

### 6️⃣ [6-tratamento-erros.php](./6-tratamento-erros.php)

Demonstra tratamento completo de erros e exceções.

```bash
php 6-tratamento-erros.php
```

**Cobre:**
- Arquivo não encontrado
- Credenciais inválidas
- Captcha requerido
- Erros de conexão
- Timeout

---

### 7️⃣ [7-integracao-bd.php](./7-integracao-bd.php)

Integração com banco de dados para auditoria e tracking.

**Funcionalidades:**
- Registrar envios em BD
- Rastreamento de status
- Retry de falhas
- Logging completo

---

## ⚙️ Configuração

Antes de executar qualquer exemplo, altere as credenciais:

```php
$usuario = 'SEU_USUARIO_API_60_CARACTERES';
$senha   = 'SUA_SENHA_API_64_CARACTERES';
```

### Como Obter Credenciais

1. Acesse https://www.averbeporto.com.br
2. Login com seu usuário web
3. Vá em **Cadastro do Usuário**
4. Clique em **X** para gerar credenciais de API
5. Copie os valores gerados

---

## 📂 Estrutura de Arquivos

```
examples/
├── README.md                 (este arquivo)
├── 1-upload-simples.php      
├── 2-upload-conteudo.php     
├── 3-upload-lote-zip.php     
├── 4-consulta-protocolo.php  
├── 5-consulta-inversa.php    
├── 6-tratamento-erros.php    
├── 7-integracao-bd.php       
└── arquivos/                 (créar manualmente com XMLs)
    ├── exemplo.xml
    └── lote.zip
```

---

## 🧪 Executar um Exemplo

### Exemplo 1: Upload Simples

```bash
# 1. Adicone um arquivo XML real em examples/arquivos/
cp /seu/arquivo.xml examples/arquivos/exemplo.xml

# 2. Altere as credenciais em 1-upload-simples.php

# 3. Execute
php examples/1-upload-simples.php
```

**Saída esperada:**
```
📤 Enviando arquivo: /.../ .xml
📋 Resposta da API:
{...}
✅ Status: Processado: XML guardado com sucesso
🎯 Protocolo ANTT: 1234567890123456789012345678901234567890
✨ Upload concluído com sucesso!
```

---

## 🚨 Troubleshooting

### Erro: "arquivo não encontrado"

Crie a pasta `examples/arquivos/` e adicione seus XMLs:

```bash
mkdir examples/arquivos
cp seu-xml.xml examples/arquivos/
```

### Erro: "login falhou"

Verifique:
- Usuário tem 60 caracteres
- Senha tem 64 caracteres
- São credenciais de API (não web)
- Não expirou (validade: 1 semana)

### Erro: "HTTP 403"

Veja [Troubleshooting - HTTP 403](../docs/TROUBLESHOOTING.md#-erro-http-403-forbidden)

---

## 📚 Mais Informações

- **Manual Completo:** [docs/MANUAL.md](../docs/MANUAL.md)
- **API Reference:** [docs/API.md](../docs/API.md)
- **Guia de Erros:** [docs/TROUBLESHOOTING.md](../docs/TROUBLESHOOTING.md)
- **README Principal:** [README.md](../README.md)

---

## 💡 Próximos Passos

1. Teste o **Exemplo 1** com um XML real
2. Explore o **Exemplo 6** para tratamento de erros
3. Veja **Exemplo 7** para integração com BD
4. Leia a documentação completa

---

**Dúvidas?**
- Email: seu.email@exemplo.com
- Issues: https://github.com/seu-usuario/averbporto/issues
- Wiki: https://github.com/seu-usuario/averbporto/wiki

Bom estudo! 🚀
