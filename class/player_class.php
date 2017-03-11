<?php

/**
   * Players
   * 
   * 
   */
class Player {

	// properties: PLAYER_bio
	public $playerID, $first_name, $middle_name, $last_name, $suffix, $nick_name, $birth_name, $birth_date, $college1, $college2, $college3, $hand;

	// properties: PLAYER_year
	public $current_year, $current_year_count, $current_total, $current_total_count;

	// constructed properties: per game
	public $per_game; // array ( MIN, FG, FGA, TP, TPA, FT, FTA, ORB, TRB, AST, STL, BLK, TOV, PF, PTS )

	// constructed properties: per 36 min
	public $per_36_min; // array ( FG, FGA, TP, TPA, FT, FTA, ORB, TRB, AST, STL, BLK, TOV, PF, PTS )

	// constructed properties: per 100 poss
	public $per_100_poss; // array ( FG, FGA, TP, TPA, FT, FTA, ORB, TRB, AST, STL, BLK, TOV, PF, PTS )

	// constructed properties: per 100 poss
	public $per_75_poss; // array ( FG, FGA, TP, TPA, FT, FTA, ORB, TRB, AST, STL, BLK, TOV, PF, PTS )

	// constructed properties: other
	public $height_formatted, $display_name;


	/**
	* Class constructor
	* 
	*/
	public function __construct() {

		// default display_name to concat of first_name, last_name
		// this will be overwritten when PLAYER_years get added
		// used just in case PLAYER_years fails but still need a display name
		$this->display_name = $this->first_name . " " . $this->last_name;

		// set current_total to be an array
		$this->current_total = array();

	}


	/**
	* Fill player's current year data
	* 
	* @param array temp_current_year	array of PLAYER_year data
	*/
	public function setYears( $temp_current_year ) {

		$this->current_year = $temp_current_year;
		$this->current_year_count = count( $temp_current_year );

		// update the display name if there are PLAYER_year rows
		if ( $this->current_year_count > 0 ) {
			$this->getDisplayName();
		}

	}


	/**
	* Get final first/last name of player to display
	* 
	*/
	public function getDisplayName() {

		$final_year = $this->current_year_count - 1;
		$this->display_name = $this->current_year[$final_year]["first_name"] . " " . $this->current_year[$final_year]["last_name"];

	}


