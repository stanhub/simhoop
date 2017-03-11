<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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

	// grab teamID from URL
	if ( ( isset( $_GET[ "id" ] ) ) && ( !empty( $_GET[ "id" ] ) ) ) {
		$teamID = $_GET[ "id" ];
	}

	// grab year from URL
	if ( ( isset( $_GET[ "year" ] ) ) && ( !empty( $_GET[ "year" ] ) ) ) {
		$year = $_GET[ "year" ];
	}

	// get player bio
	$sql = "SELECT b.* FROM TEAM_bio b WHERE b.teamID = :teamID AND b.year = :year AND " . $preview_mode;
	$query = $db->prepare( $sql );
	$query->bindValue( ':teamID', $teamID, PDO::PARAM_STR );
	$query->bindValue( ':year', $year, PDO::PARAM_INT );
	$query->setFetchMode( PDO::FETCH_CLASS, 'Team' );
	$query->execute();
	$current_team = $query->fetch();

	// if no rows found, pull Chicago Bulls 1996
	if ( $query->rowCount() === 0 ) {

		$teamID = "CHI";
		$year = 1996;
		$preview_mode = "live < 10";

		$query = $db->prepare( 'SELECT b.* FROM TEAM_bio b WHERE b.teamID = :teamID AND b.year = :year' );
		$query->bindValue( ':teamID', $teamID, PDO::PARAM_STR );
		$query->bindValue( ':year', $year, PDO::PARAM_INT );
		$query->setFetchMode( PDO::FETCH_CLASS, 'Team' );
		$query->execute();
		$current_team = $query->fetch();

	}

	// get data from PLAYER_bio of players on this team
	$sql = "SELECT b.* FROM PLAYER_bio b, PLAYER_year y WHERE y.teamID = :teamID AND b.playerID = y.playerID AND y.year = :year AND y." . $preview_mode;
	$query = $db->prepare( $sql );
	$query->bindValue( ':teamID', $teamID, PDO::PARAM_STR );
	$query->bindValue( ':year', $year, PDO::PARAM_INT );
	$query->setFetchMode( PDO::FETCH_CLASS, 'Player' );
	$query->execute();
	$current_player = $query->fetchAll();

	$player_count = count( $current_player );

	// get player years
	for ( $i = 0; $i < $player_count; $i++ ) {

		$sql = "SELECT y.* FROM PLAYER_year y WHERE y.playerID = :playerID AND y.teamID = :teamID AND y.year = :year AND y." . $preview_mode;
		$query = $db->prepare( $sql );
		$query->bindValue( ':playerID', $current_player[$i]->playerID, PDO::PARAM_STR );
		$query->bindValue( ':teamID', $teamID, PDO::PARAM_STR );
		$query->bindValue( ':year', $year, PDO::PARAM_INT );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$current_year = $query->fetchAll();

		$current_player[$i]->setYears( $current_year );

	}

	// get player stats
	for ( $i = 0; $i < $player_count; $i++ ) {

		$query = $db->prepare( "SELECT t.* FROM PLAYER_tot t WHERE t.yearID = :yearID" );
		$query->bindValue( ':yearID', $current_player[$i]->current_year[0]["yearID"], PDO::PARAM_INT );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$temp_tot = $query->fetch();

		array_push( $current_player[$i]->current_total, $temp_tot );

		// update pergame, per36min, per75poss and per100poss stats
		$current_player[$i]->current_total_count = $current_player[$i]->current_year_count;
		$current_player[$i]->setPerGame();
		$current_player[$i]->setPer36min();
		$current_player[$i]->per75and100poss();

	}

	// is there a previous year for this franchise?
	$prev_year = $year - 1;
	$query = $db->prepare( 'SELECT b.* FROM TEAM_bio b WHERE b.franchiseID = :franchiseID AND b.year = :prev_year' );
	$query->bindValue( ':franchiseID', $current_team->franchiseID, PDO::PARAM_STR );
	$query->bindValue( ':prev_year', $prev_year, PDO::PARAM_INT );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$prev_team = $query->fetch();

	// is there a next year for this franchise?
	$next_year = $year + 1;
	$query = $db->prepare( 'SELECT b.* FROM TEAM_bio b WHERE b.franchiseID = :franchiseID AND b.year = :next_year' );
	$query->bindValue( ':franchiseID', $current_team->franchiseID, PDO::PARAM_STR );
	$query->bindValue( ':next_year', $next_year, PDO::PARAM_INT );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$next_team = $query->fetch();

	// get league data
	$query = $db->prepare( 'SELECT l.* FROM LEAGUE l WHERE l.league = :league' );
	$query->bindValue( ':league', $current_team->league, PDO::PARAM_STR );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$league = $query->fetch();

	// create previous / next year links (if they exist)
	$prev_next_links = "";
	if ( $prev_team || $next_team ) { $prev_next_links = "<p>"; }
	if ( $prev_team ) {
		$prev_next_links .= "<a href='team.php?id=" . $prev_team["teamID"] . "&amp;year=" . $prev_year . "' title='" . $prev_team["team_name"] . " (" . $prev_year . ")'>Previous Year</a>";
	}
	if ( $next_team ) {
		if ( $prev_team ) {
			$prev_next_links .= " &mdash; ";
		}
		$prev_next_links .= "<a href='team.php?id=" . $next_team["teamID"] . "&amp;year=" . $next_year . "' title='" . $next_team["team_name"] . " (" . $next_year . ")'>Next Year</a>";
	}
	if ( $prev_team || $next_team ) { $prev_next_links .= "</p>"; }

	// are there any partial seasons for this player?
	$any_partials = 0;
	for ( $i = 0; $i < $player_count; $i++ ) {

		if ( intval( $current_player[$i]->current_year[0]["partial"] ) === 1 ) {

			$any_partials = 1;
			break;

		}

	}
