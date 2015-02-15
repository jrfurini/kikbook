$( '.box-sel-rodada-button-left, .box-sel-rodada-button-right, .box-sel-rodada' ).click	(
		function(event){
					event.preventDefault();
					if ( check_for_change() )
					{
						urlPage = $( this ).children( "a" ).attr( 'href' );
						window.open( urlPage, "_self" );
					}
				}
	);

$( 'dd#selecao_campeonato button.checkin-camp' ).click (
		function(event){
			event.preventDefault();
			if ( check_for_change() )
			{
				urlPage = $( this ).attr( 'address' );
				window.open( urlPage, "_self" );
			}
		}
	);

$( 'dd#selecao_campeonato button.checkout-camp' ).click (
		function(event){
			event.preventDefault();
			if ( check_for_change() )
			{
				urlPage = $( this ).attr( 'address' );

				$( "#confirm-checkout-camp" ).dialog({
					resizable: false,
					width: 400,
					height: 170,
					modal: true,
					title: "Confirmação",
					buttons: {
						"Sair do Campeonato": function() {
							$( this ).dialog( "close" );
							window.open( urlPage, "_self" );
						},
						"Continuar no Campeonato": function() {
							$( this ).dialog( "close" );
						}
					}
				});
				
				
			}
		}
	);

$( "button.do-pers-clas, button.simule" ).click(
		function(event){
			event.preventDefault();
			if ( check_for_change() )
			{
				$ri = $( "#pers-clas-rod-ini" ).val();
				$rf = $( "#pers-clas-rod-fin" ).val();
				$mc = $( "#pers-clas-meu-chute" ).children( "button.active" ).attr( 'value' );
				
				url = "/classificacao/personalizada/" + $ri + "/" + $rf + "/null/" + $mc;
				window.open( url, "_self" );
			}
		}
	);


$( "a.kik_extrato" ).click(
		function(event){
			event.preventDefault();
			if ( check_for_change() )
			{
				$dados = $( 'form' ).serialize();

				$.ajax({
					 url: "/kik_movimento/get_movto"
					,dataType: 'html'
					,type: 'post'
					,data: $dados
					,error: function( jqXHR, textStatus, errorThrown )	{
											// error
											$body = $( "div#kikExtrato" ).children( "div.modal-body" );
											$( $body ).html( "Não conseguimos obter o seu extrato. Por favor, tente mais tarde." );
											}
					,success: function( ret ) {
												// OK
												$body = $( "div#kikExtrato" ).children( "div.modal-body" );
												$( $body ).html( ret );
											}
						});
			}
		}
	);

