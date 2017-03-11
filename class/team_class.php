<?php

/**
   * Teams
   * 
   * 
   */
class Team {

	// properties: TEAM_bio
	public $uniqueID, $year, $league, $teamID, $parentID, $name, $location, $franchiseID, $conference, $division, $partial, $GP, $win, $loss, $tie, $forfeit_win, $forfeit_loss, $pace, $arena, $notes, $editNotes, $orderID, $childOrderID;

	public function __construct() {

	}


	/**
	* Create a percentage from a nominator and denominator
	* 
	* @param int	nominator	nominator of percentage
	* @param int	denominator	denominator of percentage
	* @return dec	resultPct	percentage to return
	*/
	public function findPct( $nominator, $denominator ) {

		if ( empty ( $denominator ) || $denominator === 0 ) {
			return;
		}

		$resultPct = number_format( ( $nominator / $denominator ), 3 );
		return $resultPct;

	}
}
?>
