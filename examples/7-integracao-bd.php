<?php

/**
 * EXEMPLO 7: Integração com Banco de Dados
 * 
 * Demonstra como integrar o envio com registros em BD
 */

require_once __DIR__ . '/../src/AverbePorto.php';

use AverbePorto\AverbePorto;

// ===== CONFIGURAÇÃO =====
$usuario = 'SEU_USUARIO_API_60_CARACTERES';
$senha   = 'SUA_SENHA_API_64_CARACTERES';

/**
 * Exemplo de estrutura de BD (SQLite/MySQL)
 * 
 * CREATE TABLE averbacoes (
 *     id INT PRIMARY KEY AUTO_INCREMENT,
 *     chave VARCHAR(44) UNIQUE,
 *     tipo VARCHAR(10),
 *     protocolo VARCHAR(40),
 *     status VARCHAR(20),
 *     data_envio DATETIME,
 *     data_processamento DATETIME,
 *     resposta JSON,
 *     INDEX idx_chave (chave),
 *     INDEX idx_status (status)
 * );
 */

class AverbacaoManager {
    private $ap;
    private $pdo;
    
    public function __construct($usuario, $senha, $pdo) {
        $this->ap = new AverbePorto($usuario, $senha);
        $this->pdo = $pdo;
    }
    
    /**
     * Envia arquivo e registra no BD
     */
    public function enviarComRegistro($caminhoArquivo, $chave, $tipo) {
        try {
            echo "📤 Enviando: $caminhoArquivo\n";
            
            // Registrar inicialmente como pendente
            $this->registrarPendente($chave, $tipo, $caminhoArquivo);
            
            // Enviar
            $response = $this->ap->uploadArquivo($caminhoArquivo);
            
            // Processar resposta
            if (AverbePorto::uploadOk($response)) {
                $protocolo = AverbePorto::extrairProtocolo($response);
                $this->registrarSucesso($chave, $protocolo, $response);
                echo "✅ Sucesso! Protocolo: $protocolo\n";
                return true;
            } else {
                $status = AverbePorto::interpretarStatus($response);
                $this->registrarErro($chave, $status, $response);
                echo "⚠️  Aviso: $status\n";
                return false;
            }
            
        } catch (RuntimeException $e) {
            $this->registrarErro($chave, $e->getMessage(), []);
            echo "❌ Erro: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function registrarPendente($chave, $tipo, $arquivo) {
        $sql = "INSERT INTO averbacoes (chave, tipo, status, data_envio, resposta)
                VALUES (:chave, :tipo, 'PENDENTE', NOW(), :resposta)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':chave' => $chave,
            ':tipo' => $tipo,
            ':resposta' => json_encode(['arquivo' => $arquivo])
        ]);
    }
    
    private function registrarSucesso($chave, $protocolo, $response) {
        $sql = "UPDATE averbacoes 
                SET status = 'SUCESSO', 
                    protocolo = :protocolo,
                    data_processamento = NOW(),
                    resposta = :resposta
                WHERE chave = :chave";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':chave' => $chave,
            ':protocolo' => $protocolo,
            ':resposta' => json_encode($response)
        ]);
    }
    
    private function registrarErro($chave, $erro, $response) {
        $sql = "UPDATE averbacoes 
                SET status = 'ERRO', 
                    data_processamento = NOW(),
                    resposta = :resposta
                WHERE chave = :chave";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':chave' => $chave,
            ':resposta' => json_encode(array_merge($response, ['erro' => $erro]))
        ]);
    }
    
    /**
     * Retenta arquivos com erro
     */
    public function retentarFalhas($limite = 10) {
        $sql = "SELECT * FROM averbacoes 
                WHERE status = 'ERRO'
                LIMIT :limite";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($registros as $reg) {
            echo "🔄 Retentando: {$reg['chave']}\n";
            // Aqui você teria a lógica de retentar
        }
    }
    
    /**
     * Consulta status de um envio
     */
    public function consultarStatus($chave) {
        $sql = "SELECT * FROM averbacoes WHERE chave = :chave";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':chave' => $chave]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// ===== EXEMPLO DE USO =====

// Se você tiver um BD SQLite conectado:
// $pdo = new PDO('sqlite:averbacoes.db');
// $manager = new AverbacaoManager($usuario, $senha, $pdo);
// $manager->enviarComRegistro('arquivo.xml', '4401234567890123456789012345678901234567', 'CARGA');

echo "Exemplo de integração com BD criado.\n";
echo "Descomente a seção 'EXEMPLO DE USO' para testar.\n";
