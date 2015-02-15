function set_sel( $p_area )
{
	$.ajax({
		 url: '/painel/set_sel/' + $p_area
		,dataType: 'json'
		,type: 'post'
			});
}

function contract_all()
{
	$obj_expanded = $( 'div.painel ol li div.expand' );
	$( $obj_expanded ).animate( { width: '225px' } );
	$( $obj_expanded ).removeClass( "expand" ).addClass( "contract" );
	$( 'div.painel div.sel-button button.btn.btn-primary' ).children( "i" ).removeClass( "icon-white" );
	$( 'div.painel div.sel-button button.btn.btn-primary' ).removeClass( "btn-primary" );

	$( "div.painel ol li div.clas-painel div.clas-big" ).hide();
	$( "div.painel ol li div.clas-painel div.clas-small" ).show();

	$( "table.kik-micro-calend td.kik-micro-calend-header td.day-h, div.kik-micro-calend-jogos td.gm.img.camp, div.kik-micro-calend-jogos td.gm.img.camp, div.kik-micro-calend-jogos td.gm.sig.ar, div.kik-micro-calend-jogos td.gm.clock, td.kik-micro-calend-ctrl" ).hide();
}
function expand( $p_area )
{
	$obj_selected = $( 'div.painel ol li div.area.' + $p_area );

	v_setsel_timer = setTimeout( function() 	{	set_sel( $p_area );
												}, ( 2000 ) );	
	
	if ( $p_area == 'estatistica' )
	{
		$( 'div.painel div.sel-button button.btn.btn-primary' ).children( "i" ).removeClass( "icon-white" );
		$( 'div.painel div.sel-button button.btn.btn-primary' ).removeClass( "btn-primary" );
//		window.location.assign( "#estatistica" );
        $('html, body').animate({ scrollTop: $("#estatistica").offset().top }, 1000);
	}
	else
	{
		contract_all();

		$( $obj_selected ).removeClass( "contract" ).addClass( "expand" ).show( 'slow' );
		
		if ( $p_area == 'clas-painel' )
		{
			$( "div.painel ol li div.clas-painel div.clas-small" ).hide();
			$( $obj_selected ).animate( { width: '500px' } );
			$( "div.painel ol li div.clas-painel div.clas-big" ).show();
			$( "div.painel ol li div.clas-painel div.clas-big" ).animate( { width: '500px' } );
		}
		else if ( $p_area == 'news' )
		{
			$( $obj_selected ).animate( { width: '500px' } );
			$( $obj_selected ).children( "iframe" ).animate( { width: '500px' } );
		}
		else if ( $p_area == 'calendar' )
		{
			$( $obj_selected ).animate( { width: '500px' } );
			$( "table.kik-micro-calend td.kik-micro-calend-header td.day-h, div.kik-micro-calend-jogos td.gm.img.camp, div.kik-micro-calend-jogos td.gm.img.camp, div.kik-micro-calend-jogos td.gm.sig.ar, div.kik-micro-calend-jogos td.gm.clock, td.kik-micro-calend-ctrl" ).show();
		}
		else
		{
			$( $obj_selected ).animate( { width: '500px' } );
		}
	}
	
	$( 'div.painel div.sel-button button.btn.' + $p_area ).addClass( "btn-primary" );
	$( 'div.painel div.sel-button button.btn.' + $p_area ).children( "i" ).addClass( "icon-white" );
}

$( 'div.painel div.sel-button button.btn.clas-painel' ).click	(
			function(event){
						event.preventDefault();
						expand( 'clas-painel' );
					}
		);

$( 'div.painel div.sel-button button.btn.news' ).click	(
			function(event){
						event.preventDefault();
						expand( 'news' );
					}
		);

$( 'div.painel div.sel-button button.btn.calendar' ).click	(
			function(event){
						event.preventDefault();
						expand( 'calendar' );
					}
		);

$( 'div.painel div.sel-button button.btn.estatistica' ).click	(
			function(event){
						event.preventDefault();
						expand( 'estatistica' );
					}
		);

$( 'div.painel div.area-horiz button.btn.topo' ).click	(
			function(event){
						event.preventDefault();
				        $('html, body').animate({ scrollTop: $("body").offset().top }, 1000);
					}
		);

$( 'div.painel div.area.calendar td.kik-micro-calend-header.detail td.calend-nav' ).click	(
			function(event){
						event.preventDefault();
				        $('html, body').animate({ scrollTop: $("body").offset().top }, 0);
					}
		);

$( 'td.kik-micro-calend-ctrl div.campeonatos button.sel-tipo-camp' ).click	(
			function(event){
						event.preventDefault();
						if ( check_for_change() )
						{
							tipo_camp = $( this ).attr( 'value' );
							window.open( '/painel/calendario/null/' + tipo_camp, "_self" );
						}
					}
		);

$( ' div.campo-borda' ).on( 'click', 'a.show_all', 
			function(event){
						showAllAutoComplete( this );
					}
	);

function do_filtrar( $obj ){
	var url = $( $obj ).attr( 'last_url' );
	var equipe = $( 'input#equipe' ).val();
	if ( !equipe ) { equipe = 'none' }
	var arena  = $( 'input#arena' ).val();
	if ( !arena ) { arena = 'none' }

	window.open( url + '/' + equipe + '/' + arena, "_self" );
}

$( "button.calendar-filtrar" ).click (
			function(event){
				event.preventDefault();
				do_filtrar( this );
			}
	);
$("input#equipe, input#arena").keydown(
			function(event){ 
					if ( event.which == 13 ) // Pressionou enter.
					{
						event.preventDefault();
						do_filtrar( $( "button.calendar-filtrar" ) );
					}
			}
	);

$( "button.calendar-filtrar-limpar" ).click (
			function(event){
				event.preventDefault();

				var url = $( this ).attr( 'last_url' );

				window.open( url + '/none/none', "_self" );
			}
	);
