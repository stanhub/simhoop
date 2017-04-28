<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php

	// connect, css
	include "includes/head.php";

?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php

	// grab preview status from URL
	$preview_mode = "live = 1";			// show only content marked live
	if ( ( isset( $_GET[ "preview" ] ) ) && ( !empty( $_GET[ "preview" ] ) ) ) {
		$preview_mode = "live < 10";	// show everything live or not
	}

	// grab playerID from URL
	if ( ( isset( $_GET[ "id" ] ) ) && ( !empty( $_GET[ "id" ] ) ) ) {
		$playerID = $_GET[ "id" ];
	}

	// get player bio
	$sql = "SELECT b.* FROM PLAYER_bio b WHERE b.playerID = :playerID AND " . $preview_mode;
	$query = $db->prepare( $sql );
	$query->bindValue( ':playerID', $playerID, PDO::PARAM_STR );
	$query->setFetchMode( PDO::FETCH_CLASS, 'Player' );
	$query->execute();
	$current_player = $query->fetch();

	// if no rows found, pull Michael Jordan
	if ( $query->rowCount() === 0 ) {

		$playerID = "jordamic01";
		$preview_mode = "live < 10";

		$query = $db->prepare( "SELECT b.* FROM PLAYER_bio b WHERE b.playerID = :playerID" );
		$query->bindValue( ':playerID', $playerID, PDO::PARAM_STR );
		$query->setFetchMode( PDO::FETCH_CLASS, 'Player' );
		$query->execute();
		$current_player = $query->fetch();

	}

	// get player years
	$sql = "SELECT y.* FROM PLAYER_year y WHERE y.playerID = :playerID AND y." . $preview_mode . " ORDER BY y.season, y.year, y.orderID";
	$query = $db->prepare( $sql );
	$query->bindValue( ':playerID', $playerID, PDO::PARAM_STR );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_year = $query->fetchAll();

	$current_player->setYears( $current_year );

	// get player total stats
	if ( $query->rowCount() > 0 ) {		// don't get any if there weren't any PLAYER_year rows found

		for ( $i = 0; $i < $current_player->current_year_count; $i++ ) {

			$query = $db->prepare( "SELECT t.* FROM PLAYER_tot t WHERE t.yearID = :yearID" );
			$query->bindValue( ':yearID', $current_player->current_year[$i]["yearID"], PDO::PARAM_INT );
			$query->setFetchMode( PDO::FETCH_ASSOC );
			$query->execute();
			$temp_tot = $query->fetch();

			array_push( $current_player->current_total, $temp_tot );

		}

		// update pergame, per36min, per75poss and per100poss stats
		$current_player->current_total_count = $current_player->current_year_count;
		$current_player->setPerGame();
		$current_player->setPer36min();
		$current_player->per75and100poss();

	}

	// are there any partial seasons for this player?
	$any_partials = 0;
	for ( $i = 0; $i < $current_player->current_year_count; $i++ ) {

		if ( intval( $current_player->current_year[$i]["partial"] ) === 1 ) {

			$any_partials = 1;
			break;

		}

	}

?>

<title>SimHoop: <?php echo $current_player->display_name; ?></title>
</head>

<body id="player">

	<div id="content">

		<div class="info">

			<h1><?php echo $current_player->display_name; ?></h1>

<?php

	// full name
	echo $current_player->first_name, " ", $current_player->middle_name, " ", $current_player->last_name;
	if ( ! empty( $current_player->suffix ) ) {
		echo ", ", $current_player->suffix;
	}

	// birth date, birth name, hand
	if ( ! empty( $current_player->birth_date ) ) {
		echo "<p>Born ";

		if ( ! empty( $current_player->birth_name ) ) {
			echo $current_player->birth_name, " &mdash; ";
		}

		if ( ! empty( $current_player->birth_date ) ) {
			$player_birth_date = strtotime( $current_player->birth_date );
			echo date( 'F j, Y', $player_birth_date ), " ";
		}

		if ( ! empty( $current_player->hand ) ) {

			switch ( $current_player->hand ) {
				case "B":
					$player_hand = "Ambidextrous";
					break;
				case "L":
					$player_hand = "Left-Handed";
					break;
				case "R":
					$player_hand = "Right-Handed";
					break;
				case "L2R":
					$player_hand = "Right-Handed (Previously Left-Handed)";
					break;
			}

			echo "&mdash; Shoots ", $player_hand;

		}
		
		echo "</p>";
	}

	// nickname
	if ( ! empty( $current_player->nick_name ) ) {
		echo "<p>";
		if ( strpos( $current_player->nick_name, "," ) !== FALSE ) {
			echo "Nicknames: ";
		} else {
			echo "Nickname: ";
		}
		echo $current_player->nick_name, "</p>";
	}

	// colleges
	if ( ! empty( $current_player->college1 ) ) {
		echo "<p>", $current_player->college1;
		if ( ! empty ( $current_player->college2 ) ) {
			echo " &mdash; ", $current_player->college2;
			if ( ! empty( $current_player->college3 ) ) {
				echo " &mdash; ", $current_player->college3;
			}
		}
		echo "</p>";
	}

