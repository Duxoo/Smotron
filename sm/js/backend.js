$(document).ready(function() {
	$('.sm-upper-tabs li').click(function() {
		if(!$(this).hasClass('seleted')) {
			$('.sm-upper-tabs li').removeClass('selected');
			$(this).addClass('selected');
			$('.sm-tab-content').hide();
			$('.sm-tab-content[data-tab="'+$(this).attr('data-tab')+'"]').show();
		}
	});
});