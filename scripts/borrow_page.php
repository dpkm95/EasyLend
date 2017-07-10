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
$bookid=$_SESSION['bookid'];
$userid=$_SESSION['userid'];
require_once 'login.php';
$db_server =  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

$db = mysql_select_db(DB_DATABASE);
if(!$db) die("Unable to select database: " . mysql_error());
$no_results=1;

$query2 = "SELECT * FROM `BOOK_OWNER` WHERE `BOOK_OWNER`.`book_id`='$bookid'";
$result2 = mysql_query($query2);
if (!$result2) die ("Database access failed: " . mysql_error());

$rows=mysql_num_rows($result2);
if($rows==0) $no_results=-1;
echo <<< _END
<head>
	<title>Easylend</title>
</head>
<form method="post">
<table>  
<tr>        
<td class="d1"><h3>LENDER DETAILS-</h3></td>
<td class="d3"><a href="search.php">back</a></td>
</tr>
</table>
</form>
<hr/>
_END;
for ($j = 0 ; $j < $rows ; ++$j)
{	
	$user_details=mysql_fetch_row($result2);	
	if($userid!=$user_details[2]){
	$query4 = "SELECT `fname`,`lname`,`section`,`department`,`semester` FROM `USER` WHERE `USER`.`id`='$user_details[2]'";
	$result4 = mysql_query($query4);
	if (!$result4) die ("Database access failed: " . mysql_error());
	$ud=mysql_fetch_row($result4);	
	echo <<<_END
	<pre>
			Name     		$ud[0] $ud[1]			
			Department     		$ud[3] 
			Semester     		$ud[4] 
			Section     		$ud[2]
	</pre>
_END;

if($user_details[3]==1){
echo <<< _END
<form method="post" style="padding-left	:180px;">
<select name="duration" size="1" multiple="multiple" >
<option value="1">1 </option>
<option value="3">3</option>
<option value="6">6</option>
<option value="12">12</option>
</select>
<input type="hidden" name="b_id" value="$user_details[1]" />
<input type="hidden" name="lender_id" value="$user_details[2]" />
<form method="post" style="padding-right:200px;">
<input type="hidden" name="borrow" value="yes" />
<input type="submit" value="BORROW" /></form>
_END;

}else{
echo <<< _END
<form method="post" style="padding-left:180px">
<input type="hidden" name="b_id" value="$user_details[1]" />
<input type="hidden" name="lender_id" value="$user_details[2]" />
<input type="hidden" name="reserve" value="yes" />
<input type="submit" value="RESERVE" /></form>
_END;
}
echo "<hr/>";
}else if($rows==1) $no_results=-1;
}
if($no_results==-1) echo "<br/>We're Sorry, Nobody owns this book. :(";

if(isset($_POST['reserve'])&& isset($_POST['b_id'])&& isset($_POST['lender_id'])){
$requestee_id = get_post('lender_id');
$book_id = get_post('b_id');
$requester_id = $_SESSION['userid'];

$query2 = "SELECT fname,lname FROM `USER` WHERE `USER`.`id`='$requester_id'";
$result2 = mysql_query($query2);
if (!$result2) die ("Database access failed: " . mysql_error());
$requester_name=mysql_fetch_row($result2);

$query3 = "SELECT title FROM `BOOK` WHERE `BOOK`.`id`='$book_id'";
$result3 = mysql_query($query3);
if (!$result3) die ("Database access failed: " . mysql_error());
$r_title=mysql_fetch_row($result3);
$book_title=$r_title[0];

$q_seqno = "SELECT MAX(`sequence_no`) FROM `RESERVATION` WHERE `requestee_id` ='$requestee_id' AND `book_id`='$book_id'"; 
$res_seq = mysql_query($q_seqno);
if(!empty($res_seq)){
if (!$res_seq) die ("Database access failed: " . mysql_error());
$res=mysql_fetch_row($res_seq);
$seq_no = $res[0]+1;
}else{
	$seq_no=1;
} 

if($seq_no!=0){
$q_reserve = "INSERT INTO `RESERVATION`(requester_id,requestee_id,book_id,sequence_no) VALUES"."('$requester_id', '$requestee_id', '$book_id','$seq_no')";	
$r_reserve = mysql_query($q_reserve);
if (!$r_reserve) die ("Database insertion failed: " . mysql_error());

$message = "$requester_name[0] $requester_name[1] reserved $book_title";

$q_notify = "INSERT INTO `NOTIFICATION`(`message`,`type`,`uid`) VALUES"."( '$message',1,'$requestee_id')";
$r_notify = mysql_query($q_notify);
if (!$r_notify) die ("Database insertion failed: " . mysql_error());
}
header('Location: dashboard.php');
}

if(isset($_POST['borrow'])&& isset($_POST['b_id'])&& isset($_POST['lender_id'])){
$requestee_id = get_post('lender_id');
$book_id = get_post('b_id');
$requester_id = $_SESSION['userid'];
if(isset($_POST['duration']))
	$duration = get_post('duration');
else 
	$duration = 1;
echo $duration;

$query2 = "SELECT `fname`,`lname` FROM `USER` WHERE `USER`.`id`='$requester_id'";
$result2 = mysql_query($query2);
if (!$result2) die ("Database access failed: " . mysql_error());
$requester_name=mysql_fetch_row($result2);

$query3 = "SELECT `title` FROM `BOOK` WHERE `BOOK`.`id`='$book_id'";
$result3 = mysql_query($query3);
if (!$result3) die ("Database access failed: " . mysql_error());
$r_title=mysql_fetch_row($result3);
$book_title=$r_title[0];

$q_get_trans = "SELECT MAX(`id`) FROM `TRANSACTION`";
$r_get_trans = mysql_query($q_get_trans);
if (!$r_get_trans) die ("Database insertion failed: " . mysql_error());
$r_max=mysql_fetch_row($r_get_trans);
if(mysql_num_rows($r_get_trans)==0) $tid=1;
else $tid=$r_max[0]+1;

$q_transact = "INSERT INTO `TRANSACTION`(`id`,`lender_id`,`lendee_id`,`book_id`,`duration`) VALUES"."('$tid','$requestee_id', '$requester_id', '$book_id','$duration')";	
$r_transact = mysql_query($q_transact);
if (!$r_transact) die ("Database insertion failed: " . mysql_error());
$message = "$requester_name[0] $requester_name[1] requested $book_title for $duration month(s).";

$q_notify = "INSERT INTO `NOTIFICATION`(`tid`,`message`,`type`,`uid`) VALUES"."( '$tid','$message',0,'$requestee_id')";
$r_notify = mysql_query($q_notify);
if (!$r_notify) die ("Database insertion failed: " . mysql_error());

header('Location: dashboard.php');
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