?>

		</div> <!-- .info -->

		<div class="section_title">
			<a href="#" data-section="real-stats" title="Show / Hide Real Statistics"><span class="section_title_icon">[ - ]</span> <b>Real Statistics</b></a>
		</div>

		<div class="section" id="real-stats">

			<div class="tabs">
				<li class="active"><a href="#" data-tab="tab-totals" title="Real Totals Stats">Totals</a></li>
				<li><a href="#" data-tab="tab-per-game" title="Real Per Game Stats">Per Game</a></li>
				<li><a href="#" data-tab="tab-per-36-min" title="Real Per 36 Minutes Stats">Per 36 Minutes</a></li>
				<li><a href="#" data-tab="tab-per-100-poss" title="Real Per 100 Possessions Stats">Per 100 Possessions</a></li>
				<li><a href="#" data-tab="tab-per-75-poss" title="Real Per 75 Possessions Stats">Per 75 Possessions</a></li>
				<li><a href="#" data-tab="tab-notes" title="Notes">Notes</a></li>
<?php
	if ( $any_partials === 1 ) {
?>
				<a href="#" class="hide_partials" title="Hide Partial Seasons">Hide Partials</a>
<?php
	}
?>
			</div>

			<div class="tab" id="tab-totals">
				<table class="tablesorter">
				<thead>
				<tr class="text_right">
					<th class="text_center" title="Season">Sn</th>
					<th title="Year">Year</th>
					<th class="text_center" title="League">Lg</th>
					<th class="text_center" title="Team">Tm</th>
					<th class="text_left" title="Player Name">Player</th>
					<th class="text_center" title="Age">Age</th>
					<th class="text_center" title="Height">Ht</th>
					<th title="Weight">Wt</th>
					<th class="text_center" title="Position">Pos</th>
					<th class="text_center" title="Real Position">Pos*</th>
					<th title="Games Played">GP</th>
					<th title="Minutes Played">MIN</th>
					<th title="Field Goals Made">FG</th>
					<th title="Field Goal Attempts">FGA</th>
					<th title="Field Goal Percentage">FG%</th>
					<th title="Three-Pointers Made">3P</th>
					<th title="Three-Point Attempts">3PA</th>
					<th title="Three-Point Percentage">3P%</th>
					<th title="Free Throws Made">FT</th>
					<th title="Free Throw Attempts">FTA</th>
					<th title="Free Throw Percentage">FT%</th>
					<th title="Offensive Rebounds">ORB</th>
					<th title="Defensive Rebounds">DRB</th>
					<th title="Total Rebounds">TRB</th>
					<th title="Assists">AST</th>
					<th title="Steals">STL</th>
					<th title="Blocks">BLK</th>
					<th title="Turnovers">TOV</th>
					<th title="Personal Fouls">PF</th>
					<th title="Technical Fouls">TF</th>
					<th title="Points">PTS</th>
					<th title="Pos Fixed By B-R.com">?</th>
				</tr>
				</thead>
				<tbody>
