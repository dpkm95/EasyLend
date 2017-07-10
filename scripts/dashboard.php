<?php
session_start();
$userid = $_SESSION['userid'];
if($userid==-1)
	header('Location: home.php');

require_once 'login.php';
$db_server =  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

$db = mysql_select_db(DB_DATABASE);
if(!$db) die("Unable to select database: " . mysql_error());

$query = "SELECT `USER`.`fname`,`USER`.`lname` FROM `USER` WHERE `USER`.`id`='$userid'";
$name = mysql_fetch_row(mysql_query($query));
if (!$name) die ("Database access failed: " . mysql_error());

$q_late = "SELECT `id`,`end_date` FROM `TRANSACTION` WHERE `status`=2 AND `lendee_id`='$userid'";
$r_late = mysql_query($q_late);
if (!$r_late) die ("Database access failed: " . mysql_error());
$count = mysql_num_rows($r_late);
for ($j = 0 ; $j < $count ; ++$j){
	$data=mysql_fetch_row($r_late);
	$t_id=$data[0];
	$end_date=$data[1];	
	if($end_date-strtotime("now")<0){
		$q_set_late = "UPDATE `TRANSACTION` SET `status`=4 WHERE `id`='$t_id'";		
		$r_set_late = mysql_query($q_set_late);
		if (!$r_set_late) die ("Database access failed: " . mysql_error());
	}
}		

echo <<< _END
<html>
<head>
	<title>Easylend</title>
</head>
<body>
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
	}
	.d3 {
	    text-align:right;
	}
	.title{
		padding-top:0.1px;
	}
	</style>

	<table>
    <tr>
        <td class="d1">
        <img src="easylend-05.png" alt="Easy Lend" style="float:left;width:80px;height:40px;display:block;">
       	<div class="title" style="display: inline-block;">
        <h3 align="left">DASHBOARD: Welcome $name[0] $name[1]</h3>       
        </div>
        </td>
        <td class="d3">        	
        	<a href="home.php">Sign out</a>
        </td>
    </tr>
	</table>

	<hr/>

	<table>
    <tr>
        <td class="d1">
            <a href="search.php">Search</a>            
        </td>
        <td class="d2">
            <a href="mybooks_page.php">My Books</a>
        </td>
        <td class="d3">
        	<a href="dashboard.php">Settings</a>
        </td>
    </tr>
	</table>
	<hr/>	
_END;

echo <<< _END
<font color="blue">NOTIFICATIONS-</font>
_END;

