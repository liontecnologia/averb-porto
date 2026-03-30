<?php

namespace AverbePorto;

/**
 * AverbePorto - Biblioteca PHP para integração com a API da Porto Seguro
 *
 * Cobre todas as funcionalidades do Manual de Integração AverbePorto:
 *  - Login com sessão via cookie
 *  - Upload de XML ou ZIP para averbação
 *  - Consulta de protocolo ANTT por chave
 *  - Consulta inversa: protocolo → chave
 *
 * Uso mínimo:
 *   $ap = new AverbePorto('USUARIO', 'SENHA');
 *   $res = $ap->upload('conteudo_do_xml');
 *   $res = $ap->uploadArquivo('/caminho/arquivo.xml');
 *   $res = $ap->consultarChave(['4401...44', '4402...44']);
 *   $res = $ap->consultarProtocolo(['1234567890123456789012345678901234567890']);
 *
 * @author  Biblioteca gerada a partir do Manual de Integração AverbePorto v2.0
 * @version 2.0
 */
class AverbePorto
{
    // -------------------------------------------------------------------------
    // Constantes da API
    // -------------------------------------------------------------------------

    const ENDPOINT   = 'https://apis.averbeporto.com.br/php/conn.php';
    const COMP       = 5;
    const PATH_GUARD = 'eguarda/php/';
    const PATH_PROT  = 'atwe/php/';
    const USER_AGENT = 'Mozilla/5.0 AverbePorto-PHP/2.0';

    // Valores válidos para o parâmetro recipient
    const RECIPIENT_AUTO        = '';   // Automático (recomendado)
    const RECIPIENT_EMBARCADOR  = 'E';
    const RECIPIENT_FORNECEDOR  = 'F';
    const RECIPIENT_TRANSPORTADOR = 'T';
    const RECIPIENT_DUPLO_RAMO  = 'D';

    // -------------------------------------------------------------------------
    // Propriedades internas
    // -------------------------------------------------------------------------

    private string $user;
    private string $pass;
    private string $endpoint;
    private int    $comp;
    private string $cookieFile;
    private bool   $loggedIn = false;
    private int    $timeout  = 60;

    // -------------------------------------------------------------------------
    // Construtor / Destrutor
    // -------------------------------------------------------------------------

    /**
     * @param string $user     Usuário de API (60 caracteres) gerado no "Cadastro do Usuário"
     * @param string $pass     Senha de API (64 caracteres) gerada no "Cadastro do Usuário"
     * @param string $endpoint Endpoint da API (padrão: ENDPOINT)
     * @param int    $comp     Código da empresa (padrão: 5)
     * @param int    $timeout  Timeout em segundos (padrão: 60)
     */
    public function __construct(
        string $user,
        string $pass,
        string $endpoint = self::ENDPOINT,
        int    $comp     = self::COMP,
        int    $timeout  = 60
    ) {
        $this->user     = $user;
        $this->pass     = $pass;
        $this->endpoint = $endpoint;
        $this->comp     = $comp;
        $this->timeout  = $timeout;

        $this->cookieFile = tempnam(sys_get_temp_dir(), 'averbeporto_');
    }