?>

<title>SimHoop: <?php echo $current_team->team_name; ?> <?php echo $current_team->year; ?></title>
</head>

<body id="team">

	<div id="content">

		<div class="info">

			<h1><?php echo $current_team->team_name; ?> <span class="small"><?php echo $current_team->year; ?></h1>

<?php
	// show previous / next year links (if they exist)
	echo $prev_next_links;

	// league name and dates
	$league_end = ( empty( $league["end"] ) ) ? "Present" : $league["end"];
	echo "<p><a href='league.php?id=", $league["league"], "' title='", $league["league_name"], "'>", $league["league_name"], "</a> (", $league["start"], "-", $league_end, ")</p>";

	// conference and division
	$conference_division = "";
	if ( ! empty( $current_team->conference ) ) {
		$conference_division = $current_team->conference . " Conference";
	}
	if ( ! empty( $current_team->division ) ) {
		if ( ! empty( $current_team->conference ) ) {
			$conference_division = $conference_division . " &mdash; ";
		}
		$conference_division = $conference_division . $current_team->division . " Division";
	}
	if ( ! empty( $conference_division ) ) {
		echo "<p>", $conference_division, "</p>";
	}

	// location
	$arena_location = "";
	if ( ! empty( $current_team->arena ) ) {
		$arena_location = $current_team->arena;
	}
	if ( ! empty( $current_team->location ) ) {
		if ( ! empty( $current_team->arena ) ) {
			$arena_location = $arena_location . " in ";
		}
		$arena_location = $arena_location . $current_team->location;
	}
	if ( ! empty( $arena_location ) ) {
		echo "<p>", $arena_location, "</p>";
	}

	// win loss record, forfeits and pace
	$record_pace = "";
	// win record
	if ( ! empty( $current_team->win ) && ! empty( $current_team->loss ) ) {
		$record_pace = "Record: " . $current_team->win . "-" . $current_team->loss . " (" . $current_team->findPct( $current_team->win, $current_team->GP ) . ")";
	}
	// forfeit record
	if ( ! empty( $current_team->forfeit_win ) || ! empty( $current_team->forfeit_loss ) ) {
		$record_pace .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Forfeits: " . $current_team->forfeit_win . "-" . $current_team->forfeit_loss . " <span class='help_icon' title='Forfeits are included in win-loss record'>?</span>";
	}
	// team pace
	if ( ! empty( $current_team->pace ) ) {
		$record_pace .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pace: " . $current_team->pace;
	}
	echo $record_pace;

