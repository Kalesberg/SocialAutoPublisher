<?php
session_start();
require_once '../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Facebook\Facebook;
use Happyr\LinkedIn\LinkedIn;

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']))
	die('Access denied');

$alerts = [];
function tweet($message) {
	global $alerts;
	if(!isset($_SESSION['twitter.token'])) {
		$alerts[] = 'You are not connected to Twitter';
		return;
	}
	
	$key = $_SESSION['twitter.key'];
	$secret = $_SESSION['twitter.secret'];
	$access_token = $_SESSION['twitter.token'];
	$twitter = new TwitterOAuth($key, $secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
	$media = $twitter->upload('media/upload', array('media' => 'https://awesomewallpaper.files.wordpress.com/2012/11/galaxyngc3190_2560x1600.jpg'));
	$parameters = array(
		'status' => $message,
		'media_ids' => $media->media_id_string
	);
	$twitter->post('statuses/update', $parameters);
	
	return true;
}
function postToFacebook($message, $pages = array()) {
	global $alerts;
	if(!isset($_SESSION['facebook.token'])) {
		$alerts[] = 'You are not connected to Facebook';
		return;
	}
	$config = $_SESSION['facebook.config'];
	$access_token = $_SESSION['facebook.token'];
	$facebook = new Facebook($config);
	$facebook->setDefaultAccessToken($access_token);
	
	$postdata = [
		'message'	=>	$message,
		'link'		=>	'https://awesomewallpaper.files.wordpress.com/2012/11/galaxyngc3190_2560x1600.jpg'
	];
	
	$response = $facebook->get('/me/accounts');
	$response = $response->getGraphEdge();
	
	$page_tokens = array();
	foreach($response as $node) {
		$page = $node->asArray();
		$page_tokens[$page['id']] = $page['access_token'];
	}
	
	try {
		foreach($pages as $pid) {
			if($pid == 'me')
				$facebook->post('/me/feed', $postdata);
			else
				$facebook->post('/' . $pid . '/feed', $postdata, $page_tokens[$pid]);
		}
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  $alerts[] =  'Graph returned an error: ' . $e->getMessage();
	  return;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  $alerts[] = 'Facebook SDK returned an error: ' . $e->getMessage();
	  return;
	}
	
	return true;
}
function getFBPages() {
	if(!isset($_SESSION['facebook.token']))
		return [];
	
	$config = $_SESSION['facebook.config'];
	$access_token = $_SESSION['facebook.token'];
	$facebook = new Facebook($config);
	$facebook->setDefaultAccessToken($access_token);
	
	$response = $facebook->get('/me/accounts');
	$response = $response->getGraphEdge();
	
	$pages = array();
	$pages[] = array('id' => 'me', 'name' => 'Your Profile');
	foreach($response as $node) {
		$page = $node->asArray();
		unset($page['perms']);
		$pages[] = $page;
	}
	
	return $pages;
}
function postToLinkedIn($message) {
	global $alerts;
	
	if(!isset($_SESSION['linkedin.token'])) {
		$alerts[] = 'You are not connected to LinkedIn';
		return;
	}
	
	$id = $_SESSION['linkedin.id'];
	$secret = $_SESSION['linkedin.secret'];
	$access_token = $_SESSION['linkedin.token'];
	$linkedIn = new LinkedIn($id, $secret);
	$linkedIn->setAccessToken($access_token);
	$options = array('json'=>
		array(
			'content' => array(
				'title' => 'Image',
				'submitted-url' => 'http://auto-publishing.herokuapp.com/',
				'submitted-image-url' => 'https://awesomewallpaper.files.wordpress.com/2012/11/galaxyngc3190_2560x1600.jpg'
			),
			'comment' => $message,
			'visibility' => array(
				'code' => 'anyone'
			)
		)
	);
	$result = $linkedIn->post('v1/people/~/shares', $options);
	
	return true;
}

if(isset($_POST['msg'])) {
	$message = $_POST['msg'];
	$pages = isset($_POST['fbpages']) ? $_POST['fbpages'] : array();

	tweet($message);
	postToFacebook($message, $pages);
	postToLinkedIn($message);
	
	if(count($alerts) > 0) {
		$response = join("\n", $alerts);
		exit($response);
	}
	exit('ok');
}
elseif(isset($_GET['action'])) {
	$method = $_GET['action'];
	$return = $method();
	$return = json_encode($return);
	
	exit($return);
}