$q_notif = "SELECT * FROM `NOTIFICATION` WHERE `uid`='$userid' AND `seen`=0";
$r_notif=mysql_query($q_notif);
if (!$r_notif) die ("Database access failed: " . mysql_error());
$notif_count = mysql_num_rows($r_notif);
for ($j = 0 ; $j < $notif_count ; ++$j){
$notif = mysql_fetch_row($r_notif);
if($notif[1]==0){	
echo <<<_END
<pre>
	-> $notif[2] 
</pre>
_END;
if($notif[5]==0){
echo <<< _END
<table>
    <tr>       
        <td class="d1">
        	<form method="post" style="padding-left:60px;">        	
			<input type="hidden" name="accept" value="yes" />
			<input type="hidden" name="nid" value ="$notif[0]"/>
			<input type="submit" value="ACCEPT" /></form>
        </td>
        <td class="d1">
        	<form method="post" style="padding-right:600px;">
        	<input type="hidden" name="nid" value ="$notif[0]"/>
        	<input type="hidden" name="tid" value ="$notif[4]"/>
			<input type="hidden" name="reject" value="yes" />
			<input type="submit" value="REJECT" /></form>
        </td>
    </tr>
	</table>
_END;
if(isset($_POST['accept'])&&get_post('nid')==$notif[0]){
echo <<< _END
<table style="padding-left:60px;"><form method="post">
<tr><td>Meet Location*</td><td><input type="text" size="30" maxlength="50"
	name="location" /></td>
</tr><tr><td>Meet Time*</td><td><input type="time" size="30" maxlength="50"
	name="time" /></td>
</tr><tr><td>
<input type="hidden" name="tid" value ="$notif[4]"/>
<input type="hidden" name="nid" value ="$notif[0]"/>
<input type="hidden" name="send" value="yes"/>
<input type="submit" value="Send"/></td>
</tr></form></table>
_END;
}
}
if($notif[5]==1||$notif[5]==-1||$notif[5]==4){
echo <<< _END
<table>
    <tr>       
        <td class="d1" style="padding-left:60px;">
        	<form method="post">
        	<input type="hidden" name="nid" value ="$notif[0]"/>
			<input type="hidden" name="ok" value="yes" />
			<input type="submit" value="OK" /></form>
        </td>
    </tr>
	</table>
_END;
}
if($notif[5]==2){
echo <<< _END
<form method="post" style="padding-left:60px;" >
<input type="hidden" name="tid" value ="$notif[4]"/>
<input type="hidden" name="nid" value ="$notif[0]"/>
<input type="hidden" name="received" value="yes" />
<input type="submit" value="RECEIVED" /></form>
_END;
}
if($notif[5]==3){
echo <<< _END
<form method="post" style="padding-left:60px;" >
<input type="hidden" name="tid" value ="$notif[4]"/>
<input type="hidden" name="nid" value ="$notif[0]"/>
<input type="hidden" name="returned" value="yes" />
<input type="submit" value="RECEIVED" /></form>
_END;
}
}
}

echo "<hr/>";

if(isset($_POST['nid'])&&isset($_POST['received'])&&isset($_POST['tid'])){
$nid=get_post('nid');
$tid=get_post('tid');

$q_get_duration = "SELECT `lendee_id`,`book_id`,`duration` FROM `TRANSACTION` WHERE `id`='$tid'";
$r_get_duration = mysql_query($q_get_duration);
if (!$r_get_duration) die ("Database insertion failed: " . mysql_error());
$r_duration=mysql_fetch_row($r_get_duration);
$duration=$r_duration[2];
$userid = $_SESSION['userid'];
$lendee_id = $r_duration[0];
$book_id=$r_duration[1];


$q_seen = "UPDATE `NOTIFICATION` SET `seen`=1 WHERE `id`='$nid'";
$r_seen = mysql_query($q_seen);
if (!$r_seen) die ("Database insertion failed: " . mysql_error());

$start_date=strtotime("now");
$end_date=strtotime("+$duration month", $start_date);

$q_transact = "UPDATE `TRANSACTION` SET `start_date`='$start_date',`end_date`='$end_date',`status`=2 WHERE `id`='$tid'";	
$r_transact = mysql_query($q_transact);
if (!$r_transact) die ("Database insertion failed: " . mysql_error());

header('Location: '.$_SERVER['REQUEST_URI']);
}

