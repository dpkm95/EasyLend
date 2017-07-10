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
require_once 'login.php';
$db_server =  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

$db = mysql_select_db(DB_DATABASE);
if(!$db) die("Unable to select database: " . mysql_error());

session_start();
$lenderid=$_SESSION['lenderid'];
$lendername = $_SESSION['lendername'];

echo <<< _END
<head>
	<title>Easylend</title>
</head>
<form method="post">
<table>  
<tr>        
<td class="d1"><h3>Book owned by: $lendername</h3></td>
<td class="d3"><a href="search.php">back</a></td>
</tr>
</table>
</form>
<hr/>
_END;

$query3 = "SELECT `book_id`,`availability` FROM `BOOK_OWNER` WHERE `owner_id`='$lenderid'";
$result3 = mysql_query($query3);
if (!$result3){
	die ("Database access failed: " . mysql_error());		
	$books_count=0;
}else{	
	$books_count = mysql_num_rows($result3);	
}
$mybooks=1;
if($books_count==0) $mybooks=-1;
for ($j = 0 ; $j < $books_count ; ++$j)
{
	$user_books=mysql_fetch_row($result3);
	$bookid=$user_books[0];
	$availability=$user_books[1];

	$query4 = "SELECT * FROM `BOOK` WHERE `id`='$bookid'";
	$result4 = mysql_query($query4);
	if (!$result4){
		die ("Database access failed: " . mysql_error());	
	}
	$book_details=mysql_fetch_row($result4);
	$subid=$book_details[3];
	$query5 = "SELECT * FROM `SUBJECT` WHERE `id`='$subid'";
	$result5 = mysql_query($query5);
	if (!$result5) die ("Database access failed: " . mysql_error());
	$sub_details=mysql_fetch_row($result5);

	echo <<<_END
<br/><br/>
<pre>
			Title 			$book_details[1]
			Publisher 		$book_details[2]
			Author    		$book_details[5]
  			Edition   		$book_details[6]
  			Subject 		$sub_details[1]
  			Semester 		$sub_details[2]
  			Department 		$sub_details[3]
</pre>
_END;
if($availability==1){
echo <<< _END
<form method="post" style="padding-left	:180px;">
<select name="duration" size="1" multiple="multiple" >
<option value="1">1 </option>
<option value="3">3</option>
<option value="6">6</option>
<option value="12">12</option>
</select>
<input type="hidden" name="b_id" value="$book_details[0]" />
<input type="hidden" name="lender_id" value="$lenderid" />
<form method="post" style="padding-right:200px;">
<input type="hidden" name="borrow" value="yes" />
<input type="submit" value="BORROW" /></form>
_END;

}else{
echo <<< _END
<form method="post" style="padding-left:180px">
<input type="hidden" name="b_id" value="$book_details[0]" />
<input type="hidden" name="lender_id" value="$lenderid" />
<input type="hidden" name="reserve" value="yes" />
<input type="submit" value="RESERVE" /></form>
_END;
}
echo "<hr/>";
}
if($mybooks==-1) echo "<br/><br/>No book(s) found. :(";

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
$q_reserve = "INSERT INTO `RESERVATION`(requester_id,requestee_id,book_id,sequence_no) VALUES('$requester_id', '$requestee_id', '$book_id','$seq_no')";	
echo $q_reserve;
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