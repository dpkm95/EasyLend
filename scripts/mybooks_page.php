<style>
	table {
	    width: 100%;
	}
	td {
	    vertical-align: top;
	}
	.d1 {
	    text-align: left;
	}
	.d2 {
	    text-align:center;
	    padding-left: 50px;
	}
	.d3 {
	    text-align:right;
	    padding-left: 100px;
	}
	</style>

<?php
session_start();
$userid=$_SESSION['userid'];
require_once 'login.php';
$db_server =  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

$db = mysql_select_db(DB_DATABASE);
if(!$db) die("Unable to select database: " . mysql_error());

$query = "SELECT * FROM `BOOK_OWNER` WHERE `owner_Id`='$userid'";
$result = mysql_query($query);

if (!$result) die ("Database access failed: " . mysql_error());
$rows=mysql_num_rows($result);

if(isset($_POST['add'])){
	header('Location: search.php');
}

echo "";
echo <<< _END
<head>
	<title>Easylend</title>
</head>
<table>
<form method="post">
	<tr>
	<td class="d1">MY BOOKS:</td>
	<td class="d2">
	<form method="post">
	<input type="hidden" name="b_id" value="yes" />
	<input type="hidden" name="add" value="yes" />
	<input type="submit" value="ADD" style="float: center; width: 80px"/>
	</form>
	</td>	
	<td class="d3"><a href="dashboard.php">back</a></td>
</form>
</table>
<hr/>
<br/><br/>
_END;

for ($j = 0 ; $j < $rows ; ++$j)
{
	$book = mysql_fetch_row($result);	
	$query2 = "SELECT * FROM `BOOK` WHERE `BOOK`.`Id`='$book[1]'";
	$result2 = mysql_query($query2);
	if (!$result2) die ("Database access failed: " . mysql_error());
	$book_details=mysql_fetch_row($result2);

	$query3 = "SELECT * FROM `SUBJECT`,`BOOK` WHERE `SUBJECT`.`Id`=`BOOK`.`subject_Id` and `BOOK`.`Id`='$book[0]'";
	$result3 = mysql_query($query3);
	if (!$result3) die ("Database access failed: " . mysql_error());
	$sub_details=mysql_fetch_row($result3);

	echo <<<_END
<br/><br/>
<pre>
	Title     		$book_details[1]
	Publisher 		$book_details[2]
	Author    		$book_details[5]
  	Edition   		$book_details[6]
  	Subject 		$sub_details[1]
  	Semester 		$sub_details[2]
  	Department 		$sub_details[3]
</pre>
<form method="post" style="padding-left:60px;">
<input type="hidden" name="b_id" value="$book_details[0]" />
<input type="hidden" name="remove" value="yes" />
<input type="submit" value="REMOVE" /></form>
_END;
echo "<hr/>";
}


if(isset($_POST['remove']) &&
	isset($_POST['b_id'])) {
	$bookid=get_post('b_id');
	$query = "SELECT `id` FROM `TRANSACTION` WHERE `lender_id`='$userid' and `book_id`='$bookid' and `status`>=2";
	$result=mysql_query($query);
	if(!$result) echo "delete failed:".mysql_error();
	$rows=mysql_num_rows($result);

	if($rows==0){
		$query1 = "DELETE FROM `BOOK_OWNER` WHERE `book_Id`='$bookid' AND `owner_Id`='$userid'";
		$result1=mysql_query($query1);
		if(!$result1) echo "delete failed:".mysql_error();
		header('Location: '.$_SERVER['REQUEST_URI']);
	}
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