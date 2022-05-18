<?php
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Register CPT: cinza_grid
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action( 'init', 'cgrid_register_post_type' );
function cgrid_register_post_type() {
	$labels = [
		'name'                     => esc_html__( 'Cinza Grids', 'your-textdomain' ),
		'singular_name'            => esc_html__( 'Grid', 'your-textdomain' ),
		'add_new'                  => esc_html__( 'Add New', 'your-textdomain' ),
		'add_new_item'             => esc_html__( 'Add new Grid', 'your-textdomain' ),
		'edit_item'                => esc_html__( 'Edit Grid', 'your-textdomain' ),
		'new_item'                 => esc_html__( 'New Grid', 'your-textdomain' ),
		'view_item'                => esc_html__( 'View Grid', 'your-textdomain' ),
		'view_items'               => esc_html__( 'View Grids', 'your-textdomain' ),
		'search_items'             => esc_html__( 'Search Grids', 'your-textdomain' ),
		'not_found'                => esc_html__( 'No Grids found', 'your-textdomain' ),
		'not_found_in_trash'       => esc_html__( 'No Grids found in Trash', 'your-textdomain' ),
		'parent_item_colon'        => esc_html__( 'Parent Grid:', 'your-textdomain' ),
		'all_items'                => esc_html__( 'All Grids', 'your-textdomain' ),
		'archives'                 => esc_html__( 'Grid Archives', 'your-textdomain' ),
		'attributes'               => esc_html__( 'Grid Attributes', 'your-textdomain' ),
		'insert_into_item'         => esc_html__( 'Insert into Grid', 'your-textdomain' ),
		'uploaded_to_this_item'    => esc_html__( 'Uploaded to this Grid', 'your-textdomain' ),
		'featured_image'           => esc_html__( 'Featured image', 'your-textdomain' ),
		'set_featured_image'       => esc_html__( 'Set featured image', 'your-textdomain' ),
		'remove_featured_image'    => esc_html__( 'Remove featured image', 'your-textdomain' ),
		'use_featured_image'       => esc_html__( 'Use as featured image', 'your-textdomain' ),
		'menu_name'                => esc_html__( 'Cinza Grid', 'your-textdomain' ),
		'filter_items_list'        => esc_html__( 'Filter Grids list', 'your-textdomain' ),
		'filter_by_date'           => esc_html__( '', 'your-textdomain' ),
		'items_list_navigation'    => esc_html__( 'Grids list navigation', 'your-textdomain' ),
		'items_list'               => esc_html__( 'Grids list', 'your-textdomain' ),
		'item_published'           => esc_html__( 'Grid published', 'your-textdomain' ),
		'item_published_privately' => esc_html__( 'Grid published privately', 'your-textdomain' ),
		'item_reverted_to_draft'   => esc_html__( 'Grid reverted to draft', 'your-textdomain' ),
		'item_scheduled'           => esc_html__( 'Grid scheduled', 'your-textdomain' ),
		'item_updated'             => esc_html__( 'Grid updated', 'your-textdomain' ),
		'text_domain'              => esc_html__( 'your-textdomain', 'your-textdomain' ),
	];
	
	$args = [
		'label'               => esc_html__( 'Grids', 'your-textdomain' ),
		'labels'              => $labels,
		'description'         => '',
		'public'              => true,
		'hierarchical'        => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => false,
		'show_in_rest'        => true,
		'query_var'           => false,
		'can_export'          => true,
		'delete_with_user'    => false,
		'has_archive'         => false,
		'rest_base'           => '',
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-admin-generic',
		'menu_position'       => '',
		'capability_type'     => 'post',
		'supports'            => ['title', 'revisions', 'custom-fields'],
		'taxonomies'          => [],
		'rewrite'             => ['with_front' => false],
	];

	register_post_type( 'cinza_grid', $args );
}

add_filter( 'manage_cinza_grid_posts_columns', 'set_custom_edit_cinza_grid_columns' );
function set_custom_edit_cinza_grid_columns($columns) {
    $columns['shortcode'] = __( 'Shortcode', 'your_text_domain' );
    return $columns;
}