if(isset($_POST['nid'])&&isset($_POST['returned'])&&isset($_POST['tid'])){
$nid=get_post('nid');
$tid=get_post('tid');

$q_get_duration = "SELECT `book_id` FROM `TRANSACTION` WHERE `id`='$tid'";
$r_get_duration = mysql_query($q_get_duration);
if (!$r_get_duration) die ("Database insertion failed: " . mysql_error());
$r_duration=mysql_fetch_row($r_get_duration);
$userid = $_SESSION['userid'];
$book_id=$r_duration[0];

$q_seen = "UPDATE `NOTIFICATION` SET `seen`=1 WHERE `id`='$nid'";
$r_seen = mysql_query($q_seen);
if (!$r_seen) die ("Database insertion failed: " . mysql_error());

$return_date=strtotime("now");

$q_transact = "UPDATE `TRANSACTION` SET `return_date`='$return_date',`status`=3 WHERE `id`='$tid'";	
$r_transact = mysql_query($q_transact);
if (!$r_transact) die ("Database insertion failed: " . mysql_error());

$q_available = "UPDATE `BOOK_OWNER` SET `availability`=1 WHERE `owner_id`='$userid' AND `book_id`='$book_id'";	
$r_available = mysql_query($q_available);
if (!$r_available) die ("Database insertion failed: " . mysql_error());	

$q_notify_reservers = "SELECT * FROM `RESERVATION` WHERE `book_id`='$book_id' AND `sequence_no`<>-1";
$r_notify_reservers = mysql_query($q_notify_reservers);
if (!$r_notify_reservers) die ("Database insertion failed: " . mysql_error());	
$reservers_count = mysql_num_rows($r_notify_reservers);
for ($j = 0 ; $j < $reservers_count ; ++$j){
	$reserver_details=mysql_fetch_row($r_notify_reservers);
	$rid = $reserver_details[1];
	$reservation_id = $reserver_details[0];
	$query2 = "SELECT `fname`,`lname` FROM `USER` WHERE `USER`.`Id`='$reserver_details[2]'";
	$result2 = mysql_query($query2);
	if (!$result2) die ("Database access failed: " . mysql_error());
	$lender_name=mysql_fetch_row($result2);

	$query3 = "SELECT `title` FROM `BOOK` WHERE `BOOK`.`Id`='$reserver_details[3]'";
	$result3 = mysql_query($query3);
	if (!$result3) die ("Database access failed: " . mysql_error());
	$r_title=mysql_fetch_row($result3);
	$book_title=$r_title[0];

	$message = "$book_title is available with $lender_name[0] $lender_name[1]. Be the first to borrow";
	$q_notify = "INSERT INTO `NOTIFICATION`(`message`,`type`,`uid`) VALUES"."('$message',4,'$rid')";
	$r_notify = mysql_query($q_notify);
	if (!$r_notify) die ("Database insertion failed: " . mysql_error());

	$q_unreserve = "UPDATE `RESERVATION` SET `sequence_no`=-1 WHERE `id`='$reservation_id'";	
	$r_unreserve = mysql_query($q_unreserve);
	if (!$r_unreserve) die ("Database insertion failed: " . mysql_error());
}
header('Location: '.$_SERVER['REQUEST_URI']);
}

if(isset($_POST['nid'])&&isset($_POST['send'])&&isset($_POST['tid'])){		
if(isset($_POST['location'])&&isset($_POST['time'])){
	$nid=get_post('nid');
	$tid=get_post('tid');
	$location=get_post('location');
	$time = get_post('time');

	$q_get_duration = "SELECT `lendee_id`,`book_id`,`duration` FROM `TRANSACTION` WHERE `id`='$tid'";
	$r_get_duration = mysql_query($q_get_duration);
	if (!$r_get_duration) die ("Database insertion failed: " . mysql_error());
	$r_duration=mysql_fetch_row($r_get_duration);
	$duration=$r_duration[2];
	$userid = $_SESSION['userid'];
	$lendee_id = $r_duration[0];
	$book_id=$r_duration[1];

	$q_available = "UPDATE `BOOK_OWNER` SET `availability`=0 WHERE `owner_id`='$userid' AND `book_id`='$book_id'";	
	$r_available = mysql_query($q_available);
	if (!$r_available) die ("Database insertion failed: " . mysql_error());	

	$q_seen = "UPDATE `NOTIFICATION` SET `seen`=1 WHERE `id`='$nid'";
	$r_seen = mysql_query($q_seen);
	if (!$r_seen) die ("Database insertion failed: " . mysql_error());

	$query2 = "SELECT `fname`,`lname` FROM `USER` WHERE `USER`.`Id`='$userid'";
	$result2 = mysql_query($query2);
	if (!$result2) die ("Database access failed: " . mysql_error());
	$lender_name=mysql_fetch_row($result2);

	$query3 = "SELECT `title` FROM `BOOK` WHERE `BOOK`.`Id`='$book_id'";
	$result3 = mysql_query($query3);
	if (!$result3) die ("Database access failed: " . mysql_error());
	$r_title=mysql_fetch_row($result3);
	$book_title=$r_title[0];

	$message = "Meet $lender_name[0] $lender_name[1] at $time near $location and collect $book_title.";
	$q_notify = "INSERT INTO `NOTIFICATION`(`message`,`type`,`uid`,`tid`) VALUES"."('$message',2,'$lendee_id','$tid')";
	$r_notify = mysql_query($q_notify);
	if (!$r_notify) die ("Database insertion failed: " . mysql_error());
	header('Location: '.$_SERVER['REQUEST_URI']);
}
}

