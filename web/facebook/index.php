<?php
session_start();
require_once '../../vendor/autoload.php';

use Facebook\Facebook;

$configPath = '.config.json';
if(!file_exists($configPath))
	throw new \Exception('Not found .config.json');
$contents = file_get_contents($configPath);

$config = (array) json_decode($contents);

$fb = new Facebook($config);

if(!isset($_SESSION['facebook.token'])) {
	$helper = $fb->getRedirectLoginHelper();
	try {
		$accessToken = $helper->getAccessToken();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	if (isset($accessToken)) {
		// Logged in!
		$_SESSION['facebook.config'] = $config;
		$_SESSION['facebook.token'] = (string) $accessToken;
	}
	else {
		$permissions = ['user_posts', 'publish_pages', 'publish_actions', 'manage_pages'];
		$isHttps = ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') || (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] === '443')));
		$redirect = ($isHttps ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$loginURL = $helper->getLoginUrl($redirect, $permissions);
		header("Location: " . $loginURL);
	}
}
?>
<script>
	window.opener.facebookPages();
	window.opener.popup.close();
</script>