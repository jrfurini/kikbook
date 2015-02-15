function resize_areas()
{
	v_window_width_size = $( window ).width();
	v_window_height_size = $( window ).outerHeight( true );

	$header = $( "header" );
	$topmenu = $( "header nav.topmenu" );
	$busca = $( "header section.busca" );
	$acoes = $( "div.pre-acoes" );
	$footer = $( "footer" );

	v_height		=	0;
	v_pos_content	=	0;
	if ( $header.css( 'display' ) == 'block' )
	{
		v_height = v_height + $topmenu.outerHeight( true );
		v_height = v_height + $busca.outerHeight( true );
		v_pos_content = v_pos_content + $topmenu.outerHeight( true );
		v_pos_content = v_pos_content + $busca.outerHeight( true );
	}
	if ( $footer.css( 'display' ) == 'block' )
	{ 
		v_height = v_height + $footer.outerHeight( true );
	}
	
	v_height = v_height + $acoes.outerHeight( true );

//	alert(  v_height + ' top=' + $topmenu.outerHeight( true ) + ' bus=' +  $busca.outerHeight( true )  + ' foo=' + $footer.outerHeight( true ) + ' ac=' +  $acoes.outerHeight( true ) );

//	v_height	=	( v_height * 2 );
	h = parseInt( v_window_height_size - ( v_height + 33 ) );

//alert(  ' v_height=' + v_height + ' v_window_height_size=' + v_window_height_size );

	$( 'div.conteudo' ).css( 'top', v_pos_content + 'px' );
	
	$( 'aside nav' ).css( "max-height", h + 9 );
	$( 'div.controller_menu' ).css( "max-height", h );
	$( 'section#conteudo' ).css( "max-height", h + 26 );
	$( 'section#conteudo' ).css( "min-height", h + 26 );
	$( 'section#edicao' ).css( "max-height", h + 11 );

	/*
	""                       width: 254px;
	"nav#left-content"       width: 925px;
	"nav#left-content"       width: 650px;
	"nav#center-content"     width: 275px;
	"nav#right-content"      width: 80px;
	*/

	if ( v_window_width_size > 980 )
	{
		v_width_diff = ( v_window_width_size - 985 );
		$( 'div.sel-campeonato' ).css( "width", 271 + v_width_diff );
		$( 'div.left-content' ).css( "width",   650 + v_width_diff );
		$( 'div.left-content-full' ).css( "width",   769 + v_width_diff );
		$( 'div.horizontal-ad' ).css( "margin-left", 504 + v_width_diff );
	}
	if ( v_window_width_size == 980 )
	{
		$( 'div.sel-campeonato' ).css( "width", 200 );
		$( 'div.left-content' ).css( "width",   650 );
		$( 'div.left-content-full' ).css( "width",   769 );
		$( 'div.horizontal-ad' ).css( "margin-left", 504 );
	}
}
$(window).load(function() {
	initialze_page();
	resize_areas();
});

$(window).resize(function()
{
	resize_areas();
});