?>

		</div> <!-- .info -->

		<div class="section_title">
			<a href="#" data-section="real-stats" title="Show / Hide Real Statistics"><span class="section_title_icon">[ - ]</span> <b>Real Statistics</b></a>
		</div>

		<div class="section" id="real-stats">

			<div class="tabs">
				<li class="active"><a href="#" data-tab="tab-totals" title="Real Total Stats">Totals</a></li>
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
					<?php if ( $current_player[0]->current_year[0]["year"] > 2000 ) { ?><th title="Pos Fixed By B-R.com">?</th><?php } ?>
				</tr>
				</thead>
				<tbody>
<?php

	// empty reference_ids
	$reference_ids = "";

	for ( $i = 0; $i < $player_count; $i++ ) {

		$this_player = $current_player[$i];
		$partial = ( $this_player->current_year[0]["partial"] == 1 ) ? "partial" : "";

		// add to reference ids string
		$reference_ids .= $this_player->current_year[0]["reference"] . ",";

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[0]["season"] ) ) { echo $this_player->current_year[0]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[0]["year"]; ?>&amp;league=<?php echo $league["league"]; ?>" title="<?php echo $this_player->current_year[0]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[0]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $league["league"]; ?>" title="<?php echo $league["league_name"]; ?>"><?php echo $this_player->current_year[0]["league"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["teamID"]; ?></td>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[0]["last_name"], ",", $this_player->current_year[0]["first_name"]; ?></span><a href="player.php?id=<?php echo $this_player->current_year[0]["playerID"]; ?>" title="<?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?>"><?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[0]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[0]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[0]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[0]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[0]["GP"]; ?></td>
					<td><?php echo $this_player->current_total[0]["MIN"]; ?></td>
					<td><?php echo $this_player->current_total[0]["FG"]; ?></td>
					<td><?php echo $this_player->current_total[0]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->current_total[0]["FG"], $this_player->current_total[0]["FGA"] ); ?></td>
					<td><?php echo $this_player->current_total[0]["TP"]; ?></td>
					<td><?php echo $this_player->current_total[0]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->current_total[0]["TP"], $this_player->current_total[0]["TPA"] ); ?></td>
					<td><?php echo $this_player->current_total[0]["FT"]; ?></td>
					<td><?php echo $this_player->current_total[0]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->current_total[0]["FT"], $this_player->current_total[0]["FTA"] ); ?></td>
					<td><?php echo $this_player->current_total[0]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[0]["ORB"] !== NULL ) { echo number_format( ( $this_player->current_total[0]["TRB"] - $this_player->current_total[0]["ORB"] ), 0 ); } ?></td>
					<td><?php echo $this_player->current_total[0]["TRB"]; ?></td>
					<td><?php echo $this_player->current_total[0]["AST"]; ?></td>
					<td><?php echo $this_player->current_total[0]["STL"]; ?></td>
					<td><?php echo $this_player->current_total[0]["BLK"]; ?></td>
					<td><?php echo $this_player->current_total[0]["TOV"]; ?></td>
					<td><?php echo $this_player->current_total[0]["PF"]; ?></td>
					<td></td>
					<td><?php echo $this_player->current_total[0]["PTS"]; ?></td>
<?php
		} else {
?>
					<td colspan="21"></td>
<?php
		}
