<?php

/**
 * EXEMPLO 1: Upload Simples
 * 
 * Demonstra como fazer o upload de um arquivo XML simples
 */

require_once __DIR__ . '/../src/AverbePorto.php';

use AverbePorto\AverbePorto;

// ===== CONFIGURAÇÃO =====
$usuario = 'SEU_USUARIO_API_60_CARACTERES';
$senha   = 'SUA_SENHA_API_64_CARACTERES';

try {
    // 1. Inicializar a biblioteca
    $ap = new AverbePorto($usuario, $senha);
    
    // 2. Ler o arquivo XML
    $xmlFile = __DIR__ . '/arquivos/exemplo.xml';
    
    if (!file_exists($xmlFile)) {
        throw new RuntimeException("Arquivo não encontrado: $xmlFile");
    }
    
    echo "📤 Enviando arquivo: $xmlFile\n";
    
    // 3. Enviar via método do arquivo (recomendado)
    $response = $ap->uploadArquivo($xmlFile);
    
    // 4. Verificar resultado
    echo "\n📋 Resposta da API:\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
    // 5. Interpretar resposta
    echo "\n✅ Status: " . AverbePorto::interpretarStatus($response) . "\n";
    
    // 6. Se processado com sucesso, extrair protocolo
    if (AverbePorto::uploadOk($response)) {
        $protocolo = AverbePorto::extrairProtocolo($response);
        echo "🎯 Protocolo ANTT: $protocolo\n";
    }
    
} catch (RuntimeException $e) {
    echo "\n❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Upload concluído com sucesso!\n";
