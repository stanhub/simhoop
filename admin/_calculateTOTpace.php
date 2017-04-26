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

<title>SimHoop DB: Calculate TOT Pace</title>
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

	// turn on big selects sql
	$query = $db->prepare( "SET SQL_BIG_SELECTS=1" );
	$query->execute();

	// get all players from this year who played a TOT
	$query = $db->prepare( "SELECT a.playerID, a.year, a.teamID, a.pace, x.MIN
		FROM PLAYER_year a, PLAYER_tot x
		WHERE a.playerID IN (
			SELECT b.playerID
			FROM PLAYER_year b
			WHERE b.league = 'NBA'
			AND b.year = :current_year1
			AND b.teamID = 'TOT' )
		AND a.year = :current_year2
		AND a.teamID != 'TOT'
		AND a.playerID = x.playerID
		AND a.teamID = x.teamID
		AND a.year = x.year
		ORDER BY a.playerID, a.teamID" );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->bindValue( ":current_year1", $current_year, PDO::PARAM_INT );
	$query->bindValue( ":current_year2", $current_year, PDO::PARAM_INT );
	$query->execute();
	$current_player = $query->fetchAll();

	// turn off big selects sql
	$query = $db->prepare( "SET SQL_BIG_SELECTS=0" );
	$query->execute();

	// no year passed error message
	if ( $no_year_passed ) {
?>
	<strong style="color: #ff0000">No Year Passed!</strong><br /><br />
<?php
	}

	// go through each player and update their pace
	$i = $pace_x_min = $total_min = 0;
	$prev_player = $current_player[ 0 ][ "playerID" ];
	foreach( $current_player as $player ) {
		if ( $player[ "playerID" ] !== $prev_player ) {
			$new_pace = $pace_x_min / $total_min;

			// update new pace
			$query = $db->prepare( "UPDATE PLAYER_year y SET y.pace = :new_pace WHERE y.playerID = :playerID AND y.year = :current_year AND y.teamID = 'TOT'" );
			$query->bindValue( ":new_pace", $new_pace, PDO::PARAM_INT );
			$query->bindValue( ":playerID", $prev_player, PDO::PARAM_INT );
			$query->bindValue( ":current_year", $current_year, PDO::PARAM_INT );
			$query->execute();

			// revert/update vars
			$pace_x_min = $total_min = 0;
			$i++;
		}

		$pace_x_min += $player[ "MIN" ] * $player[ "pace" ];
		$total_min += $player[ "MIN" ];

		// update prev_player
		$prev_player = $player[ "playerID" ];
	}
?>

<?php echo $i; ?> Done!

</div>

</body>
</html>