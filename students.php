<?php

// 1) Require admin session (protect this page)
require __DIR__ . '/includes/session_admin.php';
require_admin();

// 2) Single DB connection
require __DIR__ . '/includes/db.php';

// 3) Search (prepared) or list all
$searchTerm = '';
if (isset($_POST['search'])) {
    $searchTerm = trim($_POST['valueToSearch'] ?? '');
    $like = "%{$searchTerm}%";
    $stmt = $dbc->prepare("SELECT fullname, email FROM student WHERE fullname LIKE ? ORDER BY fullname ASC");
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $search_result = $stmt->get_result();
    $stmt->close();
} else {
    $search_result = $dbc->query("SELECT fullname, email FROM student ORDER BY fullname ASC");
}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="shortcut icon" type="png" href="images/icon/favicon.png">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Comaptible" content="IE=edge">
	<title>LearnEd</title>
	<meta name="desciption" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="style01.css">
	<script type="text/javascript" src="script.js"></script>
	<script src="https://code.jquery.com/jquery-3.2.1.js"></script>
	<script>
		$(window).on('scroll', function(){
  			if($(window).scrollTop()){
  			  $('nav').addClass('black');
 			 }else {
 		   $('nav').removeClass('black');
 		 }
		})
	</script>

	<style type="text/css">
		#searchbar {
			border: none;
			width: 180%;
			height: 45px;
			margin-left: -200%;
			margin-top: -150%;
			border: 2px solid #DF2771;
			padding: 2%;
			padding-left: 10%;
			border-radius: 25px;
			font-size: 120%;
			margin-bottom: 10%;
		}
		.srch { height: 40px; width: 50%; border: 2px solid yellow; }
		#title { text-alig
