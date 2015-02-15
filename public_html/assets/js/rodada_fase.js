$( "button.action.calcula_rodada" ).click(
			function(event) {
							event.preventDefault();
							if ( check_for_change() )
							{
								if ( $( this ).attr( "rodada_fase_id" ) )
								{
									urlAtualiza = '/campeonato_versao_classificacao/classificar/' + $( this ).attr( "rodada_fase_id" );
									$( 'div#dialog_atualiza' ).remove();
									$new_iframe = $( "<div id='dialog_atualiza'><iframe id='iframe_atualiza'></iframe></div>" ).appendTo( document.body );
									
									$( 'iframe#iframe_atualiza' ).attr( 'src', urlAtualiza ).attr( 'height', '580' ).attr( 'width', '783' );
									$( "div#dialog_atualiza:ui-dialog" ).dialog( "destroy" );
									$( "div#dialog_atualiza" ).dialog(
																		{
																			 resizable: false
																			,height:600
																			,width: 800
																			,modal: true
																			,buttons:	{
																							"Fechar": function(){
																													$( "div#dialog_atualiza" ).dialog( "close" );
																													$( 'div#dialog_atualiza' ).remove();
																												}
																						}
																		}
																	);
								}
								else
								{
									show_warning( "Não há rodada selecionada para atualizar." );
								}
							}
						}
);

$( "button.action.atualiza_rodada" ).click(
		function(event) {
						event.preventDefault();
						if ( check_for_change() )
						{
							if ( $( this ).attr( "rodada_fase_id" ) )
							{
								urlAtualiza = '/integracao/rodada_fase/' + $( this ).attr( "rodada_fase_id" );
								$( 'div#dialog_atualiza' ).remove();
								$new_iframe = $( "<div id='dialog_atualiza'><iframe id='iframe_atualiza'></iframe></div>" ).appendTo( document.body );
								
								$( 'iframe#iframe_atualiza' ).attr( 'src', urlAtualiza ).attr( 'height', '580' ).attr( 'width', '783' );
								$( "div#dialog_atualiza:ui-dialog" ).dialog( "destroy" );
								$( "div#dialog_atualiza" ).dialog(
																	{
																		 resizable: false
																		,height:600
																		,width: 800
																		,modal: true
																		,buttons:	{
																						"Fechar": function(){
																												$( "div#dialog_atualiza" ).dialog( "close" );
																												$( 'div#dialog_atualiza' ).remove();
																											}
																					}
																	}
																);
							}
							else
							{
								show_warning( "Não há rodada selecionada para atualizar." );
							}
						}
					}
);
