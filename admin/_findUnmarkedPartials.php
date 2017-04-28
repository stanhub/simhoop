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

<title>SimHoop DB: Find Unmarked Partials</title>
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

	// get passed league
	$current_league = "NBA";
	if ( ( isset( $_GET[ "league" ] ) ) && ( !empty( $_GET[ "league" ] ) ) ) {
		$current_league = $_GET[ "league" ];
	}

	// turn on big selects sql
	$query = $db->prepare( "SET SQL_BIG_SELECTS=1" );
	$query->execute();

	// get all players who have a TOT for this year-league
	$query = $db->prepare( "SELECT a.* FROM PLAYER_year a WHERE a.year = :current_year1 AND a.league = :current_league1 AND a.teamID != 'TOT'
		AND a.playerID IN (
			SELECT b.playerID FROM PLAYER_year b WHERE b.year = :current_year2 AND b.league = :current_league2 AND b.teamID = 'TOT' )
		ORDER BY a.playerID" );
	$query->bindValue( ":current_year1", $current_year, PDO::PARAM_INT );
	$query->bindValue( ":current_league1", $current_league, PDO::PARAM_STR );
	$query->bindValue( ":current_year2", $current_year, PDO::PARAM_INT );
	$query->bindValue( ":current_league2", $current_league, PDO::PARAM_STR );
	$query->setFetchMode( PDO::FETCH_ASSOC );
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

	// go through each player and update their position_real
	$i = $x = 0;
	$bad_playerIDs = $prev_player = "";
	foreach( $current_player as $player ) {
		$i++;
		$is_bad = false;
		if ( $player[ "playerID" ] != $prev_player ) {
			if ( $player[ "partial" ] < 1 || empty( $player[ "partial" ] ) || $player[ "orderID" ] < 0 || empty( $player[ "orderID" ] ) ) {
				$bad_playerIDs .= $player[ "playerID" ] . "<br />";
				$prev_player = $player[ "playerID" ];
				$x++;
			}
		}
	}
?>

<?php
	echo $current_year, " ", $current_league;
?>
<br />
<?php echo $i; ?> Checked (<?php echo $x; ?> <?php if ( $x === 1 ) { echo "was"; } else { echo "were"; } ?> bad)!<br /><br />
<?php echo $bad_playerIDs; ?>
</div>

</body>
</html>