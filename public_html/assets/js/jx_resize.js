
$(window).load(function() {
	v_window_width_size = $( window ).width();
	v_window_height_size = $( window ).height();

	v_height	=	( $( "header nav.topmenu" ).height() +
					  $( "header section.busca" ).height() +
					  $( "section.acoes" ).height() +
					  $( "footer" ).height() ) *
					2;
	h = parseInt( v_window_height_size - v_height );
	
	$( 'aside nav' ).css( "max-height", h );
	$( 'div.controller_menu' ).css( "max-height", h );
	$( 'section#conteudo' ).css( "max-height", h + 35 );
	$( 'section#edicao' ).css( "max-height", h + 35 );
	
	initialze_page();
});

$(window).resize(function() {
	v_window_width_size = $( window ).width();
	v_window_height_size = $( window ).height();

	v_height	=	( $( "header nav.topmenu" ).height() +
					  $( "header section.busca" ).height() +
					  $( "section.acoes" ).height() +
					  $( "footer" ).height() ) *
					2;
	h = parseInt( v_window_height_size - v_height );

	$( 'aside nav' ).css( "max-height", h );
	$( 'div.controller_menu' ).css( "max-height", h );
	$( 'section#conteudo' ).css( "max-height", h + 35 );
	$( 'section#edicao' ).css( "max-height", h + 35 );
});
