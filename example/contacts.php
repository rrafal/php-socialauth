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

<table>
	<tr>
		<th>Name</th>
		<th>Email</th>
		<th>Phone</th>
		<th>Action</th>
	</tr>
	
<?php foreach($provider->listContacts() as $contact): ?>
	<tr>
		<td><?php echo htmlentities($contact['name']); ?></td>
		<td><?php echo htmlentities($contact['email']); ?></td>
		<td><?php echo htmlentities($contact['phone']); ?></td>
		<td><a href="view_contact.php?id=<?php echo urlencode($contact['id']); ?>">View</a></td>
	</tr>
<?php endforeach; ?>
</table>


<a href="index.php">Home</a>
</body>
</html>

