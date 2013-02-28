<?php
include_once __DIR__ . '/util.php';
@session_start();

if( empty($_SESSION['provider']) || empty($_SESSION['user_id']) ){
	SocialAuth_Util::redirect('index.php');
}


?>
<html>
<head>
</head>
<body>
<h1>Login</h1>
<dl>
	<dt>User ID</dt>
	<dd><?php echo htmlentities($_SESSION['user_id']); ?></dd>
	<dt>Display Name</dt>
	<dd><?php echo htmlentities($_SESSION['display_identifier']); ?></dd>

<?php foreach($_SESSION['profile'] as $k => $v): ?>
	<dt><?php echo htmlentities($k); ?></dt>
	<dd><?php echo htmlentities($v); ?></dd>
<?php endforeach; ?>
</dl> 


<a href="index.php">Home</a>
</body>
</html>

