<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php

	// connect, css
	$is_this_admin = true;
	include "../includes/connect.php";
	include "../includes/css.php";
	include "../includes/scripts.php";

?>

<style>
p { margin: 5px 0; }
input { width: 50px; }
</style>

<script>
$( document ).ready( function() {
	// automatically update href parameters when inputs lose focus
	$( "input" ).blur( function() {
		var new_year = $( "input#year" ).val(),
			new_league = $( "input#league" ).val();
		$( "a" ).each( function() {
			var old_href = $( this ).attr( "href" );
			$( this ).attr( "href", old_href.substring( 0, old_href.indexOf( "?" ) ) + "?id=" + new_year + "&league=" + new_league );
		} );
	} );
} );
</script>

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
?>
	<form>
		Year: <input type="text" id="year" value="<?php echo $current_year; ?>" /> (ex: 1989)<br />
		League: <input type="text" id="league" value="NBA" /> (ex: NBA)
	</form>


	<p><a href="_calculateTOTpace.php?id=<?php echo $current_year; ?>" target="_blank">
		Calculate Pace for TOT's</a></p>
	<!--p><a href="_calculatePositionReal.php?id=<?php echo $current_year; ?>" target="_blank">
		Calculate Position Real Numbers (ex: 3.02)</a-->
	<p><a href="_matchHeightWeight.php?id=<?php echo $current_year; ?>" target="_blank">
		Match NBA height/weight to previous NBA height/weight</a></p>

</div>

</body>
</html>