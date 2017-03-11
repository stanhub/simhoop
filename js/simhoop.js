// doc ready
$( document ).ready(function() {

	/* **********
	 * sections
	 * **********/
	$( ".section_title a" ).click( function() {

		var $el = $( this ),
			myIcon = $el.find( ".section_title_icon" ),
			mySection = $el.data( "section" );

		if ( myIcon.html() === "[ - ]" ) {

			myIcon.html( "[ + ]" );

		} else {

			myIcon.html( "[ - ]" );

		}

		$( "#" + mySection ).toggleClass( "collapsed" );

		return false;

	});


	/* **********
	 * tabs
	 * **********/
	$( ".tabs li a" ).click( function() {

		var thisClickedTab = $( this );
			tabsParent = thisClickedTab.parents( ".tabs" );

		// hide currently active tab's section
		var thisSection = "#" + tabsParent.find( "li.active a" ).data( "tab" );
		$( thisSection ).addClass( "collapsed" );

		// remove active class from tab
		tabsParent.find( "li.active" ).removeClass( "active" );

		// add active class to clicked tab
		thisClickedTab.parent().addClass( "active" );

		// show clicked tab's section
		thisSection = "#" + thisClickedTab.data( "tab" ),
		$( thisSection ).toggleClass( "collapsed" );
		
		return false;

	});

});


