jQuery(document).ready(function($) {

    // init Isotope
    var $grid = $('.grid').isotope({
        itemSelector: '.element-item',
        layoutMode: 'fitRows',
        getSortData: {
            title: '.title',
            color: '.color'
        }
    });
    
    // filter functions
    var filterFns = {
        // show if color is...
        yellow: function() {
            var color = $(this).find('.color').text();
            return color.match( /Yellow/ );
        },
        blue: function() {
            var color = $(this).find('.color').text();
            return color.match( /Blue/ );
        },
        purple: function() {
            var color = $(this).find('.color').text();
            return color.match( /Purple/ );
        }
    };
    
    // bind filter button click
    $('#filters').on( 'click', 'button', function() {
        var filterValue = $( this ).attr('data-filter');
        // use filterFn if matches value
        filterValue = filterFns[ filterValue ] || filterValue;
        $grid.isotope({ filter: filterValue });
    });
    
    // bind sort button click
    $('#sorts').on( 'click', 'button', function() {
        var sortByValue = $(this).attr('data-sort-by');
        $grid.isotope({ sortBy: sortByValue });
    });
    
    // change is-checked class on buttons
    $('.button-group').each( function( i, buttonGroup ) {
        var $buttonGroup = $( buttonGroup );
        $buttonGroup.on( 'click', 'button', function() {
        $buttonGroup.find('.is-checked').removeClass('is-checked');
        $( this ).addClass('is-checked');
        });
    });

});