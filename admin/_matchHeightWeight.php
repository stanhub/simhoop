<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php

	// connect, css
	$is_this_admin = true;
	include "../includes/connect.php";
	include "../includes/css.php";

?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>SimHoop DB: Match Height/Weight</title>
</head>

<body>

<div id="content">

<?php
	// get passed current year
	$no_year_passed = true;
	if ( ( isset( $_GET[ "id" ] ) ) && ( !empty( $_GET[ "id" ] ) ) ) {
		$current_year = $_GET[ "id" ];
		$no_year_passed = false;
	}

	// get most recent year in PLAYER_year
	$query = $db->prepare( "SELECT y.* FROM PLAYER_year y WHERE y.league='NBA' AND y.year=:current_year AND ( y.height = 0 OR y.weight = 0 )" );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->bindValue( ":current_year", $current_year, PDO::PARAM_INT );
	$query->execute();
	$current_player = $query->fetchAll();

	// get last year in PLAYER_year
	$query = $db->prepare( "SELECT y.* FROM PLAYER_year y WHERE y.league='NBA' AND y.year=:prev_year" );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->bindValue( ":prev_year", ( $current_year - 1 ), PDO::PARAM_INT );
	$query->execute();
	$prev_player = $query->fetchAll();

	// no year passed error message
	if ( $no_year_passed ) {
?>
	<strong style="color: #ff0000">No Year Passed!</strong><br /><br />
<?php
	}

	// go through each player and update their height/weight
	$i = 0;
	foreach( $current_player as $player ) {
		// go through prev player
		foreach( $prev_player as $old_player ) {
			if ( $player[ "playerID"] !== $old_player[ "playerID" ] ) {
				continue;
			}

			$query = $db->prepare( "UPDATE PLAYER_year y SET y.height = :height, y.weight = :weight WHERE y.playerID = :playerID AND y.year = :current_year" );
			$query->setFetchMode( PDO::FETCH_ASSOC );
			$query->bindValue( ":height", $old_player[ "height" ], PDO::PARAM_INT );
			$query->bindValue( ":weight", $old_player[ "weight" ], PDO::PARAM_INT );
			$query->bindValue( ":playerID", $player[ "playerID" ], PDO::PARAM_INT );
			$query->bindValue( ":current_year", $current_year, PDO::PARAM_INT );
			$query->execute();

			$i++;
		}
	}
?>

<?php echo $i; ?> Done!

</div>

</body>
</html>