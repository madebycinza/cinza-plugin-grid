jQuery(document).ready(function($) {

    // Add new slide
    $( '#add-slide' ).on('click', function() {
        var row = $( '.empty-row.screen-reader-text' ).clone(true);
        row.removeClass( 'empty-row screen-reader-text' );
        row.insertBefore( '#cslider-fieldset tbody>tr:last' );
        
        // Increments global count
		var count = document.getElementById("cslider_id_count");
		count.value = ++count.value;
		
		// Attributes slide ID from count 
		var slide = row.find( ".cslider_cell_id" );
		slide.val("slider-cell-" + count.value);
		
        return false;
    });
  
    // Delete slide
    $( '.delete-slide' ).on('click', function() {
        if (confirm('Are you sure?')) {
            $(this).parents('tr').remove();
            return false;
        }
    });

    // Remove slide image
    $(".button.remove-img").on('click', function(){
        $(this).siblings(".cslider-img-url").val("");
        $(this).siblings(".cslider-img-id").val("");
        $(this).parent().parent().parent().find(".cslider-img-preview-inner").css("background-color", "transparent");
        $(this).parent().parent().parent().find(".cslider-img-preview-inner").css("background-image", "url()");
    });

    // Sorting
    $( function() {
        $( ".cslider-fieldset-sortable tbody" ).sortable();
    } );

    // Enqueue Media
    $('.cslider-img-btn').on('click', function(event) {
        open_wp_media_modal(
            event, 
            $(this).siblings(".cslider-img-url"), 
            $(this).siblings(".cslider-img-id"),
            $(this).parent().parent().parent().find(".cslider-img-preview-inner")
        );
    });
    
    function open_wp_media_modal(event, img_url, img_id, img_preview) {
        var logo_selection;
        
        // Befault action of the button event will not be triggered
        event.preventDefault();
        
        // If the upload object has already been created, reopen the dialog
        if (logo_selection) {
            logo_selection.open();
            return;
        }
        // Extend the wp.media object
        logo_selection = wp.media.frames.file_frame = wp.media({
            title: 'Select media',
            button: {
            text: 'Select media'
        }, multiple: false });
        
        // When a file is selected, grab the URL and set it as the text field's value
        logo_selection.on('select', function() {
            var attachment = logo_selection.state().get('selection').first().toJSON();
            jQuery(img_url).val(attachment.url);
            jQuery(img_id).val(attachment.id);
            jQuery(img_preview).css({ 'background-image' : 'url('+ attachment.url +')', 'background-color' : '#f6f7f7' });
        });
        
        // Open the upload dialog
        logo_selection.open();
    }

});

function cslider_copy_shortcode(elementID) {
	var copyText = document.getElementById(elementID);
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	navigator.clipboard.writeText(copyText.value);
}