?>
					<?php if ( $current_player[0]->current_year[0]["year"] > 2000 ) { ?><td><?php if ( $this_player->current_year[0]["doneposition"] === "1" ) { echo "<strong>1</strong>"; } ?></td><?php } ?>
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

	for ( $i = 0; $i < $player_count; $i++ ) {

		$this_player = $current_player[$i];
		$partial = ( $this_player->current_year[0]["partial"] == 1 ) ? "partial" : "";

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[0]["season"] ) ) { echo $this_player->current_year[0]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[0]["year"]; ?>&amp;league=<?php echo $league["league"]; ?>" title="<?php echo $this_player->current_year[0]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[0]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $league["league"]; ?>" title="<?php echo $league["league_name"]; ?>"><?php echo $this_player->current_year[0]["league"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["teamID"]; ?></td>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[0]["last_name"], ",", $this_player->current_year[0]["first_name"]; ?></span><a href="player.php?id=<?php echo $this_player->current_year[0]["playerID"]; ?>" title="<?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?>"><?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[0]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[0]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[0]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[0]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[0]["GP"]; ?></td>
					<td><?php echo $this_player->per_game[0]["MIN"]; ?></td>
					<td><?php echo $this_player->per_game[0]["FG"]; ?></td>
					<td><?php echo $this_player->per_game[0]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_game[0]["FG"], $this_player->per_game[0]["FGA"] ); ?></td>
					<td><?php echo $this_player->per_game[0]["TP"]; ?></td>
					<td><?php echo $this_player->per_game[0]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_game[0]["TP"], $this_player->per_game[0]["TPA"] ); ?></td>
					<td><?php echo $this_player->per_game[0]["FT"]; ?></td>
					<td><?php echo $this_player->per_game[0]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_game[0]["FT"], $this_player->per_game[0]["FTA"] ); ?></td>
					<td><?php echo $this_player->per_game[0]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[0]["ORB"] !== NULL ) { echo number_format( ( $this_player->per_game[0]["TRB"] - $this_player->per_game[0]["ORB"] ), 1 ); } ?></td>
					<td><?php echo $this_player->per_game[0]["TRB"]; ?></td>
					<td><?php echo $this_player->per_game[0]["AST"]; ?></td>
					<td><?php echo $this_player->per_game[0]["STL"]; ?></td>
					<td><?php echo $this_player->per_game[0]["BLK"]; ?></td>
					<td><?php echo $this_player->per_game[0]["TOV"]; ?></td>
					<td><?php echo $this_player->per_game[0]["PF"]; ?></td>
					<td></td>
					<td><?php echo $this_player->per_game[0]["PTS"]; ?></td>
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

	for ( $i = 0; $i < $player_count; $i++ ) {

		$this_player = $current_player[$i];
		$partial = ( $this_player->current_year[0]["partial"] == 1 ) ? "partial" : "";

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[0]["season"] ) ) { echo $this_player->current_year[0]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[0]["year"]; ?>&amp;league=<?php echo $league["league"]; ?>" title="<?php echo $this_player->current_year[0]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[0]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $league["league"]; ?>" title="<?php echo $league["league_name"]; ?>"><?php echo $this_player->current_year[0]["league"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["teamID"]; ?></td>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[0]["last_name"], ",", $this_player->current_year[0]["first_name"]; ?></span><a href="player.php?id=<?php echo $this_player->current_year[0]["playerID"]; ?>" title="<?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?>"><?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[0]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[0]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[0]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[0]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[0]["GP"]; ?></td>
					<td><?php echo $this_player->current_total[0]["MIN"]; ?></td>
					<td><?php echo $this_player->per_36_min[0]["FG"]; ?></td>
					<td><?php echo $this_player->per_36_min[0]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_36_min[0]["FG"], $this_player->per_36_min[0]["FGA"] ); ?></td>
					<td><?php echo $this_player->per_36_min[0]["TP"]; ?></td>
					<td><?php echo $this_player->per_36_min[0]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_36_min[0]["TP"], $this_player->per_36_min[0]["TPA"] ); ?></td>
					<td><?php echo $this_player->per_36_min[0]["FT"]; ?></td>
					<td><?php echo $this_player->per_36_min[0]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_36_min[0]["FT"], $this_player->per_36_min[0]["FTA"] ); ?></td>
					<td><?php echo $this_player->per_36_min[0]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[0]["ORB"] !== NULL ) { echo number_format( ( $this_player->per_36_min[0]["TRB"] - $this_player->per_36_min[0]["ORB"] ), 1 ); } ?></td>
					<td><?php echo $this_player->per_36_min[0]["TRB"]; ?></td>
					<td><?php echo $this_player->per_36_min[0]["AST"]; ?></td>
					<td><?php echo $this_player->per_36_min[0]["STL"]; ?></td>
					<td><?php echo $this_player->per_36_min[0]["BLK"]; ?></td>
					<td><?php echo $this_player->per_36_min[0]["TOV"]; ?></td>
					<td><?php echo $this_player->per_36_min[0]["PF"]; ?></td>
					<td></td>
					<td><?php echo $this_player->per_36_min[0]["PTS"]; ?></td>
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
					<th title="Points Per 100 Possessions">PTS</th>
				</tr>
				</thead>
				<tbody>
