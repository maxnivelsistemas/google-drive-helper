<?php

require __DIR__ . '/vendor/autoload.php';

/**
 * Esta classe facilita a conexão com a API do google drive, até o momento a sua
 * única função é enviar arquivos grandes para o google drive de forma fragmentada
 * (através de chunks)
 * 
 * @author Yuri de Paula
 */
class GoogleDrive {
	
    private $applicatioName;
    private $credentialsPath;
    private $refreshToken;
    private $chunkSize;
    private $scopes;
    private $config;

    /**
     * Fração mínima, em bytes, que pode ser envida atravéz do metodo de uploade 
     * de arquivo fracionado do google drive.
     * @var int
     */
    const TAMANHO_MINIMO_CHUNK =  262144;
	
    public function __construct() {

    }

    /**
    * Retorn um cliente autenticado.
    * 
    * @param array $config array com os dados de configurações e credenciais
    * 
    * @throws \Exception Os dados de credenciais precisam ser informados, caso
    * o argumento $config não tenha sido passado ou a propriedade credentialsPath
    * não tenha sido setada, ele gera uma exceção.
    * 
    * @return Google_Client
    */
    private function getCliente($config = []){
        $cliente = new Google_Client();
        $cliente->setApplicationName($this->applicatioName);
        $cliente->setScopes($this->scopes);
        if (empty($config)){
            if ($this->credentialsPath){
                $cliente->setAuthConfigFile($this->credentialsPath);
            } else {
                throw new Exception('Caminho para o arquivo de credenciais ou credenciais via parametro config precisa ser fornecido.');
            }
        } else {
            $cliente->setAuthConfig($config);
        }
        $cliente->setAccessType('offline');
        
        if (file_exists('token.json')) {
            $accessToken = json_decode(file_get_contents('token.json'), true);
            $cliente->setAccessToken($accessToken);
        } else {
            $cliente->fetchAccessTokenWithRefreshToken($this->refreshToken);
        }
        if ($cliente->isAccessTokenExpired()) {
            $cliente->getRefreshToken();
            $cliente->fetchAccessTokenWithRefreshToken($cliente->getRefreshToken());
            if (!file_exists(dirname('token.json'))) {
                mkdir(dirname('token.json'), 0700, true);
            }
            file_put_contents('token.json', json_encode($cliente->getAccessToken()));
        }
        return $cliente;
    }

    /**
    * Faz o uploade de um arquivo muito grande (maior do que a pilha de memoria do php suporta) 
    * 
    * @throws \Exception o tamanho do chunk (fração do arquivo), caso o parametro $chunkSize não
    * tenha sido passado ou a propriedade chunkSize não tenha sido setada, ira gerar uma exceção.
    * 
    * @return void
    */
    public function uploadLargerFile($filePath, $chunkSize = null){
        if (!$this->chunkSize){
            if (!$chunkSize){
                throw new Exception('chunkSize precisa ser informado.');
            } else {
                $this->chunkSize = $chunkSize;    
            }
        }
        $cliente = $this->getCliente($this->config);
        $driveService = new Google_Service_Drive($cliente);
        $cliente->setDefer(true);
        $file = new Google_Service_Drive_DriveFile();
        $file->name = $filePath;
        $name = explode(DIRECTORY_SEPARATOR, $filePath);
        $name = $name[count($name) - 1];
        $request = $driveService->files->create($file);
        $media = new Google_Http_MediaFileUpload($cliente, $request, "text/plain", null, true, $this->chunkSize); 
        $media->setFileSize(filesize($filePath));
        $status = false; 
        $handle = fopen($filePath, "rb"); 
        while(!$status && !feof($handle)) { 
            $chunk = fread($handle, $this->chunkSize); 
            $status = $media->nextChunk($chunk);
        } 
        $cliente->setDefer(false);
    }
    
    public function setApplicatioName($applicatioName) {
        $this->applicatioName = $applicatioName;
    }

    public function setCredentialsPath($credentialsPath) {
        $this->credentialsPath = $credentialsPath;
    }

    public function setScopes($scopes) {
        $this->scopes = $scopes;
    }

    public function setChunkSize($chunkSize) {
        $this->chunkSize = $chunkSize;
    }
    
    public function setRefreshToken($refreshToken) {
        $this->refreshToken = $refreshToken;
    }
    
    public function setConfig($config) {
        $this->config = $config;
    }
}