<?php

require __DIR__ . '/vendor/autoload.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GoogleDrive
 *
 * @author maxnivel
 */
class GoogleDrive {
    public function __construct() {
        define('APPLICATION_NAME', 'Google Drive API PHP Quickstart');
        define('CREDENTIALS_PATH', __DIR__ . '/credentials.json');
        define('TOKEN_PATH', __DIR__ . '/token.json');
        define('CHUNK_SIZE', 262144);
    }

    /**
    * Returns an authorized API client.
    * @return Google_Client the authorized client object
    */
    private function getClient(){
        $client = new Google_Client();
        $client->setApplicationName(APPLICATION_NAME);
        $client->setScopes('https://www.googleapis.com/auth/drive.file');
        $client->setAuthConfigFile(CREDENTIALS_PATH);
        $client->setAccessType('offline');
        
        if (file_exists(TOKEN_PATH)) {
            $accessToken = json_decode(file_get_contents(TOKEN_PATH), true);
            $client->setAccessToken($accessToken);
        }
        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            $client->getRefreshToken();
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            // Save the token to a file.
            if (!file_exists(dirname(TOKEN_PATH))) {
                mkdir(dirname(TOKEN_PATH), 0700, true);
            }
            file_put_contents(TOKEN_PATH, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    /**
    * Faz o uploade de um arquivo muito grande (maior do que a pilha de memoria do php suporta) 
    * @return void
    */
    public function upload_larger_file($filePath){
        try {
            $client = $this->getClient();
            $driveService = new Google_Service_Drive($client);
            $client->setDefer(true);
            $file = new Google_Service_Drive_DriveFile();
            $file->name = $filePath;
            $name = explode(DIRECTORY_SEPARATOR, $filePath);
            $name = $name[count($name) - 1];
            $request = $driveService->files->create($file);
            $media = new Google_Http_MediaFileUpload($client, $request, "text/plain", null, true, CHUNK_SIZE); 
            $media->setFileSize(filesize($filePath)); 
            $status = false; 
            $handle = fopen($filePath, "rb"); 
            while(!$status && !feof($handle)) { 
                $chunk = fread($handle, CHUNK_SIZE); 
                $status = $media->nextChunk($chunk);
            } 
            $client->setDefer(false);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}