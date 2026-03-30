# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2024-12-01

### Added

- API completa para integração com Porto Seguro AverbePorto
- Autenticação com sessão via cookie
- Upload de XML e ZIP para averbação
- Suporte a NF-e, NFC-e, CT-e, MDF-e
- Consulta de protocolo ANTT por chave
- Consulta inversa (protocolo → chave)
- Métodos estáticos para interpretação de resposta
- Tratamento robusto de erros
- Gerenciamento automático de sessão (1 semana)
- Exemplos de uso para diferentes cenários
- Documentação completa em português
- Suporte a PHP 7.4+

### Documentation

- README.md com instruções de instalação e uso rápido
- Manual completo em docs/MANUAL.md
- API Reference em docs/API.md
- Troubleshooting em docs/TROUBLESHOOTING.md
- 7 exemplos práticos de uso
- Integração com banco de dados
- Tratamento de erros e retry

### Security

- Validação de User-Agent obrigatório
- Uso de credenciais de API separadas (não web)
- SSL/TLS verificado por padrão
- Proteção contra rate limiting

## [1.0.0] - 2024-11-15

### Added

- Versão inicial da biblioteca
- Funcionalidade básica de upload
- Consulta de protocolo

---

## Como Contribuir

Para maiores informações sobre versioning, consulte [CONTRIBUTING.md](./CONTRIBUTING.md).
