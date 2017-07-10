<html><head><title>Easylend SignUp</title>
<div align="right">
<a href="home.php" style="text-align:"right";">back</a>
</div>
<h2>Create Account:</h2><hr/>
<style>.signup { border: 1px solid #999999;
	font: normal 14px helvetica; color:#444444; }</style>
</head><body>

<center>
<table class="signup" border="0" cellpadding="2"
	cellspacing="5" bgcolor="#eeeeee">
<th colspan="2" align="center">Signup Form</th>
<form method="post">
	 <tr><td>First Name*</td><td><input type="text" size="30" maxlength="50"
	name="fname" /></td>
</tr><tr><td>Last Name*</td><td><input type="text" size="30" maxlength="50"
	name="lname" /></td>
</tr><tr><td>Email*</td><td><input type="text" maxlength="50" size="30"
	name="email" /></td>
</tr><tr><td>Phone*</td><td><input type="text" maxlength="10" size="10"
	name="phone" /></td>
</tr><tr><td>Department</td><td><br/><select name="department" size="1" multiple="multiple">
<option value="none"></option>
<option value="CS">CS</option>
<option value="EC">EC</option>
<option value="ME">ME</option>
<option value="IS">IS</option>
<option value="TE">TE</option>
<option value="CV">CV</option>
</select></td>
</tr><tr><td>Section</td><td><br/><select name="section" size="1" multiple="multiple">
<option value="none"></option>
<option value="A">A</option>
<option value="B">B</option>
<option value="C">C</option>
<option value="D">D</option>
<option value="E">E</option>
<option value="F">F</option>
</select></td>
</tr><tr><td>Semester</td><td><br/><select name="semester" size="1" multiple="multiple">
<option value="none"></option>
<option value="1">I</option>
<option value="2">II</option>
<option value="3">III</option>
<option value="4">IV</option>
<option value="5">V</option>
<option value="6">VI</option>
<option value="7">VII</option>
<option value="8">VIII</option>
</select></td>
</tr><tr><td>Password*</td><td><input type="password" maxlength="50" size="30"
	name="password" /></td>
</tr><tr><td colspan="2" align="center">
	<input type="hidden" name="signup" value="yes"/>
	<input type="submit" value="Signup" /></td>
</tr></form></table>
</center>

<?php
session_start();

require_once 'login.php';
$db_server =  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

$db = mysql_select_db(DB_DATABASE);
if(!$db) die("Unable to select database: " . mysql_error());

if(isset($_POST['signup']) &&
	isset($_POST['fname']) &&
	isset($_POST['lname']) &&
	isset($_POST['email']) &&
	isset($_POST['phone']) &&
	isset($_POST['password'])) {	
	$fname=get_post('fname');
	$lname=get_post('lname');
	$email=get_post('email');
	$phone=get_post('phone');
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    	echo "<script type='text/javascript'>alert('Please enter a valid email')</script>";
		exit;
	}
	if(!check_email_phone($email,$phone)){
		echo "<script type='text/javascript'>alert('email/phone already registered!')</script>";
		exit;
	}
	$password=get_post('password');
	$salt1		= 'wei23a*';
	$salt2 		= '%12B/';
	$token		=  md5("$salt1$password$salt2");
	if(isset($_POST['department'])){
		$department=$_POST['department'];
	}else{
		$department='-';
	}
	if(isset($_POST['semester'])){
		$semester=$_POST['semester'];
	}else{
		$semester = -1;
	}
	if(isset($_POST['section'])){
		$section=$_POST['section'];
	}else{
		$section='-';
	}
	
	$query1 = "INSERT INTO `USER`(`fname`,`lname`,`section`,`department`,`semester`,`phone_no`,`email_Id`) VALUES"."('$fname', '$lname', '$section', '$department', '$semester',$phone,'$email')";	
	$result1=mysql_query($query1);
	$query = "SELECT `Id` FROM `USER` WHERE `email_Id`='$email'";
	$res = mysql_fetch_row(mysql_query($query));
	$uid=$res[0];
	$_SESSION['userid']=$uid; 
	$query2 = "INSERT INTO `USER_PASSWORD`(`user_Id`,`email_Id`,`password`) VALUES"."('$uid', '$email', '$token')";	
	$result2=mysql_query($query2);
	if (!$result2 || !$result1)
		echo "INSERT failed:".mysql_error();	
	else{		
		header('Location: dashboard.php');
	}
}

function check_email_phone($email,$phone){

	$query1 = "SELECT `Id` FROM `USER` WHERE `email_id`='$email'";	
	$res1=mysql_query($query1);
	if (!$res1) die ("Database access failed: " . mysql_error());
	$num1=mysql_num_rows($res1);	
	$query2 = "SELECT `Id` FROM `USER` WHERE `phone_no`='$phone'";
	$res2=mysql_query($query2);
	if (!$res1) die ("Database access failed: " . mysql_error());
	$num2=mysql_num_rows($res2);
	if($num1==0 && $num2==0) return true;
	else false;
}

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

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
    // last request was more than 30 minutes ago
    echo "<script type='text/javascript'>alert('Session timeout')</script>";
    header('Location: home.php');	   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time();
?>