<?php

/**
 * EXEMPLO 4: Consulta de Protocolo ANTT
 * 
 * Demonstra como consultar o protocolo ANTT a partir de uma ou mais chaves
 */

require_once __DIR__ . '/../src/AverbePorto.php';

use AverbePorto\AverbePorto;

// ===== CONFIGURAÇÃO =====
$usuario = 'SEU_USUARIO_API_60_CARACTERES';
$senha   = 'SUA_SENHA_API_64_CARACTERES';

try {
    $ap = new AverbePorto($usuario, $senha);
    
    // Chaves de acesso de 44 dígitos
    $chaves = [
        '4401234567890123456789012345678901234567',  // CT-e
        '3505890123456789012345678901234567890123',  // NF-e
    ];
    
    echo "🔍 Consultando protocolos das chaves...\n";
    foreach ($chaves as $chave) {
        echo "   - $chave\n";
    }
    
    // Consultar
    $response = $ap->consultarChave($chaves);
    
    echo "\n📋 Resultado:\n";
    
    if (!empty($response['S'])) {
        foreach ($response['S'] as $item) {
            echo "   Chave: {$item['chave']}\n";
            echo "   Protocolo: {$item['protocolo']}\n";
            echo "   ---\n";
        }
    } else {
        echo "   Nenhum resultado encontrado\n";
    }
    
    // Outras opções de saída
    echo "\n💡 Dicas:\n";
    echo "   - Formato: 'json' (padrão), 'xml' ou 'csv'\n";
    echo "   - Download: use segundo parâmetro = 1\n";
    
} catch (RuntimeException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
