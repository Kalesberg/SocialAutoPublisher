<?php
session_start();
require_once '../../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
$configPath = '.config.json';
if(!file_exists($configPath))
	throw new \Exception('Not found .config.json');
$contents = file_get_contents($configPath);

$config = json_decode($contents);

if(!isset($_SESSION['twitter.token'])) {
	if(!isset($_REQUEST['oauth_token'])) {
		$connection = new TwitterOAuth($config->ConsumerKey, $config->ConsumerSecret);
		$isHttps = ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') || (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] === '443')));
		
		$redirect = ($isHttps ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $redirect));
		$_SESSION['twitter.oauth_token'] = $request_token['oauth_token'];
		$_SESSION['twitter.oauth_token_secret'] = $request_token['oauth_token_secret'];
		$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
		header("Location: " . $url);
	}
	else {
		$request_token = [];
		$request_token['oauth_token'] = $_SESSION['twitter.oauth_token'];
		$request_token['oauth_token_secret'] = $_SESSION['twitter.oauth_token_secret'];
		
		if($request_token['oauth_token'] !== $_REQUEST['oauth_token'])
			die('Something went wrong..');
		
		$connection = new TwitterOAuth($config->ConsumerKey, $config->ConsumerSecret, $request_token['oauth_token'], $request_token['oauth_token_secret']);
		$access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);
		
		$_SESSION['twitter.key'] = $config->ConsumerKey;
		$_SESSION['twitter.secret'] = $config->ConsumerSecret;
		$_SESSION['twitter.token'] = $access_token;
	}
}
?>
<script>
	window.opener.popup.close();
</script>