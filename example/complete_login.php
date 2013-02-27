<?php
include_once __DIR__ . '/util.php';
$provider_name = $_GET['provider'];
$identifier = $_GET['identifier'];

$return_url =  SocialAuth_Util::getRootUrl() . 'complete_login.php?provider='.urlencode($provider_name);

$provider = SocialAuth_Util::getProvider($provider_name);
$provider->setReturnUrl( $return_url );

$info = $provider->completeLogin($_SERVER['QUERY_STRING']);

?>
<html>
<head>
</head>
<body>
<h1>Login</h1>
<dl>
	<dt>User ID</dt>
	<dd><?php echo htmlentities($provider->getUserId()); ?></dd>
	<dt>Display Name</dt>
	<dd><?php echo htmlentities($provider->getDisplayIdentifier()); ?></dd>

<?php foreach($info as $k => $v): ?>
	<dt><?php echo htmlentities($k); ?></dt>
	<dd><?php echo htmlentities($v); ?></dd>
<?php endforeach; ?>
</dl> 
</body>
</html>

