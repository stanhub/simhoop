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

	// grab franchiseID from URL
	if ( ( isset( $_GET[ "id" ] ) ) && ( !empty( $_GET[ "id" ] ) ) ) {
		$franchiseID = $_GET[ "id" ];
	}

	// get teams in this franchise in order
	$sql = "SELECT t.* FROM TEAM_bio t WHERE t.franchiseID = :franchiseID AND t." . $preview_mode . " ORDER BY t.year";
	$query = $db->prepare( $sql );
	$query->bindValue( ':franchiseID', $franchiseID, PDO::PARAM_INT );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_team = $query->fetchAll();

	// if no rows found, pull Los Angeles Lakers
	if ( $query->rowCount() === 0 ) {

		$preview_mode = "live < 10";

		// get Lakers franchiseID
		$query = $db->prepare( "SELECT t.franchiseID FROM TEAM_bio t WHERE t.teamID = 'LAL' LIMIT 0,1" );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$current_franchiseID = $query->fetch();
		$franchiseID = intval( $current_franchiseID["franchiseID"] );

		$query = $db->prepare( "SELECT t.* FROM TEAM_bio t WHERE t.franchiseID = :franchiseID AND live = 1 ORDER BY t.year" );
		$query->bindValue( ':franchiseID', $franchiseID, PDO::PARAM_INT );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$current_team = $query->fetchAll();

	}

	// get franchise sums
	$sql = "SELECT SUM( t.win ) as WINi, SUM( t.loss ) as LOSSi, sum( t.playoffs ) as PLAYOFFSi, sum( t.finals ) as FINALSi, sum( t.championship ) as CHAMPSi FROM TEAM_bio t WHERE t.franchiseID = :franchiseID AND t." . $preview_mode;
	$query = $db->prepare( $sql );
	$query->bindValue( ':franchiseID', $franchiseID, PDO::PARAM_INT );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_sum = $query->fetch();

	$team_count = count( $current_team );
	$final_team = $team_count - 1;
	$most_recent_name = $current_team[$final_team]["team_name"];
?>

<title>SimHoop: <?php echo $most_recent_name; ?> Franchise</title>
</head>

<body id="franchise">

	<div id="content">

		<div class="info">

			<h1><?php echo $most_recent_name; ?> Franchise</h1>

<?php
		// most recent location
		echo "<p>", $current_team[$final_team]["location"], " <span class='help_icon' title='Most recent location of franchise'>?</span></p>";

		// years active and number of seasons
		echo "<p>", $current_team[0]["year"], " to ", $current_team[$final_team]["year"], " (", $team_count, " seasons)</p>";

		// how many leagues franchise was in
		$unique_league = "";
		$league_list = array();
		for ( $i = 0; $i < $team_count; $i++ ) {

			if ( $current_team[$i]["league"] != $unique_league ) {
				array_push( $league_list, $current_team[$i]["league"] );
			}

		}
		$unique_league_list = array_unique( $league_list );
		$league_list = implode( ", ", $unique_league_list );
		echo "<p>Leagues: (", count( $unique_league_list ), ") ", $league_list, "</p>";

		// how many team names
		$unique_name = "";
		$name_list = array();
		for ( $i = 0; $i < $team_count; $i++ ) {

			if ( $current_team[$i]["team_name"] != $unique_name ) {
				array_push( $name_list, $current_team[$i]["team_name"] );
			}

		}
		$unique_name_list = array_unique( $name_list );
		$name_list = implode( ", ", $unique_name_list );
		echo "<p>Team Names: (", count( $unique_name_list ), ") ", $name_list, "</p>";

		// win-loss record
		echo "<p>Record: ", $current_sum["WINi"], "-", $current_sum["LOSSi"], " (", number_format( ( $current_sum["WINi"] / ( $current_sum["WINi"] + $current_sum["LOSSi"] ) ), 3 ), ")</p>";

		// playoff appearances
		echo "<p>Playoff Appearances: ", $current_sum["PLAYOFFSi"], "</p>";

		// finals appearances
		echo "<p>Finals Appearances: ", $current_sum["FINALSi"], "</p>";

		// championships
		echo "<p>Championships: ", $current_sum["CHAMPSi"], "</p>";