    public function __destruct()
    {
        if (file_exists($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    // -------------------------------------------------------------------------
    // Métodos públicos principais
    // -------------------------------------------------------------------------

    /**
     * Envia o conteúdo de um XML (ou ZIP) para averbação.
     *
     * @param string      $content   Conteúdo bruto do arquivo XML ou ZIP
     * @param string      $recipient Tipo do remetente (use as constantes RECIPIENT_*)
     * @param string|null $filename  Nome do arquivo (opcional, usado no multipart)
     * @return array                 Resposta decodificada da API
     *
     * Exemplo de retorno de sucesso:
     * {
     *   "success": 1,
     *   "S": { "P": 1, "D": 0, "R": 0, "N": 0 },
     *   "prot": "1234567890123"
     * }
     *
     * Campos de S:
     *   P = Processado (guardado com sucesso)
     *   D = Duplicado (XML já existente)
     *   R = Rejeitado (XML não parece ser do tipo certo)
     *   N = Negado (não é XML ou ZIP)
     */
    public function upload(
        string  $content,
        string  $recipient = self::RECIPIENT_AUTO,
        ?string $filename  = null
    ): array {
        $this->ensureLoggedIn();

        // Cria arquivo temporário com o conteúdo
        $tmpFile = tmpfile();
        fwrite($tmpFile, $content);
        rewind($tmpFile);

        $meta     = stream_get_meta_data($tmpFile);
        $mime     = mime_content_type($meta['uri']) ?: 'application/xml';
        $name     = $filename ?? 'arquivo.xml';
        $curlFile = new \CURLFile($meta['uri'], $mime, $name);

        $payload = [
            'comp' => $this->comp,
            'mod'  => 'Upload',
            'path' => self::PATH_GUARD,
            'v'    => 2,
            'file' => $curlFile,
        ];

        if ($recipient !== '') {
            $payload['recipient'] = $recipient;
        }

        $result = $this->post($payload);

        fclose($tmpFile);

        return $result;
    }

    /**
     * Envia um arquivo XML ou ZIP a partir do caminho no disco.
     *
     * @param string $filePath  Caminho absoluto do arquivo
     * @param string $recipient Tipo do remetente (use as constantes RECIPIENT_*)
     * @return array            Resposta decodificada da API
     * @throws \RuntimeException Se o arquivo não existir
     */
    public function uploadArquivo(
        string $filePath,
        string $recipient = self::RECIPIENT_AUTO
    ): array {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("AverbePorto: arquivo não encontrado: {$filePath}");
        }

        $content  = file_get_contents($filePath);
        $filename = basename($filePath);

        return $this->upload($content, $recipient, $filename);
    }

    /**
     * Consulta o protocolo ANTT a partir de uma ou mais chaves de acesso
     * (CT-e, NF-e, MDF-e — 44 dígitos).
     *
     * @param string[] $chaves  Array de chaves de acesso (44 dígitos cada)
     * @param string   $out     Formato de saída: 'json' | 'xml' | 'csv'
     * @param int      $download 0 = display | 1 = download
     * @param string   $delim   Delimitador para CSV (padrão: ',')
     * @return array            Resposta decodificada da API
     *
     * Exemplo de retorno:
     * {
     *   "success": 1,
     *   "S": [
     *     { "chave": "4401...", "protocolo": "1234..." }
     *   ]
     * }
     */
    public function consultarChave(
        array  $chaves,
        string $out      = 'json',
        int    $download = 0,
        string $delim    = ','
    ): array {
        $this->ensureLoggedIn();

        $payload = [
            'comp'     => $this->comp,
            'mod'      => 'Protocolo',
            'path'     => self::PATH_PROT,
            'out'      => $out,
            'download' => $download,
            'delim'    => $delim,
        ];

        // A API espera chave[] como array no POST
        foreach ($chaves as $chave) {
            $payload['chave[]'] = $chave;
        }

        // Para múltiplas chaves, precisa montar query string manualmente
        $queryParts = $this->buildQueryWithArray('chave', $chaves, $payload);

        return $this->postRaw($queryParts);
    }

    /**
     * Consulta inversa: obtém a chave de acesso a partir de um ou mais protocolos ANTT.
     *
     * @param string[] $protocolos  Array de protocolos ANTT
     * @param string   $out         Formato de saída: 'json' | 'xml' | 'csv'
     * @param int      $download    0 = display | 1 = download
     * @param string   $delim       Delimitador para CSV (padrão: ',')
     * @return array                Resposta decodificada da API
     */
    public function consultarProtocolo(
        array  $protocolos,
        string $out      = 'json',
        int    $download = 0,
        string $delim    = ','
    ): array {
        $this->ensureLoggedIn();

        $payload = [
            'comp'     => $this->comp,
            'mod'      => 'Protocolo',
            'path'     => self::PATH_PROT,
            'out'      => $out,
            'download' => $download,
            'delim'    => $delim,
        ];

        $queryParts = $this->buildQueryWithArray('protocolo', $protocolos, $payload);

        return $this->postRaw($queryParts);
    }

    /**
     * Força um novo login, descartando a sessão atual.
     * Útil quando a sessão expirar (validade: 1 semana com credenciais de API).
     *
     * @return array Resposta do login
     */
    public function relogin(): array
    {
        $this->loggedIn = false;

        // Limpa cookie atual
        if (file_exists($this->cookieFile)) {
            file_put_contents($this->cookieFile, '');
        }

        return $this->login();
    }

    /**
     * Retorna se há uma sessão ativa.
     */
    public function estaLogado(): bool
    {
        return $this->loggedIn;
    }

    // -------------------------------------------------------------------------
    // Métodos de interpretação da resposta
    // -------------------------------------------------------------------------

    /**
     * Interpreta o campo S do retorno do upload e retorna uma descrição legível.
     *
     * @param array $response Resposta da API (retorno de upload())
     * @return string
     */
    public static function interpretarStatus(array $response): string
    {
        if (empty($response['success'])) {
            $msg = $response['error']['msg'] ?? 'Erro desconhecido';
            return "Falha na comunicação: {$msg}";
        }

        if (isset($response['logout'])) {
            return 'Sessão expirada ou credenciais inválidas';
        }

        if (isset($response['error']['code']) && $response['error']['code'] === '02') {
            return 'Captcha exigido. Acesse o sistema web para resolver: ' . ($response['captcha_url'] ?? '');
        }

        $s = $response['S'] ?? [];

        if (!empty($s['P'])) {
            return 'Processado: XML guardado com sucesso';
        }
        if (!empty($s['D'])) {
            return 'Duplicado: XML já existente';
        }
        if (!empty($s['R'])) {
            return 'Rejeitado: XML não parece ser do tipo correto';
        }
        if (!empty($s['N'])) {
            return 'Negado: não é XML ou ZIP';
        }

        $errorMsg = $response['error']['msg'] ?? null;
        return $errorMsg ? "Erro: {$errorMsg}" : 'Resposta desconhecida';
    }

    /**
     * Retorna true se o upload foi processado com sucesso (P=1 e prot presente).
     */
    public static function uploadOk(array $response): bool
    {
        return !empty($response['success'])
            && !empty($response['S']['P'])
            && isset($response['prot']);
    }

    /**
     * Extrai o protocolo ANTT da resposta do upload.
     * Para ZIP retorna array de protocolos; para XML único retorna string.
     *
     * @param array $response
     * @return string|array|null
     */
    public static function extrairProtocolo(array $response)
    {
        return $response['prot'] ?? null;
    }

    // -------------------------------------------------------------------------
    // Métodos privados
    // -------------------------------------------------------------------------

    /**
     * Garante login antes de qualquer operação.
     */
    private function ensureLoggedIn(): void
    {
        if ($this->loggedIn) {
            return;
        }

        $this->login();
    }

    /**
     * Executa o login na API e armazena a sessão via cookie.
     *
     * @return array Resposta do login
     * @throws \RuntimeException
     */
    private function login(): array
    {
        $payload = [
            'mod'  => 'login',
            'comp' => $this->comp,
            'user' => $this->user,
            'pass' => $this->pass,
        ];

        // Login usa x-www-form-urlencoded (não multipart)
        $result = $this->postForm($payload);

        // Sessão expirada / credenciais inválidas
        if (!empty($result['logout'])) {
            throw new \RuntimeException(
                'AverbePorto: login falhou — usuário ou senha inválidos, ou sessão expirada'
            );
        }

        // Captcha exigido
        if (isset($result['error']['code']) && $result['error']['code'] === '02') {
            throw new \RuntimeException(
                'AverbePorto: captcha exigido. Resolva em: ' . ($result['captcha_url'] ?? self::ENDPOINT)
            );
        }

        // Falha genérica de autenticação
        if (empty($result['success']) || empty($result['C'])) {
            $msg = $result['error']['msg'] ?? 'sem mensagem';
            throw new \RuntimeException("AverbePorto: login falhou — {$msg}");
        }

        $this->loggedIn = true;

        return $result;
    }

    /**
     * POST application/x-www-form-urlencoded (usado no login).
     */
    private function postForm(array $fields): array
    {
        $ch = $this->buildCurl();

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        ]);

        return $this->execute($ch);
    }

