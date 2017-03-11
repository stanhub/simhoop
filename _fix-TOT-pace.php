<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php

	// connect, css
	include "includes/head.php";

?>

</head>
<body>

<?php

	// get all TOT player years that have a NULL pace
	$sql = "SELECT y.* FROM PLAYER_year y WHERE y.teamID = 'TOT' AND y.pace IS NULL AND y.league IN ('ABA','NBA') AND y.year > 1950 ORDER BY year DESC";
	$query = $db->prepare( $sql );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_player_TOT = $query->fetchAll();

	// go through each player
	$TOT_count = count( $current_player_TOT );
	for ( $i = 0; $i < $TOT_count; $i++ ) {

		// get all the teams this player appeared on this season
		$sql = "SELECT y.pace, t.min FROM PLAYER_year y, PLAYER_tot t WHERE y.playerID = :playerID AND y.year = :year AND y.yearID = t.yearID AND y.teamID != 'TOT'";
		$query = $db->prepare( $sql );
		$query->bindValue( ':playerID', $current_player_TOT[$i]["playerID"], PDO::PARAM_STR );
		$query->bindValue( ':year', $current_player_TOT[$i]["year"], PDO::PARAM_INT );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$current_player = $query->fetchAll();

		// empty vars
		$sum_min = 0;
		$sum_pace_x_min = 0;
		$tot_pace = "";

		// go through each team player played on this year
		$player_count = count( $current_player );
		$no_min = 0;	// 1 = player has a team-year with no minutes
		for ( $j = 0; $j < $player_count; $j++ ) {

			if ( empty( $current_player[$j]["min"] ) ) {

				$no_min = 1;

			} else {

				$sum_min += $current_player[$j]["min"];
				$sum_pace_x_min += ( $current_player[$j]["pace"] * $current_player[$j]["min"] );

			}

		}

		if ( $no_min === 0 ) {

			// find TOT pace
			$tot_pace = $sum_pace_x_min / $sum_min;

			// get all the teams this player appeared on this season
			$sql = "UPDATE PLAYER_year y SET y.pace = :pace WHERE y.playerID = :playerID AND y.year = :year AND y.teamID = 'TOT'";
			$query = $db->prepare( $sql );
			$query->bindValue( ':pace', strval( $tot_pace ), PDO::PARAM_STR );
			$query->bindValue( ':playerID', $current_player_TOT[$i]["playerID"], PDO::PARAM_STR );
			$query->bindValue( ':year', $current_player_TOT[$i]["year"], PDO::PARAM_INT );
			$query->execute();

		}

	}

?>

</body>
</html>