<?php
	// set league variables
	$prev_league = "";
	$league_name = array();
	$prev_teamID = "";
	$team_name = array();

	// empty reference_ids
	$reference_ids = "";

	for ( $i = 0; $i < $current_player->current_year_count; $i++ ) {

		$this_player = $current_player;
		$partial = ( $this_player->current_year[$i]["partial"] == 1 ) ? "partial" : "";

		// build array of league full names
		// if this is a new league, get the league's full name
		if ( $this_player->current_year[$i]["league"] !== $prev_league ) {

			$query = $db->prepare( "SELECT l.* FROM LEAGUE l WHERE l.league = :league" );
			$query->bindValue( ':league', $this_player->current_year[$i]["league"], PDO::PARAM_STR );
			$query->setFetchMode( PDO::FETCH_ASSOC );
			$query->execute();
			$current_league = $query->fetch();

			// update league variables
			$prev_league = $this_player->current_year[$i]["league"];
			array_push( $league_name, $current_league["league_name"] );

		// if this is same league, copy previous league name
		} else {

			array_push( $league_name, $league_name[( $i-1 )] );

		}

		// build array of team full names
		// if this is a new team, get the team's full name
		if ( $this_player->current_year[$i]["teamID"] !== $prev_teamID ) {

			// new team is TOT
			if ( $this_player->current_year[$i]["teamID"] === "TOT" ) {

				array_push( $team_name, "Total" );

			// new team is a regular team
			} else {

				$query = $db->prepare( "SELECT t.* FROM TEAM_bio t WHERE t.teamID = :teamID AND t.year = :year" );
				$query->bindValue( ':teamID', $this_player->current_year[$i]["teamID"], PDO::PARAM_STR );
				$query->bindValue( ':year', $this_player->current_year[$i]["year"], PDO::PARAM_INT );
				$query->setFetchMode( PDO::FETCH_ASSOC );
				$query->execute();
				$current_team_name = $query->fetch();

				array_push( $team_name, $current_team_name["team_name"] );

			}

			// update team variables
			$prev_teamID = $this_player->current_year[$i]["teamID"];

		// if this is same league, copy previous league name
		} else {

			array_push( $team_name, $team_name[( $i-1 )] );

		}

		// add to reference ids string
		$reference_ids .= $this_player->current_year[$i]["reference"] . ",";

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[$i]["season"] ) ) { echo $this_player->current_year[$i]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[$i]["year"]; ?>&amp;league=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $this_player->current_year[$i]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[$i]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $league_name[$i]; ?>"><?php echo $this_player->current_year[$i]["league"]; ?></a></td>
<?php
	// handle team row
	if ( $this_player->current_year[$i]["teamID"] === "TOT" ) {
?>
					<td class="text_center">TOT</td>
<?php
	} else {
?>
					<td class="text_center"><a href="team.php?id=<?php echo $this_player->current_year[$i]["teamID"]; ?>&amp;year=<?php echo $this_player->current_year[$i]["year"]; ?>" title="<?php echo $team_name[$i], " (", $this_player->current_year[$i]["year"], ")"; ?>"><?php echo $this_player->current_year[$i]["teamID"]; ?></a></td>
<?php
	}
?>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[$i]["last_name"], ",", $this_player->current_year[$i]["first_name"]; ?></span><?php echo $this_player->current_year[$i]["first_name"], " ", $this_player->current_year[$i]["last_name"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[$i]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[$i]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[$i]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[$i]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[$i]["GP"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["MIN"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["FG"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->current_total[$i]["FG"], $this_player->current_total[$i]["FGA"] ); ?></td>
					<td><?php echo $this_player->current_total[$i]["TP"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->current_total[$i]["TP"], $this_player->current_total[$i]["TPA"] ); ?></td>
					<td><?php echo $this_player->current_total[$i]["FT"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->current_total[$i]["FT"], $this_player->current_total[$i]["FTA"] ); ?></td>
					<td><?php echo $this_player->current_total[$i]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[$i]["ORB"] !== NULL ) { echo number_format( ( $this_player->current_total[$i]["TRB"] - $this_player->current_total[$i]["ORB"] ), 0 ); } ?></td>
					<td><?php echo $this_player->current_total[$i]["TRB"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["AST"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["STL"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["BLK"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["TOV"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["PF"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["TF"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["PTS"]; ?></td>
<?php
		} else {
?>
					<td colspan="21"></td>
<?php
		}
?>
					<td>
						<?php
							if ( $this_player->current_year[$i]["year"] > 2000 ) {
								if ( $this_player->current_year[$i]["doneposition"] === "1" ) {
									echo "<strong>1</strong>";
								}
							} else {
								echo "x";
							} ?>
					</td>
				</tr>
<?php
	}
?>
				</tbody>
				</table>
			</div> <!-- #tab-totals -->

<?php
	// remove final "," in reference_ids
	$reference_ids = rtrim( $reference_ids, "," );

	// remove all duplicates reference ids
	$reference_ids = implode( ",", array_keys( array_flip( explode( ",", $reference_ids ) ) ) );

	// sort the reference ids
	$reference_array = explode( ",", $reference_ids );
	sort( $reference_array );

	$reference_placeholders = implode( ",", array_fill( 0, count( $reference_array ), "?" ) );
	$query = $db->prepare( "SELECT r.* FROM REFERENCE r WHERE r.refID IN (" . $reference_placeholders . ")" );
	foreach( $reference_array as $k => $ref ) {
		$query->bindValue( ($k+1), $ref );
	}
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_reference = $query->fetchAll();

?>


			<div class="tab collapsed" id="tab-per-game">
				<table class="tablesorter">
				<thead>
				<tr class="text_right">
					<th class="text_center" title="Season">Sn</th>
					<th title="Year">Year</th>
					<th class="text_center" title="League">Lg</th>
					<th class="text_center" title="Team">Tm</th>
					<th class="text_left" title="Player Name">Player</th>
					<th class="text_center" title="Age">Age</th>
					<th class="text_center" title="Height">Ht</th>
					<th title="Weight">Wt</th>
					<th class="text_center" title="Position">Pos</th>
					<th class="text_center" title="Real Position">Pos*</th>
					<th title="Games Played">GP</th>
					<th title="Minutes Played Per Game">MIN</th>
					<th title="Field Goals Made Per Game">FG</th>
					<th title="Field Goal Attempts Per Game">FGA</th>
					<th title="Field Goal Percentage">FG%</th>
					<th title="Three-Pointers Made Per Game">3P</th>
					<th title="Three-Point Attempts Per Game">3PA</th>
					<th title="Three-Point Percentage">3P%</th>
					<th title="Free Throws Made Per Game">FT</th>
					<th title="Free Throw Attempts Per Game">FTA</th>
					<th title="Free Throw Percentage">FT%</th>
					<th title="Offensive Rebounds Per Game">ORB</th>
					<th title="Defensive Rebounds Per Game">DRB</th>
					<th title="Total Rebounds Per Game">TRB</th>
					<th title="Assists Per Game">AST</th>
					<th title="Steals Per Game">STL</th>
					<th title="Blocks Per Game">BLK</th>
					<th title="Turnovers Per Game">TOV</th>
					<th title="Personal Fouls Per Game">PF</th>
					<th title="Technical Fouls">TF</th>
					<th title="Points Per Game">PTS</th>
				</tr>
				</thead>
				<tbody>
<?php

	for ( $i = 0; $i < $current_player->current_year_count; $i++ ) {

		$this_player = $current_player;
		$partial = ( $this_player->current_year[$i]["partial"] == 1 ) ? "partial" : "";

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[$i]["season"] ) ) { echo $this_player->current_year[$i]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[$i]["year"]; ?>&amp;league=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $this_player->current_year[$i]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[$i]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $league_name[$i]; ?>"><?php echo $this_player->current_year[$i]["league"]; ?></a></td>
<?php
	// handle team row
	if ( $this_player->current_year[$i]["teamID"] === "TOT" ) {
?>
					<td class="text_center">TOT</td>
<?php
	} else {
?>
					<td class="text_center"><a href="team.php?id=<?php echo $this_player->current_year[$i]["teamID"]; ?>&amp;year=<?php echo $this_player->current_year[$i]["year"]; ?>" title="<?php echo $team_name[$i], " (", $this_player->current_year[$i]["year"], ")"; ?>"><?php echo $this_player->current_year[$i]["teamID"]; ?></a></td>
<?php
	}
?>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[$i]["last_name"], ",", $this_player->current_year[$i]["first_name"]; ?></span><?php echo $this_player->current_year[$i]["first_name"], " ", $this_player->current_year[$i]["last_name"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[$i]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[$i]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[$i]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[$i]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[$i]["GP"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["MIN"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["FG"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_game[$i]["FG"], $this_player->per_game[$i]["FGA"] ); ?></td>
					<td><?php echo $this_player->per_game[$i]["TP"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_game[$i]["TP"], $this_player->per_game[$i]["TPA"] ); ?></td>
					<td><?php echo $this_player->per_game[$i]["FT"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_game[$i]["FT"], $this_player->per_game[$i]["FTA"] ); ?></td>
					<td><?php echo $this_player->per_game[$i]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[$i]["ORB"] !== NULL ) { echo number_format( ( $this_player->per_game[$i]["TRB"] - $this_player->per_game[$i]["ORB"] ), 1 ); } ?></td>
					<td><?php echo $this_player->per_game[$i]["TRB"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["AST"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["STL"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["BLK"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["TOV"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["PF"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["TF"]; ?></td>
					<td><?php echo $this_player->per_game[$i]["PTS"]; ?></td>
<?php
		} else {
?>
					<td colspan="21"></td>
<?php
		}
?>
				</tr>
<?php
	}
?>
				</tbody>
				</table>
			</div>	<!-- #tab-per-game -->


			<div class="tab collapsed" id="tab-per-36-min">
				<table class="tablesorter">
				<thead>
				<tr class="text_right">
					<th class="text_center" title="Season">Sn</th>
					<th title="Year">Year</th>
					<th class="text_center" title="League">Lg</th>
					<th class="text_center" title="Team">Tm</th>
					<th class="text_left" title="Player">Player</th>
					<th class="text_center" title="Age">Age</th>
					<th class="text_center" title="Height">Ht</th>
					<th title="Weight">Wt</th>
					<th class="text_center" title="Position">Pos</th>
					<th class="text_center" title="Real Position">Pos*</th>
					<th title="Games Played">GP</th>
					<th title="Minutes Played">MIN</th>
					<th title="Field Goals Made Per 36 Minutes">FG</th>
					<th title="Field Goal Attempts Per 36 Minutes">FGA</th>
					<th title="Field Goal Percentage">FG%</th>
					<th title="Three-Pointers Made Per 36 Minutes">3P</th>
					<th title="Three-Point Attempts Per 36 Minutes">3PA</th>
					<th title="Three-Point Percentage">3P%</th>
					<th title="Free Throws Made Per 36 Minutes">FT</th>
					<th title="Free Throw Attempts Per 36 Minutes">FTA</th>
					<th title="Free Throw Percentage">FT%</th>
					<th title="Offensive Rebounds Per 36 Minutes">ORB</th>
					<th title="Defensive Rebounds Per 36 Minutes">DRB</th>
					<th title="Total Rebounds Per 36 Minutes">TRB</th>
					<th title="Assists Per 36 Minutes">AST</th>
					<th title="Steals Per 36 Minutes">STL</th>
					<th title="Blocks Per 36 Minutes">BLK</th>
					<th title="Turnovers Per 36 Minutes">TOV</th>
					<th title="Personal Fouls Per 36 Minutes">PF</th>
					<th title="Technical Fouls">TF</th>
					<th title="Points Per 36 Minutes">PTS</th>
				</tr>
				</thead>
				<tbody>
<?php

	for ( $i = 0; $i < $current_player->current_year_count; $i++ ) {

		$this_player = $current_player;
		$partial = ( $this_player->current_year[$i]["partial"] == 1 ) ? "partial" : "";

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[$i]["season"] ) ) { echo $this_player->current_year[$i]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[$i]["year"]; ?>&amp;league=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $this_player->current_year[$i]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[$i]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $league_name[$i]; ?>"><?php echo $this_player->current_year[$i]["league"]; ?></a></td>
<?php
	// handle team row
	if ( $this_player->current_year[$i]["teamID"] === "TOT" ) {
?>
					<td class="text_center">TOT</td>
<?php
	} else {
?>
					<td class="text_center"><a href="team.php?id=<?php echo $this_player->current_year[$i]["teamID"]; ?>&amp;year=<?php echo $this_player->current_year[$i]["year"]; ?>" title="<?php echo $team_name[$i], " (", $this_player->current_year[$i]["year"], ")"; ?>"><?php echo $this_player->current_year[$i]["teamID"]; ?></a></td>
<?php
	}
?>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[$i]["last_name"], ",", $this_player->current_year[$i]["first_name"]; ?></span><?php echo $this_player->current_year[$i]["first_name"], " ", $this_player->current_year[$i]["last_name"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[$i]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[$i]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[$i]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[$i]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[$i]["GP"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["MIN"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["FG"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_36_min[$i]["FG"], $this_player->per_36_min[$i]["FGA"] ); ?></td>
					<td><?php echo $this_player->per_36_min[$i]["TP"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_36_min[$i]["TP"], $this_player->per_36_min[$i]["TPA"] ); ?></td>
					<td><?php echo $this_player->per_36_min[$i]["FT"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_36_min[$i]["FT"], $this_player->per_36_min[$i]["FTA"] ); ?></td>
					<td><?php echo $this_player->per_36_min[$i]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[$i]["ORB"] !== NULL ) { echo number_format( ( $this_player->per_36_min[$i]["TRB"] - $this_player->per_36_min[$i]["ORB"] ), 1 ); } ?></td>
					<td><?php echo $this_player->per_36_min[$i]["TRB"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["AST"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["STL"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["BLK"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["TOV"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["PF"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["TF"]; ?></td>
					<td><?php echo $this_player->per_36_min[$i]["PTS"]; ?></td>
<?php
		} else {
?>
					<td colspan="21"></td>
<?php
		}
?>
				</tr>
<?php
	}
?>
				</tbody>
				</table>
			</div> <!-- #tab-per-36-min -->


			<div class="tab collapsed" id="tab-per-100-poss">
				<table class="tablesorter">
				<thead>
				<tr class="text_right">
					<th class="text_center" title="Season">Sn</th>
					<th title="Year">Year</th>
					<th class="text_center" title="League">Lg</th>
					<th class="text_center" title="Team">Tm</th>
					<th class="text_left" title="Player">Player</th>
					<th class="text_center" title="Age">Age</th>
					<th class="text_center" title="Height">Ht</th>
					<th title="Weight">Wt</th>
					<th class="text_center" title="Position">Pos</th>
					<th class="text_center" title="Real Position">Pos*</th>
					<th title="Games Played">GP</th>
					<th title="Minutes Played">MIN</th>
					<th title="Field Goals Made Per 100 Possessions">FG</th>
					<th title="Field Goal Attempts Per 100 Possessions">FGA</th>
					<th title="Field Goal Percentage">FG%</th>
					<th title="Three-Pointers Made Per 100 Possessions">3P</th>
					<th title="Three-Point Attempts Per 100 Possessions">3PA</th>
					<th title="Three-Point Percentage">3P%</th>
					<th title="Free Throws Made Per 100 Possessions">FT</th>
					<th title="Free Throw Attempts Per 100 Possessions">FTA</th>
					<th title="Free Throw Percentage">FT%</th>
					<th title="Offensive Rebounds Per 100 Possessions">ORB</th>
					<th title="Defensive Rebounds Per 100 Possessions">DRB</th>
					<th title="Total Rebounds Per 100 Possessions">TRB</th>
					<th title="Assists Per 100 Possessions">AST</th>
					<th title="Steals Per 100 Possessions">STL</th>
					<th title="Blocks Per 100 Possessions">BLK</th>
					<th title="Turnovers Per 100 Possessions">TOV</th>
					<th title="Personal Fouls Per 100 Possessions">PF</th>
					<th title="Technical Fouls">TF</th>
					<th class="border_right" title="Points Per 100 Possessions">PTS</th>
					<th title="Team Pace">Pc</th>
				</tr>
				</thead>
				<tbody>
<?php

	for ( $i = 0; $i < $current_player->current_year_count; $i++ ) {

		$this_player = $current_player;
		$partial = ( $this_player->current_year[$i]["partial"] == 1 ) ? "partial" : "";

		$this_pace = $this_player->current_year[$i]["pace"];
		if ( $this_player->current_year[$i]["teamID"] === "TOT" && !empty( $this_pace ) ) {

			$this_pace = "<span class='italics'>" . $this_pace . "</span> <span class='help_icon' title='Calculated Pace ((Pace * Team MIN) / Total MIN)'>?</span>";

		}

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[$i]["season"] ) ) { echo $this_player->current_year[$i]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[$i]["year"]; ?>&amp;league=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $this_player->current_year[$i]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[$i]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $league_name[$i]; ?>"><?php echo $this_player->current_year[$i]["league"]; ?></a></td>
<?php
	// handle team row
	if ( $this_player->current_year[$i]["teamID"] === "TOT" ) {
?>
					<td class="text_center">TOT</td>
<?php
	} else {
?>
					<td class="text_center"><a href="team.php?id=<?php echo $this_player->current_year[$i]["teamID"]; ?>&amp;year=<?php echo $this_player->current_year[$i]["year"]; ?>" title="<?php echo $team_name[$i], " (", $this_player->current_year[$i]["year"], ")"; ?>"><?php echo $this_player->current_year[$i]["teamID"]; ?></a></td>
<?php
	}
?>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[$i]["last_name"], ",", $this_player->current_year[$i]["first_name"]; ?></span><?php echo $this_player->current_year[$i]["first_name"], " ", $this_player->current_year[$i]["last_name"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[$i]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[$i]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[$i]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[$i]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[$i]["GP"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["MIN"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["FG"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_100_poss[$i]["FG"], $this_player->per_100_poss[$i]["FGA"] ); ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["TP"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_100_poss[$i]["TP"], $this_player->per_100_poss[$i]["TPA"] ); ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["FT"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_100_poss[$i]["FT"], $this_player->per_100_poss[$i]["FTA"] ); ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[$i]["ORB"] !== NULL ) { echo number_format( ( $this_player->per_100_poss[$i]["TRB"] - $this_player->per_100_poss[$i]["ORB"] ), 1 ); } ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["TRB"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["AST"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["STL"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["BLK"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["TOV"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["PF"]; ?></td>
					<td><?php echo $this_player->per_100_poss[$i]["TF"]; ?></td>
					<td class="border_right"><?php echo $this_player->per_100_poss[$i]["PTS"]; ?></td>
					<td><?php echo $this_pace; ?></td>
<?php
		} else {
?>
					<td class="border_right" colspan="21"></td>
					<td><?php echo $this_pace; ?></td>
<?php
		}
?>
				</tr>
<?php
	}
?>
				</tbody>
				</table>
			</div> <!-- #tab-per-75-poss -->


			<div class="tab collapsed" id="tab-per-75-poss">
				<table class="tablesorter">
				<thead>
				<tr class="text_right">
					<th class="text_center" title="Season">Sn</th>
					<th title="Year">Year</th>
					<th class="text_center" title="League">Lg</th>
					<th class="text_center" title="Team">Tm</th>
					<th class="text_left" title="Player">Player</th>
					<th class="text_center" title="Age">Age</th>
					<th class="text_center" title="Height">Ht</th>
					<th title="Weight">Wt</th>
					<th class="text_center" title="Position">Pos</th>
					<th class="text_center" title="Real Position">Pos*</th>
					<th title="Games Played">GP</th>
					<th title="Minutes Played">MIN</th>
					<th title="Field Goals Made Per 75 Possessions">FG</th>
					<th title="Field Goal Attempts Per 75 Possessions">FGA</th>
					<th title="Field Goal Percentage">FG%</th>
					<th title="Three-Pointers Made Per 75 Possessions">3P</th>
					<th title="Three-Point Attempts Per 75 Possessions">3PA</th>
					<th title="Three-Point Percentage">3P%</th>
					<th title="Free Throws Made Per 75 Possessions">FT</th>
					<th title="Free Throw Attempts Per 75 Possessions">FTA</th>
					<th title="Free Throw Percentage">FT%</th>
					<th title="Offensive Rebounds Per 75 Possessions">ORB</th>
					<th title="Defensive Rebounds Per 75 Possessions">DRB</th>
					<th title="Total Rebounds Per 75 Possessions">TRB</th>
					<th title="Assists Per 75 Possessions">AST</th>
					<th title="Steals Per 75 Possessions">STL</th>
					<th title="Blocks Per 75 Possessions">BLK</th>
					<th title="Turnovers Per 75 Possessions">TOV</th>
					<th title="Personal Fouls Per 75 Possessions">PF</th>
					<th title="Technical Fouls">TF</th>
					<th class="border_right" title="Points Per 75 Possessions">PTS</th>
					<th title="Team Pace">Pc</th>
				</tr>
				</thead>
				<tbody>
<?php

	for ( $i = 0; $i < $current_player->current_year_count; $i++ ) {

		$this_player = $current_player;
		$partial = ( $this_player->current_year[$i]["partial"] == 1 ) ? "partial" : "";

		$this_pace = $this_player->current_year[$i]["pace"];
		if ( $this_player->current_year[$i]["teamID"] === "TOT" && !empty( $this_pace ) ) {

			$this_pace = "<span class='italics'>" . $this_pace . "</span> <span class='help_icon' title='Calculated Pace ((Pace * Team MIN) / Total MIN)'>?</span>";

		}

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[$i]["season"] ) ) { echo $this_player->current_year[$i]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[$i]["year"]; ?>&amp;league=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $this_player->current_year[$i]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[$i]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $league_name[$i]; ?>"><?php echo $this_player->current_year[$i]["league"]; ?></a></td>
<?php
	// handle team row
	if ( $this_player->current_year[$i]["teamID"] === "TOT" ) {
?>
					<td class="text_center">TOT</td>
<?php
	} else {
?>
					<td class="text_center"><a href="team.php?id=<?php echo $this_player->current_year[$i]["teamID"]; ?>&amp;year=<?php echo $this_player->current_year[$i]["year"]; ?>" title="<?php echo $team_name[$i], " (", $this_player->current_year[$i]["year"], ")"; ?>"><?php echo $this_player->current_year[$i]["teamID"]; ?></a></td>
<?php
	}
?>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[$i]["last_name"], ",", $this_player->current_year[$i]["first_name"]; ?></span><?php echo $this_player->current_year[$i]["first_name"], " ", $this_player->current_year[$i]["last_name"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[$i]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[$i]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[$i]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[$i]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[$i]["GP"]; ?></td>
					<td><?php echo $this_player->current_total[$i]["MIN"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["FG"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_75_poss[$i]["FG"], $this_player->per_75_poss[$i]["FGA"] ); ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["TP"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_75_poss[$i]["TP"], $this_player->per_75_poss[$i]["TPA"] ); ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["FT"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_75_poss[$i]["FT"], $this_player->per_75_poss[$i]["FTA"] ); ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[$i]["ORB"] !== NULL ) { echo number_format( ( $this_player->per_75_poss[$i]["TRB"] - $this_player->per_75_poss[$i]["ORB"] ), 1 ); } ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["TRB"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["AST"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["STL"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["BLK"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["TOV"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["PF"]; ?></td>
					<td><?php echo $this_player->per_75_poss[$i]["TF"]; ?></td>
					<td class="border_right"><?php echo $this_player->per_75_poss[$i]["PTS"]; ?></td>
					<td><?php echo $this_pace; ?></td>
<?php
		} else {
?>
					<td class="border_right" colspan="21"></td>
					<td><?php echo $this_pace; ?></td>
<?php
		}
?>
				</tr>
<?php
	}
?>
				</tbody>
				</table>
			</div> <!-- #tab-per-75-poss -->


			<div class="tab collapsed" id="tab-notes">
				<table class="tablesorter">
				<thead>
				<tr class="text_right">
					<th class="text_center" title="Season">Sn</th>
					<th title="Year">Year</th>
					<th class="text_center" title="League">Lg</th>
					<th class="text_center" title="Team">Tm</th>
					<th class="text_left" title="Player">Player</th>
					<th class="text_center" title="Age">Age</th>
					<th class="text_center" title="Height">Ht</th>
					<th title="Weight">Wt</th>
					<th class="text_center" title="Position">Pos</th>
					<th class="text_center" title="Real Position">Pos*</th>
					<th title="Percentage of Playing Time at Point Guard">PG</td>
					<th title="Percentage of Playing Time at Shooting Guard">SG</td>
					<th title="Percentage of Playing Time at Small Forward">SF</td>
					<th title="Percentage of Playing Time at Power Forward">PF</td>
					<th title="Percentage of Playing Time at Center">C</td>
					<th class="text_left" title="Notes">Notes</th>
					<th class="text_center" title="Reference Numbers">Ref</th>
				</tr>
				</thead>
				<tbody>
<?php

	for ( $i = 0; $i < $current_player->current_year_count; $i++ ) {

		$this_player = $current_player;
		$partial = ( $this_player->current_year[$i]["partial"] == 1 ) ? "partial" : "";

		// convert reference IDs to local ref (ex: 1,17 = 1,4 if 17 would be the 4th highest reference on the page)
		$these_refs_old = explode( ",", $this_player->current_year[$i]["reference"] );
		$these_refs_new = array();
		foreach( $these_refs_old as $j => $old_ref) {

			foreach( $current_reference as $k => $ref ) {

				if ( $ref["refID"] === $old_ref ) {
					array_push( $these_refs_new, ( $k + 1 ) );
				}

			}

		}
		sort( $these_refs_new );

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[$i]["season"] ) ) { echo $this_player->current_year[$i]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[$i]["year"]; ?>&amp;league=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $this_player->current_year[$i]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[$i]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $this_player->current_year[$i]["league"]; ?>" title="<?php echo $league_name[$i]; ?>"><?php echo $this_player->current_year[$i]["league"]; ?></a></td>
<?php
	// handle team row
	if ( $this_player->current_year[$i]["teamID"] === "TOT" ) {
?>
					<td class="text_center">TOT</td>
<?php
	} else {
?>
					<td class="text_center"><a href="team.php?id=<?php echo $this_player->current_year[$i]["teamID"]; ?>&amp;year=<?php echo $this_player->current_year[$i]["year"]; ?>" title="<?php echo $team_name[$i], " (", $this_player->current_year[$i]["year"], ")"; ?>"><?php echo $this_player->current_year[$i]["teamID"]; ?></a></td>
<?php
	}
?>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[$i]["last_name"], ",", $this_player->current_year[$i]["first_name"]; ?></span><?php echo $this_player->current_year[$i]["first_name"], " ", $this_player->current_year[$i]["last_name"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[$i]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[$i]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[$i]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[$i]["position_real"]; ?></td>
					<td><?php echo $this_player->current_year[ $i ][ "pos_PG" ]; ?></td>
					<td><?php echo $this_player->current_year[ $i ][ "pos_SG" ]; ?></td>
					<td><?php echo $this_player->current_year[ $i ][ "pos_SF" ]; ?></td>
					<td><?php echo $this_player->current_year[ $i ][ "pos_PF" ]; ?></td>
					<td class="border_right"><?php echo $this_player->current_year[ $i ][ "pos_C" ]; ?></td>
					<td class="text_left"><?php echo $this_player->current_year[$i]["notes"]; ?></td>
					<td class="text_center"><?php echo implode( ",", $these_refs_new ); ?></td>
				</tr>
<?php
	}
?>
				</tbody>
				</table>


				<div class="section_title">
					<a href="#" data-section="references" title="Show / Hide References"><span class="section_title_icon">[ - ]</span> <b>References</b></a>
				</div>

				<div class="section" id="references">
					<table>
					<thead>
					<tr class="text_left">
						<th class="text_center" title="Reference Number">Ref</th>
						<th title="Description">Description</th>
						<th title="Date of Change">Date</th>
					</tr>
					</thead>
					<tbody>
<?php
	$reference_count = count( $current_reference );
	for ( $i = 0; $i < $reference_count; $i++ ) {

		// convert start and end dates
		$ref_end = "";
		$ref_start = date( "F j, Y", strtotime( $current_reference[$i]["start"] ) );
		if ( $current_reference[$i]["start"] !== $current_reference[$i]["end"] ) {
			$ref_end = date( "F j, Y", strtotime( $current_reference[$i]["end"] ) );
		}
?>
					<tr class="text_left">
						<td class="text_center"><?php echo ( $i + 1 ); ?></td>
						<td><?php echo $current_reference[$i]["description"]; ?> from <?php echo $current_reference[$i]["source"]; ?></td>
						<td><?php echo $ref_start; ?><?php if ( $ref_end !== "" ) { ?> to <?php echo $ref_end; } ?></td>
					</tr>
<?php
	}
?>
					</tbody>
					</table>
				</div> <!-- #references -->


			</div> <!-- #tab-notes -->

		</div> <!-- #real-stats -->

	</div> <!-- #content -->

<?php include "includes/scripts.php"; ?>

</body>
</html>