    /**
     * POST multipart/form-data (usado no upload).
     */
    private function post(array $fields): array
    {
        $ch = $this->buildCurl();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        return $this->execute($ch);
    }

    /**
     * POST com query string montada manualmente (para arrays chave[] / protocolo[]).
     */
    private function postRaw(string $queryString): array
    {
        $ch = $this->buildCurl();

        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        return $this->execute($ch);
    }

    /**
     * Monta a query string com suporte a parâmetros array (chave[], protocolo[]).
     */
    private function buildQueryWithArray(string $arrayKey, array $values, array $baseParams): string
    {
        // Remove o arrayKey do base se existir
        unset($baseParams[$arrayKey . '[]'], $baseParams[$arrayKey]);

        $parts = [];

        foreach ($baseParams as $k => $v) {
            $parts[] = urlencode($k) . '=' . urlencode((string)$v);
        }

        foreach ($values as $v) {
            $parts[] = urlencode($arrayKey . '[]') . '=' . urlencode($v);
        }

        return implode('&', $parts);
    }

    /**
     * Inicializa o handle cURL com as opções comuns.
     */
    private function buildCurl(): \CurlHandle
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->endpoint,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADER         => false,
            CURLOPT_USERAGENT      => self::USER_AGENT,
            CURLOPT_COOKIEJAR      => $this->cookieFile,
            CURLOPT_COOKIEFILE     => $this->cookieFile,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_ENCODING       => 'gzip, deflate', // --compressed equivalente
        ]);

        return $ch;
    }

    /**
     * Executa o handle cURL, verifica erros e decodifica o JSON.
     */
    private function execute(\CurlHandle $ch): array
    {
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException("AverbePorto: erro cURL — {$error}");
        }

        if ($httpCode === 403) {
            throw new \RuntimeException(
                'AverbePorto: acesso bloqueado (HTTP 403). Verifique o User-Agent ou o subdomínio utilizado.'
            );
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                "AverbePorto: resposta inválida da API (HTTP {$httpCode}): {$response}"
            );
        }

        return $decoded;
    }
}