<?php

	for ( $i = 0; $i < $player_count; $i++ ) {

		$this_player = $current_player[$i];
		$partial = ( $this_player->current_year[0]["partial"] == 1 ) ? "partial" : "";

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[0]["season"] ) ) { echo $this_player->current_year[0]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[0]["year"]; ?>&amp;league=<?php echo $league["league"]; ?>" title="<?php echo $this_player->current_year[0]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[0]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $league["league"]; ?>" title="<?php echo $league["league_name"]; ?>"><?php echo $this_player->current_year[0]["league"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["teamID"]; ?></td>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[0]["last_name"], ",", $this_player->current_year[0]["first_name"]; ?></span><a href="player.php?id=<?php echo $this_player->current_year[0]["playerID"]; ?>" title="<?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?>"><?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[0]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[0]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[0]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[0]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[0]["GP"]; ?></td>
					<td><?php echo $this_player->current_total[0]["MIN"]; ?></td>
					<td><?php echo $this_player->per_100_poss[0]["FG"]; ?></td>
					<td><?php echo $this_player->per_100_poss[0]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_100_poss[0]["FG"], $this_player->per_100_poss[0]["FGA"] ); ?></td>
					<td><?php echo $this_player->per_100_poss[0]["TP"]; ?></td>
					<td><?php echo $this_player->per_100_poss[0]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_100_poss[0]["TP"], $this_player->per_100_poss[0]["TPA"] ); ?></td>
					<td><?php echo $this_player->per_100_poss[0]["FT"]; ?></td>
					<td><?php echo $this_player->per_100_poss[0]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_100_poss[0]["FT"], $this_player->per_100_poss[0]["FTA"] ); ?></td>
					<td><?php echo $this_player->per_100_poss[0]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[0]["ORB"] !== NULL ) { echo number_format( ( $this_player->per_100_poss[0]["TRB"] - $this_player->per_100_poss[0]["ORB"] ), 1 ); } ?></td>
					<td><?php echo $this_player->per_100_poss[0]["TRB"]; ?></td>
					<td><?php echo $this_player->per_100_poss[0]["AST"]; ?></td>
					<td><?php echo $this_player->per_100_poss[0]["STL"]; ?></td>
					<td><?php echo $this_player->per_100_poss[0]["BLK"]; ?></td>
					<td><?php echo $this_player->per_100_poss[0]["TOV"]; ?></td>
					<td><?php echo $this_player->per_100_poss[0]["PF"]; ?></td>
					<td></td>
					<td><?php echo $this_player->per_100_poss[0]["PTS"]; ?></td>
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
			</div> <!-- #tab-per-100-poss -->


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
					<th title="Points Per 75 Possessions">PTS</th>
				</tr>
				</thead>
				<tbody>
