<?php
include_once __DIR__ . '/util.php';
@session_start();

if( empty($_SESSION['provider']) || empty($_SESSION['user_id']) ){
	SocialAuth_Util::redirect('index.php');
}

$provider = $provider = SocialAuth_Util::getProvider($_SESSION['provider']);
$provider->loadUser($_SESSION['user_id']);


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

<?php foreach($provider->getProfile() as $k => $v): ?>
	<dt><?php echo htmlentities($k); ?></dt>
	<dd><?php
		if(is_string($v)){
			echo htmlentities($v);
		} else {
			echo htmlentities(json_encode($v));
		}
?></dd>
<?php endforeach; ?>
</dl> 


<a href="index.php">Home</a>
</body>
</html>

