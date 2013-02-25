<?php
include_once __DIR__ . '/util.php';
$provider_name = $_GET['provider'];
$provider = SocialAuth_Util::getProvider($provider_name);
$provider->setIdentifier($identifier);
$provider->setReturnUrl( SocialAuth_Util::getRootUrl() . 'complete_login.php?provider='.$provider_name);

$info = $provider->completeLogin($SERVER['QUERY_STRING']);


?>
<html>
<head>
</head>
<body>
<h1>Login</h1>
<dl>
<?php foreach($info as $k => $v): ?>
	<dt><?php echo htmlentities($k); ?></dt>
	<dd><?php echo htmlentities($v); ?></dd>
<?php endforeach; ?>
</dl> 
</body>
</html>
