<?php

/**
 * EXEMPLO 5: Consulta Inversa (Protocolo → Chave)
 * 
 * Demonstra como consultar a chave de acesso a partir do protocolo ANTT
 */

require_once __DIR__ . '/../src/AverbePorto.php';

use AverbePorto\AverbePorto;

// ===== CONFIGURAÇÃO =====
$usuario = 'SEU_USUARIO_API_60_CARACTERES';
$senha   = 'SUA_SENHA_API_64_CARACTERES';

try {
    $ap = new AverbePorto($usuario, $senha);
    
    // Protocolos ANTT
    $protocolos = [
        '1234567890123456789012345678901234567890',
        '9876543210987654321098765432109876543210',
    ];
    
    echo "🔍 Consultando chaves inversas (protocolo → chave)...\n";
    foreach ($protocolos as $protocolo) {
        echo "   - $protocolo\n";
    }
    
    // Consultar
    $response = $ap->consultarProtocolo($protocolos);
    
    echo "\n📋 Resultado:\n";
    
    if (!empty($response['S'])) {
        foreach ($response['S'] as $item) {
            echo "   Protocolo: {$item['protocolo']}\n";
            echo "   Chave: {$item['chave']}\n";
            echo "   ---\n";
        }
    } else {
        echo "   Nenhum resultado encontrado\n";
    }
    
} catch (RuntimeException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
