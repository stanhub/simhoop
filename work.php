<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php

	// connect, css
	$is_this_admin = false;
	include "includes/head.php";

?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php

	// get year
	$this_year = 1989;
	if ( ( isset( $_GET[ "year" ] ) ) && ( !empty( $_GET[ "year" ] ) ) ) {
		$this_year = intval( $_GET[ "year" ] );
	}

	// get league
	$this_league = "NBA";
	if ( ( isset( $_GET[ "league" ] ) ) && ( !empty( $_GET[ "league" ] ) ) ) {
		$this_league = $_GET[ "league" ];
	}

	// get playerID
	$this_playerID = "";
	if ( ( isset( $_GET[ "player" ] ) ) && ( !empty( $_GET[ "player" ] ) ) ) {
		$this_playerID = $_GET[ "player" ];
	}

	// handle composite stats (ex: PG or PG-SG [PG * 2 + SG * 1] )
	function find_average( $avg_array, $stat, $pos1, $pos2 ) {
		if ( isset( $pos2 ) && $pos2 !== "" ) {
			return ( ( $avg_array[ $pos1 ][ $stat ] * 2 ) + $avg_array[ $pos2 ][ $stat ] ) / 3;
		} else {
			return $avg_array[ $pos1 ][ $stat ];
		}
	}

	// get all non partial players from this year-league
	$query = $db->prepare( "SELECT y.first_name, y.last_name, y.position, y.position_real, y.pace, y.height, y.weight, t.*
		FROM PLAYER_tot t, PLAYER_year y
		WHERE t.year = :year AND t.league = :league AND y.partial = 0
		AND y.year = t.year AND y.playerID = t.playerID AND y.teamID = t.teamID" );
	$query->bindValue( ":year", $this_year, PDO::PARAM_INT );
	$query->bindValue( ":league", $this_league, PDO::PARAM_STR );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_player = $query->fetchAll();

?>

<title>SimHoop: Work Shit</title>
</head>

<body>

	<div id="content">

	<strong><?php echo $this_year, " ", $this_league; ?></strong>
	<table>
	<thead>
	<tr class="text_right" style="background-color: #000000; color: #ffff00;">
		<td class="text_left">Position</td>
		<td title="Amount of players at this position">Amt</td>
		<td title="Height">Ht</td>
		<td title="Weight">Wt</td>
		<td>FGA</td>
		<td>FG%</td>
		<td>3PA</td>
		<td>3P%</td>
		<td>FTA</td>
		<td>FT%</td>
		<td>ORB</td>
		<td>TRB</td>
		<td>AST</td>
		<td>STL</td>
		<td>BLK</td>
		<td>TOV</td>
		<td>PF</td>
		<td>PTS</td>
	</tr>
	</thead>
	<tbody>
<?php

	$current_position = [ "PG", "SG", "SF", "PF", "C" ];

	// run through all positions
	$position_averages = [];
	foreach( $current_position as $position ) {
		$temp = [ "FG" => 0, "FGA" => 0, "TP" => 0, "TPA" => 0, "FT" => 0, "FTA" => 0, "ORB" => 0, "TRB" => 0,
			"AST" => 0, "STL" => 0, "BLK" => 0, "TOV" => 0, "PF" => 0, "PTS" => 0, "height" => 0, "weight" => 0 ];
		$number_of_players_at_this_position = 0;

		// run through all players
		foreach( $current_player as $player ) {

			// only take stats if player played this position
			if ( strpos( $player[ "position_real" ], $position ) === false ) {
				continue;
			}

			// skip players without a pace value
			if ( $player[ "pace" ] === "" || intval( $player[ "pace" ] ) === 0 ) {
				continue;
			}

			// add per75poss stats
			$temp_poss = ( $player[ "pace" ] / 48 ) * $player[ "MIN" ];
			foreach ($temp as $key => $t) {
				if ( $key === "height" || $key === "weight" ) {
					$temp[ $key ] += $player[ $key ];
					continue;
				}

				$next_stat = 0;
				if ( !is_null( $player[ $key ] ) ) {
					$next_stat = !empty( $player[ $key ] ) ? ( $player[ $key ] / ( $temp_poss ) ) * 75 : 0 ;
				}
				$temp[ $key ] += $next_stat;
			}

			// increase number of players at this positiong
			$number_of_players_at_this_position++;
		}

		// average out all per75poss stats
		foreach( $temp as $key => $t ) {
			$temp[ $key ] = $t / $number_of_players_at_this_position;
		}

		// add position stats to position_averages array
		$position_averages[ $position ] = $temp;
?>
	<tr class="text_right">
		<td class="text_left"><?php echo $position; ?></td>
		<td><?php echo $number_of_players_at_this_position; ?></td>
		<td><?php echo number_format( $temp[ "height" ], 1 ); ?></td>
		<td><?php echo number_format( $temp[ "weight" ], 1 ); ?></td>
		<td><?php echo number_format( $temp[ "FGA" ], 1 ); ?></td>
		<td><?php echo number_format( ( $temp[ "FG" ] / $temp[ "FGA" ] ), 3 ); ?></td>
		<td><?php echo number_format( $temp[ "TPA" ], 1 ); ?></td>
		<td><?php echo number_format( ( $temp[ "TP" ] / $temp[ "TPA" ] ), 3 ); ?></td>
		<td><?php echo number_format( $temp[ "FTA" ], 1 ); ?></td>
		<td><?php echo number_format( ( $temp[ "FT" ] / $temp[ "FTA" ] ), 3 ); ?></td>
		<td><?php echo number_format( $temp[ "ORB" ], 1 ); ?></td>
		<td><?php echo number_format( $temp[ "TRB" ], 1 ); ?></td>
		<td><?php echo number_format( $temp[ "AST" ], 1 ); ?></td>
		<td><?php echo number_format( $temp[ "STL" ], 1 ); ?></td>
		<td><?php echo number_format( $temp[ "BLK" ], 1 ); ?></td>
		<td><?php echo number_format( $temp[ "TOV" ], 1 ); ?></td>
		<td><?php echo number_format( $temp[ "PF" ], 1 ); ?></td>
		<td><?php echo number_format( $temp[ "PTS" ], 1 ); ?></td>
	</tr>
<?php
	}
?>
	</tbody>
	</table>

<?php
	if ( $this_playerID ) {
		// get player data from this year
		$query = $db->prepare( "SELECT y.first_name, y.last_name, y.position, y.position_real, y.pace, y.height, y.weight, t.*
			FROM PLAYER_tot t, PLAYER_year y
			WHERE t.year = :year AND t.league = :league AND y.playerID = :playerID AND y.partial = 0
			AND y.year = t.year AND y.playerID = t.playerID AND y.teamID = t.teamID" );
		$query->bindValue( ":year", $this_year, PDO::PARAM_INT );
		$query->bindValue( ":league", $this_league, PDO::PARAM_STR );
		$query->bindValue( ":playerID", $this_playerID, PDO::PARAM_STR );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$current_player = $query->fetch();

		// convert stats to per75poss
		$temp_poss = ( $current_player[ "pace" ] / 48 ) * $current_player[ "MIN" ];
		$stats = [ "FG", "FGA", "TP", "TPA", "FT", "FTA", "ORB", "TRB", "AST", "STL", "BLK", "TOV", "PF", "PTS", "height", "weight" ];
		foreach ( $stats as $key => $s ) {
			if ( $s === "height" || $s === "weight" ) {
				$my_player[ $s ] = $current_player[ $s ];
				continue;
			}

			if ( is_null( $current_player[ $s ] ) ) {
				$my_player[ $s ] = 0;
			} else {
				$my_player[ $s ] = ( $current_player[ $s ] > 0 ) ? ( $current_player[ $s ] / ( $temp_poss ) ) * 75 : 0 ;
			}
		}
?>

	<strong><?php echo $current_player[ "first_name" ], " ", $current_player[ "last_name" ]; ?></strong>
	<table>
	<thead>
	<tr class="text_right" style="background-color: #000000; color: #ffff00;">
		<td class="text_left">Position</td>
		<td class="text_left">Team</td>
		<td title="Height">Ht</td>
		<td title="Weight">Wt</td>
		<td>FGA</td>
		<td>FG%</td>
		<td>3PA</td>
		<td>3P%</td>
		<td>FTA</td>
		<td>FT%</td>
		<td>ORB</td>
		<td>TRB</td>
		<td>AST</td>
		<td>STL</td>
		<td>BLK</td>
		<td>TOV</td>
		<td>PF</td>
		<td>PTS</td>
	</tr>
	</thead>
	<tbody>
	<tr class="text_right">
		<td class="text_left"><?php echo $current_player[ "position_real" ]; ?></td>
		<td class="text_left"><?php echo $current_player[ "teamID" ]; ?></td>
		<td><?php echo number_format( $my_player[ "height" ], 1 ); ?></td>
		<td><?php echo number_format( $my_player[ "weight" ], 1 ); ?></td>
		<td><?php echo number_format( $my_player[ "FGA" ], 1 ); ?></td>
		<td><?php echo number_format( ( $my_player[ "FG" ] / $my_player[ "FGA" ] ), 3 ); ?></td>
		<td><?php echo number_format( $my_player[ "TPA" ], 1 ); ?></td>
		<td><?php echo number_format( ( $my_player[ "TP" ] / $my_player[ "TPA" ] ), 3 ); ?></td>
		<td><?php echo number_format( $my_player[ "FTA" ], 1 ); ?></td>
		<td><?php echo number_format( ( $my_player[ "FT" ] / $my_player[ "FTA" ] ), 3 ); ?></td>
		<td><?php echo number_format( $my_player[ "ORB" ], 1 ); ?></td>
		<td><?php echo number_format( $my_player[ "TRB" ], 1 ); ?></td>
		<td><?php echo number_format( $my_player[ "AST" ], 1 ); ?></td>
		<td><?php echo number_format( $my_player[ "STL" ], 1 ); ?></td>
		<td><?php echo number_format( $my_player[ "BLK" ], 1 ); ?></td>
		<td><?php echo number_format( $my_player[ "TOV" ], 1 ); ?></td>
		<td><?php echo number_format( $my_player[ "PF" ], 1 ); ?></td>
		<td><?php echo number_format( $my_player[ "PTS" ], 1 ); ?></td>
	</tr>
<?php
		$all_positions = explode( "-", $current_player[ "position_real" ] );
		$pos1 = $all_positions[0];
		$pos2 = isset( $all_positions[1] ) ? $all_positions[1] : "";
?>
	<tr class="text_right">
		<td class="text_left"><?php echo $current_player[ "position_real" ]; ?></td>
		<td class="text_left">Avg</td>
		<td><?php echo number_format( find_average( $position_averages, "height", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "weight", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "FGA", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "FG", $pos1, $pos2 )
			/ find_average( $position_averages, "FGA", $pos1, $pos2 ), 3 ) ?></td>
		<td><?php echo number_format( find_average( $position_averages, "TPA", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "TP", $pos1, $pos2 )
			/ find_average( $position_averages, "TPA", $pos1, $pos2 ), 3 ) ?></td>
		<td><?php echo number_format( find_average( $position_averages, "FTA", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "FT", $pos1, $pos2 )
			/ find_average( $position_averages, "FTA", $pos1, $pos2 ), 3 ) ?></td>
		<td><?php echo number_format( find_average( $position_averages, "ORB", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "TRB", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "AST", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "STL", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "BLK", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "TOV", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "PF", $pos1, $pos2 ), 1 ); ?></td>
		<td><?php echo number_format( find_average( $position_averages, "PTS", $pos1, $pos2 ), 1 ); ?></td>
	</tr>
	</tbody>
	</table>
<?php
	}
?>

	</div> <!-- #content -->

<?php include "includes/scripts.php"; ?>

</body>
</html>
