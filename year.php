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

	// grab year from URL
	if ( ( isset( $_GET[ "id" ] ) ) && ( !empty( $_GET[ "id" ] ) ) ) {
		$year = $_GET[ "id" ];
	}

	// grab league from URL
	$passed_league = "";
	if ( ( isset( $_GET[ "league" ] ) ) && ( !empty( $_GET[ "league" ] ) ) ) {
		$passed_league = $_GET[ "league" ];
	}

	// get teams in this year
	$sql = "SELECT t.* FROM TEAM_bio t WHERE t.year = :year AND t." . $preview_mode . " ORDER BY t.league, t.team_name";
	$query = $db->prepare( $sql );
	$query->bindValue( ':year', $year, PDO::PARAM_INT );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$current_team = $query->fetchAll();

	// if no rows found, pull most recent year
	if ( $query->rowCount() === 0 ) {

		$preview_mode = "live < 10";

		// get most recent year in database
		$query = $db->prepare( "SELECT t.year FROM TEAM_bio t ORDER BY t.year DESC LIMIT 0,1" );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$current_year = $query->fetch();
		$year = intval( $current_year["year"] );

		$query = $db->prepare( "SELECT t.* FROM TEAM_bio t WHERE t.year = :year AND live = 1 ORDER BY t.league, t.team_name" );
		$query->bindValue( ':year', $year, PDO::PARAM_INT );
		$query->setFetchMode( PDO::FETCH_ASSOC );
		$query->execute();
		$current_team = $query->fetchAll();

	}

	$team_count = count( $current_team );

	// determine which league to reveal
	$league_to_show = "NBA";
	if ( $year < 1950 ) {
		$league_to_show = "BAA";
	}
	// if a valid league is passed, make it the league to show
	switch ( $passed_league ) {
		case "ABA":
		case "BAA":
		case "NBA":
			$league_to_show = $passed_league;
		default:
			break;
	}

	// is there a prev year?
	$prev_year = $year - 1;
	$sql = "SELECT b.* FROM TEAM_bio b WHERE b.year = :prev_year AND b." . $preview_mode;
	$query = $db->prepare( $sql );
	$query->bindValue( ':prev_year', $prev_year, PDO::PARAM_INT );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$prev_team = $query->fetch();

	// is there a next year?
	$next_year = $year + 1;
	$sql = "SELECT b.* FROM TEAM_bio b WHERE b.year = :next_year AND b." . $preview_mode;
	$query = $db->prepare( $sql );
	$query->bindValue( ':next_year', $next_year, PDO::PARAM_INT );
	$query->setFetchMode( PDO::FETCH_ASSOC );
	$query->execute();
	$next_team = $query->fetch();

	// create previous / next year links (if they exist)
	$prev_next_links = "";
	if ( $prev_team || $next_team ) { $prev_next_links = "<p>"; }
	if ( $prev_team ) {
		$prev_next_links .= "<a href='year.php?id=" . $prev_year . "' title='" . $prev_year . " Professional Basketball Leagues'>Previous Year</a>";
	}
	if ( $next_team ) {
		if ( $prev_team ) {
			$prev_next_links .= " &mdash; ";
		}
		$prev_next_links .= "<a href='year.php?id=" . $next_year . "' title='" . $next_year . " Professional Basketball Leagues'>Next Year</a>";
	}
	if ( $prev_team || $next_team ) { $prev_next_links .= "</p>"; }
?>

<title>SimHoop: <?php echo $year; ?> Professional Basketball Leagues</title>
</head>

<body id="year">

	<div id="content">

		<div class="info">

			<h1><?php echo $year; ?> <span class="small">Professional Basketball Leagues</span></h1>

		</div> <!-- .info -->