// window load
$( window ).load( function() {

	/* **********
	 * tablesorter plugin: height
	 * **********/
	$.tablesorter.addParser({
		id: 'height',
		is: function( s ) {
			// return false so this parser is not auto detected 
			return false;
		},
		format: function( s ) {
			// format your data for normalization
			return s.toLowerCase()
				.replace( "5'1\"", "ab" )
				.replace( "5'2\"", "ac" )
				.replace( "5'3\"", "ad" )
				.replace( "5'4\"", "ae" )
				.replace( "5'5\"", "af" )
				.replace( "5'6\"", "ag" )
				.replace( "5'7\"", "ah" )
				.replace( "5'8\"", "ai" )
				.replace( "5'9\"", "aj" )
				.replace( "5'0\"", "ak" )
				.replace( "5'10\"", "al" )
				.replace( "5'11\"", "am" )
				.replace( "6'1\"", "ao" )
				.replace( "6'2\"", "ap" )
				.replace( "6'3\"", "aq" )
				.replace( "6'4\"", "ar" )
				.replace( "6'5\"", "as" )
				.replace( "6'6\"", "at" )
				.replace( "6'7\"", "au" )
				.replace( "6'8\"", "av" )
				.replace( "6'9\"", "aw" )
				.replace( "6'0\"", "ax" )
				.replace( "6'10\"", "ay" )
				.replace( "6'11\"", "az" )
				.replace( "7'1\"", "bb" )
				.replace( "7'2\"", "bc" )
				.replace( "7'3\"", "bd" )
				.replace( "7'4\"", "be" )
				.replace( "7'5\"", "bf" )
				.replace( "7'6\"", "bg" )
				.replace( "7'7\"", "bh" )
				.replace( "7'8\"", "bi" )
				.replace( "7'9\"", "bj" )
				.replace( "7'0\"", "bk" )
				.replace( "7'10\"", "bl" )
				.replace( "7'11\"", "bm" )
				.replace( "5'", "aa" )
				.replace( "6'", "an" )
				.replace( "7'", "ba" )
				.replace( "99999999", "zz" );
		},
		// set type, either numeric or text
		type: 'text'
	});


	/* **********
	 * tablesorter plugin: position
	 * **********/
	$.tablesorter.addParser({
		id: 'position',
		is: function( s ) {
			// return false so this parser is not auto detected 
			return false;
		},
		format: function( s ) {
			// format your data for normalization
			return s.toLowerCase()
				.replace( "g-f-c", 21 )
				.replace( "f-g-c", 22 )
				.replace( "f-c-g", 23 )
				.replace( "c-g-f", 24 )
				.replace( "c-f-g", 25 )
				.replace( "pg-sg", 2 )
				.replace( "sg-pg", 3 )
				.replace( "sg-sf", 6 )
				.replace( "sf-sg", 8 )
				.replace( "sf-pf", 11 )
				.replace( "pf-sf", 12 )
				.replace( "pf-c", 15 )
				.replace( "c-pf", 17 )
				.replace( "pg", 0 )
				.replace( "sg", 4 )
				.replace( "sf", 10 )
				.replace( "pf", 13 )
				.replace( "g-f", 5 )
				.replace( "f-g", 7 )
				.replace( "f-c", 14 )
				.replace( "c-f", 16 )
				.replace( "g-c", 19 )
				.replace( "c-g", 20 )
				.replace( "g", 1 )
				.replace( "f", 9 )
				.replace( "c", 18 );
		},
		// set type, either numeric or text
		type: 'numeric'
	});

	/* **********
	 * tablesorter plugin: real position
	 * **********/
	$.tablesorter.addParser({
		id: 'real_position',
		is: function( s ) {
			// return false so this parser is not auto detected 
			return false;
		},
		format: function( s ) {
			// format your data for normalization
			return s.toLowerCase()
				.replace( "pg-sg", 2 )
				.replace( "sg-pg", 3 )
				.replace( "sg-sf", 5 )
				.replace( "sf-sg", 6 )
				.replace( "sf-pf", 8 )
				.replace( "pf-sf", 9 )
				.replace( "pf-c", 11 )
				.replace( "c-pf", 12 )
				.replace( "pg", 1 )
				.replace( "sg", 4 )
				.replace( "sf", 7 )
				.replace( "pf", 10 )
				.replace( "c", 13 );
		},
		// set type, either numeric or text
		type: 'numeric'
	});

	/* **********
	 * tablesorter plugin: handleEmptyCell
	 * **********/
	var handleEmptyCell = function( node ) {

		if ( node.innerHTML.length === 0 ) {
			return "99999999";
		} else {
			return node.innerHTML;
		}

	}


	/* **********
	 * tablesorter: franchise page
	 * **********/
	$franchise_tables = $( "#franchise table.tablesorter" );
	if ( $franchise_tables.find( "tbody tr" ).length ) {
		$franchise_tables.tablesorter({
			sortList: [[0,0]],
			textExtraction: handleEmptyCell
		});
	}


	/* **********
	 * tablesorter: league page
	 * **********/
	$league_tables = $( "#league table.tablesorter" );
	if ( $league_tables.find( "tbody tr" ).length ) {
		$league_tables.tablesorter({
			sortList: [[0,0]],
			textExtraction: handleEmptyCell
		});
	}


	/* **********
	 * tablesorter: player page
	 * **********/
	$player_tables = $( "#player table.tablesorter" );
	if ( $player_tables.find( "tbody tr" ).length ) {
		$player_tables.tablesorter({
			sortList: [[1,0]],
			headers: {
				6: { sorter: 'height' },
				8: { sorter: 'position' },
				9: { sorter: 'real_position' }
			},
			textExtraction: handleEmptyCell
		});
	}


	/* **********
	 * tablesorter: team page
	 * **********/
	$team_tables = $( "#team table.tablesorter" );
	if ( $team_tables.find( "tbody tr" ).length ) {
		$team_tables.tablesorter({
			sortList: [[4,0]],
			headers: {
				6: { sorter: 'height' },
				8: { sorter: 'position' },
				9: { sorter: 'real_position' }
			},
			textExtraction: handleEmptyCell
		});
	}


	/* **********
	 * tablesorter: year page
	 * **********/
	$year_tables = $( "#year table.tablesorter" );
	if ( $year_tables.find( "tbody tr" ).length ) {
		$year_tables.tablesorter({
			sortList: [[2,0]],
			textExtraction: handleEmptyCell
		});
	}


	/* **********
	 * tool tip
	 * **********/
	$( "a, th, .help_icon" ).hover(function(){

		// vars
		var title = $( this ).attr( "title" );

		// Hover over code
		$( this ).data( "tip_text", title ).removeAttr( "title" );
		$( "<p class='tool_tip'></p>" )
			.text( title )
			.appendTo( "body" )
			.fadeIn( "slow" );

	}, function() {

		// Hover out code
		$( this ).attr( "title", $( this ).data( "tip_text" ) );
		$( ".tool_tip" ).remove();

	}).mousemove( function( e ) {

		var mousex = e.pageX + 0, //Get X coordinates
			mousey = e.pageY + 10, //Get Y coordinates
			tooltipWidth = $( ".tool_tip" ).outerWidth();
			contentWidth = $( "#content" ).outerWidth();

		// ensure tool tip remains inside #content
		if ( mousex + tooltipWidth > contentWidth ) {
			mousex = e.pageX - tooltipWidth;
		}

		$( ".tool_tip" )
		.css( { top: mousey, left: mousex } )

	});


	/* **********
	 * hide partial
	 * **********/
	var $partials = $( ".partial" );
	$( "a.hide_partials" ).click( function() {

		$partials.toggleClass( "partial_hidden" );

		// hide all partial rows
		var partial_display_text = "Hide Partials",
			partial_title_text = "Hide Partial Seasons";

		if ( $( this ).text() === "Hide Partials" ) {

			partial_display_text = "Show Partials";
			partial_title_text = "Show Partial Seasons";

		}

		$( this ).text( partial_display_text );
		$( this ).attr( "title", partial_title_text );

		return false;

	})


	/* **********
	 * clicked table row
	 * **********/
	$( "table tr" ).click( function() {

		$( this ).toggleClass( "selected" );

	});

});
