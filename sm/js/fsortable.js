(function( $ ) {
	$.fn.fSortable = function(callback) {
		var table = $(this);
		table.find('tbody').sortable( { 
			items: "> tr",
			handle: ".icon16.sort",
			update: function(event, ui) {
				var itemsArray = new Array();
				table.find('tbody tr').each(function(index) {
					itemsArray.push($(this).attr('data-item'));
				});
				callback(itemsArray);
			}
		});
	};
})(jQuery);