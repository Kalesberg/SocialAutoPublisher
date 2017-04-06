<?php
session_start();
require_once '../../vendor/autoload.php';

use Happyr\LinkedIn\LinkedIn;

$configPath = '.config.json';
if(!file_exists($configPath))
	throw new \Exception('Not found .config.json');
$contents = file_get_contents($configPath);

$config = json_decode($contents);

$linkedIn=new LinkedIn($config->ClientID, $config->ClientSecret);
if(!isset($_SESSION['linkedin.token'])) {
	if(!isset($_REQUEST['code'])) {
		$isHttps = ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') || (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] === '443')));
		$redirect = ($isHttps ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$url = $linkedIn->getLoginUrl(['redirect_uri' => $redirect]);
		header("Location: " . $url);
	}
	else {
		if ($linkedIn->isAuthenticated()) {
			$_SESSION['linkedin.id'] = $config->ClientID;
			$_SESSION['linkedin.secret'] = $config->ClientSecret;
			$_SESSION['linkedin.token'] = (string)$linkedIn->getAccessToken();
		} 
	}
}
?>
<script>
	window.opener.popup.close();
</script>