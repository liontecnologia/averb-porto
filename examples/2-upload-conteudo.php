<?php

/**
 * EXEMPLO 2: Upload por Conteúdo
 * 
 * Demonstra como fazer upload enviando o conteúdo XML diretamente
 */

require_once __DIR__ . '/../src/AverbePorto.php';

use AverbePorto\AverbePorto;

// ===== CONFIGURAÇÃO =====
$usuario = 'SEU_USUARIO_API_60_CARACTERES';
$senha   = 'SUA_SENHA_API_64_CARACTERES';

try {
    $ap = new AverbePorto($usuario, $senha);
    
    // Exemplo 1: XML mínimo (você deve usar um XML real)
    $xmlContent = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<cteProc>
    <!-- Adicione aqui o seu XML completo de CT-e -->
</cteProc>
XML;
    
    echo "📤 Enviando XML por conteúdo...\n";
    
    // Enviar o conteúdo
    $response = $ap->upload($xmlContent, AverbePorto::RECIPIENT_AUTO, 'documento.xml');
    
    echo "✅ Resposta: " . AverbePorto::interpretarStatus($response) . "\n";
    
    if (AverbePorto::uploadOk($response)) {
        echo "🎯 Protocolo: " . AverbePorto::extrairProtocolo($response) . "\n";
    }
    
} catch (RuntimeException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
