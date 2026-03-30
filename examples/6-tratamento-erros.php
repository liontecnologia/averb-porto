<?php

/**
 * EXEMPLO 6: Tratamento Completo de Erros
 * 
 * Demonstra como tratar diferentes tipos de erro
 */

require_once __DIR__ . '/../src/AverbePorto.php';

use AverbePorto\AverbePorto;

// ===== CONFIGURAÇÃO =====
$usuario = 'SEU_USUARIO_API_60_CARACTERES';  // Alterar com credenciais reais
$senha   = 'SUA_SENHA_API_64_CARACTERES';    // Alterar com credenciais reais

function enviarComTratamento($caminho) {
    try {
        echo "📤 Processando: $caminho\n";
        
        $ap = new AverbePorto($usuario, $senha);
        $response = $ap->uploadArquivo($caminho);
        
        // Processar resposta
        if (AverbePorto::uploadOk($response)) {
            echo "✅ Sucesso!\n";
            echo "   Protocolo: " . AverbePorto::extrairProtocolo($response) . "\n";
            return true;
        } else {
            echo "⚠️  Aviso: " . AverbePorto::interpretarStatus($response) . "\n";
            return false;
        }
        
    } catch (RuntimeException $e) {
        $msg = $e->getMessage();
        
        // Diferentes causas de erro
        if (strpos($msg, 'arquivo não encontrado') !== false) {
            echo "❌ Arquivo não existe\n";
            
        } elseif (strpos($msg, 'login falhou') !== false) {
            echo "❌ Credenciais inválidas\n";
            echo "   Verifique usuário e senha de API\n";
            
        } elseif (strpos($msg, 'captcha exigido') !== false) {
            echo "❌ Captcha requerido\n";
            echo "   Acesse o portal web para desbloquear\n";
            
        } elseif (strpos($msg, 'acesso bloqueado') !== false) {
            echo "❌ Acesso bloqueado (HTTP 403)\n";
            echo "   Verifique subdomínio e User-Agent\n";
            
        } elseif (strpos($msg, 'erro cURL') !== false) {
            echo "❌ Erro de conexão\n";
            echo "   Verifique internet e certificados SSL\n";
            
        } else {
            echo "❌ Erro genérico: $msg\n";
        }
        
        return false;
    }
}

// ===== TESTE =====

echo "🧪 Teste de Tratamento de Erros\n";
echo "================================\n\n";

// Teste 1: Arquivo inexistente
echo "[TESTE 1] Arquivo não encontrado\n";
enviarComTratamento(__DIR__ . '/arquivos/nao-existe.xml');
echo "\n";

// Teste 2: Arquivo válido
echo "[TESTE 2] Arquivo válido\n";
// Descomente e adicione um arquivo real:
// enviarComTratamento(__DIR__ . '/arquivos/exemplo.xml');
echo "(Descomente para testar com arquivo real)\n";
echo "\n";

// Teste 3: Credenciais inválidas
echo "[TESTE 3] Credenciais inválidas\n";
try {
    $ap = new AverbePorto('USUARIO_INVALIDO', 'SENHA_INVALIDA');
    $ap->uploadArquivo(__DIR__ . '/arquivos/exemplo.xml');
} catch (RuntimeException $e) {
    echo "Erro capturado: " . $e->getMessage() . "\n";
}
