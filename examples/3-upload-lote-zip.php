<?php

/**
 * EXEMPLO 3: Upload em Lote (ZIP)
 * 
 * Demonstra como enviar múltiplos XMLs compactados em um arquivo ZIP
 */

require_once __DIR__ . '/../src/AverbePorto.php';

use AverbePorto\AverbePorto;

// ===== CONFIGURAÇÃO =====
$usuario = 'SEU_USUARIO_API_60_CARACTERES';
$senha   = 'SUA_SENHA_API_64_CARACTERES';

try {
    $ap = new AverbePorto($usuario, $senha);
    
    // Caminho do arquivo ZIP
    $zipFile = __DIR__ . '/arquivos/lote.zip';
    
    if (!file_exists($zipFile)) {
        throw new RuntimeException("Arquivo ZIP não encontrado: $zipFile");
    }
    
    echo "📦 Enviando lote em ZIP...\n";
    echo "   Arquivo: $zipFile\n";
    
    // Ler o ZIP
    $zipContent = file_get_contents($zipFile);
    
    // Enviar
    $response = $ap->upload($zipContent, AverbePorto::RECIPIENT_AUTO, 'lote.zip');
    
    echo "\n📋 Resultado:\n";
    echo json_encode($response['S'] ?? [], JSON_PRETTY_PRINT) . "\n";
    
    // Para ZIPs, o campo S também retorna informações de processamento
    $s = $response['S'] ?? [];
    echo "\n📊 Estatísticas:\n";
    echo "   Processados (P): " . ($s['P'] ?? 0) . "\n";
    echo "   Duplicados (D):  " . ($s['D'] ?? 0) . "\n";
    echo "   Rejeitados (R):  " . ($s['R'] ?? 0) . "\n";
    echo "   Negados (N):     " . ($s['N'] ?? 0) . "\n";
    
    // Para ZIPs com sucesso, pode haver array de protocolos
    if (isset($response['prot']) && is_array($response['prot'])) {
        echo "\n🎯 Protocolos:\n";
        foreach ($response['prot'] as $idx => $prot) {
            echo "   [$idx] $prot\n";
        }
    }
    
} catch (RuntimeException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Upload em lote concluído!\n";
