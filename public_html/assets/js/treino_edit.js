// remap jQuery to $
(function($){})(window.jQuery);

$(document).ready(function (){
	function trocar_str_ate( $obj_str, $str, $new_str )
	{
		while ( $obj_str.indexOf( $str ) != -1 )
		{
//if ( $str == '##tab_id##' ) { alert( 'trocando para ' + $new_str );}
			$obj_str = $obj_str.replace( $str, $new_str );
		}
		
		return $obj_str;
	}

	// SELECAO DE EXERCICIO
	$accordion = 	$( "#exercicios_catalogo" ).accordion( {
															 collapsible: false
															,autoHeight: false
															,navigation: true
															,fillSpace: false
														});

	$accordion.css( 'max-height', '450px' ).css( 'min-height', '250px' );
	$accordion.on( 'dblclick', '#exercicios_catalogo_id', 
			function() {
							addExercicio( $( this ), 'new' );
						});

	if ( navigator.platform == 'iPad' )
	{
//		new webkit_draggable( $( "#exercicios_catalogo li" ), 	{
		new webkit_draggable( webkit_tools.$( 'exercicios_catalogo_id' ), 	{
			 revert : true
			,scroll : true
			});

	}
	else
	{
		$( "#exercicios_catalogo li" ).draggable({
			 appendTo: "body"
			,helper: "clone"
		});
	}
	// SELECAO DE EXERCICIO: fim



	// TABS - Sub treinos
	var $tab_title_input = $( "#tab_title"),
		$tab_content_input = $( "#treino_categoria_id" );
	var tab_counter = 0;
	var $tab_selected;
	var $tab_index_selected;
	var tab_title;

	var $tabs = $( "#tabs").tabs(	{
										 tabTemplate: "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close'>Remover o Treino</span></li>"
										,selected: 0
										,select: function( event, ui )	{
																			$tab_selected		=	$( "div#tabs-" + ui.index );
																			$tab_index_selected	=	ui.index;
//alert( 'tab.1=' + $tab_index_selected );
																		}
										,add: function( event, ui )	{
																		var tab_content = "Treino " + tab_counter;
																		var subTreinoHTML = $("#treino_sub_modelo").children( "div" ).html();
																		subTreinoHTML		=	trocar_str_ate( subTreinoHTML, '##tab_id##', tab_counter );

																		$( "div#tabs-" + tab_counter ).append( subTreinoHTML );

																		jx_set_input_edit_functions( "div#tabs-" + tab_counter );

																		if ( navigator.platform == 'iPad' )
																		{
																			/*	webkit_drop.add	(	webkit_tools.$('cart'),	{
																																 accept : ['catalog']
																																,onOver : function()	{
																																							webkit_tools.$('bluedroppable').addClass( 'ui-state-highlight' )
																																						}
																																,onDrop : function()	{
																																							webkit_tools.$( this ).find( ".placeholder" ).remove();
																																							webkit_tools.$( "<li></li>" ).text( ui.draggable.text() ).appendTo( this );
																																						}
																														});*/
																		}
																		else
																		{
																			$( "div#tabs-" + tab_counter + " ol#lista_de_exercicios" ).droppable	({
																						 activeClass: "ui-state-default"
																						,hoverClass: "ui-state-hover"
																						,accept: ":not(.ui-sortable-helper)"
																						,drop: function( event, ui ){
																														addExercicio( ui.draggable, 'new' );
																													}
																					}).sortable(	{
																										 items: "li:not(.placeholder)"
																										,placeholder: "ui-state-highlight"
																										,sort: function()	{
																																$( this ).removeClass( "ui-state-default" );
																															}
																										,stop: function()	{
																																// Ajusta sequencia de exibição.
																																$.each( $( 'li', this ).find( 'input[name^="treino_exercicio[seq_execucao]"]' ), function( key, value ){ value.setAttribute( 'value', key+1 ); } );
																																set_changed();
																															}
																									}).disableSelection();
																		}
																	}
									}
								);

	// Criar um novo treino (aba).
	function criaTreinoSub( treino_sub ) {
		tab_counter++;
		if ( treino_sub == null )
		{
			tab_title = "T " + tab_counter;
		}
		else
		{
			tab_title = treino_sub.cod;
		}
		$tab_index_selected	=	tab_counter;
//alert( 'tab.2=' + $tab_index_selected );
		$tabs.tabs( "add", "#tabs-" + tab_counter, tab_title );
		return tab_counter;
	}
	
	// Adiciona novo exercicio ao treino
	function addExercicio( $rows, $oper )
	{
		$tab_selected.find( "li.placeholder" ).remove();
		$ol_exerc_tab	=	$tab_selected.find( 'ol#lista_de_exercicios' );
		$ol_fields		=	$tab_selected.find( 'ol#fields_treino_sub' );

		if ( $oper == 'new' )
		{
			$new_li			=	$( "<li></li>" ).html( '' ). appendTo( $ol_exerc_tab );
			$html_exerc		=	$("div#exercicio_modelo").children( "li" ).html();
			$html_exerc		=	trocar_str_ate( $html_exerc, '##tab_id##', $tab_index_selected );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##exerc_title##', $rows.text() + '(' + $rows.attr( 'nome_grupo_corporal' ) + ')' );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##seq_execucao##', $new_li.index() + 1 );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##id##', '' );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##treino_sub_id##', $( "div#tabs-" + $tab_index_selected + ' input[name="treino_sub[id][' + $tab_index_selected + '][]"]' ).val() );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##exercicio_id##', $rows.attr( 'exercicio_id' ) );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##intensidade_peso_min##', '' );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##intensidade_peso_max##', '' );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##repeticao_min##', '' );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##repeticao_max##', '' );
			$html_exerc		=	trocar_str_ate( $html_exerc, '##intervalo_repeticao##', '' );
			
			$new_li.html( $html_exerc ).addClass( 'ui-state-default ui-draggable' ).attr( 'exercicio_id', $rows.attr( 'exercicio_id' ) );

			// ajusta a altura da área onde estão os li.
			h = $ol_fields.height();
			h = h + 57;
			$ol_fields.css( 'height', h + 'px' );

			set_changed();
			jx_set_input_edit_functions( "div#tabs-" + $tab_index_selected + ' ol#lista_de_exercicios [exercicio_id="' + $rows.attr( 'exercicio_id' ) + '"]' );
		}
		else
		{
			for ( rows_i=0; rows_i < $rows.length; rows_i++ )
			{
				$new_li			=	$( "<li></li>" ).html( '' ). appendTo( $ol_exerc_tab );
				$html_exerc		=	$("div#exercicio_modelo").children( "li" ).html();
				$html_exerc		=	trocar_str_ate( $html_exerc, '##tab_id##', $tab_index_selected );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##exerc_title##', $rows[ rows_i ].exercicio_nome + '(' + $rows[ rows_i ].grupo_corporal_nome + ')' );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##seq_execucao##', $rows[ rows_i ].seq_execucao/*$new_li.index() + 1*/ );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##id##', $rows[ rows_i ].id );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##treino_sub_id##', $rows[ rows_i ].treino_sub_id );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##exercicio_id##', $rows[ rows_i ].exercicio_id );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##intensidade_peso_min##', $rows[ rows_i ].intensidade_peso_min );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##intensidade_peso_max##', $rows[ rows_i ].intensidade_peso_max );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##repeticao_min##', $rows[ rows_i ].repeticao_min );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##repeticao_max##', $rows[ rows_i ].repeticao_max );
				$html_exerc		=	trocar_str_ate( $html_exerc, '##intervalo_repeticao##', $rows[ rows_i ].intervalo_repeticao );
				$new_li.html( $html_exerc ).addClass( 'ui-state-default ui-draggable' ).attr( 'exercicio_id', $rows[ rows_i ].exercicio_id );

				// ajusta a altura da área onde estão os li.
				h = $ol_fields.height();
				h = h + 57;
				$ol_fields.css( 'height', h + 'px' );

				jx_set_input_edit_functions( "div#tabs-" + tab_counter + ' ol#lista_de_exercicios [exercicio_id="' + $rows[ rows_i ].exercicio_id + '"]' );
			}
		}
	}

	// Cria uma nova aba para um novo treino.
	$( ".add_tab" ).click( function()	{
											tab_id = criaTreinoSub();
											
											$( "div#tabs-" + tab_id + ' input[name="treino_sub[id][' + tab_id + '][]"]' ).val( '' );
											$( "div#tabs-" + tab_id + ' input[name="treino_sub[cod][' + tab_id + '][]"]' ).val( tab_title );
											$( "div#tabs-" + tab_id + ' input[name="treino_sub[treino_id][' + tab_id + '][]"]' ).val( $( 'input[name="treino[id][]"]' ).val() );
											$( "div#tabs-" + tab_id + ' input[name="treino_sub[treino_categoria_id][' + tab_id + '][]"]' ).val( '' );
											$( "div#tabs-" + tab_id + ' input[name="treino_sub[titleFK_treino_categoria_id][' + tab_id + '][]"]' ).val( '' );
											$( "div#tabs-" + tab_id + ' input[name="treino_sub[descr][' + tab_id + '][]"]' ).val( '' );
											$tab_selected		=	$( "div#tabs-" + tab_id );
											$tabs.tabs( "option", "selected", 0 );
											$tabs.tabs( "option", "selected", tab_id );
										}
								);

	// Elimina o exercicio selecionado.
	$( "#tabs span#remove_exercicio" ).live( "click", function()	{

		var curr_li_exerc = $( this ).parent().parent().parent().parent().parent();

		$( "#confirm-delete" ).dialog({
			resizable: false,
			width: 400,
			height: 170,
			modal: true,
			buttons: {
				"Excluir Exercício": function() {
					$( this ).dialog( "close" );

					curr_li_exerc.css( 'display', 'none' );
					curr_li_exerc.attr( 'deleted', 'yes' );
					inputVal = $( 'input[name^="treino_exercicio[id]"]', curr_li_exerc ).val();
					$( 'input[name^="treino_exercicio[id]"]', curr_li_exerc ).val( inputVal * (-1) );

					var index_li = 0;
					$.each( $( 'li', curr_li_exerc.parent() ), function()	{
																				if ( $( this ).attr( 'deleted' ) != 'yes' )
																				{
																					index_li = index_li+1;
																					$( this ).find( 'input[name^="treino_exercicio[seq_execucao]"]' ).val( index_li );
																				}
																			});

					$ol_fields		=	$tab_selected.find( 'ol#fields_treino_sub' );
					h = $ol_fields.height();
					h = h - 57;
					$ol_fields.css( 'height', h + 'px' );

					set_changed();
				},
				"Cancelar": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
);
	
	// Elimina o treino selecionado.
	$( "#tabs span.ui-icon-close" ).live( "click", function()	{

		var currTab = this;

		$( "#confirm-delete" ).dialog({
			resizable: false,
			width: 400,
			height: 170,
			modal: true,
			buttons: {
				"Excluir Treino": function() {
					$( this ).dialog( "close" );

					var index = $( "li", $tabs ).index( $( currTab ).parent() );

					inputVal = $( 'div#tabs-' + index + ' input[name^="treino_sub[id]"]' ).val();
					$( 'div#tabs-' + index + ' input[name^="treino_sub[id]"]' ).val( inputVal * (-1) );

					$tabs.tabs( "option", "selected", 1 );
					
					$tabs.tabs( "remove", index );
					set_changed();
				},
				"Cancelar": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
);
	// TABS: fim
	
	// carrega treino sub
	if ( $( 'input[name="treino[id][]"]' ).val() != null )
	{
//		treino_id = $( 'input[name="treino[id][]"]' ).serialize();

		$.ajax({
			 url: "/treino/get_treino_sub/" + $( 'input[name="treino[id][]"]' ).val()
			,dataType: 'json'
			,type: 'get'
//			,data: treino_id
			,success: function( treinos ) {
										if ( treinos.length != 0 )
										{
											for ( iT=0; iT < treinos.length; iT++ )
											{
												tab_id = criaTreinoSub( treinos[iT] );
												$( "div#tabs-" + tab_id + ' input[name="treino_sub[id][' + tab_id + '][]"]' ).val( treinos[iT].id );
												$( "div#tabs-" + tab_id + ' input[name="treino_sub[cod][' + tab_id + '][]"]' ).val( treinos[iT].cod );
												$( "div#tabs-" + tab_id + ' input[name="treino_sub[treino_id][' + tab_id + '][]"]' ).val( treinos[iT].treino_id );
												$( "div#tabs-" + tab_id + ' input[name="treino_sub[treino_categoria_id][' + tab_id + '][]"]' ).val( treinos[iT].treino_categoria_id );
												$( "div#tabs-" + tab_id + ' input[name="treino_sub[titleFK_treino_categoria_id][' + tab_id + '][]"]' ).val( treinos[iT].treino_categoria_nome );
												$( "div#tabs-" + tab_id + ' input[name="treino_sub[descr][' + tab_id + '][]"]' ).val( treinos[iT].descr );
												$tab_selected		=	$( "div#tabs-" + tab_id );

												//Exercícios
												if ( treinos[iT].exercicios.length != 0 )
												{
													addExercicio( treinos[iT].exercicios, 'query' );
												}
												tab_id_last = tab_id;
											}
											$tabs.tabs( "option", "selected", tab_id_last );
											$tabs.tabs( "option", "selected", 1 );
										}
									}
			,error: function( ret ) {
										alert( 'falhou retorno dos treinos.' );
									}
				});
	}
	// fim: carrega treino sub
});