add_action( 'manage_cinza_grid_posts_custom_column' , 'custom_cinza_grid_column', 10, 2 );
function custom_cinza_grid_column( $column, $post_id ) {
	switch ( $column ) {
		case 'shortcode' :
			echo('[cinza_grid id="'. esc_attr($post_id) .'"]');
			break;
	}
}

add_filter ( 'manage_cinza_grid_posts_columns', 'add_cinza_grid_columns', 99, 99 );
function add_cinza_grid_columns ( $columns ) {
	unset($columns['title']);
	unset($columns['shortcode']);
	unset($columns['date']);
	unset($columns['rank_math_seo_details']);
	unset($columns['rank_math_title']);
	unset($columns['rank_math_description']);

	return array_merge ( $columns, array ( 
		'title' => __ ('Title'),
		'shortcode' => __ ( 'Shortcode' ),
		'date' => __('Date')
	) );
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add Meta Boxes
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action( 'add_meta_boxes', 'cgrid_add_fields_meta_boxes', 99, 99 );
function cgrid_add_fields_meta_boxes() {
	add_meta_box('cgrid-options', 'Options', 'cgrid_meta_box_options', 'cinza_grid', 'normal', 'default');
	add_meta_box('cgrid-documentation', 'Documentation', 'cgrid_meta_box_doc', 'cinza_grid', 'side', 'default');
	remove_meta_box( 'rank_math_metabox' , 'cinza_grid' , 'normal' ); 
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Meta Box: _cgrid_options
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_meta_box_options( $post ) {
	global $post;
    $cgrid_options = get_post_meta( $post->ID, '_cgrid_options', true );
	wp_nonce_field( 'cgrid_meta_box_nonce', 'cgrid_meta_box_nonce' );
	
	// Set default values
	$temp_minHeight = 300;
	$temp_fullWidth = 0;
	
	// Get saved values
	if ( !empty($cgrid_options) ) {
		$temp_minHeight = esc_attr($cgrid_options['cgrid_minHeight']);
		$temp_fullWidth = esc_attr($cgrid_options['cgrid_fullWidth']);
	}

	?>
	<table id="cgrid-optionset" width="100%">
		<thead>
			<tr>
				<td class="cgrid-options" colspan="3">
					<p>Size</p>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
            	<td class="cgrid-options col-1">
                    <label for="cgrid_minHeight">minHeight</label>
				</td>
				<td class="cgrid-options col-2">
                    <input type="text" name="cgrid_minHeight" id="cgrid_minHeight" class="cgrid-minHeight" value="<?php echo esc_attr($temp_minHeight); ?>" /> <span>px</span>
                </td>
                <td class="cgrid-options col-3">
					Manually sets the Grid min-height in pixels. <em>Set value to zero to disable this option.</em>
                </td>
            </tr>
			<tr>
				<td class="cgrid-options col-1">
					<label for="cgrid_fullWidth">fullWidth</label>
				</td>
				<td class="cgrid-options col-2">
					<input type="checkbox" name="cgrid_fullWidth" id="cgrid_fullWidth" class="widefat cgrid-fullWidth" value="1" <?php checked('1', $temp_fullWidth); ?> />
				</td>
                <td class="cgrid-options col-3">
					Force full width.
                </td>
			</tr>
		</tbody>
	</table>
    <?php
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Meta Box: _cgrid_doc
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_meta_box_doc( $post ) {
	?><a href="https://isotope.metafizzy.co/options.html" target="_blank" class="preview button">Isotope documentation</a><?php
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Save Meta Boxes
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('save_post', 'cgrid_save_fields_meta_boxes');
function cgrid_save_fields_meta_boxes($post_id) {
	if ( ! isset( $_POST['cgrid_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['cgrid_meta_box_nonce'], 'cgrid_meta_box_nonce' ) )
		return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;

	// Save _cgrid_options
	$cgrid_minHeight = sanitize_text_field($_POST['cgrid_minHeight']);
	$cgrid_fullWidth = sanitize_key($_POST['cgrid_fullWidth']);

	$new = array();
	$new['cgrid_minHeight'] = empty($cgrid_minHeight) ? '0' : wp_strip_all_tags($cgrid_minHeight);
	$new['cgrid_fullWidth'] = $cgrid_fullWidth ? '1' : '0';

	update_post_meta($post_id, '_cgrid_options', $new);
}

?>