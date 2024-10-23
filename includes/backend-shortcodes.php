<?php

add_action( 'init', 'cgrid_shortcodes_init' );
function cgrid_shortcodes_init() {
	add_shortcode( 'cinzagrid', 'cgrid_shortcode' ); // Main
	add_shortcode( 'cinza_grid', 'cgrid_shortcode' ); // Fallback
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Grid shortcode
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_shortcode( $atts = [], $content = null, $tag = 'cinzagrid' ) {

	// Enqueue scripts and styles
	wp_enqueue_script('isotope');
	wp_enqueue_style('animate');
	wp_enqueue_style('cgrid-frontend');

	// Normalize attribute keys to lowercase
	$atts = array_change_key_case( (array) $atts, CASE_LOWER );

	// Override default attributes with user attributes
	$cgrid_atts = shortcode_atts(
			array(
					'id' => '',
			), $atts, $tag
	);

	$grid_id = intval( $cgrid_atts['id'] );
	$cgrid_options = get_post_meta($grid_id, '_cgrid_options', true);
	$cgrid_skin = get_post_meta($grid_id, '_cgrid_skin', true);

	// Shortcode validation
	if ( empty($grid_id) || !$grid_id || empty($cgrid_options) ) {
			return "<p class='cgrid-error'>ERROR: Please enter a valid Cinza Grid ID.</p>";
	}

	$post_status = get_post_status($grid_id);
	if ($post_status === false || $post_status === 'draft') {
			return "<p class='cgrid-error'>ERROR: This Cinza Grid is not published yet.</p>";
	}

	// Fetch the post object to ensure it exists and is not trashed
	$post = get_post($grid_id);
	if (!$post || $post->post_status === 'trash') {
			return "<p class='cgrid-error'>ERROR: The Cinza Grid does not exist or has been deleted.</p>";
	}

	// Get setting values with validation
	$cgrid_posttype = isset($cgrid_options['cgrid_posttype']) ? esc_attr($cgrid_options['cgrid_posttype']) : 'post';
	$cgrid_orderby = isset($cgrid_options['cgrid_orderby']) ? esc_attr($cgrid_options['cgrid_orderby']) : 'date';
	$cgrid_orderby_meta = isset($cgrid_options['cgrid_orderby_meta']) ? esc_attr($cgrid_options['cgrid_orderby_meta']) : '';
	$cgrid_order = isset($cgrid_options['cgrid_order']) ? esc_attr($cgrid_options['cgrid_order']) : 'ASC';
	$cgrid_num = isset($cgrid_options['cgrid_num']) ? esc_attr($cgrid_options['cgrid_num']) : '-1';
	$cgrid_tax = isset($cgrid_options['cgrid_tax']) ? esc_attr($cgrid_options['cgrid_tax']) : '';
	$cgrid_tax_terms = isset($cgrid_options['cgrid_tax_terms']) ? esc_attr($cgrid_options['cgrid_tax_terms']) : '';
	$cgrid_sorting = isset($cgrid_options['cgrid_sorting']) ? esc_attr($cgrid_options['cgrid_sorting']) : '';
	$cgrid_filters = isset($cgrid_options['cgrid_filters']) ? esc_attr($cgrid_options['cgrid_filters']) : '';

	$cgrid_layout = isset($cgrid_options['cgrid_layout']) ? esc_attr($cgrid_options['cgrid_layout']) : 'fitRows';
	$cgrid_full_width = isset($cgrid_options['cgrid_full_width']) ? esc_attr($cgrid_options['cgrid_full_width']) : '0';
	$cgrid_query_string = isset($cgrid_options['cgrid_query_string']) ? esc_attr($cgrid_options['cgrid_query_string']) : '0';
	$cgrid_max_filter = isset($cgrid_options['cgrid_max_filter']) ? esc_attr($cgrid_options['cgrid_max_filter']) : '-1';

	$cgrid_breakpoint_1 = 1;
	$cgrid_columns_1 = isset($cgrid_options['cgrid_columns_1']) ? esc_attr($cgrid_options['cgrid_columns_1']) : '1';
	$cgrid_height_1 = isset($cgrid_options['cgrid_height_1']) ? esc_attr($cgrid_options['cgrid_height_1']) : '0';
	$cgrid_spacing_1 = isset($cgrid_options['cgrid_spacing_1']) ? esc_attr($cgrid_options['cgrid_spacing_1']) : '20';

	$cgrid_breakpoint_2 = isset($cgrid_options['cgrid_breakpoint_2']) ? esc_attr($cgrid_options['cgrid_breakpoint_2']) : '500';
	$cgrid_columns_2 = isset($cgrid_options['cgrid_columns_2']) ? esc_attr($cgrid_options['cgrid_columns_2']) : '2';
	$cgrid_height_2 = isset($cgrid_options['cgrid_height_2']) ? esc_attr($cgrid_options['cgrid_height_2']) : '0';
	$cgrid_spacing_2 = isset($cgrid_options['cgrid_spacing_2']) ? esc_attr($cgrid_options['cgrid_spacing_2']) : '20';

	$cgrid_breakpoint_3 = isset($cgrid_options['cgrid_breakpoint_3']) ? esc_attr($cgrid_options['cgrid_breakpoint_3']) : '700';
	$cgrid_columns_3 = isset($cgrid_options['cgrid_columns_3']) ? esc_attr($cgrid_options['cgrid_columns_3']) : '3';
	$cgrid_height_3 = isset($cgrid_options['cgrid_height_3']) ? esc_attr($cgrid_options['cgrid_height_3']) : '0';
	$cgrid_spacing_3 = isset($cgrid_options['cgrid_spacing_3']) ? esc_attr($cgrid_options['cgrid_spacing_3']) : '20';

	$cgrid_breakpoint_4 = isset($cgrid_options['cgrid_breakpoint_4']) ? esc_attr($cgrid_options['cgrid_breakpoint_4']) : '900';
	$cgrid_columns_4 = isset($cgrid_options['cgrid_columns_4']) ? esc_attr($cgrid_options['cgrid_columns_4']) : '4';
	$cgrid_height_4 = isset($cgrid_options['cgrid_height_4']) ? esc_attr($cgrid_options['cgrid_height_4']) : '0';
	$cgrid_spacing_4 = isset($cgrid_options['cgrid_spacing_4']) ? esc_attr($cgrid_options['cgrid_spacing_4']) : '20';

	$cgrid_breakpoint_5 = isset($cgrid_options['cgrid_breakpoint_5']) ? esc_attr($cgrid_options['cgrid_breakpoint_5']) : '1200';
	$cgrid_columns_5 = isset($cgrid_options['cgrid_columns_5']) ? esc_attr($cgrid_options['cgrid_columns_5']) : '5';
	$cgrid_height_5 = isset($cgrid_options['cgrid_height_5']) ? esc_attr($cgrid_options['cgrid_height_5']) : '0';
	$cgrid_spacing_5 = isset($cgrid_options['cgrid_spacing_5']) ? esc_attr($cgrid_options['cgrid_spacing_5']) : '20';

	// Retrieves an array of the latest posts, or posts matching the given criteria
	// https://developer.wordpress.org/reference/functions/get_posts/
	$aux_orderby_meta = $cgrid_orderby === "meta_value";
	$aux_taxonomy = !empty($cgrid_tax) && !empty($cgrid_tax_terms);
	$aux_taxonomy_terms = explode (",", $cgrid_tax_terms);

    if ($aux_orderby_meta && $aux_taxonomy) {
    //echo('Scenario 1');
		$args = array(
			'post_type' => $cgrid_posttype,
			'post_status' => 'publish',
			'numberposts' => $cgrid_num,
			'meta_key' => $cgrid_orderby_meta,
			'orderby' => 'meta_value',
			'order' => $cgrid_order,
		    'tax_query' => array(
		        array(
		            'taxonomy' => $cgrid_tax,
		            'field'    => 'slug',
		            'terms'    => $aux_taxonomy_terms,
		        ),
		    ),
		);
    } else if (!$aux_orderby_meta && $aux_taxonomy) {
    //echo('Scenario 2');
		$args = array(
			'post_type' => $cgrid_posttype,
			'post_status' => 'publish',
			'numberposts' => $cgrid_num,
			'orderby' => $cgrid_orderby,
			'order' => $cgrid_order,
		    'tax_query' => array(
		        array(
		            'taxonomy' => $cgrid_tax,
		            'field'    => 'slug',
		            'terms'    => $aux_taxonomy_terms,
		        ),
		    ),
		);
    } else if ($aux_orderby_meta && !$aux_taxonomy) {
    //echo('Scenario 3');
		$args = array(
			'post_type' => $cgrid_posttype,
			'post_status' => 'publish',
			'numberposts' => $cgrid_num,
			'meta_key' => $cgrid_orderby_meta,
			'orderby' => 'meta_value',
			'order' => $cgrid_order,
		);
    } else {
    //echo('Scenario 4');
		$args = array(
			'post_type' => $cgrid_posttype,
			'post_status' => 'publish',
			'numberposts' => $cgrid_num,
			'orderby' => $cgrid_orderby,
			'order' => $cgrid_order,
		);
    }
	$posts = get_posts( $args );

	// Sorting
	$sorts = '';
	$sorts_data = '';

	if(!empty($cgrid_sorting)) {
		$sorts .= '<div id="cinza-grid-'.$grid_id.'-sorts" class="cinza-grid-button-group">';
			$sort_lines = preg_split("/\r\n|\n|\r/", $cgrid_sorting);
			$sorts_btns_temp = '';
			$original_order_flag = false;

			foreach ($sort_lines as $sort_line) {
				if(!empty($sort_line)) {
					$sort_atts = explode ("/", $sort_line);

					if(trim($sort_atts[0]) == 'default') {
						$sorts_btns_temp .= '<button class="button is-checked" data-sort-by="original-order">'. trim($sort_atts[1]) .'</button>';;
						$sorts_data .= '';
						$original_order_flag = true;
					} else {
						$sorts_btns_temp .= '<button class="button" data-sort-by="'. trim($sort_atts[0]) .'">'. trim($sort_atts[1]) .'</button>';
						$sorts_data .= '\'' . trim($sort_atts[0]) . '\': ' . '\'.' . trim($sort_atts[0]) . '\', ';
					}
				}
			}

			if($original_order_flag == false) {
				$sorts .= '<button class="button is-checked" data-sort-by="original-order">Default</button>' . $sorts_btns_temp;
			} else {
				$sorts .= $sorts_btns_temp;
			}
		$sorts .= '</div>';
	}

    // Filter
	$filters = '';
	$filters_temp = empty($cgrid_options['cgrid_filters']) ? '' : $cgrid_options['cgrid_filters'];

    if(!empty($filters_temp)) {
		$filters = '<div id="cinza-grid-'.$grid_id.'-filters">';
			$filter_lines = preg_split("/\r\n|\n|\r/", $filters_temp);

			foreach ($filter_lines as $filter_line) {
				if(!empty($filter_line)) {

					$filter_first_split = explode('/', $filter_line, 2);
					$filter_second_split = explode('/', $filter_first_split[1], 2);
					$filter_substring1 = $filter_first_split[0];
					$filter_substring2 = $filter_second_split[0];
					$filter_substring3 = $filter_second_split[1];

					$filters .= '<div class="cinza-grid-button-group" data-filter-group="'. str_replace(" ", "-", trim(strtolower($filter_substring2))) .'">';

						// First button
						$filters .= '<button class="button is-checked" data-filter="*">'. trim($filter_substring2) .'</button>';

						// All other buttons
						$filter_buttons = explode (",", $filter_substring3);
						foreach ($filter_buttons as $filter_button) {
							$characters = array("&amp;", " ", "---", "/");
							$encoded_classes = str_replace($characters, '-', trim(strtolower($filter_button)));
							$filters .= '<button class="button" id="'. $encoded_classes .'" data-filter=".'. $encoded_classes .'">'. trim($filter_button) .'</button>';
						}
					$filters .= '</div>';
				}
			}
	    $filters .= '</div>';
    }

	if ($cgrid_layout == "fitrows") $cgrid_layout = "fitRows";

	$script = "<script>

	jQuery(window).load(function() {
		var grid = jQuery('#cinza-grid-".$grid_id."').isotope();
	});

	jQuery(document).ready(function($) {

	    var grid = $('#cinza-grid-".$grid_id."').isotope
	    ({
	        itemSelector: '.cinza-grid-item',
	        layoutMode: '".$cgrid_layout."',
	        transitionDuration: '0.4s',
	        getSortData: {".$sorts_data."}
	    });

	    if( '".$sorts."' != '' )
	    {
	    	// bind sort button click
		    $('#cinza-grid-".$grid_id."-sorts').on( 'click', 'button', function()
		    {
		        var sortByValue = $(this).attr('data-sort-by');
		        grid.isotope({ sortBy: sortByValue });
		    });

		    // change is-checked class on buttons
		    $('#cinza-grid-".$grid_id."-sorts').each( function( i, buttonGroup )
		    {
		        var buttonGroup = $( buttonGroup );
		        buttonGroup.on( 'click', 'button', function() {
		        buttonGroup.find('.is-checked').removeClass('is-checked');
		        $( this ).addClass('is-checked');
		        });
		    });
		}

		if( '".$filters."' != '' )
		{
			// store filter for each group
			var filters = {};
			var filterButtonGroup = $('#cinza-grid-".$grid_id."-filters');

			filterButtonGroup.on( 'click', '.button', function( event )
			{
				var button = $( event.currentTarget );

				// get group key
				var buttonGroup = button.parents('.cinza-grid-button-group');
				var filterGroup = buttonGroup.attr('data-filter-group');

				// set filter for group
				filters[ filterGroup ] = button.attr('data-filter');

				// combine filters
				var filterValue = concatValues( filters );

				// limit number if items filtered
				if(".intval($cgrid_max_filter)." > 0) {
				    // Set max number of items visible when filter is active
				    var filterSelector = filterValue.replace(/\*/g, '');
				    var items = $('#cinza-grid-".$grid_id." .cinza-grid-item').removeClass('filter-limit');

				    if(filterSelector) {
				        var filteredItems = items.filter(filterSelector);
				        filteredItems.slice(".intval($cgrid_max_filter).").addClass('filter-limit');
				    }
				}

				// set filter for Isotope, which triggers the script for layout
				grid.isotope({ filter: filterValue });

				// change query string in real time
				if( ".$cgrid_query_string." == 1 ) {
					location.hash = 'filter=' + encodeURIComponent( filterValue );
				}
			});

			// change is-checked class on buttons
			$('.cinza-grid-button-group').each( function( i, buttonGroup )
			{
				var buttonGroup = $( buttonGroup );
				buttonGroup.on( 'click', 'button', function( event )
				{
					buttonGroup.find('.is-checked').removeClass('is-checked');
					$( event.currentTarget ).addClass('is-checked');
				});
			});
		}

		// flatten object by concatenating values
		function concatValues( obj )
		{
			var value = '';
			for ( var prop in obj )
			{
				value += obj[ prop ];
			}
			return value;
		}

		// URL query string
		// Example: https://vinicius.razorfrog.dev/grid-shortcode-test/#filter=.blue
		// Example: https://vinicius.razorfrog.dev/grid-shortcode-test/#filter=.blue.small.scrollto-rowID

		function getHashFilter()
		{
			var matches = location.hash.match( /filter=([^&]+)/i );
			var hashFilter = matches && matches[1];
			return hashFilter && decodeURIComponent( hashFilter );
		}

		var isIsotopeInit = false;
		function onHashchange()
		{
			var hashFilter = getHashFilter();

			if ( !hashFilter && isIsotopeInit ) {
				return;
			}

			isIsotopeInit = true;

			grid.isotope
			({
				itemSelector: '.cinza-grid-item',
				filter: hashFilter
			});

			if ( hashFilter )
			{
				// remove first dot so we don't have hashSplit[0] empty
				var hashSplit = hashFilter.substring(1, hashFilter.length).split('.');

				// checks if scrollto is in the query string
				var scrollCheck = hashFilter.indexOf('scrollto');

				// if scrollto is in found in the array
				if ( scrollCheck > -1 )
				{
					// get scrollto ID, which should always be the last item in the array
					var scrollID = '#' + hashSplit[hashSplit.length - 1].replace('scrollto-','');

					// remove scrollto item from the array
					hashSplit.splice(hashSplit.length - 1, 1);

					// scroll to ID
					document.querySelector(String(scrollID)).scrollIntoView({ behavior:  'smooth' });
				}

				hashSplit.forEach(element =>
				{
					filterButtonGroup.find('[data-filter=\".' + element + '\"]').click();
				});
			}
		}
		$(window).on( 'hashchange', onHashchange );
		onHashchange();

	});
	</script>";

  // Grid items
  $grid = '<div id="cinza-grid-'.$grid_id.'" class="cinza-grid">';
	if( !empty( $posts ) ){
		$debug = "";

		foreach ( $posts as $post ) {
			$grid_item = $cgrid_skin['cgrid_skin_content'];
			$filter_classes = "";

			// Replace %date('l F j, Y')%
			$pattern_date = "/%date\('([^']+)'\)%/";
			$grid_item = preg_replace_callback($pattern_date, function($matches) use ($post) {
					$date_format = $matches[1];
					return get_the_date($date_format, $post->ID);
			}, $grid_item);

			// Replace %meta('field_name')%
			$pattern_meta = "/%meta\('([^']+)'\)%/";
			$grid_item = preg_replace_callback($pattern_meta, function($matches) use ($post) {
					$meta_field = $matches[1];
					return get_post_meta($post->ID, $meta_field, true);
			}, $grid_item);

			if(!empty($filters_temp)) {
				foreach ($filter_lines as $filter_line) {
					$filter_classes .= filter_meta_replace($post, $filter_line);
				}
			}

			// Replace %tax('taxonomy_name')%
			$pattern_tax = "/%tax\('([^']+)'\)%/";
			$grid_item = preg_replace_callback($pattern_tax, function($matches) use ($post) {
					$taxonomy_name = $matches[1];
					$term_list = get_the_terms($post->ID, $taxonomy_name);
					if ($term_list && !is_wp_error($term_list)) {
							$terms_array = array_map(function($term) {
									return esc_attr($term->name);
							}, $term_list);
							return join(' ', $terms_array);
					} else {
							return "Invalid taxonomy.";
					}
			}, $grid_item);

			if(!empty($filters_temp)) {
				foreach ($filter_lines as $filter_line) {
					$filter_classes .= filter_tax_replace($post, $filter_line);
				}
			}

			// Replace %taxsep('taxonomy_name')%
			$pattern_taxsep = "/%taxsep\('([^']+)'\)%/";
			$grid_item = preg_replace_callback($pattern_taxsep, function($matches) use ($post) {
					$taxonomy_name = $matches[1];
					$term_list = get_the_terms($post->ID, $taxonomy_name);
					if ($term_list && !is_wp_error($term_list)) {
							$terms_array = array_map(function($term) {
									return esc_attr($term->name);
							}, $term_list);
							return join(', ', $terms_array);
					} else {
							return "Invalid taxonomy.";
					}
			}, $grid_item);

			// Replace %taxurl('taxonomy_name')%
			$pattern_taxurl = "/%taxurl\('([^']+)'\)%/";
			$grid_item = preg_replace_callback($pattern_taxurl, function($matches) use ($post) {
					$taxonomy_name = $matches[1];
					$term_list = get_the_terms($post->ID, $taxonomy_name);
					if ($term_list && !is_wp_error($term_list)) {
							$terms_array = array_map(function($term) use ($taxonomy_name) {
									return '<a href="' . esc_url(get_term_link($term->slug, $taxonomy_name)) . '">' . esc_html($term->name) . '</a>';
							}, $term_list);
							return join(', ', $terms_array);
					} else {
							return "Invalid taxonomy.";
					}
			}, $grid_item);

			// Replace %img('img_size')%
			$pattern_imgsize = "/%img\('([^']+)'\)%/";
			$grid_item = preg_replace_callback($pattern_imgsize, function($matches) use ($post) {
					$size = $matches[1];
					return get_the_post_thumbnail($post->ID, $size);
			}, $grid_item);

			// Replace %imgurl('img_size')%
			$pattern_imgurlsize = "/%imgurl\('([^']+)'\)%/";
			$grid_item = preg_replace_callback($pattern_imgurlsize, function($matches) use ($post) {
					$size = $matches[1];
					return get_the_post_thumbnail_url($post->ID, $size);
			}, $grid_item);

			// Replace PHP shortcode
			preg_match_all('/%\[([^\s\]]+)([^\]]*)\]%/', $grid_item, $shortcode_matches);
			foreach ($shortcode_matches[0] as $index => $shortcode_tag) {
					$shortcode_name = $shortcode_matches[1][$index];
					$shortcode_params = $shortcode_matches[2][$index];
					$shortcode_output = do_shortcode("[$shortcode_name$shortcode_params id='{$post->ID}']"); // Add the post ID to the shortcode parameters
					$grid_item = str_replace($shortcode_tag, $shortcode_output, $grid_item);
			}

			// Replace easy tags
	    $code1 = array(
	    	'%title%',
	    	'%url%',
	    	'%slug%',
	    	'%date%',
	    	'%img%',
	    	'%imgurl%',
	    	'%content%',
	    	'%excerpt%',
	    );

	    $code2 = array(
				get_the_title($post->ID),
				get_permalink($post->ID),
				$post->post_name,
				get_the_date('F j, Y', $post->ID),
				get_the_post_thumbnail($post->ID,'full'),
				get_the_post_thumbnail_url($post->ID,'full'),
				wpautop($post->post_content),
				get_the_excerpt($post->ID),
	    );

			$characters = array("&amp;", "---", "/");
			$encoded_classes = str_replace($characters, '-', strtolower($filter_classes));
			$grid .= '<div class="cinza-grid-item cinza-grid-'. $post->ID . $encoded_classes.'">'. str_replace($code1, $code2, $grid_item) .'</div>';
		}
	}
    $grid .= '</div>';

    // Style
    $style = "<style>";
		$style .= css_breakpoint($grid_id, $cgrid_breakpoint_1, $cgrid_columns_1, $cgrid_full_width, $cgrid_height_1, $cgrid_spacing_1);
		$style .= css_breakpoint($grid_id, $cgrid_breakpoint_2, $cgrid_columns_2, $cgrid_full_width, $cgrid_height_2, $cgrid_spacing_2);
		$style .= css_breakpoint($grid_id, $cgrid_breakpoint_3, $cgrid_columns_3, $cgrid_full_width, $cgrid_height_3, $cgrid_spacing_3);
		$style .= css_breakpoint($grid_id, $cgrid_breakpoint_4, $cgrid_columns_4, $cgrid_full_width, $cgrid_height_4, $cgrid_spacing_4);
		$style .= css_breakpoint($grid_id, $cgrid_breakpoint_5, $cgrid_columns_5, $cgrid_full_width, $cgrid_height_5, $cgrid_spacing_5);
    $style .= "</style>";

    //return $debug . $sorts . $filters . $grid . $style . $script;
    return $sorts . $filters . $grid . $style . $script;
}

function filter_meta_replace($post, $filters_temp) {
	if(strpos($filters_temp, '%meta(') !== false) {
		$meta_start_position = strpos($filters_temp, "%meta(");
		$meta_open_paranthesis = $meta_start_position + 5;
		$meta_close_paranthesis = $meta_start_position + strpos(substr($filters_temp, $meta_start_position, $meta_start_position+50), ")");
		$meta_code = substr($filters_temp, $meta_start_position+1, $meta_close_paranthesis-$meta_start_position);
		$meta_code_args = substr($filters_temp, $meta_open_paranthesis+2, $meta_close_paranthesis-$meta_open_paranthesis-3);
		$meta_formatted = get_post_meta( $post->ID, $meta_code_args, true );
		return " ".str_replace(' ', '-', strtolower($meta_formatted));
	}
}

function filter_tax_replace($post, $filters_temp) {
	if(strpos($filters_temp, '%tax(') !== false) {
		$tax_start_position = strpos($filters_temp, "%tax(");
		$tax_open_paranthesis = $tax_start_position + 5;
		$tax_close_paranthesis = $tax_start_position + strpos(substr($filters_temp, $tax_start_position, $tax_start_position+50), ")");
		$tax_code = substr($filters_temp, $tax_start_position+1, $tax_close_paranthesis-$tax_start_position);
		$tax_code_args = substr($filters_temp, $tax_open_paranthesis+1, $tax_close_paranthesis-$tax_open_paranthesis-2);
		$term_list = get_the_terms( $post->ID, $tax_code_args );
		if( $term_list && ! is_wp_error( $term_list ) ) {
			$terms_array = array();
			foreach ( $term_list as $term ) {
				$terms_array[] = str_replace(' ', '-', esc_attr($term->name) );
			}
			$tax_formatted = join( ' ', $terms_array );
			return " ".strtolower($tax_formatted);
		}
	}
}

function css_breakpoint($grid_id, $breakpoint, $col, $full_width, $height, $space) {
    $style = "
    @media only screen and (min-width: ". $breakpoint ."px) {";

	    if (boolval($full_width)) {
		    if ($col == 1) {
			    $style .= "
				#cinza-grid-".$grid_id." {
	                width: calc(100vw - 2 * ". $space ."px);
	                margin-left: calc(-50vw + ". $space ."px);
			        position: relative;
			        left: 50%;
			        right: 50%;
				}
				#cinza-grid-".$grid_id." .cinza-grid-item {
					width: 100%;
					min-height: ". $height ."px;
					margin: 0px 0px ". $space ."px 0px;
				}";
		    } else {
		        $style .=  "
		        #cinza-grid-".$grid_id." {
	                width: calc(100vw - ". $space ."px);
	                margin-left: calc(-50vw + ". $space ."px / 2);
			        position: relative;
			        left: 50%;
			        right: 50%;
	            }
				#cinza-grid-".$grid_id." .cinza-grid-item {
					width: calc(100% / ". $col ." - ". $space ."px - 0.5px); /* -0.5px to be safe */
					min-height: ". $height ."px;
					margin: calc(". $space ."px / 2);
				}";
		    }
	    } else {
		    if ($col == 1) {
			    $style .= "
				#cinza-grid-".$grid_id." {
					width: 100%;
					margin: 0px;
				}
				#cinza-grid-".$grid_id." .cinza-grid-item {
					width: 100%;
					min-height: ". $height ."px;
					margin: 0px 0px ". $space ."px 0px;
				}";
		    } else {
		        $style .=  "
				#cinza-grid-".$grid_id." {
					width: calc(100% + ". $space ."px);
					margin: 0 calc(-". $space ."px / 2);
				}
				#cinza-grid-".$grid_id." .cinza-grid-item {
					width: calc(100% / ". $col ." - ". $space ."px - 0.5px); /* -0.5px to be safe */
					min-height: ". $height ."px;
					margin: calc(". $space ."px / 2);
				}";
		    }
	    }
    $style .= "}";

	return $style;
}
