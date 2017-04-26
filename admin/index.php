<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php

	// connect, css
	$is_this_admin = true;
	include "../includes/connect.php";
	include "../includes/css.php";

?>

<style>
p { margin: 5px 0;}
</style>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>SimHoop DB: Admin</title>
</head>

<body>

<div id="content">

<?php
	// get most recent year in PLAYER_year
	$query = $db->prepare( "SELECT DISTINCT y.year FROM PLAYER_year y WHERE league='NBA' ORDER BY y.year DESC" );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_year = $query->fetch();
	$current_year = $current_year[ "year" ];
	$prev_year = $current_year - 1;
?>

	<p><a href="_matchHeightWeight.php?id=<?php echo $current_year; ?>" target="_blank">Match <?php echo $current_year; ?> NBA height/weight to <?php echo $prev_year; ?> NBA height/weight</a></p>
	<p><a href="_calculateTOTpace.php?id=<?php echo $current_year; ?>" target="_blank">Calculate pace for <?php echo $current_year; ?> TOT's</a></p>

</div>

</body>
</html>