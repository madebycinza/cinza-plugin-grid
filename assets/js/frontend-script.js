jQuery(document).ready(function($) {

/*
	// BEGIN: "My First Grid" test
	// init Isotope
    var $grid = $('.cinza-grid').isotope({
        itemSelector: '.cinza-grid-item',
        layoutMode: 'fitRows',
        getSortData: {
            title: '.title',
            color: '.color'
        }
    });
    
    // filter functions
    var filterFns = {
        // show if color is...
        red: function() {
            var color = $(this).find('.color').text();
            return color.match( /Red/ );
        },
        brown: function() {
            var color = $(this).find('.color').text();
            return color.match( /Brown/ );
        },
        purple: function() {
            var color = $(this).find('.color').text();
            return color.match( /Purple/ );
        },
        green: function() {
            var color = $(this).find('.color').text();
            return color.match( /Green/ );
        },
        blue: function() {
            var color = $(this).find('.color').text();
            return color.match( /Blue/ );
        }
    };
    
    // bind filter button click
    $('#cinza-grid-filters').on( 'click', 'button', function() {
        var filterValue = $( this ).attr('data-filter');
        // use filterFn if matches value
        filterValue = filterFns[ filterValue ] || filterValue;
        $grid.isotope({ filter: filterValue });
    });
    
    // bind sort button click
    $('#cinza-grid-sorts').on( 'click', 'button', function() {
        var sortByValue = $(this).attr('data-sort-by');
        $grid.isotope({ sortBy: sortByValue });
    });
    
    // change is-checked class on buttons
    $('.cinza-grid-button-group').each( function( i, buttonGroup ) {
        var $buttonGroup = $( buttonGroup );
        $buttonGroup.on( 'click', 'button', function() {
        $buttonGroup.find('.is-checked').removeClass('is-checked');
        $( this ).addClass('is-checked');
        });
    });
    // END: "My First Grid" test
*/

/*
    // BEGIN: "My Second Grid" test
    // init Isotope
    var $grid = $('.cinza-grid').isotope({
        itemSelector: '.cinza-grid-item2',
        layoutMode: 'fitRows',
    });
    
    // store filter for each group
    var filters = {};
    
    $('.cinza-grid-filters').on( 'change', function( event ) {
        var $select = $( event.target );
        
        // get group key
        var filterGroup = $select.attr('value-group');
        console.log(filterGroup);
        
        // set filter for group
        filters[ filterGroup ] = event.target.value;
        console.log(event.target.value);
        
        // combine filters
        var filterValue = concatValues( filters );
        
        // set filter for Isotope
        $grid.isotope({ filter: filterValue });
    });
    
    // flatten object by concatting values
    function concatValues( obj ) {
        var value = '';
        for ( var prop in obj ) {
            value += obj[ prop ];
        }
        return value;
    }
    // END: "My Second Grid" test
*/
	
	
});







