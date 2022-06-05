( function($) {
    $(document).ready(function() {
        $('.sm-upper-tabs li').click(function() {
            if(!$(this).hasClass('seleted')) {
                $('.sm-upper-tabs li').removeClass('selected');
                $(this).addClass('selected');
                $('.sm-tab-content').hide();
                $('.sm-tab-content[data-tab="'+$(this).attr('data-tab')+'"]').show();
            }
        });

        function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                vars[key] = value;
            });
            return vars;
        }

        function setReferralCookie() {
            var ref = getUrlVars()['ref'];
            console.log(ref);
        }


    });
})(jQuery);