# Contributing

Obrigado pelo interesse em contribuir para o AverbePorto!

## Como Contribuir

### Reportar Bugs

1. Verifique se o bug já foi reportado em [Issues](https://github.com/seu-usuario/averbporto/issues)
2. Se não encontrar, abra uma nova issue com:
   - Descrição clara do problema
   - Passos para reproduzir
   - PHP version
   - Mensagem de erro completa
   - Seu código (sem credenciais)

### Sugestões de Melhoria

1. Use o título "Sugestão:" em nova issue
2. Descreva o caso de uso
3. Explique por que seria útil

### Pull Requests

1. Faça fork do projeto
2. Crie uma branch (`git checkout -b feature/melhoria`)
3. Commit com mensagens claras (`git commit -m 'Adiciona melhoria'`)
4. Push para a branch (`git push origin feature/melhoria`)
5. Abra um Pull Request

## Padrões de Código

- PSR-12: Padrão de codificação PHP
- Documentação em português
- Type hints obrigatórios
- Testes para novas features

### Executar Testes

```bash
composer test
composer check
```

### Estilo de Código

```bash
phpcs src/ --standard=PSR12
phpcbf src/ --standard=PSR12  # Auto-fix
```

## Processo de Review

1. Código deve passar em testes
2. Cobertura mínima de 80%
3. Sem warnings/erros do phpcs
4. Documentação atualizada
5. Changelog atualizado

## Código de Ética

- Respeito com todos
- Não discriminação
- Feedback construtivo
- Foco na qualidade

## Dúvidas?

- Abra uma issue com tag `dúvida`
- Email: seu.email@exemplo.com
- Wiki: https://github.com/seu-usuario/averbporto/wiki

Obrigado por contribuir! 🎉
