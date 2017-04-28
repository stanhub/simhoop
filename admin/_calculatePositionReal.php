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

<title>SimHoop DB: Calculate Position Real</title>
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

	// get passed playerID
	$current_playerID = "";
	if ( ( isset( $_GET[ "playerID" ] ) ) && ( !empty( $_GET[ "playerID" ] ) ) ) {
		$current_playerID = $_GET[ "playerID" ];
	}

	// turn on big selects sql
	$query = $db->prepare( "SET SQL_BIG_SELECTS=1" );
	$query->execute();

	// get all players from this year who played this year-league
	if ( !empty( $current_playerID ) ) {
		$query = $db->prepare( "SELECT y.* FROM PLAYER_year y WHERE y.playerID = :current_playerID" );
		$query->bindValue( ":current_playerID", $current_playerID, PDO::PARAM_INT );
	} else {
		$query = $db->prepare( "SELECT y.* FROM PLAYER_year y WHERE y.year = :current_year AND y.league = :current_league" );
		$query->bindValue( ":current_year", $current_year, PDO::PARAM_INT );
		$query->bindValue( ":current_league", $current_league, PDO::PARAM_STR );
	}
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
	foreach( $current_player as $player ) {
		// get total of all positions (because some don't add up to 100)
		$pos_total = $player[ "pos_PG" ] + $player[ "pos_SG" ] + $player[ "pos_SF" ] + $player[ "pos_PF" ] + $player[ "pos_C" ];
		$pos_value[ "PG" ] = ( empty( $player[ "pos_PG" ] ) ) ? 0 : ( $player[ "pos_PG" ] / $pos_total ) * 1;
		$pos_value[ "SG" ] = ( empty( $player[ "pos_SG" ] ) ) ? 0 : ( $player[ "pos_SG" ] / $pos_total ) * 2;
		$pos_value[ "SF" ] = ( empty( $player[ "pos_SF" ] ) ) ? 0 : ( $player[ "pos_SF" ] / $pos_total ) * 3;
		$pos_value[ "PF" ] = ( empty( $player[ "pos_PF" ] ) ) ? 0 : ( $player[ "pos_PF" ] / $pos_total ) * 4;
		$pos_value[ "C" ] = ( empty( $player[ "pos_C" ] ) ) ? 0 : ( $player[ "pos_C" ] / $pos_total ) * 5;
		$position_real = array_sum( $pos_value );
		// update db
		if ( $position_real > 0 ) {
			$sql = "UPDATE PLAYER_year y SET y.position_real = :position_real
				WHERE y.playerID = :playerID AND y.teamID = :teamID AND y.year = :current_year AND y.league = :current_league";
		} else {
			$sql = "UPDATE PLAYER_year y SET y.position_real = NULL
				WHERE y.playerID = :playerID AND y.teamID = :teamID AND y.year = :current_year AND y.league = :current_league";
			$x++; // update null amount
		}
		$query = $db->prepare( $sql );
		if ( $position_real > 0 ) {
			$query->bindValue( ":position_real", $position_real, PDO::PARAM_INT );
		}
		$query->bindValue( ":playerID", $player[ "playerID" ], PDO::PARAM_STR );
		$query->bindValue( ":teamID", $player[ "teamID" ], PDO::PARAM_STR );
		$query->bindValue( ":current_year", $player[ "year" ], PDO::PARAM_INT );
		$query->bindValue( ":current_league", $player[ "league" ], PDO::PARAM_STR );
		$query->execute();
		$i++; //update amount completed
	}
?>

<?php
	if ( !empty( $current_playerID ) ) {
		echo $playerID;
	} else {
		echo $current_year, " ", $current_league;
	}
?>
<br />
<?php echo $i; ?> Done (<?php echo $x; ?> null)!

</div>

</body>
</html>