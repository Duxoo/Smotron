$(document).ready(function() {
	/*var params = window
		.location
		.search
		.replace('?','')
		.split('&')
		.reduce(
			function(p,e){
				var a = e.split('=');
				p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
				return p;
			},
			{}
		);
	if(typeof(params['ref']) != "undefined"){
		console.log(params['ref']);
	}*/

    $('.sm-upper-tabs li').click(function() {
        if(!$(this).hasClass('seleted')) {
            $('.sm-upper-tabs li').removeClass('selected');
            $(this).addClass('selected');
            $('.sm-tab-content').hide();
            $('.sm-tab-content[data-tab="'+$(this).attr('data-tab')+'"]').show();
        }
    });
	$(window).scroll(function() {
		var position = $(window).scrollTop();
		if(position >= 60) {
			$('.header').addClass('fixed');
			$('.up-button').removeClass('hidden');
		}
		else {
			$('.header').removeClass('fixed');
			$('.up-button').addClass('hidden');
		}
	});
	$(window).scroll();

	$(window).resize(function() {
		var width = $(window).width();
		if(width < 992) {
			$('.data-content').css('max-width', '1280px');
		}
		else {
			if(width > 1280) {
				width = 1280;
			}
			$('.data-content').css('max-width', (width-295)+'px');
		}
	});
	$(window).resize();

	$('.up-button').click(function() {
		$("body,html").animate({
			scrollTop:0
		}, 500);
		return false;
	});

	$('.side-menu-deploy').click(function() {
		var menu = $(this).attr('data-menu');
		$('.side-menu[data-menu="'+menu+'"]').addClass('opened');
		$('.side-menu-overlay').addClass('deployed').addClass('non-opaque');
	});

	$('.side-menu-close').click(function() {
		$(this).closest('.side-menu').removeClass('opened');
		$('.side-menu-overlay').removeClass('non-opaque').delay(200).removeClass('deployed');
	});

	$('.side-menu-overlay').click(function() {
		$('.side-menu').removeClass('opened');
		$(this).removeClass('non-opaque').delay(200).removeClass('deployed');
	});

	$('.sm-tabs-full li').live('click', function() {
		if(!$(this).hasClass('selected')) {
			var block = $(this).closest('.sm-tab-block').attr('data-block');
			var item = $(this).attr('data-block');
			var level = parseInt($(this).closest('.sm-tab-block').attr('data-level'));
			$('.'+block).hide();
			$('.'+block+'[data-block="'+item+'"]').show();
			$(this).parent().find('li').removeClass('selected');
			$(this).addClass('selected');

			var mobileTab = $(this).closest('.sm-tab-block').find('.sm-tabs-mobile-options li[data-block="'+item+'"]');
			var mobileSel = $(this).closest('.sm-tab-block').find('.sm-tabs-mobile-selection');
			mobileSel.find('.sm-tabs-mobile-selection-text').text(mobileTab.text()).attr('data-block', mobileTab.attr('data-block'));
			$(this).closest('.sm-tab-block').find('.sm-tabs-mobile-options li').show();
			mobileTab.hide();

			var hash = window.location.hash.replace('#', '');
			hash = hash.split('/');
			hash[level-1] = item;
			var updatedHash = new Array();
			$.each(hash, function(index, value) {
				if(index < level) {
					updatedHash[index] = value;
				}
			});
			window.location.hash = updatedHash.join('/');
		}
		var async_url = $(this).attr('data-ajax');
		if(async_url && !$(this).attr('data-loaded')) {
			var block = $(this).closest('.sm-tab-block').attr('data-block');
			var item = $(this).attr('data-block');
			$(this).attr('data-loaded', '1');
			$('.'+block+'[data-block="'+item+'"]').find('.ajax-content').load(async_url, function() {
				$(window).resize();
			});
		}
		else {
			$(window).resize();
		}
	});

	$('.sm-tabs-mobile-options li').live('click', function() {
		var item = $(this).attr('data-block');
		$(this).closest('.sm-tab-block').find('.sm-tabs-full li[data-block="'+item+'"]').trigger('click');
	});

	$(document).click(function(e) {
		var target = $(e.target);
		if(target.hasClass('sm-tabs-mobile-selection-text') || target.hasClass('sm-tabs-mobile-selection-arrow')) {
			target = target.parent();
		}
		if(target.hasClass('sm-tabs-mobile-selection')) {
			if(target.hasClass('opened')) {
				target.removeClass('opened');
				target.find('.sm-tabs-mobile-selection-arrow').removeClass('arrow-up-white').addClass('arrow-down-white');
				target.parent().find('.sm-tabs-mobile-options').hide();
			}
			else {
				$('.sm-tabs-mobile-selection').removeClass('opened');
				$('.sm-tabs-mobile-selection .sm-tabs-mobile-selection-arrow').removeClass('arrow-up-white').addClass('arrow-down-white');
				$('.sm-tabs-mobile-options').hide();
				target.addClass('opened');
				target.find('.sm-tabs-mobile-selection-arrow').removeClass('arrow-down-white').addClass('arrow-up-white');
				target.parent().find('.sm-tabs-mobile-options').show();
			}
		}
		else {
			$('.sm-tabs-mobile-selection').removeClass('opened');
			$('.sm-tabs-mobile-selection .sm-tabs-mobile-selection-arrow').removeClass('arrow-up-white').addClass('arrow-down-white');
			$('.sm-tabs-mobile-options').hide();
		}
	});

	// HASH LOADER
	var hash = window.location.hash.replace('#', '');
	if(hash.length > 0) {
		hash = hash.split('/');
		$.each(hash, function(index, value) {
			var level = index + 1;
			if($('.sm-tab-block[data-level="'+level+'"]').length) {
				$('.sm-tab-block[data-level="'+level+'"]').find('.sm-tabs-full li[data-block="'+value+'"]').trigger('click');
			}
		});
	}
	$('.sm-tabs-full li.selected').each(function(index) {
		var async_url = $(this).attr('data-ajax');
		if(async_url && !$(this).attr('data-loaded')) {
			$(this).trigger('click');
		}
	});
});