if(isset($_POST['nid'])&&isset($_POST['reject'])&&isset($_POST['tid'])){	
	$nid=get_post('nid');
	$tid=get_post('tid');

	$q_get_trans = "SELECT `lendee_id`,`book_id` FROM `TRANSACTION` WHERE `id`='$tid'";
	$r_get_trans = mysql_query($q_get_trans);
	if (!$r_get_trans) die ("Database insertion failed: " . mysql_error());
	$r_duration=mysql_fetch_row($r_get_trans);
	$userid = $_SESSION['userid'];
	$lendee_id = $r_duration[0];
	$book_id=$r_duration[1];

	$q_transact = "UPDATE `TRANSACTION` SET `status`=-1 WHERE `id`='$tid'";	
	$r_transact = mysql_query($q_transact);
	if (!$r_transact) die ("Database insertion failed: " . mysql_error());

	$q_seen = "UPDATE `NOTIFICATION` SET `seen`=1 WHERE `id`='$nid'";
	$r_seen = mysql_query($q_seen);
	if (!$r_seen) die ("Database insertion failed: " . mysql_error());

	$query2 = "SELECT `fname`,`lname` FROM `USER` WHERE `USER`.`Id`='$userid'";
	$result2 = mysql_query($query2);
	if (!$result2) die ("Database access failed: " . mysql_error());
	$lender_name=mysql_fetch_row($result2);

	$query3 = "SELECT `title` FROM `BOOK` WHERE `BOOK`.`Id`='$book_id'";
	$result3 = mysql_query($query3);
	if (!$result3) die ("Database access failed: " . mysql_error());
	$r_title=mysql_fetch_row($result3);
	$book_title=$r_title[0];

	$message = "$lender_name[0] $lender_name[1] rejected your request for $book_title.";
	$q_notify = "INSERT INTO `NOTIFICATION`(`message`,`type`,`uid`,`tid`) VALUES"."('$message',-1,'$lendee_id','$tid')";
	$r_notify = mysql_query($q_notify);
	if (!$r_notify) die ("Database insertion failed: " . mysql_error());
	header('Location: '.$_SERVER['REQUEST_URI']);
}

/*---------------------------------------display borrowed books---------------------------------------*/

