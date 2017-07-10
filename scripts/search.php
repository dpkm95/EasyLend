<!DOCTYPE html>
<html>
<head>
	<title>Easylend</title>
</head>
<body>  
<form method="post">
<table>  
<tr>        
<td class="d1">
<form method="POST">
    Search books by: 
	<input type="radio" name="set_search" value="book" <?php 	
	if (isset($_POST['set']) && $_POST['set_search'] == 'book'){
	setcookie("selection", "book");
	header('Location: '.$_SERVER['REQUEST_URI']);}?>>Book
	<input type="radio" name="set_search" value="course"<?php
	if (isset($_POST['set']) && $_POST['set_search'] == 'course'){
	setcookie("selection", "course");
	header('Location: '.$_SERVER['REQUEST_URI']);}?>>Course
	<input type="radio" name="set_search" value="user"<?php
	if (isset($_POST['set']) && $_POST['set_search'] == 'user'){
	setcookie("selection", "user");
	header('Location: '.$_SERVER['REQUEST_URI']);}?>>User
    <input type="submit" name="set" value="Set" style="float: center; width: 60px"/>
</form>	
</td>
<td class="back"><a href="dashboard.php">back</a></td>
</tr>
</table>
</form>
<hr/>  
           
	<style>
	table {
	    width: 100%;
	}
	td {
	    vertical-align: top;
	}
	.back {
	    text-align: right;
	}
	.d1 {
	    text-align: left;
	}
	.d2 {
	    text-align:left;
	    padding-left: 50px;
	}
	.d3 {
	    text-align:left;
	    padding-left: 100px;
	}
	.d4 {
		text-align:left;
	    padding-left: 50px;
	}
	.d5 {
	    padding-left: 50px;
	}
	</style>

<?php

if($_COOKIE['selection']=='course'){
echo <<< _END
<form method="post">
<table>  
<tr>        
<td class="d1">
<input type="checkbox" name="subject"/> Subject
</td>
<td class="d1">
<input type="checkbox" name="department"/> Department
</td>
<td class="d1">
<input type="checkbox" name="semester"/> Semester
</td>
<td>
<input type="submit" name="select" value="Select" />
</td>
</tr>
</table>
</form><hr/>
_END;
}
else if($_COOKIE['selection']=='book'){
echo <<< _END
<form method="post">
<table>  
<tr>
<td class="d1">
<input type="checkbox" name="title"/> Title      
</td>
<td class="d1">
<input type="checkbox" name="author" /> Author
</td>
<td class="d1">
<input type="checkbox" name="edition"/> Edition
</td>
<td class="d1">
<input type="checkbox" name="publisher"/> Publisher
</td>
<td>
<input type="submit" name="select" value="Select" />
</td>
</tr>
</table>
</form><hr/>
_END;
}
else if($_COOKIE['selection']=='user'){
echo <<< _END
<form method="post">
<table>  
<tr>
<td class="d1">
<input type="checkbox" name="fname"/> First Name      
</td>
<td class="d1">
<input type="checkbox" name="lname" /> Last Name
</td>
<td class="d1">
<input type="checkbox" name="section"/> Section
</td>
<td class="d1">
<input type="checkbox" name="department"/> Department
</td>
<td class="d1">
<input type="checkbox" name="semester"/> Semester
</td>
<td>
<input type="submit" name="select" value="Select" />
</td>
</tr>
</table>
</form><hr/>
_END;
}



//-------------------------------------------------------------------------------------//

require_once 'login.php';

$db_server =  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

$db = mysql_select_db(DB_DATABASE);
if(!$db) die("Unable to select database: " . mysql_error());	

session_start();



//-------------------------------------------------------------------------------------//

if(isset($_POST['select'])) {
echo <<< _END
<pre><form method="post">
_END;
if(isset($_POST['subject'])) {
echo <<< _END
	<br/>
	Subject     <input type="text" name="tb_subject"/>
_END;
}
if(isset($_POST['title'])) {
echo <<< _END
	<br/>
	Title       <input type="text" name="tb_title"/>
_END;
}
if(isset($_POST['author'])) {
echo <<< _END
	<br/>
	Author      <input type="text" name="tb_author"/>
_END;
}
if(isset($_POST['edition'])) {
echo <<< _END
	<br/>
	Edition     <input type="text" name="tb_edition"/>
_END;
}
if(isset($_POST['publisher'])) {
echo <<< _END
	<br/>
	Publisher   <input type="text" name="tb_publisher"/>
_END;
}
if(isset($_POST['fname'])) {
echo <<< _END
	<br/>
	First Name  <input type="text" name="tb_fname"/>
_END;
}
if(isset($_POST['lname'])) {
echo <<< _END
	<br/>
	Last Name   <input type="text" name="tb_lname"/>
_END;
}
if(isset($_POST['department'])) {
echo <<< _END
	<br/>
	Department  <input type="text" name="tb_department"/>
_END;
}
if(isset($_POST['semester'])) {
echo <<< _END
	<br/>
	Semester    <input type="text" name="tb_semester"/>
_END;
}
if(isset($_POST['section'])) {
echo <<< _END
	<br/>
	Section     <input type="text" name="tb_section"/>
_END;
}

echo <<< _END
	<br/><br/>
		<input type="submit" name="search" value="Search" style="float: center; width: 150px"/>
	</form>
	</pre>
_END;
}

