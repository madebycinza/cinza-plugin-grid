jQuery(document).ready(function($) {

});

function cgrid_copy_shortcode(elementID) {
	var copyText = document.getElementById(elementID);
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	navigator.clipboard.writeText(copyText.value);
}