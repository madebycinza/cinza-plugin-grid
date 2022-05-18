<?php
	
add_action( 'init', 'cgrid_shortcodes_init' );
function cgrid_shortcodes_init() {
	add_shortcode( 'cinza_grid', 'cgrid_shortcode' );
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// grid shortcode
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
function cgrid_shortcode( $atts = [], $content = null, $tag = 'cinza_grid' ) {

	// Enqueue scripts
    wp_enqueue_script('isotope');
    wp_enqueue_style('animate');
    wp_enqueue_style('cgrid-frontend');
	
    // Normalize attribute keys, lowercase
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );
 
    // Override default attributes with user attributes
    $cgrid_atts = shortcode_atts(
        array(
            'id' => 'Empty',
        ), $atts, $tag
    );
	$grid_id = intval( $cgrid_atts['id'] );
    $cgrid_options = get_post_meta($grid_id, '_cgrid_options', true);

	// Shortcode validation
    if ( $grid_id == 'Empty' || !is_int($grid_id) || empty($cgrid_options) ) {
        return "<p class='cgrid-error'>Please enter a valid grid ID.</p>";
    }

    // Query: _cgrid_options
    $options = ' \'{ ';

        // Query validations
        if (intval(esc_attr($cgrid_options['cgrid_autoPlay'])) > 0) {
            $valid_autoPlay = '"autoPlay": '. esc_attr($cgrid_options['cgrid_autoPlay']) .','; 
        } else {
            $valid_autoPlay = '"autoPlay": false,'; 
        }

        if (esc_attr($cgrid_options['cgrid_animation']) == "fade") {
            wp_enqueue_style('flickity-fade');
            wp_enqueue_script('flickity-fade');
            $valid_fade = '"fade": true,'; 
        } else {
            $valid_fade = '';
        }

        // Behavior
        $options .= '"draggable": ' . (boolval(esc_attr($cgrid_options['cgrid_draggable'])) ? "true" : "false") . ',';
        $options .= '"freeScroll": ' . (boolval(esc_attr($cgrid_options['cgrid_freeScroll'])) ? "true" : "false") . ',';
        $options .= '"wrapAround": ' . (boolval(esc_attr($cgrid_options['cgrid_wrapAround'])) ? "true" : "false") . ',';
        $options .= '"groupCells": ' . esc_attr($cgrid_options['cgrid_groupCells']) . ',';
        $options .= $valid_autoPlay;
        $options .= $valid_fade;
        $options .= '"pauseAutoPlayOnHover": ' . (boolval(esc_attr($cgrid_options['cgrid_pauseAutoPlayOnHover'])) ? "true" : "false") . ',';
        $options .= '"adaptiveHeight": ' . (boolval(esc_attr($cgrid_options['cgrid_adaptiveHeight'])) ? "true" : "false") . ',';
        $options .= '"watchCSS": ' . (boolval(esc_attr($cgrid_options['cgrid_watchCSS'])) ? "true" : "false") . ',';
        $options .= '"dragThreshold": "' . esc_attr($cgrid_options['cgrid_dragThreshold']) . '",';
        $options .= '"selectedAttraction": "' . esc_attr($cgrid_options['cgrid_selectedAttraction']) . '",';
        $options .= '"friction": "' . esc_attr($cgrid_options['cgrid_friction']) . '",';
        $options .= '"freeScrollFriction": "' . esc_attr($cgrid_options['cgrid_freeScrollFriction']) . '",';
        
        // Images
        $options .= '"imagesLoaded": "true",';
        $options .= '"lazyLoad": "false",';

        // Setup
        $options .= '"cellSelector": ".grid-cell",';
        $options .= '"initialIndex": 0,';
        $options .= '"accessibility": "true",';
        $options .= '"setGallerySize": ' . (boolval(esc_attr($cgrid_options['cgrid_setGallerySize'])) ? "true" : "false") . ',';
        $options .= '"resize": ' . (boolval(esc_attr($cgrid_options['cgrid_resize'])) ? "true" : "false") . ',';

        // Cell
        $options .= '"cellAlign": "' . esc_attr($cgrid_options['cgrid_cellAlign']) . '",';
        $options .= '"contain": ' . (boolval(esc_attr($cgrid_options['cgrid_contain'])) ? "true" : "false") . ',';
        $options .= '"percentPosition": ' . (boolval(esc_attr($cgrid_options['cgrid_percentPosition'])) ? "true" : "false") . ',';

        // UI
        $options .= '"prevNextButtons": ' . (boolval(esc_attr($cgrid_options['cgrid_prevNextButtons'])) ? "true" : "false") . ',';
        $options .= '"pageDots": ' . (boolval(esc_attr($cgrid_options['cgrid_pageDots'])) ? "true" : "false");

    $options .= ' }\' ';

    // Dynamic style 
    $ds_minHeight = intval(esc_attr($cgrid_options['cgrid_minHeight']));
    $ds_maxHeight = intval(esc_attr($cgrid_options['cgrid_maxHeight']));

    $style = "<style>";
    $style .=  ".cinza_grid-".$grid_id." {
                    height: ". ( ($ds_minHeight + $ds_maxHeight) / 2) ."px; /* Temporary while it loads, removed with jQuery */
                    opacity: 0;
                    overflow: hidden; /* Temporary while it loads, removed with jQuery */
                }
                
                .cinza_grid-".$grid_id." .grid-cell .grid-cell-image {
                    object-fit: ". esc_attr($cgrid_options['cgrid_imgFit']) .";
                }";

    $dynamic_minHeight = 'auto';
    $dynamic_maxHeight = 'auto';
    if ($ds_minHeight > 0) {$dynamic_minHeight = $ds_minHeight ."px";}
    if ($ds_maxHeight> 0) {$dynamic_maxHeight = $ds_maxHeight ."px";}
    $style .=  ".cinza_grid-".$grid_id.", 
                .cinza_grid-".$grid_id." .flickity-viewport, 
                .cinza_grid-".$grid_id." .grid-cell, 
                .cinza_grid-".$grid_id." .grid-cell .grid-cell-image {
                    min-height: ". $dynamic_minHeight .";
                    max-height: ". $dynamic_maxHeight .";
                }";

    if (intval(esc_attr($cgrid_options['cgrid_fullWidth'])) > 0) {
        $style .=  ".cinza_grid-".$grid_id." {
                        width: 100vw;
                        position: relative;
                        left: 50%;
                        right: 50%;
                        margin-left: -50vw;
                        margin-right: -50vw;
                    }";
    }

    if(!empty($cgrid_static['cgrid_static_overlay'])) {
        $style .=  ".cinza_grid-".$grid_id." .grid-cell:after {
                        content: '';
                        position: absolute;
                        display: block;
                        top: 0;
                        bottom: 0;
                        width: 100%;
                        height: 100%;
                        background: ". $cgrid_static['cgrid_static_overlay'] .";
                        z-index: 1;
                    }";
    }
    $style .= "</style>";

    // Output
    $o = '
    <h2>Filter</h2>
    <div id="filters" class="button-group">
      <button class="button is-checked" data-filter="*">show all</button>
      <button class="button" data-filter="yellow">yellow</button>
      <button class="button" data-filter="blue">blue</button>
      <button class="button" data-filter="purple">purple</button>
    </div>
    
    <h2>Sort</h2>
    <div id="sorts" class="button-group">
      <button class="button is-checked" data-sort-by="original-order">original order</button>
      <button class="button" data-sort-by="title">title</button>
      <button class="button" data-sort-by="color">color</button>
    </div>
    
    <div class="grid">
      <div class="element-item transition metal " data-category="transition">
        <h3 class="title">One</h3>
        <p class="color">Yellow</p>
      </div>
      <div class="element-item metalloid " data-category="metalloid">
        <h3 class="title">Two</h3>
        <p class="color">Blue</p>
      </div>
      <div class="element-item post-transition metal " data-category="post-transition">
        <h3 class="title">Three</h3>
        <p class="color">Purple</p>
      </div>
    </div>
    
    '. $style;
    return $o;
}