if(isset($_POST['search'])&&$_COOKIE['selection']=='book'){
$cond = array("","","","");

if(isset($_POST['tb_title'])) {
$var=get_post('tb_title');
$cond[0]="`BOOK`.`title` LIKE '%$var%'";
}
if(isset($_POST['tb_author'])) {
$var=get_post('tb_author');
$cond[1]="`BOOK`.`author` LIKE '%$var%'";
}
if(isset($_POST['tb_edition'])) {
$var=get_post('tb_edition');
$cond[2]="`BOOK`.`edition`='$var'";
}
if(isset($_POST['tb_publisher'])) {
$var=get_post('tb_publisher');
$cond[3]="`BOOK`.`publisher` LIKE '%$var%'";
}

$i=0;
$condition="";
for(;$i<4;$i++){
if($cond[$i]!=""){
	$condition=$cond[$i];
	break;
}
}
for($i+=1;$i<4;++$i){
if($cond[$i]!="")
	$condition=$condition."&&".$cond[$i];
}
$mybooks=1;
$query1 = "SELECT * FROM `BOOK` WHERE $condition";
$result = mysql_query($query1);
if (!$result){
	die ("Database access failed: " . mysql_error());	
}

$rows=mysql_num_rows($result);
if($rows==0) $mybooks=-1;

echo "SEARCH RESULTS:";
for ($j = 0 ; $j < $rows ; ++$j)
{	
	$row = mysql_fetch_row($result);
	$query3 = "SELECT * FROM `SUBJECT`,`BOOK` WHERE `SUBJECT`.`Id`=`BOOK`.`subject_Id` and `SUBJECT`.`Id`='$row[3]'";
	$result3 = mysql_query($query3);

	if (!$result3){
		die ("Database access failed: " . mysql_error());		
	}
	$row3=mysql_fetch_row($result3);
	echo <<<_END
<br/><br/>
<pre>
	Title     		$row[1]
	Publisher 		$row[2]
	Author    		$row[5]
  	Edition   		$row[6]
  	Subject 		$row3[1]
  	Semester 		$row3[2]
  	Department 		$row3[3]
</pre>
<form method="post">
<table>  
<tr>        
<td class="d4">
<form method="post">
<input type="hidden" name="b_id" value="$row[0]" />
<input type="hidden" name="add" value="yes" />
<input type="submit" value="ADD" /></form>
</td>
<td class="d5">
<form method="post">
<input type="hidden" name="b_id2" value="$row[0]" />
<input type="hidden" name="borrow" value="yes" />
<input type="submit" value="BORROW" /></form>
</td>
</tr>
</table>
</form><hr/>
_END;
}
if($mybooks==-1) echo "<br/><br/>No books found. :(";
}

//-------------------------------------------------------------------------------------//

else if(isset($_POST['search'])&&$_COOKIE['selection']=='course'){
$cond = array("","","");
if(isset($_POST['tb_subject'])) {
$var=get_post('tb_subject');
$cond[0]="`SUBJECT`.`title` LIKE '%$var%'";
}
if(isset($_POST['tb_department'])) {
$var=get_post('tb_department');
$cond[1]="`SUBJECT`.`department` LIKE '%$var%'";
}
if(isset($_POST['tb_semester'])) {
$var=get_post('tb_semester');
$cond[2]="`SUBJECT`.`semester`='$var'";
}

$i=0;
$condition="";
for(;$i<3;$i++){
if($cond[$i]!=""){
	$condition=$cond[$i];
	break;
}
}
for($i+=1;$i<3;++$i){
if($cond[$i]!="")
	$condition=$condition."&&".$cond[$i];	
}
$mybooks=1;
$query = "SELECT * FROM `SUBJECT` WHERE $condition";
$result = mysql_query($query);
if (!$result) die ("Database access failed: " . mysql_error());
$rows=mysql_num_rows($result);
if($rows==0) $mybooks=-1;

echo "SEARCH RESULTS:";

for ($j = 0 ; $j < $rows ; ++$j){
$row2 = mysql_fetch_row($result);
$query2 = "SELECT * FROM `SUBJECT`,`BOOK` WHERE `SUBJECT`.`Id`=`BOOK`.`subject_Id` and `SUBJECT`.`Id`='$row2[0]'";	
$result2 = mysql_query($query2);
if (!$result2) die ("Database access failed: " . mysql_error());
$row=mysql_fetch_row($result2);
	echo <<<_END
<br/><br/>
<pre>
	Title     		$row[5]
	Publisher 		$row[6]
	Author    		$row[9]
  	Edition   		$row[10]
  	Subject 		$row2[1]
  	Semester 		$row2[2]
  	Department 		$row2[3]
</pre>
<form method="post">
<table>  
<tr>        
<td class="d4">
<form method="post">
<input type="hidden" name="b_id" value="$row[4]" />
<input type="hidden" name="add" value="yes" />
<input type="submit" value="ADD" /></form>
</td>
<td class="d5">
<form method="post">
<input type="hidden" name="b_id2" value="$row[4]" />
<input type="hidden" name="borrow" value="yes" />
<input type="submit" value="BORROW" /></form>
</td>
</tr>
</table>
</form><hr/>
_END;
}
if($mybooks==-1) echo "<br/><br/>No books found. :(";
}