	/**
	* Create per game stats
	* 
	*/
	public function setPerGame() {

		for ( $i = 0; $i < $this->current_total_count; $i++ ) {

			$this->per_game[$i]["MIN"] = "";
			if ( !is_null( $this->current_total[$i]["MIN"] ) ) {
				$this->per_game[$i]["MIN"] = number_format( ( $this->current_total[$i]["MIN"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["FG"] = "";
			if ( !is_null( $this->current_total[$i]["FG"] ) ) {
				$this->per_game[$i]["FG"] = number_format( ( $this->current_total[$i]["FG"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["FGA"] = "";
			if ( !is_null( $this->current_total[$i]["FGA"] ) ) {
				$this->per_game[$i]["FGA"] = number_format( ( $this->current_total[$i]["FGA"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["TP"] = "";
			if ( !is_null( $this->current_total[$i]["TP"] ) ) {
				$this->per_game[$i]["TP"] = number_format( ( $this->current_total[$i]["TP"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["TPA"] = "";
			if ( !is_null( $this->current_total[$i]["TPA"] ) ) {
				$this->per_game[$i]["TPA"] = number_format( ( $this->current_total[$i]["TPA"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["FT"] = "";
			if ( !is_null( $this->current_total[$i]["FT"] ) ) {
				$this->per_game[$i]["FT"] = number_format( ( $this->current_total[$i]["FT"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["FTA"] = "";
			if ( !is_null( $this->current_total[$i]["FTA"] ) ) {
				$this->per_game[$i]["FTA"] = number_format( ( $this->current_total[$i]["FTA"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["ORB"] = "";
			if ( !is_null( $this->current_total[$i]["ORB"] ) ) {
				$this->per_game[$i]["ORB"] = number_format( ( $this->current_total[$i]["ORB"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["TRB"] = "";
			if ( !is_null( $this->current_total[$i]["TRB"] ) ) {
				$this->per_game[$i]["TRB"] = number_format( ( $this->current_total[$i]["TRB"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["AST"] = "";
			if ( !is_null( $this->current_total[$i]["AST"] ) ) {
				$this->per_game[$i]["AST"] = number_format( ( $this->current_total[$i]["AST"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["STL"] = "";
			if ( !is_null( $this->current_total[$i]["STL"] ) ) {
				$this->per_game[$i]["STL"] = number_format( ( $this->current_total[$i]["STL"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["BLK"] = "";
			if ( !is_null( $this->current_total[$i]["BLK"] ) ) {
				$this->per_game[$i]["BLK"] = number_format( ( $this->current_total[$i]["BLK"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["TOV"] = "";
			if ( !is_null( $this->current_total[$i]["TOV"] ) ) {
				$this->per_game[$i]["TOV"] = number_format( ( $this->current_total[$i]["TOV"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["PF"] = "";
			if ( !is_null( $this->current_total[$i]["PF"] ) ) {
				$this->per_game[$i]["PF"] = number_format( ( $this->current_total[$i]["PF"] / $this->current_total[$i]["GP"] ), 1 );
			}
			$this->per_game[$i]["PTS"] = "";
			if ( !is_null( $this->current_total[$i]["PTS"] ) ) {
				$this->per_game[$i]["PTS"] = number_format( ( $this->current_total[$i]["PTS"] / $this->current_total[$i]["GP"] ), 1 );
			}

		}

	}


	/**
	* Create per 36 minutes stats
	* 
	*/
	public function setPer36min() {

		for ( $i = 0; $i < $this->current_total_count; $i++ ) {

			if ( $this->current_total[$i]["MIN"] === NULL ) {

				// set empty per36min stats
				$this->per_36_min[$i]["FG"] = "";
				$this->per_36_min[$i]["FGA"] = "";
				$this->per_36_min[$i]["TP"] = "";
				$this->per_36_min[$i]["TPA"] = "";
				$this->per_36_min[$i]["FT"] = "";
				$this->per_36_min[$i]["FTA"] = "";
				$this->per_36_min[$i]["ORB"] = "";
				$this->per_36_min[$i]["TRB"] = "";
				$this->per_36_min[$i]["AST"] = "";
				$this->per_36_min[$i]["STL"] = "";
				$this->per_36_min[$i]["BLK"] = "";
				$this->per_36_min[$i]["TOV"] = "";
				$this->per_36_min[$i]["PF"] = "";
				$this->per_36_min[$i]["PTS"] = "";

			} else {

				// set per36min stats
				$this->per_36_min[$i]["FG"] = number_format( ( ( $this->current_total[$i]["FG"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				$this->per_36_min[$i]["FGA"] = number_format( ( ( $this->current_total[$i]["FGA"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				$this->per_36_min[$i]["TP"] = "";
				if ( $this->current_total[$i]["TP"] !== NULL ) {
					$this->per_36_min[$i]["TP"] = number_format( ( ( $this->current_total[$i]["TP"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				}
				$this->per_36_min[$i]["TPA"] = "";
				if ( $this->current_total[$i]["TPA"] !== NULL ) {
					$this->per_36_min[$i]["TPA"] = number_format( ( ( $this->current_total[$i]["TPA"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				}
				$this->per_36_min[$i]["FT"] = number_format( ( ( $this->current_total[$i]["FT"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				$this->per_36_min[$i]["FTA"] = number_format( ( ( $this->current_total[$i]["FTA"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				$this->per_36_min[$i]["ORB"] = "";
				if ( $this->current_total[$i]["ORB"] !== NULL ) {
					$this->per_36_min[$i]["ORB"] = number_format( ( ( $this->current_total[$i]["ORB"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				}
				$this->per_36_min[$i]["TRB"] = number_format( ( ( $this->current_total[$i]["TRB"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				$this->per_36_min[$i]["AST"] = number_format( ( ( $this->current_total[$i]["AST"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				$this->per_36_min[$i]["STL"] = "";
				if ( $this->current_total[$i]["STL"] !== NULL ) {
					$this->per_36_min[$i]["STL"] = number_format( ( ( $this->current_total[$i]["STL"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				}
				$this->per_36_min[$i]["BLK"] = "";
				if ( $this->current_total[$i]["BLK"] !== NULL ) {
					$this->per_36_min[$i]["BLK"] = number_format( ( ( $this->current_total[$i]["BLK"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				}
				$this->per_36_min[$i]["TOV"] = "";
				if ( $this->current_total[$i]["TOV"] !== NULL ) {
					$this->per_36_min[$i]["TOV"] = number_format( ( ( $this->current_total[$i]["TOV"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				}
				$this->per_36_min[$i]["PF"] = number_format( ( ( $this->current_total[$i]["PF"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );
				$this->per_36_min[$i]["PTS"] = number_format( ( ( $this->current_total[$i]["PTS"] / $this->current_total[$i]["MIN"] ) * 36 ), 1 );

			}

		}

	}


	/**
	* Create per 100 possession and per 75 possession stats
	*
	*/
	public function per75and100poss() {

		for ( $i = 0; $i < $this->current_total_count; $i++ ) {

			$temp_pace = $this->current_year[$i]["pace"];

			if ( empty( $temp_pace ) || empty( $this->current_total[$i]["MIN"] ) ) {

				// set empty per100poss
				$this->per_100_poss[$i]["FG"] = "";
				$this->per_100_poss[$i]["FGA"] = "";
				$this->per_100_poss[$i]["TP"] = "";
				$this->per_100_poss[$i]["TPA"] = "";
				$this->per_100_poss[$i]["FT"] = "";
				$this->per_100_poss[$i]["FTA"] = "";
				$this->per_100_poss[$i]["ORB"] = "";
				$this->per_100_poss[$i]["TRB"] = "";
				$this->per_100_poss[$i]["AST"] = "";
				$this->per_100_poss[$i]["STL"] = "";
				$this->per_100_poss[$i]["BLK"] = "";
				$this->per_100_poss[$i]["TOV"] = "";
				$this->per_100_poss[$i]["PF"] = "";
				$this->per_100_poss[$i]["PTS"] = "";

				// set empty per75poss
				$this->per_75_poss[$i]["FG"] = "";
				$this->per_75_poss[$i]["FGA"] = "";
				$this->per_75_poss[$i]["TP"] = "";
				$this->per_75_poss[$i]["TPA"] = "";
				$this->per_75_poss[$i]["FT"] = "";
				$this->per_75_poss[$i]["FTA"] = "";
				$this->per_75_poss[$i]["ORB"] = "";
				$this->per_75_poss[$i]["TRB"] = "";
				$this->per_75_poss[$i]["AST"] = "";
				$this->per_75_poss[$i]["STL"] = "";
				$this->per_75_poss[$i]["BLK"] = "";
				$this->per_75_poss[$i]["TOV"] = "";
				$this->per_75_poss[$i]["PF"] = "";
				$this->per_75_poss[$i]["PTS"] = "";

			} else {

				$temp_poss = ( $temp_pace / 48 ) * $this->current_total[$i]["MIN"];

				// set per100poss
				$this->per_100_poss[$i]["FG"] = number_format( ( ( ( $this->current_total[$i]["FG"] / ( $temp_poss ) ) * 100 ) ), 1);
				$this->per_100_poss[$i]["FGA"] = number_format( ( ( ( $this->current_total[$i]["FGA"] / ( $temp_poss ) ) * 100 ) ), 1);
				$this->per_100_poss[$i]["TP"] = "";
				if ( $this->current_total[$i]["TP"] !== NULL ) {
					$this->per_100_poss[$i]["TP"] = number_format( ( ( ( $this->current_total[$i]["TP"] / ( $temp_poss ) ) * 100 ) ), 1);
				}
				$this->per_100_poss[$i]["TPA"] = "";
				if ( $this->current_total[$i]["TPA"] !== NULL ) {
					$this->per_100_poss[$i]["TPA"] = number_format( ( ( ( $this->current_total[$i]["TPA"] / ( $temp_poss ) ) * 100 ) ), 1);
				}
				$this->per_100_poss[$i]["FT"] = number_format( ( ( ( $this->current_total[$i]["FT"] / ( $temp_poss ) ) * 100 ) ), 1);
				$this->per_100_poss[$i]["FTA"] = number_format( ( ( ( $this->current_total[$i]["FTA"] / ( $temp_poss ) ) * 100 ) ), 1);
				$this->per_100_poss[$i]["ORB"] = "";
				if ( $this->current_total[$i]["ORB"] !== NULL ) {
					$this->per_100_poss[$i]["ORB"] = number_format( ( ( ( $this->current_total[$i]["ORB"] / ( $temp_poss ) ) * 100 ) ), 1);
				}
				$this->per_100_poss[$i]["TRB"] = number_format( ( ( ( $this->current_total[$i]["TRB"] / ( $temp_poss ) ) * 100 ) ), 1);
				$this->per_100_poss[$i]["AST"] = number_format( ( ( ( $this->current_total[$i]["AST"] / ( $temp_poss ) ) * 100 ) ), 1);
				$this->per_100_poss[$i]["STL"] = "";
				if ( $this->current_total[$i]["STL"] !== NULL ) {
					$this->per_100_poss[$i]["STL"] = number_format( ( ( ( $this->current_total[$i]["STL"] / ( $temp_poss ) ) * 100 ) ), 1);
				}
				$this->per_100_poss[$i]["BLK"] = "";
				if ( $this->current_total[$i]["BLK"] !== NULL ) {
					$this->per_100_poss[$i]["BLK"] = number_format( ( ( ( $this->current_total[$i]["BLK"] / ( $temp_poss ) ) * 100 ) ), 1);
				}
				$this->per_100_poss[$i]["TOV"] = "";
				if ( $this->current_total[$i]["TOV"] !== NULL ) {
					$this->per_100_poss[$i]["TOV"] = number_format( ( ( ( $this->current_total[$i]["TOV"] / ( $temp_poss ) ) * 100 ) ), 1);
				}
				$this->per_100_poss[$i]["PF"] = number_format( ( ( ( $this->current_total[$i]["PF"] / ( $temp_poss ) ) * 100 ) ), 1);
				$this->per_100_poss[$i]["PTS"] = number_format( ( ( ( $this->current_total[$i]["PTS"] / ( $temp_poss ) ) * 100 ) ), 1);

				// set per75poss
				$this->per_75_poss[$i]["FG"] = number_format( ( ( ( $this->current_total[$i]["FG"] / ( $temp_poss ) ) * 75 ) ), 1);
				$this->per_75_poss[$i]["FGA"] = number_format( ( ( ( $this->current_total[$i]["FGA"] / ( $temp_poss ) ) * 75 ) ), 1);
				$this->per_75_poss[$i]["TP"] = "";
				if ( $this->current_total[$i]["TP"] !== NULL ) {
					$this->per_75_poss[$i]["TP"] = number_format( ( ( ( $this->current_total[$i]["TP"] / ( $temp_poss ) ) * 75 ) ), 1);
				}
				$this->per_75_poss[$i]["TPA"] = "";
				if ( $this->current_total[$i]["TPA"] !== NULL ) {
					$this->per_75_poss[$i]["TPA"] = number_format( ( ( ( $this->current_total[$i]["TPA"] / ( $temp_poss ) ) * 75 ) ), 1);
				}
				$this->per_75_poss[$i]["FT"] = number_format( ( ( ( $this->current_total[$i]["FT"] / ( $temp_poss ) ) * 75 ) ), 1);
				$this->per_75_poss[$i]["FTA"] = number_format( ( ( ( $this->current_total[$i]["FTA"] / ( $temp_poss ) ) * 75 ) ), 1);
				$this->per_75_poss[$i]["ORB"] = "";
				if ( $this->current_total[$i]["ORB"] !== NULL ) {
					$this->per_75_poss[$i]["ORB"] = number_format( ( ( ( $this->current_total[$i]["ORB"] / ( $temp_poss ) ) * 75 ) ), 1);
				}
				$this->per_75_poss[$i]["TRB"] = number_format( ( ( ( $this->current_total[$i]["TRB"] / ( $temp_poss ) ) * 75 ) ), 1);
				$this->per_75_poss[$i]["AST"] = number_format( ( ( ( $this->current_total[$i]["AST"] / ( $temp_poss ) ) * 75 ) ), 1);
				$this->per_75_poss[$i]["STL"] = "";
				if ( $this->current_total[$i]["STL"] !== NULL ) {
					$this->per_75_poss[$i]["STL"] = number_format( ( ( ( $this->current_total[$i]["STL"] / ( $temp_poss ) ) * 75 ) ), 1);
				}
				$this->per_75_poss[$i]["BLK"] = "";
				if ( $this->current_total[$i]["BLK"] !== NULL ) {
					$this->per_75_poss[$i]["BLK"] = number_format( ( ( ( $this->current_total[$i]["BLK"] / ( $temp_poss ) ) * 75 ) ), 1);
				}
				$this->per_75_poss[$i]["TOV"] = "";
				if ( $this->current_total[$i]["TOV"] !== NULL ) {
					$this->per_75_poss[$i]["TOV"] = number_format( ( ( ( $this->current_total[$i]["TOV"] / ( $temp_poss ) ) * 75 ) ), 1);
				}
				$this->per_75_poss[$i]["PF"] = number_format( ( ( ( $this->current_total[$i]["PF"] / ( $temp_poss ) ) * 75 ) ), 1);
				$this->per_75_poss[$i]["PTS"] = number_format( ( ( ( $this->current_total[$i]["PTS"] / ( $temp_poss ) ) * 75 ) ), 1);

			}

		}

	}


	/**
	* Convert inches to feet-inches
	* 
	* @param int	height_to_format	height (in total inches) to convert to Feet'Inches"
	* @return str	height_formatted	formated height (Feet'Inches")
	*/
	public function formatHeight( $height_to_format ) {

		$height_formatted = "";

		if ( ! empty( $height_to_format ) ) {

			$temp_feet = floor( $height_to_format / 12 );
			$temp_inches = $height_to_format - ( $temp_feet * 12 );
			$height_formatted = "{$temp_feet}'";
			if ( $temp_inches > 0 ) {
				$height_formatted .= "{$temp_inches}\"";
			}

		}

		return $height_formatted;

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
