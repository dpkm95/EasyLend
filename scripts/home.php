<?php
require_once 'login.php';
$db_server =  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

$db = mysql_select_db(DB_DATABASE);
if(!$db) die("Unable to select database: " . mysql_error());

session_start();
$_SESSION['userid']=-1;


if (isset($_POST['signin']) && isset($_POST['email']) && isset($_POST['password']))
{
	$email      = get_post('email');
	$password   = get_post('password');
	$salt1		= 'wei23a*';
	$salt2 		= '%12B/';
	if($email!='' && $password!=''){
		$query = "SELECT `USER_PASSWORD`.`password`,`USER_PASSWORD`.`user_Id` FROM `USER_PASSWORD` WHERE `USER_PASSWORD`.`email_Id`='$email'"; 
		$result = mysql_query($query);
		if (!$result) die ("Database access failed: " . mysql_error());		
		$details = mysql_fetch_row($result);
		$token=md5("$salt1$password$salt2");
		if($token==$details[0]){		
			$_SESSION['userid'] = $details[1];
			header('Location: dashboard.php');		
		}else
echo <<< _END
<script type='text/javascript'>alert('Wrong email or password, Please try again')</script>
_END;
	}else{
		echo "<script type='text/javascript'>alert('Please enter email and password')</script>";
	}	
}

echo <<< _END
	<html><head><title>	
	Welcome to EasyLend</title>
	<style>.signup { border: 1px solid #999999;
		font: normal 14px helvetica; color:#444444; }</style>
	</head>
	<body>
		<div align="center" >
		<img src="easylend-03.png" alt="Easy Lend" style="width:483px;height:200px;padding-bottom:50px">
		</div>
		
	<center>
		<table class="signup" border="0" cellpadding="2"
			cellspacing="5" bgcolor="#eeeeee">
		<th colspan="2" align="center">Login</th>
		<form method="post" action="home.php">
		</tr><tr><td>Email*</td><td><input type="text" maxlength="64" name="email" /></td>
		</tr><tr><td>Password*</td><td><input type="password" maxlength="30" name="password" /></td>
		</tr><tr><td colspan="2" align="center">
			<input type="hidden" name="signin" value="yes" />
			<input type="submit" name="signin" value="Sign in" /></form>
		</tr><tr><td colspan="2" align="right">
		    <a href="signup_form.php">Sign up</a>
		</tr></form></table>
		</center>
	</body>
_END;

function get_post($var)
{
	return sanitizeString($_POST[$var]);
}

function sanitizeString($var)
{
if (get_magic_quotes_gpc()) $var = stripslashes($var);
$var = htmlentities($var);
$var = strip_tags($var);
return $var;
}
function sanitizeMySQL($var)
{
$var = mysql_real_escape_string($var);
$var = sanitizeString($var);
return $var;
}
?>