//-------------------------------------------------------------------------------------//

if(isset($_POST['search'])&&$_COOKIE['selection']=='user'){
$cond = array("","","","","");

if(isset($_POST['tb_fname'])) {
$var=get_post('tb_fname');
$cond[0]="`USER`.`fname`='$var'";
}
if(isset($_POST['tb_lname'])) {
$var=get_post('tb_lname');
$cond[1]="`USER`.`lname`='$var'";
}
if(isset($_POST['tb_section'])) {
$var=get_post('tb_section');
$cond[2]="`USER`.`section`='$var'";
}
if(isset($_POST['tb_department'])) {
$var=get_post('tb_department');
$cond[3]="`USER`.`department`='$var'";
}
if(isset($_POST['tb_semester'])) {
$var=get_post('tb_semester');
$cond[4]="`USER`.`semester`='$var'";
}

$i=0;
$condition="";
for(;$i<5;$i++){
if($cond[$i]!=""){
	$condition=$cond[$i];
	break;
}
}
for($i+=1;$i<5;++$i){
if($cond[$i]!="")
	$condition=$condition."&&".$cond[$i];
}

$mybooks=1;
$query1 = "SELECT * FROM `USER` WHERE $condition";
$result1 = mysql_query($query1);
if (!$result1){
	die ("Database access failed: " . mysql_error());	
}
$mybooks=1;
$user_count=mysql_num_rows($result1);
if($user_count==0) $mybooks=-1;

echo "SEARCH RESULTS:";
for ($j = 0 ; $j < $user_count ; ++$j)
{
	$user_details = mysql_fetch_row($result1);
	$name = "$user_details[1] $user_details[2]";
	echo <<<_END
<br/><br/>
<pre>
			Name 			$name
			Department 		$user_details[4]
			Semester    		$user_details[5]
  			Section   		$user_details[3]  		
</pre>
_END;
echo <<< _END
<form method="post" style="padding-left	:180px;">
<input type="hidden" name="lender_name" value="$name" />
<input type="hidden" name="lender_id" value="$user_details[0]" />
<input type="hidden" name="lender_select" value="yes" />
<input type="submit" value="Select" /></form>
_END;
echo "<hr/>";
}
if($mybooks==-1) echo "<br/><br/>No user(s) found. :(";
}



//-------------------------------------------------------------------------------------//

if(isset($_POST['borrow'])&& isset($_POST['b_id2'])){
	$_SESSION['bookid']=get_post('b_id2');
	header('Location: borrow_page.php');
}

if(isset($_POST['add'])&& isset($_POST['b_id'])){
	$uid=$_SESSION['userid'];
	$bid=get_post('b_id');
	$query = "SELECT * FROM `BOOK_OWNER` WHERE `book_id`='$bid' AND `owner_id`='$uid'";
	$result=mysql_query($query);
	if (!$result) die ("Database access failed: " . mysql_error());
	$check = mysql_num_rows($result);
	if($check==0){
	$query1 = "INSERT INTO `BOOK_OWNER`(`book_Id`,`owner_Id`) VALUES"."('$bid', '$uid')";	
	$result1=mysql_query($query1);
	if (!$result1) die ("Database access failed: " . mysql_error());
	}
	header('Location: mybooks_page.php');	
}

if(isset($_POST['lender_name'])&&isset($_POST['lender_select'])&& isset($_POST['lender_id'])){
	$userid=$_SESSION['userid'];
	$lenderid=get_post('lender_id');
	if($lenderid!=$userid){	
	$_SESSION['lenderid']=$lenderid;
	$_SESSION['lendername']=get_post('lender_name');
	header('Location: lend_page.php');
	}else{
		echo "<br/><h3>Wow are you really that stupid or just pretending? :/<h3/>";
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

</body>
</html>