echo <<< _END
<font color="blue">BOOKS BORROWED-</font>
_END;
$q_borrowed = "SELECT `status`,`lender_Id`,`book_Id`,`start_date`,`end_date`,`id` FROM `TRANSACTION` WHERE `lendee_Id`='$userid' and `status`=2";
$r_borrowed=mysql_query($q_borrowed);
if (!$r_borrowed) die ("Database access failed: " . mysql_error());
$count = mysql_num_rows($r_borrowed);
for ($j = 0 ; $j < $count ; ++$j){
$borrowal = mysql_fetch_row($r_borrowed);	

if($borrowal[0]>0){
$query = "SELECT * FROM `BOOK` WHERE `BOOK`.`Id`='$borrowal[2]'";
$result = mysql_query($query);
if (!$result) die ("Database access failed: " . mysql_error());
$book_details=mysql_fetch_row($result);

$query2 = "SELECT `fname`,`lname`,`phone_no`,`email_id` FROM `USER` WHERE `USER`.`Id`='$borrowal[1]'";
$result2 = mysql_query($query2);
if (!$result2) die ("Database access failed: " . mysql_error());
$lender_details=mysql_fetch_row($result2);

$remaining_days=ceil(($borrowal[4]-time())/60/60/24);
$start_date=date("Y-m-d", $borrowal[3]);

if($remaining_days>=0 && $remaining_days<=7){
echo <<<_END
<pre>
<font color="orange">
	Book Details:
		Title     		$book_details[1]
		Publisher 		$book_details[2]
		Author    		$book_details[5]
  		Edition   		$book_details[6]
  	Lender Details:
  		Lender 			$lender_details[0] $lender_details[1]  	
  		Phone 			$lender_details[2]
  		Email 			$lender_details[3]

  	Start Date 			$start_date  	
  	Remaining Days 			$remaining_days
</font></pre>
_END;
}

if($remaining_days<0){
echo <<<_END
<pre>
<font color="red">
	Book Details:
		Title     		$book_details[1]
		Publisher 		$book_details[2]
		Author    		$book_details[5]
  		Edition   		$book_details[6]
  	Lender Details:
  		Lender 			$lender_details[0] $lender_details[1]  	
  		Phone 			$lender_details[2]
  		Email 			$lender_details[3]

  	Start Date 			$start_date  	
  	Remaining Days 			$remaining_days
</font></pre>
_END;
}
if($remaining_days>7){
echo <<<_END
<pre>
<font color="black">
<font color="green">
	Book Details:</font>
		Title     		$book_details[1]
		Publisher 		$book_details[2]
		Author    		$book_details[5]
  		Edition   		$book_details[6]<font color="green">
  	Borrower Details:</font>
  		Borrower 		$lender_details[0] $lender_details[1]  	
  		Phone 			$lender_details[2]
  		Email 			$lender_details[3]

  	Start Date 			$start_date  	
  	Remaining Days 			$remaining_days
</font></pre>
_END;
}
echo <<< _END
<form method="post" style="padding-left:60px;">
<input type="hidden" name="return" value="yes" />
<input type="hidden" name="tid" value="$borrowal[5]"/>
<input type="submit" value="RETURN" style="float: center; width: 80px"/></form>
_END;
if(isset($_POST['return'])&&get_post('tid')==$borrowal[5]){
echo <<< _END
<table style="padding-left:60px;"><form method="post">
<tr><td>Meet Location*</td><td><input type="text" size="30" maxlength="50"
	name="location" /></td>
</tr><tr><td>Meet Time*</td><td><input type="time" size="30" maxlength="50"
	name="time" /></td>
</tr><tr><td>
<input type="hidden" name="bid" value="$borrowal[2]"/>
<input type="hidden" name="tid" value="$borrowal[5]"/>
<input type="hidden" name="send2" value="yes"/>
<input type="submit" value="Send"/></td>
</tr></form></table>
_END;

}
}
}
echo "<hr/>";
/*---------------------------------------display lended books---------------------------------------*/