?>

		</div> <!-- .info -->

		<table class="tablesorter">
		<thead>
		<tr class="text_right">
			<th class="text_center" title="Year">Year</th>
			<th class="text_center" title="League">Lg</th>
			<th class="text_left" title="Team Name">Team</th>
			<th class="text_left" title="Location">Location</th>
			<th class="text_left" title="Conference">Conference</th>
			<th class="border_right text_left" title="Division">Division</th>
			<th title="Games Played">GP</th>
			<th title="Wins">W</th>
			<th title="Losses">L</th>
			<th title="Win Percentage">Pct</th>
			<th class="border_right text_left" title="Playoff Results">Playoffs</th>
			<th title="Pace">Pace</th>
		</tr>
		</thead>
		<tbody>
<?php
	// set league variables
	$prev_league = "";
	$league_name = "";

	for ( $i = 0; $i < $team_count; $i++ ) {

		// if this is a new league, get the league's full name
		if ( $current_team[$i]["league"] !== $prev_league ) {

			$query = $db->prepare( "SELECT l.* FROM LEAGUE l WHERE l.league = :league" );
			$query->bindValue( ':league', $current_team[$i]["league"], PDO::PARAM_STR );
			$query->setFetchMode( PDO::FETCH_ASSOC );
			$query->execute();
			$current_league = $query->fetch();

			// update league variables
			$prev_league = $current_team[$i]["league"];
			$league_name = $current_league["league_name"];

		}

		$playoffs = "";
		if ( $current_team[$i]["playoffs"] === "1" ) {
			$playoffs = "Made Playoffs";
		}
		if ( $current_team[$i]["finals"] === "1" ) {
			$playoffs = "Lost Finals";
		}
		if ( $current_team[$i]["championship"] === "1" ) {
			$playoffs = "<b>Won Championship</b>";
		}

?>
		<tr class="text_right">
			<td class="text_center"><a href="year.php?id=<?php echo $current_team[$i]["year"]; ?>&amp;league=<?php echo $current_team[$i]["league"]; ?>" title="<?php echo $current_team[$i]["year"]; ?> Professional Basketball Leagues"><?php echo $current_team[$i]["year"]; ?></a></td>
			<td class="text_center"><a href="league.php?id=<?php echo $current_team[$i]["league"]; ?>" title="<?php echo $league_name; ?>"><?php echo $current_team[$i]["league"]; ?></a></td>
			<td class="text_left"><a href="team.php?id=<?php echo $current_team[$i]["teamID"]; ?>&amp;year=<?php echo $current_team[$i]["year"]; ?>" title="<?php echo $current_team[$i]["team_name"]; ?> (<?php echo $current_team[$i]["year"]; ?>)"><?php echo $current_team[$i]["team_name"]; ?></a></td>
			<td class="text_left"><?php echo $current_team[$i]["location"]; ?></td>
			<td class="text_left"><?php echo $current_team[$i]["conference"]; ?></td>
			<td class="border_right text_left"><?php echo $current_team[$i]["division"]; ?></td>
			<td><?php echo $current_team[$i]["GP"]; ?></td>
			<td><?php echo $current_team[$i]["win"]; ?></td>
			<td><?php echo $current_team[$i]["loss"]; ?></td>
			<td><?php echo number_format( ( $current_team[$i]["win"] / $current_team[$i]["GP"] ), 3 ); ?></td>
			<td class="border_right text_left"><?php echo $playoffs; ?></td>
			<td><?php echo $current_team[$i]["pace"]; ?></td>
		</tr>
<?php
	}
?>
		</tbody>
		</table>

	</div> <!-- #content -->

<?php include "includes/scripts.php"; ?>

</body>
</html>
