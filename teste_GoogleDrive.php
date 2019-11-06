<?php
include 'GoogleDrive.php';

define('CLIENT_ID', '544475658637-uevgas0usfn6uhobqtfak92mc214h0vq.apps.googleusercontent.com');
define('PROJECT_ID', 'quickstart-1572033104041');
define('CLIENT_SECRET', 'PSIEZLyqd3Y8MEsc5tzu-VMq');


$teste = new GoogleDrive();
$config = [];
$config['web'] = [];
$config['web']['client_id'] = CLIENT_ID;
$config['web']['project_id'] = PROJECT_ID;
$config['web']['auth_uri'] = 'https://accounts.google.com/o/oauth2/auth';
$config['web']['token_uri'] = 'https://accounts.google.com/o/oauth2/token';
$config['web']['auth_provider_x509_cert_url'] = 'https://www.googleapis.com/oauth2/v1/certs';
$config['web']['client_secret'] = CLIENT_SECRET;
$config['web']['redirect_uris'][] = 'http://localhost';

/* Caso se queira autenticar via o arquivo de credenciais
 * $teste->setCredentialsPath('credentials.json');
 * 
 * Caso não exista o arquivo token.json
 * $teste->setRefreshToken('1\/\/0h35WbVGLlsHrCgYIARAAGBESNwF-L9IrnIPjKWr1L7AtB2aqt_zNWkg4OpmQGrBvdhLrckCD3NthmEy6rFFFCYV0L6rUJuqUhMs');
 */

$teste->setConfig($config);
$teste->setScopes(Google_Service_Drive::DRIVE);
$teste->uploadLargerFile('t.sql.gz', $teste::TAMANHO_MINIMO_CHUNK);