echo <<< _END
<font color="blue">BOOKS LENDED-</font>
_END;
$q_lended = "SELECT `status`,`lendee_Id`,`book_Id`,`start_date`,`end_date` FROM `TRANSACTION` WHERE `lender_Id`='$userid'";
$r_lended=mysql_query($q_lended);
if (!$r_lended) die ("Database access failed: " . mysql_error());
$count = mysql_num_rows($r_lended);
for ($j = 0 ; $j < $count ; ++$j){
$lend = mysql_fetch_row($r_lended);
if($lend[0]==2){		
$query = "SELECT * FROM `BOOK` WHERE `BOOK`.`Id`='$lend[2]'";
$result = mysql_query($query);
if (!$result) die ("Database access failed: " . mysql_error());
$book_details=mysql_fetch_row($result);

$query2 = "SELECT `fname`,`lname`,`phone_no`,`email_id` FROM `USER` WHERE `USER`.`Id`='$lend[1]'";
$result2 = mysql_query($query2);
if (!$result2) die ("Database access failed: " . mysql_error());
$lender_details=mysql_fetch_row($result2);

$remaining_days=ceil(($lend[4]-time())/60/60/24);
$start_date=date("Y-m-d", $lend[3]);
echo <<<_END
<pre>
<font color="black">
<font color="green">
	Book Details:</font>
		Title     		$book_details[1]
		Publisher 		$book_details[2]
		Author    		$book_details[5]
  		Edition   		$book_details[6]<font color="green">
  	Borrower Details:</font>
  		Borrower 		$lender_details[0] $lender_details[1]  	
  		Phone 			$lender_details[2]
  		Email 			$lender_details[3]

  	Start Date 			$start_date  	
  	Remaining Days 			$remaining_days
</pre>
_END;
}
if($lend[0]==4){		
$query = "SELECT * FROM `BOOK` WHERE `BOOK`.`Id`='$lend[2]'";
$result = mysql_query($query);
if (!$result) die ("Database access failed: " . mysql_error());
$book_details=mysql_fetch_row($result);

$query2 = "SELECT `fname`,`lname` FROM `USER` WHERE `USER`.`Id`='$lend[1]'";
$result2 = mysql_query($query2);
if (!$result2) die ("Database access failed: " . mysql_error());
$lender_name=mysql_fetch_row($result2);

$remaining_days=ceil(($lend[4]-time())/60/60/24);
$start_date=date("Y-m-d", $lend[3]);
echo <<<_END
<font color="red">
<pre>
	Book Details:
		Title     		$book_details[1]
		Publisher 		$book_details[2]
		Author    		$book_details[5]
  		Edition   		$book_details[6]
  	Borrower Details:
  		Borrower 		$lender_details[0] $lender_details[1]  	
  		Phone 			$lender_details[2]
  		Email 			$lender_details[3]

  	Start Date 			$start_date  	
  	Remaining Days 			$remaining_days
</font>
</pre>
_END;
}
}
echo "<hr/>";

if(isset($_POST['ok'])&&isset($_POST['nid'])){
$nid=get_post('nid');
$q_seen = "UPDATE `NOTIFICATION` SET `seen`=1 WHERE `id`='$nid'";
$r_seen = mysql_query($q_seen);
if (!$r_seen) die ("Database insertion failed: " . mysql_error());
header('Location: '.$_SERVER['REQUEST_URI']);
}

if(isset($_POST['send2'])&&isset($_POST['location'])&&isset($_POST['time'])) {
$tid=get_post('tid');
$book_id = get_post('bid');
$location=get_post('location');
$time = get_post('time');

$q_seen = "UPDATE `NOTIFICATION` SET `seen`=1 WHERE `id`='$nid'";
$r_seen = mysql_query($q_seen);
if (!$r_seen) die ("Database insertion failed: " . mysql_error());

$q_lended = "SELECT `lender_Id` FROM `TRANSACTION` WHERE `id`='$tid'";
$r_lended=mysql_query($q_lended);
if (!$r_lended) die ("Database access failed: " . mysql_error());
$r_lendee_id=mysql_fetch_row($r_lended);
$lender_id=$r_lendee_id[0];

$query2 = "SELECT `fname`,`lname` FROM `USER` WHERE `USER`.`Id`='$userid'";
$result2 = mysql_query($query2);
if (!$result2) die ("Database access failed: " . mysql_error());
$lender_name=mysql_fetch_row($result2);

$query3 = "SELECT `title` FROM `BOOK` WHERE `BOOK`.`Id`='$book_id'";
$result3 = mysql_query($query3);
if (!$result3) die ("Database access failed: " . mysql_error());
$r_title=mysql_fetch_row($result3);
$book_title=$r_title[0];

$message = "Meet $lender_name[0] $lender_name[1] at $time near $location and collect $book_title.";
$q_notify = "INSERT INTO `NOTIFICATION`(`message`,`type`,`uid`,`tid`) VALUES"."('$message',3,'$lender_id','$tid')";
$r_notify = mysql_query($q_notify);
if (!$r_notify) die ("Database insertion failed: " . mysql_error());

header('Location: '.$_SERVER['REQUEST_URI']);
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