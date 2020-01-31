(function($){

	$(document).on('click', '[data-toggle="filters"]', function(){
		var target = $($(this).attr('data-target'));
		target.toggleClass('show');
		target.find('.dropdown-menu').toggleClass('show');
	});
	
	$(document).on('click', function(e){
		if ( ! $(e.target).closest('[data-toggle="filters"]').length ) {
			$('#filters.dropdown').removeClass('show');
			$('#filters .dropdown-menu').removeClass('show');
		}
	});
	
	$(document).on('facetwp-refresh', function(e) {
		$('.facetwp-template').addClass('facet-loading');
		var params = FWP.build_query_string();
		if ( params != '' ) {
			$('html, body').animate({ scrollTop: $('.facetwp-template').offset().top - 200 }, 1000 );
		}
	});
	
	$(document).on('facetwp-loaded', function() {
		$('.facetwp-template').removeClass('facet-loading');
	});
	
	$('.gform_continue').click(function(){
		var group = $(this).closest('.gform_fields');
		$('html, body').animate({ scrollTop: group.next().offset().top - 100 }, 1000 );
	});

	$('.panel').on('show.bs.collapse', function (e) {
		e.stopPropagation();
		$(e.currentTarget).find('.panel-icon i').removeClass('fa-plus').addClass('fa-minus');
	}).on('hide.bs.collapse', function (e) {
		e.stopPropagation();
		$(e.currentTarget).find('.panel-icon i').removeClass('fa-minus').addClass('fa-plus');
	});
	
	// skewed layout
	skewLayout( '.skew-layout--left', 'left' );
	skewLayout( '.skew-layout--right', 'right' );
	$(window).resize(function() {
		skewLayout( '.skew-layout--left', 'left' );
		skewLayout( '.skew-layout--right', 'right' );
	});
	function skewLayout( el, direction ) {
		var $row = $( el );		
		if ( $row.length > 0 ) {
			var windowWidth = $(window).width(),
				rowWidth = parseInt( site.rowWidth ),
				moduleMargins = parseInt( site.moduleMargins );
				
			if ( windowWidth > ( rowWidth + ( moduleMargins * 2 ) ) ) {
				$row.find('.fl-row-content').css( 'margin-' + direction, ( windowWidth - rowWidth ) / 2 );
			} else {
				$row.find('.fl-row-content').css( 'margin-' + direction, moduleMargins );
			}
			
		}
	}
	
})(jQuery);