<?php
	// show previous / next year links (if they exist)
	echo $prev_next_links;

	// set initial vars
	$current_league_abbrev = "";

	for ( $i = 0; $i < $team_count; $i++ ) {

		// this is a new league
		if ( $current_team[$i]["league"] !== $current_league_abbrev ) {

			$current_league_abbrev = $current_team[$i]["league"];

			$query = $db->prepare( "SELECT l.* FROM LEAGUE l WHERE l.league = :league" );
			$query->bindValue( ':league', $current_league_abbrev, PDO::PARAM_STR );
			$query->setFetchMode( PDO::FETCH_ASSOC );
			$query->execute();
			$current_league = $query->fetch();

			// close previous table and section div if not first league
			if ( $i > 0 ) {
?>
			</tbody>
			</table>
		</div>

<?php
			}

			$league_end = ( ! empty ( $current_league["end"] ) ) ? $current_league["end"] : "Present";
			$league_display = "<b>" . $current_league["league_name"] . "</b> (" . $current_league["start"] . " to " . $league_end . ")<br />";
			$league_title_display = $current_league["league_name"] . " (" . $current_league["start"] . " to " . $league_end . ")";
?>
		<div class="section_title">
			<a href="#" data-section="<?php echo $current_league_abbrev; ?>" title="Show / Hide <?php echo $league_title_display; ?>"><span class="section_title_icon">[ - ]</span> <?php echo $league_display; ?></a>
		</div>

		<div class="section <?php if ( $current_league_abbrev !== $league_to_show ) { ?>collapsed<?php } ?>" id="<?php echo $current_league_abbrev; ?>">

			<table class="tablesorter">
			<thead>
			<tr class="text_right">
				<th class="text_center" title="Year">Year</th>
				<th class="text_center" title="League">Lg</th>
				<th class="text_left" title="Team Name">Team</th>
				<th class="text_left" title="Location">Location</th>
				<th class="text_left" title="Conference">Conference</th>
				<th class="text_left" title="Division">Division</th>
				<th title="Games Played">GP</th>
				<th title="Wins">W</th>
				<th title="Losses">L</th>
				<th title="Win Percentage">Pct</th>
				<th class="text_left" title="Playoff Results">Playoffs</th>
				<th title="Pace">Pace</th>
			</tr>
			</thead>
			<tbody>
<?php
		}
?>
			<tr class="text_right">
				<td class="text_center"><?php echo $current_team[$i]["year"]; ?></td>
				<td class="text_center"><a href="league.php?id=<?php echo $current_team[$i]["league"]; ?>" title="<?php echo $current_league["league_name"]; ?>"><?php echo $current_team[$i]["league"]; ?></a></td>
				<td class="text_left"><a href="team.php?id=<?php echo $current_team[$i]["teamID"]; ?>&amp;year=<?php echo $year; ?>" title="<?php echo $current_team[$i]["team_name"]; ?> (<?php echo $year; ?>)"><?php echo $current_team[$i]["team_name"]; ?></a></td>
				<td class="text_left"><?php echo $current_team[$i]["location"]; ?></td>
				<td class="text_left"><?php echo $current_team[$i]["conference"]; ?></td>
				<td class="text_left"><?php echo $current_team[$i]["division"]; ?></td>
				<td><?php echo $current_team[$i]["GP"]; ?></td>
				<td><?php echo $current_team[$i]["win"]; ?></td>
				<td><?php echo $current_team[$i]["loss"]; ?></td>
				<td><?php echo number_format( ( $current_team[$i]["win"] / $current_team[$i]["GP"] ), 3 ); ?></td>
				<td class="text_left"></td>
				<td><?php echo $current_team[$i]["pace"]; ?></td>
			</tr>
<?php
	}

	// close final table if there are leagues to show
	if ( $i > 0 ) {
?>
			</tbody>
			</table>
		</div>
<?php
	}

	// show previous / next year links (if they exist)
	echo $prev_next_links;
?>
	</div> <!-- #content -->

<?php include "includes/scripts.php"; ?>

</body>
</html>
