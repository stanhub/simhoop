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

	// grab league from URL
	if ( ( isset( $_GET[ "id" ] ) ) && ( !empty( $_GET[ "id" ] ) ) ) {
		$league = $_GET[ "id" ];
	}

	// get league data
	$sql = "SELECT l.* FROM LEAGUE l WHERE l.league = :league AND " . $preview_mode;
	$query = $db->prepare( $sql );
	$query->bindValue( ':league', $league, PDO::PARAM_STR );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_league = $query->fetch();

	// if no rows found, pull NBA
	if ( $query->rowCount() === 0 ) {

		$league = "NBA";
		$preview_mode = "live < 10";

		$query = $db->prepare( 'SELECT l.* FROM LEAGUE l WHERE l.league = :league' );
		$query->bindValue( ':league', $league, PDO::PARAM_STR );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$current_league = $query->fetch();

	}

	// get all franchises in this league
	$sql = "SELECT DISTINCT t.franchiseID FROM TEAM_bio t WHERE t.league = :league AND t." . $preview_mode . " ORDER BY t.franchiseID";
	$query = $db->prepare( $sql );
	$query->bindValue( ':league', $league, PDO::PARAM_STR );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_franchise = $query->fetchAll();

	$franchise_count = count( $current_franchise );

	// get all teams in this league
	$sql = "SELECT DISTINCT t.teamID, franchiseID FROM TEAM_bio t WHERE t.league = :league AND t." . $preview_mode. " ORDER BY t.franchiseID, t.teamID";
	$query = $db->prepare( $sql );
	$query->bindValue( ':league', $league, PDO::PARAM_STR );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_team_franchise = $query->fetchAll();
	$teams_in_league = $query->rowCount();

	// find if a partial exists
	$this_franchise = $current_team_franchise[0]["franchiseID"];
	$any_partials = 0;
	for ( $i = 1; $i < $teams_in_league; $i++ ) {

		// this team has the same franchiseID, we have a partial
		if ( $current_team_franchise[$i]["franchiseID"] === $this_franchise ) {

			$any_partials = 1;
			break;

		}

		$this_franchise = $current_team_franchise[$i]["franchiseID"];

	}
?>

<title>SimHoop: <?php echo $current_league["league_name"]; ?></title>
</head>

<body id="league">

	<div id="content">

		<div class="info">

			<h1><?php echo $current_league["league_name"]; ?></h1>

<?php
	// years of existence and length of existence
	$league_end = ( empty( $current_league["end"] ) ) ? "Present" : $current_league["end"];
	echo "<p>", $current_league["start"], " to ", $league_end;

	// length of existence
	$league_length = date("Y") - $current_league["start"];
	if ( $current_league["end"] ) {
		$league_length = $current_league["end"] - $current_league["start"] + 1;
	}
	echo " (", $league_length, " seasons)</p>";

	// number of franchises
	echo "<p>", $franchise_count, " Franchises</p>";
	
	// number of team names
	echo "<p>", $teams_in_league, " Team Names <span class='help_icon' title='Teams with unique names'>?</span></p>";
?>

		</div> <!-- .info -->

		<div class="tabs">
<?php
	if ( $any_partials === 1 ) {
?>
		<a href="#" class="hide_partials" title="Hide Partial Seasons">Hide Partials</a>
<?php
	}
?>
		</div>

		<table class="tablesorter">
		<thead>
		<tr class="text_right">
			<th class="hidden" title="Franchise Started Order">a</th>
			<th class="text_left" title="Team Name">Team</th>
			<th class="text_center" title="Number of Seasons in the <?php echo $current_league["league_name"]; ?>">Seasons</th>
			<th title="First Season in the <?php echo $current_league["league_name"]; ?>">First Season</th>
			<th class="border_right" title="Last Season in the <?php echo $current_league["league_name"]; ?>">Last Season</th>
			<th title="Games Played">GP</th>
			<th title="Wins">W</th>
			<th title="Losses">L</th>
			<th title="Win Percentage">Pct</th>
			<th title="Playoff Appearances">Playoffs</th>
			<th title="Finals Appearances">Finals</th>
			<th title="Championships Won">Champ</th>
		</tr>
		</thead>
		<tbody>
