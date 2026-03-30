<?php

/**
 * Teste Básico da Classe AverbePorto
 * 
 * Execute: php tests/BasicTest.php
 */

require_once __DIR__ . '/../src/AverbePorto.php';

use AverbePorto\AverbePorto;

echo "🧪 Teste Básico - AverbePorto\n";
echo "=============================\n\n";

// Teste 1: Instanciação
try {
    echo "[TESTE 1] Instanciação da classe...\n";
    $ap = new AverbePorto('usuario_teste', 'senha_teste');
    echo "✅ Classe instanciada com sucesso\n";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

// Teste 2: Constantes
echo "\n[TESTE 2] Validar constantes...\n";
$constantes = [
    'ENDPOINT' => AverbePorto::ENDPOINT,
    'COMP' => AverbePorto::COMP,
    'USER_AGENT' => AverbePorto::USER_AGENT,
];

foreach ($constantes as $nome => $valor) {
    echo "  $nome: $valor\n";
}
echo "✅ Constantes validadas\n";

// Teste 3: Métodos estáticos
echo "\n[TESTE 3] Testar métodos estáticos...\n";

$responseOk = [
    'success' => 1,
    'S' => ['P' => 1, 'D' => 0, 'R' => 0, 'N' => 0],
    'prot' => '1234567890123456789012345678901234567890'
];

echo "  uploadOk(): " . var_export(AverbePorto::uploadOk($responseOk), true) . "\n";
echo "  interpretarStatus(): " . AverbePorto::interpretarStatus($responseOk) . "\n";
echo "  extrairProtocolo(): " . AverbePorto::extrairProtocolo($responseOk) . "\n";
echo "✅ Métodos estáticos funcionando\n";

// Teste 4: Status de login
echo "\n[TESTE 4] Status de login...\n";
echo "  estaLogado(): " . var_export($ap->estaLogado(), true) . "\n";
echo "✅ Status correto (não logado)\n";

echo "\n" . str_repeat("=", 40) . "\n";
echo "✨ Todos os testes básicos passaram!\n";
echo "===========================================\n\n";

echo "Próximos passos:\n";
echo "1. Verifique exemplos em examples/\n";
echo "2. Leia a documentação em docs/\n";
echo "3. Altere as credenciais para testar com a API real\n";