<?php

	for ( $i = 0; $i < $player_count; $i++ ) {

		$this_player = $current_player[$i];
		$partial = ( $this_player->current_year[0]["partial"] == 1 ) ? "partial" : "";

?>
				<tr class="text_right <?php echo $partial; ?>">
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[0]["season"] ) ) { echo $this_player->current_year[0]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[0]["year"]; ?>&amp;league=<?php echo $league["league"]; ?>" title="<?php echo $this_player->current_year[0]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[0]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $league["league"]; ?>" title="<?php echo $league["league_name"]; ?>"><?php echo $this_player->current_year[0]["league"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["teamID"]; ?></td>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[0]["last_name"], ",", $this_player->current_year[0]["first_name"]; ?></span><a href="player.php?id=<?php echo $this_player->current_year[0]["playerID"]; ?>" title="<?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?>"><?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[0]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[0]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[0]["position_real"]; ?></td>
<?php
		// show stats if GP > 0
		if ( $this_player->current_total[0]["GP"] > 0 ) {
?>
					<td><?php echo $this_player->current_total[0]["GP"]; ?></td>
					<td><?php echo $this_player->current_total[0]["MIN"]; ?></td>
					<td><?php echo $this_player->per_75_poss[0]["FG"]; ?></td>
					<td><?php echo $this_player->per_75_poss[0]["FGA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_75_poss[0]["FG"], $this_player->per_75_poss[0]["FGA"] ); ?></td>
					<td><?php echo $this_player->per_75_poss[0]["TP"]; ?></td>
					<td><?php echo $this_player->per_75_poss[0]["TPA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_75_poss[0]["TP"], $this_player->per_75_poss[0]["TPA"] ); ?></td>
					<td><?php echo $this_player->per_75_poss[0]["FT"]; ?></td>
					<td><?php echo $this_player->per_75_poss[0]["FTA"]; ?></td>
					<td><?php echo $this_player->findPct( $this_player->per_75_poss[0]["FT"], $this_player->per_75_poss[0]["FTA"] ); ?></td>
					<td><?php echo $this_player->per_75_poss[0]["ORB"]; ?></td>
					<td><?php if ( $this_player->current_total[0]["ORB"] !== NULL ) { echo number_format( ( $this_player->per_75_poss[0]["TRB"] - $this_player->per_75_poss[0]["ORB"] ), 1 ); } ?></td>
					<td><?php echo $this_player->per_75_poss[0]["TRB"]; ?></td>
					<td><?php echo $this_player->per_75_poss[0]["AST"]; ?></td>
					<td><?php echo $this_player->per_75_poss[0]["STL"]; ?></td>
					<td><?php echo $this_player->per_75_poss[0]["BLK"]; ?></td>
					<td><?php echo $this_player->per_75_poss[0]["TOV"]; ?></td>
					<td><?php echo $this_player->per_75_poss[0]["PF"]; ?></td>
					<td></td>
					<td><?php echo $this_player->per_75_poss[0]["PTS"]; ?></td>
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
					<th class="text_left" title="Notes">Notes</th>
					<th class="text_center" title="References">Ref</th>
				</tr>
				</thead>
				<tbody>
<?php

	for ( $i = 0; $i < $player_count; $i++ ) {

		$this_player = $current_player[$i];
		$partial = ( $this_player->current_year[0]["partial"] == 1 ) ? "partial" : "";

		// convert reference IDs to local ref (ex: 1,17 = 1,4 if 17 would be the 4th highest reference on the page)
		$these_refs_old = explode( ",", $this_player->current_year[0]["reference"] );
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
					<td class="text_center"><?php if ( ! empty ( $this_player->current_year[0]["season"] ) ) { echo $this_player->current_year[0]["season"]; } ?></td>
					<td><a href="year.php?id=<?php echo $this_player->current_year[0]["year"]; ?>&amp;league=<?php echo $league["league"]; ?>" title="<?php echo $this_player->current_year[0]["year"]; ?> Professional Basketball Leagues"><?php echo $this_player->current_year[0]["year"]; ?></td>
					<td class="text_center"><a href="league.php?id=<?php echo $this_player->current_year[0]["league"]; ?>" title="<?php echo $league["league_name"]; ?>"><?php echo $this_player->current_year[0]["league"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["teamID"]; ?></td>
					<td class="text_left"><span class="hidden"><?php echo $this_player->current_year[0]["last_name"], ",", $this_player->current_year[0]["first_name"]; ?></span><a href="player.php?id=<?php echo $this_player->current_year[0]["playerID"]; ?>" title="<?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?>"><?php echo $this_player->current_year[0]["first_name"], " ", $this_player->current_year[0]["last_name"]; ?></a></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["age"]; ?></td>
					<td class="text_center"><?php echo $this_player->formatHeight( $this_player->current_year[0]["height"] ); ?></td>
					<td><?php echo $this_player->current_year[0]["weight"]; ?></td>
					<td class="text_center"><?php echo $this_player->current_year[0]["position"]; ?></td>
					<td class="border_right text_center"><?php echo $this_player->current_year[0]["position_real"]; ?></td>
					<td class="text_left"><?php echo $this_player->current_year[0]["notes"]; ?></td>
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

<?php
	// show previous / next year links (if they exist)
	echo $prev_next_links;
?>

	</div> <!-- #content -->

<?php include "includes/scripts.php"; ?>

</body>
</html>