<?php
	for ( $i = 0; $i < $franchise_count; $i++ ) {

		// get franchise sums
		$sql = "SELECT SUM(t.GP) as GPi, SUM(t.win) as WINi, SUM(t.loss) as LOSSi, SUM(t.playoffs) as PLAYOFFSi, SUM(t.finals) as FINALSi, SUM(t.championship) as CHAMPi FROM TEAM_bio t WHERE t.franchiseID = :franchiseID AND t.league = :league AND t." . $preview_mode;
		$query = $db->prepare( $sql );
		$query->bindValue( ':franchiseID', $current_franchise[$i]["franchiseID"], PDO::PARAM_STR );
		$query->bindValue( ':league', $league, PDO::PARAM_STR );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$franchise_sum = $query->fetch();

		// get first team in this franchise
		$sql = "SELECT t.* FROM TEAM_bio t WHERE t.franchiseID = :franchiseID AND t.league = :league AND t." . $preview_mode . " ORDER BY t.year";
		$query = $db->prepare( $sql );
		$query->bindValue( ':franchiseID', $current_franchise[$i]["franchiseID"], PDO::PARAM_STR );
		$query->bindValue( ':league', $league, PDO::PARAM_STR );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$current_team = $query->fetchAll();

		// initialize franchise variables
		$team_count = count( $current_team );
		$first_team = 0;
		$last_team = $team_count - 1;
		$prev_team_name = $current_team[0]["team_name"];
		$initial_year = $current_team[0]["year"];
		$season_count = 0;
		$number_of_team_names = 1;
		$sum_win = 0;
		$sum_loss = 0;
		$team_name_number = 1; // which team name is this (ex: 2)
		$sum_playoffs = 0;
		$sum_finals = 0;
		$sum_champs = 0;
?>
		<tr class="text_right"> <!-- franchise row -->
			<td class="hidden"><?php echo $i; ?>.0</td>
			<td class="text_left"><span class="hidden"><?php echo $current_team[$last_team]["team_name"]; ?>-0</span><a href="franchise.php?id=<?php echo $current_franchise[$i]["franchiseID"]; ?>" title="<?php echo $current_team[$last_team]["team_name"]; ?> (Franchise)"><?php echo $current_team[$last_team]["team_name"]; ?></a></td>
			<td class="text_center"><?php echo $team_count; ?></td>
			<td><a href="year.php?id=<?php echo $current_team[$first_team]["year"]; ?>&amp;league=<?php echo $league; ?>" title="<?php echo $current_team[$first_team]["year"]; ?> Professional Basketball Leagues"><?php echo $current_team[$first_team]["year"]; ?></a></td>
			<td class="border_right"><a href="year.php?id=<?php echo $current_team[$last_team]["year"]; ?>&amp;league=<?php echo $league; ?>" title="<?php echo $current_team[$last_team]["year"]; ?> Professional Basketball Leagues"><?php echo $current_team[$last_team]["year"]; ?></td>
			<td><?php echo $franchise_sum["GPi"]; ?></td>
			<td><?php echo $franchise_sum["WINi"]; ?></td>
			<td><?php echo $franchise_sum["LOSSi"]; ?></td>
			<td class="border_right"><?php echo number_format( ( $franchise_sum["WINi"] / $franchise_sum["GPi"] ), 3 ); ?></td>
			<td><?php echo $franchise_sum["PLAYOFFSi"]; ?></td>
			<td><?php echo $franchise_sum["FINALSi"]; ?></td>
			<td><?php echo $franchise_sum["CHAMPi"]; ?></td>
		</tr>
<?php

		// go through each team year
		for ( $j = 0; $j < $team_count; $j++ ) {

			// team name is the same as previous
			if ( $current_team[$j]["team_name"] === $prev_team_name ) {

				$sum_win = $sum_win + $current_team[$j]["win"];
				$sum_loss = $sum_loss + $current_team[$j]["loss"];
				$sum_playoffs = $sum_playoffs + $current_team[$j]["playoffs"];
				$sum_finals = $sum_finals + $current_team[$j]["finals"];
				$sum_champs = $sum_champs + $current_team[$j]["championship"];

			}

			// team name isn't the same as previous
			if ( $current_team[$j]["team_name"] !== $prev_team_name ) {

				$games_played = $sum_win + $sum_loss;
?>
		<tr class="partial text_right"> <!-- legacy team name row -->
			<td class="hidden"><?php echo $i; ?>.<?php echo $team_name_number; ?></td>
			<td class="text_left"><span class="hidden"><?php echo $current_team[$last_team]["team_name"]; ?>-<?php echo $team_name_number; ?></span><?php echo $prev_team_name; ?></td>
			<td class="text_center"><?php echo $season_count; ?></td>
			<td><a href="year.php?id=<?php echo $initial_year; ?>&amp;league=<?php echo $league; ?>" title="<?php echo $initial_year; ?> Professional Basketball Leagues"><?php echo $initial_year; ?></a></td>
			<td class="border_right"><a href="year.php?id=<?php echo ( $current_team[$j]["year"] - 1 ); ?>&amp;league=<?php echo $league; ?>" title="<?php echo ( $current_team[$j]["year"] - 1 ); ?> Professional Basketball Leagues"><?php echo ( $current_team[$j]["year"] - 1 ); ?></a></td>
			<td><?php echo $games_played; ?></td>
			<td><?php echo $sum_win; ?></td>
			<td><?php echo $sum_loss; ?></td>
			<td class="border_right"><?php echo number_format( ( $sum_win / $games_played ), 3 ); ?></td>
			<td><?php echo $sum_playoffs; ?></td>
			<td><?php echo $sum_finals; ?></td>
			<td><?php echo $sum_champs; ?></td>
		</tr>
<?php

				// update variables now that it's a new team name
				$prev_team_name = $current_team[$j]["team_name"];
				$initial_year = $current_team[$j]["year"];
				$season_count = 0;
				$number_of_team_names = $number_of_team_names + 1;
				$team_name_number = $team_name_number + 1;

				// reset wins, losses, playoffs, finals and championships
				$sum_win = $current_team[$j]["win"];
				$sum_loss = $current_team[$j]["loss"];
				$sum_playoffs = $current_team[$j]["playoffs"];
				$sum_finals = $current_team[$j]["finals"];
				$sum_champs = $current_team[$j]["championship"];

			}

			// update count
			$season_count = $season_count + 1;

		}

		// create a final row for the most recent team name change (if there are more than 1 team names)
		if ( $number_of_team_names > 1 ) {

			$games_played = $sum_win + $sum_loss;
?>
		<tr class="partial text_right"> <!-- current team name row -->
			<td class="hidden"><?php echo $i; ?>.<?php echo $team_name_number; ?></td>
			<td class="text_left"><span class="hidden"><?php echo $current_team[$last_team]["team_name"]; ?>-<?php echo $team_name_number; ?></span><?php echo $prev_team_name; ?></td>
			<td class="text_center"><?php echo $season_count; ?></td>
			<td><a href="year.php?id=<?php echo $initial_year; ?>&amp;league=<?php echo $league; ?>" title="<?php echo $initial_year; ?> Professional Basketball Leagues"><?php echo $initial_year; ?></a></td>
			<td class="border_right"><a href="year.php?id=<?php echo $current_team[( $team_count - 1 )]["year"]; ?>&amp;league=<?php echo $league; ?>" title="<?php echo $current_team[( $team_count - 1 )]["year"]; ?> Professional Basketball Leagues"><?php echo $current_team[( $team_count - 1 )]["year"]; ?></td>
			<td><?php echo $games_played; ?></td>
			<td><?php echo $sum_win; ?></td>
			<td><?php echo $sum_loss; ?></td>
			<td class="border_right"><?php echo number_format( ( $sum_win / $games_played ), 3 ); ?></td>
			<td><?php echo $sum_playoffs; ?></td>
			<td><?php echo $sum_finals; ?></td>
			<td><?php echo $sum_champs; ?></td>
		</tr>
<?php
		}
	}
?>
		</tbody>
		</table>

	</div> <!-- #content -->

<?php include "includes/scripts.php"; ?>

</body>
</html>
