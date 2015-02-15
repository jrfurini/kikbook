(function($){})(window.jQuery);
$('td.chutes').click	(
			function(){
						url = '/chute/dialog/' + $( this ).attr( 'rodada_id' ) + '/' + $( this ).attr( 'pessoa_id' );
						v_window_height_size = $( window ).outerHeight( true );

//						$( '#chute_modal' ).attr( 'style', 'height:' + v_window_height_size - ( v_window_height_size * 0.4 ) );

						$( 'iframe#iframe_chute' ).attr( 'src', url ).attr( 'height', v_window_height_size - ( v_window_height_size * 0.4 ) ).attr( 'width', '768' );
					}
		);
$(window).load(function() {
	$( '#chute_modal' ).modal(
			{
				 backdrop: false
				,keyboard: true
				,show: false
			}
		);
});
