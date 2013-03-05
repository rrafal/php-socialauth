<html>
<head>
</head>
<body>
<h1>Login</h1>

<ul>
	<li><a href="begin_login.php?provider=aol">with AOL</a></li>
	<li><a href="begin_login.php?provider=google">with Google</a></li>
	<li><a href="begin_login.php?provider=yahoo">with Yahoo</a></li>
	<li><a href="begin_login.php?provider=facebook">with Facebook</a></li>
	<li><a href="begin_login.php?provider=twitter">with Twitter</a></li>
</ul>

<h2>Login with OpenID</h2>
<form method="GET" action="begin_login.php">
	<input type="hidden" name="provider" value="openid" />
	Open ID: <input type="url" name="user_url" value="" />
	<input type="submit" value="Submit" />
</form>

</body>
</html>
