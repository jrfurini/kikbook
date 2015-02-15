var v_autosave_timer;
var $save_button;
var $button_text;
var $save_img;

var $v_arrow_show_timer;
var $v_arrow_state = 'hide';
var $v_arrow_direction = 'both';

var $ar_chart = [];

var $toolbar_fixed = false;

var $user_status = 'W'; //W-esperando, T-cronometrando; S-salvando;

(function($){})(window.jQuery);
$('tr.clas').click	(
			function(){
						//alert( 'show estatística equipe=' + $( this ).attr( 'eqp_id' ) + ' rod=' + $( this ).attr( 'rod_id' ) );
					}
		);

function show_message( $message, $type, $time ){
	clearTimeout( v_message_timer );
	
	$msg_obj	=	$( 'div.chute-msg.alert-' + $type );

	$( $msg_obj ).css( 'display', 'block' );

	$( $msg_obj ).html( $message );
	
	if ( !$time )
	{
		$time = 5;
	}
	v_message_timer = setTimeout( function() {	$( 'div.chute-msg' ).hide( "slow" );
												$( 'div.chute-msg span' ).removeClass( $type );
											}, ( $time * 1000 ) );
}

function show_success( $message ){
	show_message( $message, 'success', 5 );
}

function show_warning( $message ){
	show_message( $message, 'block', 5 );
}

function show_error( $message ){
	show_message( $message, 'error', 10 );
}



//autosave
function autosave()
{
	$dados = $( 'form' ).serialize();

	$save_button.removeClass( 'btn-danger' );
	$save_button.html( 'Salvando ...' );
	$save_img.css( 'display', 'block' );

//	$text_button = $( 'div.button.action.save-chute div.button.save-chute span' );
//	$text_button.text(  );

	$user_status = 'S'; //W-esperando, T-cronometrando; S-salvando;

	$.ajax({
		 url: "/chute/salvar"
		,dataType: 'json'
		,type: 'post'
		,data: $dados
		,error: function( jqXHR, textStatus, errorThrown )	{
								show_error( 'Não conseguimos salvar os chutes. Por favor pressione o botão "Salvar Manualmente" para que seus chutes sejam salvos.' );
								$save_img.css( 'display', 'none' );
								$save_button.addClass( 'btn-danger' );
								$save_button.html( 'Salvar Manualmente' );
								//show_error( 'jqXHR=' + jqXHR + 'textStatus=' +  textStatus + 'errorThrown=' + errorThrown );
								}
		,success: function( ret ) {
									if ( ret == null )
									{
										show_message( 'Não foi possível comunicar com o servidor.', 'error' );
										$save_img.css( 'display', 'none' );
										$save_button.addClass( 'btn-danger' );
										$save_button.html( 'Salvar Manualmente' );
									}
									else
									{
										if ( ret.kick.length != 0 )
										{
											$( 'tr.chute-msg-db' ).hide();

											for ( i=0; i < ret.kick.length; i++ )
											{
												$kick_obj = $( 'tr.chute[jogo_id="' + ret.kick[i].jogo_id + '"] input[name="kick[id][]"]' );
												$( $kick_obj ).val( ret.kick[i].id );
												
												if ( ret.kick[i].msg_error && ret.kick[i].msg_error.length != 0 )
												{
													$tr_db_msg = $( 'tr.chute-msg-db[jogo_id="' + ret.kick[i].jogo_id + '"]' );
													$td_tr_msg = $( $tr_db_msg ).children( 'td.jogo' );
													$( $tr_db_msg ).show();
													$( $td_tr_msg ).html( "" );

													$( $kick_obj ).parent( 'td' ).parent( 'tr.chute' ).children( 'td.status-img' ).children( 'p.db_status' ).removeClass( 'db_pend' ).removeClass( 'db_ok' ).addClass( 'db_error' );
													html_msg = "";
													for ( imsg=0; imsg < ret.kick[i].msg_error.length; imsg++ )
													{
														html_msg = html_msg + "<p>" + ret.kick[i].msg_error[imsg] + "</p>";
													}

													$( $td_tr_msg ).html( html_msg );
												}
												else
												{
													$( $kick_obj ).parent( 'td' ).parent( 'tr.chute' ).children( 'td.status-img' ).children( 'p.db_status' ).removeClass( 'db_pend' ).removeClass( 'db_error' ).addClass( 'db_ok' );
												}

												$( 'tr.chute[jogo_id="' + ret.kick[i].jogo_id + '"] input[name="kick_power[kick_id][]"]' ).val( ret.kick[i].id );
												
												if ( ret.kick_power.length != 0 )
												{
													if ( ret.kick_power[i].id < 0 ) // Excluindo.
													{
														$f_input_power_id = $( 'tr.chute[jogo_id="' + ret.kick[i].jogo_id + '"] input[name="kick_power[id][]"]' );
														$( $f_input_power_id ).val( "" );
														$( 'tr.chute[jogo_id="' + ret.kick[i].jogo_id + '"] input[name="kick_power[power_id][]"]' ).val( "" );

														f_parent_td = $f_input_power_id.parents( "td" );

														// exibe o menu.
														f_power_dropdown = f_parent_td.children( "div.dropdown" );
														f_power_dropdown.css( 'display', 'block' );

														// esconde o poder selecionado.
														f_power_selected = f_parent_td.children( "div.power-selected" );
														f_power_selected.css( 'display', 'none' );
													}
													else
													{
														$( 'tr.chute[jogo_id="' + ret.kick[i].jogo_id + '"] input[name="kick_power[id][]"]' ).val( ret.kick_power[i].id );
													}
												}
												
												if ( $user_status == 'S' ) //W-esperando, T-cronometrando; S-salvando;
												{
													$( 'tr.chute[jogo_id="' + ret.kick[i].jogo_id + '"] input[name="kick[jx_record_control][]"]' ).val( '{"STATUS":"QUERY","VALID":"TRUE"}' );
													if ( ret.kick_power.length != 0 )
													{
														if ( ret.kick_power[i].id < 0 ) // Excluindo.
														{
															$( 'tr.chute[jogo_id="' + ret.kick[i].jogo_id + '"] input[name="kick_power[jx_record_control][]"]' ).val( '{"STATUS":"NEW","VALID":"TRUE"}' );
														}
														else
														{
															$( 'tr.chute[jogo_id="' + ret.kick[i].jogo_id + '"] input[name="kick_power[jx_record_control][]"]' ).val( '{"STATUS":"QUERY","VALID":"TRUE"}' );
														}
													}
													$user_status = 'W'; //W-esperando, T-cronometrando; S-salvando;
												}
											}
										}

										if ( ret.rodada.length != 0 )
										{
											for ( r=0; r < ret.rodada.length; r++ )
											{
												qtde_total_chutes	=	$( 'div.chute-feitos[rod_id="' + ret.rodada[r].rodada_fase_id + '"] span.qtde_total_chutes' );
												qtde_chutes_feitos	=	$( 'div.chute-feitos[rod_id="' + ret.rodada[r].rodada_fase_id + '"] span.qtde_chutes_feitos' );

												qtde_total_chutes.text( ret.rodada[r].qtde_kicks.qtde_chutes_total );
												qtde_chutes_feitos.text( ret.rodada[r].qtde_kicks.qtde_chutes_feitos );
														
												// Atualiza a classe visual
												if ( ret.rodada[r].qtde_kicks.qtde_chutes_feitos == ret.rodada[r].qtde_kicks.qtde_chutes_total )
												{
													qtde_chutes_feitos.removeClass( "label-important" );
													qtde_total_chutes.removeClass( "label-important" );
													qtde_chutes_feitos.addClass( "label-success" );
													qtde_total_chutes.addClass( "label-success" );
												}
												else
												{
													qtde_chutes_feitos.removeClass( "label-success" );
													qtde_total_chutes.removeClass( "label-success" );
													qtde_chutes_feitos.addClass( "label-important" );
													qtde_total_chutes.addClass( "label-important" );
												}
												
												if ( ret.rodada[r].pessoa_rodada_fase_power.length != 0 )
												{
													for ( i=0; i < ret.rodada[r].pessoa_rodada_fase_power.length; i++ )
													{
														if ( ret.rodada[r].pessoa_rodada_fase_power[i].power_id == 1 )
														{
															power_class = "qqi";
														}
														else if ( ret.rodada[r].pessoa_rodada_fase_power[i].power_id == 2 )
														{
															power_class = "guru";
														}
														else if ( ret.rodada[r].pessoa_rodada_fase_power[i].power_id == 3 )
														{
															power_class = "duelo";
														}
														else if ( ret.rodada[r].pessoa_rodada_fase_power[i].power_id == 4 )
														{
															power_class = "tjunto";
														}
														else if ( ret.rodada[r].pessoa_rodada_fase_power[i].power_id == 5 )
														{
															power_class = "espiao";
														}
														else if ( ret.rodada[r].pessoa_rodada_fase_power[i].power_id == 6 )
														{
															power_class = "barbada";
														}
														else if ( ret.rodada[r].pessoa_rodada_fase_power[i].power_id == 7 )
														{
															power_class = "zebra";
														}
		
														// Verifique/Ajusta a qtde do poder.
														$( "p.power-qtde." + power_class ).children( 'span' ).text( ret.rodada[r].pessoa_rodada_fase_power[i].qtde_liberado - ret.rodada[r].pessoa_rodada_fase_power[i].qtde_usada );
													}
												}
											}
										}

										if ( ret.warning.length != 0 )
										{
											show_message( ret.warning[0].message, ret.warning[0].message_type );
										}
										else if ( ret.ok.length != 0 )
										{
											show_message( ret.ok[0].message, ret.ok[0].message_type );
										}

										if ( ret.fail.length != 0 )
										{
											msg = "";
											msgalert = "";
											for ( i=0; i < ret.fail.length; i++ )
											{
												msgtype = ret.fail[i].message_type;
												msg = msg + "<p>" + ret.fail[i].message + "</p>";
												msgalert = msgalert + " " + ret.fail[i].message;
											}
											show_message( msg, msgtype );
											$save_img.css( 'display', 'none' );
											$save_button.addClass( 'btn-danger' );
											$save_button.html( 'Salvar Manualmente' );
											
											// Se reload == TRUE, então o usuário perdeu a conexão. Forçamos o reload da página.
											if ( ret.reload == 'TRUE' )
											{
												$save_button.html( 'Sair' );
												alert( msgalert );
												window.location.assign( "/classificacao" );
											}
										}
										else
										{
											$save_img.css( 'display', 'none' );
											$save_button.removeClass( 'btn-danger' );
											$save_button.addClass( 'btn-success' );
											$save_button.html( 'Chutes Salvos' );
											set_form_saved();
										}
									}
								}
			});
}
function autosave_on( $obj, $force )
{
	if ( $force || ( parseInt( $( $obj ).val() ) != parseInt( $( $obj ).attr( 'db_value' ) ) ) )
	{
		autosave_off(); // reset time;
		
		$user_status = 'T'; //W-esperando, T-cronometrando; S-salvando;
		set_changed( $obj );
		$( $obj ).attr( 'db_value', $( $obj ).val() );

		$( $obj ).parent( 'td' ).parent( 'tr.chute' ).children( 'td.status-img' ).children( 'p.db_status' ).removeClass( 'db_ok' ).removeClass( 'db_error' ).addClass( 'db_pend' );
	
		$save_button.removeClass( 'btn-success' );
		$save_button.addClass( 'btn-warning' );
		$save_button.html( 'Salvar Chutes' );

		v_autosave_timer = setTimeout( function() 	{	autosave();
													}, ( 1500 ) );
	}
}
function autosave_off()
{
	$user_status = 'W'; //W-esperando, T-cronometrando; S-salvando;
	clearTimeout( v_autosave_timer );
}

function hideArrow()
{
	$v_arrow_state = 'hide';
	$( ".icon-scrool-up" ).fadeOut( 'slow', function() {});
	$( ".icon-scrool-down" ).fadeOut( 'slow', function() {});
	clearTimeout( $v_arrow_show_timer );
}
function changeArrow()
{
	if ( $v_arrow_direction == 'up' )
	{
		$arrow		=	".icon-scrool-up";
	}
	else if ( $v_arrow_direction == 'down' )
	{
		$arrow		=	".icon-scrool-down";
	}
	else
	{
		$arrow		=	".icon-scrool-down, .icon-scrool-up";
	}

	if ( $v_arrow_state == 'show' )
	{
		$v_arrow_state = 'hide';
		$( $arrow ).fadeOut( 'slow', function() {});
		$v_arrow_show_timer = setTimeout( function(){ changeArrow(); }, ( 500 ) );
	}
	else
	{
		$v_arrow_state = 'show';
		$( $arrow ).fadeTo( 'slow', 0.5,  function() { });
		$v_arrow_show_timer = setTimeout( function(){ changeArrow(); }, ( 500 ) );
	}
}

// Keyup ou change ativo o autosave.
$( 'td.kick.text.open-kick input.kick, td.kick.text-right.open-kick input.kick' ).change(
		function(event){
					autosave_on( this, false );
				}
		);
$( 'td.kick.text.open-kick input.kick, td.kick.text-right.open-kick input.kick' ).keyup(
		function(event){
					$( "div.grupo_equipe h4" ).text( event.which );
					if ( ( event.which >= 48 //  0  1  2  3  4  5  6  7  8  9
					&&     event.which <= 57
					     )
					||   event.which == 8 // backspace
					   )
					{
						autosave_on( this, false );
						
						if ( ( event.which >= 48 //  0  1  2  3  4  5  6  7  8  9
						&&     event.which <= 57
						     )
						   )
						{
							$index_atual	=	$( this ).attr( "tabIndex" );
							
							if ( $( this ).hasClass( 'ultimo' ) )
							{
								$index_atual	=	1;
							}
							else
							{
								$index_atual	=	parseInt( $index_atual ) + 1;
							}
							$( 'input.kick[tabIndex="' + $index_atual + '"]' ).focus().select();
						}
					}
				}
	);

// Save manual
$( 'button.action.save-chute' ).click(
		function(event) {
						event.preventDefault();
						clearTimeout( v_autosave_timer );

						$( "#confirm-save" ).dialog({
							resizable: false,
							width: 400,
							height: 170,
							modal: true,
							buttons: {
								"Salvar": function() {
									$( this ).dialog( "close" );
									autosave();
								},
								"Cancelar": function() {
									$( this ).dialog( "close" );
								}
							}
						});
					}
		);

function testScroll ( $obj )
{
	height_total = document.getElementById("conteudo").scrollHeight;
	hideArrow();
	$v_arrow_direction = 'none';

	if ( $( $obj ).scrollTop() >= 150 )
	{
		$v_arrow_direction = 'up';
	}

	if ( ( $( $obj ).scrollTop() + $( $obj ).height() ) < ( height_total - 70 ) || ( height_total - 70 ) == -70 )
	{
		if ( $v_arrow_direction == 'up' )
		{
			$v_arrow_direction = 'both';
		}
		else
		{
			$v_arrow_direction = 'down';			
		}
	}
	if ( $v_arrow_direction != 'none' )
	{
		$v_arrow_show_timer = setTimeout( function(){ changeArrow(); }, ( 500 ) );
	}
}

$( "section#conteudo" ).scroll(
		function (event) { 
						testScroll( this );
					}
		);

/*
 * Ajusta a toolbar de poderes e save.
 */
$( document ).scroll(
		function (event) { 
					$chute_power	=	$( "div.chute-power" );

					if ( $( $chute_power ).hasClass( 'crono' )  )
					{
						$top_lim		=	124;
						$marg_top		=	'0px';
						$marg_top_div	=	'66px';
					}
					else
					{
						$top_lim		=	243;
						$marg_top		=	'-14px';
						$marg_top_div	=	'79px';
					}
					
					if ( $( document ).scrollTop() >= $top_lim && !$toolbar_fixed)
					{
						$toolbar_fixed = true;
						$( $chute_power ).css( 'position', 'fixed' ).css( 'z-index', '900' ).css( 'margin-top', '0px' );
						$( "div.left-content div.chute" ).css( 'margin-top', $marg_top_div );
						$( "div.left-content div.chute-clas-cols" ).css( 'margin-top', $marg_top_div );
						$( "div.ads-feedback.ad_vert_inline" ).css( 'margin-top', $marg_top_div );
					}
					else if ( $( document ).scrollTop() < $top_lim && $toolbar_fixed)
					{
						$toolbar_fixed = false;
						$( $chute_power ).css( 'position', '' ).css( 'z-index', '9' ).css( 'margin-top', $marg_top );
						$( "div.left-content div.chute" ).css( 'margin-top', '0px' );
						$( "div.left-content div.chute-clas-cols" ).css( 'margin-top', '0px' );
						$( "div.ads-feedback.ad_vert_inline" ).css( 'margin-top', '0px' );
					}
			}
		);

/*
 * Poderes
 */
$( "div.dropdown.power-sel a.dropdown-toggle" ).click(
		function(event)	{
						event.preventDefault();
				}
		);

$( "button.del-power" ).click(
		function(event) {
						event.preventDefault();
						
						f_button_del = $( this );
						rod_id = f_button_del.attr( 'rod_id' );

						// Liga a toolbar.
						f_parent_td = f_button_del.parents( "td" );
						f_toolbar = f_parent_td.children( "div.btn-toolbar" );
						f_toolbar.show();

						// esconde o poder selecionado.
						f_power_selected = f_parent_td.children( "div.power-selected" );
						f_power_selected.hide();
						
						// Retira o valor ao campo.
						f_div_power_selected = f_power_selected.children( "div#kick_power" ).children( "div" );
						f_kick_power_id      = f_div_power_selected.children( 'input[name*="kick_power[id"]' );
						kick_power_id        = f_kick_power_id.val();
						f_input_power        = f_div_power_selected.children( 'input[name*="kick_power[power_id"]' );
						power_id             = f_input_power.val();

						f_kick_power_id.val( kick_power_id * (-1) );
						if ( power_id == 1 )
						{
							power_class = "qqi";
						}
						else if ( power_id == 2 )
						{
							power_class = "guru";
						}
						else if ( power_id == 3 )
						{
							power_class = "duelo";
						}
						else if ( power_id == 4 )
						{
							power_class = "tjunto";
						}
						else if ( power_id == 5 )
						{
							power_class = "espiao";
						}
						else if ( power_id == 6 )
						{
							power_class = "barbada";
						}
						else if ( power_id == 7 )
						{
							power_class = "zebra";
						}
						
						// retira a classe do poder escolhido.
						f_i_power_selected = f_power_selected.children( "i.power-sel" );
						f_i_power_selected.removeClass( power_class );

						// Verifique/Ajusta a qtde do poder.
						sec_power = $( 'section.sel-chute-power[rod_id="' + rod_id + '"]' );
						f_qtde = $( sec_power ).children( 'ol' ).children( 'li' ).children( 'i.power-qtde.' + power_class );
						qtde_power = $( f_qtde ).text();

						qtde_power = parseInt( qtde_power ) + 1;
						f_header_power = $( sec_power ).children( 'ol' ).children( 'li' ).children( 'i.power-sel.' + power_class );
						f_header_power.removeClass( "inativo" );

						$( f_qtde ).text( qtde_power );
						$( f_qtde ).removeClass( "inativo" );

						$( 'button.sel-power[power="' + power_class + '"][rod_id="' + rod_id + '"]' ).show();

						autosave_on( f_input_power, true ); // Marca como alterado e ativo o autosave.
				}
		);

$( "button.sel-power" ).click(
		function(event)	{
						event.preventDefault();

						f = $( this );
						power_class = f.attr( 'power' );
						power_id = f.attr( 'power_id' );
						kick_id = f.attr( 'kick' );
						rod_id = f.attr( 'rod_id' );

						// Verifique/Ajusta a qtde do poder.
						sec_power = $( 'section.sel-chute-power[rod_id="' + rod_id + '"]' );
						f_qtde = $( sec_power ).children( 'ol' ).children( 'li' ).children( 'i.power-qtde.' + power_class );
						qtde_power = $( f_qtde ).text();

						if ( qtde_power > 0 )
						{
							qtde_power = qtde_power - 1;
							$( f_qtde ).text( qtde_power );
							
							// acabaram os poderes deste tipo, desligamos o resto.
							if ( qtde_power == 0 )
							{
								$( 'button.sel-power[power="' + power_class + '"][rod_id="' + rod_id + '"]' ).hide();
								f_header_power = $( sec_power ).children( 'ol' ).children( 'li' ).children( 'i.power-sel.' + power_class );
								f_header_power.addClass( "inativo" );
								f_qtde.addClass( "inativo" );
							}

							f_parent_td = f.parents( "td" );

							// desliga o menu. Só um poder por chute.
							f_toolbar = f.parents( "div.btn-toolbar" );
							f_toolbar.hide();
	
							// exibe o poder selecionado.
							f_power_selected = f_parent_td.children( "div.power-selected" );
							f_power_selected.show();
							// colocar a classe do poder escolhido.
							f_i_power_selected = f_power_selected.children( "button.del-power" ).children( "i.power-sel" );
							f_i_power_selected.addClass( power_class );
							f_i_power_selected.addClass( 'power' );
							f_i_power_selected.removeClass( 'power-sel' );
							
							// Atribui o valor ao campo.
							f_div_power_selected = f_power_selected.children( "div#kick_power" ).children( "div" );
							f_input_power = f_div_power_selected.children( 'input[name*="kick_power[power_id"]' );
							f_input_power.val( power_id );

							autosave_on( f_input_power, true ); // Marca como alterado e ativo o autosave.
						}
						else
						{
							show_warning( 'Você não pode usar mais este poder nos jogos desta rodada.' ); // has_no_change_to_save							
						}
					}
		);
/*
 * fim: Poderes
 */
$( 'tr.chute' ).focusin(
		function(event) {
							event.preventDefault();
							tr = $( this );
							eqp_casa = $( tr ).attr( "eqp_id_casa" );
							eqp_vis = $( tr ).attr( "eqp_id_visitante" );
							rod_id = $( tr ).attr( "rod_id" );

							$( "table.clas tr.clas td.nome" ).css( "font-size","90%" ).css( "color", "#333333" );
							$( "table.clas tr.clas td.nome" ).css( "font-size","90%" ).css( "color", "#333333" );

							tr_casa = $( "table.clas tr.clas[eqp_id='" + eqp_casa + "']" );
							$( tr_casa ).children( "td.nome" ).css( "font-size","150%" ).css( "color", "#333333" );

							tr_vis = $( "table.clas tr.clas[eqp_id='" + eqp_vis + "']" );
							$( tr_vis ).children( "td.nome" ).css( "font-size","150%" ).css( "color", "#333333" );

							$( 'section.sel-chute-power[rod_id!="' + rod_id + '"]' ).hide();
							$( 'section.sel-chute-power[rod_id="' + rod_id + '"]' ).show();

							$( 'div.rodada-title[rod_id!="' + rod_id + '"]' ).hide();
							$( 'div.rodada-title[rod_id="' + rod_id + '"]' ).show();

							$( 'div.chute-feitos[rod_id!="' + rod_id + '"]' ).hide();
							$( 'div.chute-feitos[rod_id="' + rod_id + '"]' ).show();
						}
		);
$( 'tr.chute' ).focusout(
		function(event) {
							event.preventDefault();
							tr = $( this );
							eqp_casa = $( tr ).attr( "eqp_id_casa" );
							eqp_vis = $( tr ).attr( "eqp_id_visitante" );
							rod_id = $( tr ).attr( "rod_id" );

							$( "table.clas tr.clas td.nome" ).css( "font-size","110%" ).css("color","#333333");
							$( "table.clas tr.clas td.nome" ).css( "font-size","110%" ).css("color","#333333");
						}
		);

/* graficos */
function hidechart()
{
	$( "table.clas tr.clas.chart" ).hide();
}

function showChartChute( $obj_btn )
{
	if (!Array.prototype.indexOf) {
		Array.prototype.indexOf = function(obj, start) {
		     for (var i = (start || 0), j = this.length; i < j; i++) {
		         if (this[i] === obj) { return i; }
		     }
		     return -1;
		}	
	}
	tr_chute = $( $obj_btn ).parent( 'td' ).parent( 'tr.chute' );
	tr_chute_id = $( tr_chute ).attr( 'id' );
	jogo_id     = $( tr_chute ).attr( 'jogo_id' );

	icon = $( $obj_btn ).children( 'i' );

	if ( $( icon ).hasClass( "icon-signal" ) )
	{
		$( "tr.chute-chart#chart-" + jogo_id ).show();

		eqp_casa_id = $( tr_chute ).attr( 'eqp_id_casa' );
		eqp_vis_id  = $( tr_chute ).attr( 'eqp_id_visitante' );
		rod_id      = $( tr_chute ).attr( 'rod_id' );
		
		if ( $ar_chart.length == 0 )
		{
			// Cores em degrade(gradiente)
			Highcharts.getOptions().colors	=	Highcharts.map	(
																 Highcharts.getOptions().colors
																,function(color)	{
																						return	{
																								 radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 }
																								,stops:	[
																									 [0, color]
																									,[1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
																									]
																								};
																 					}
																 );
		}
		
		if ( $ar_chart.indexOf( jogo_id ) == -1 )
		{
			$ar_chart.push( jogo_id );
		
			var	chart_line;
			var	chart_pie;
			var	chart_bar;
			var	color_ind	=	[0,6,3];
			var	color_ind2	=	[2,6,3];
			var	 colors		=	Highcharts.getOptions().colors
				,name		=	'Chutes por Resultado';
			
			var json_data	=	{};
			$.ajax(		{
							 url: "/chute/xml/" + eqp_casa_id + '/' + eqp_vis_id + '/' + rod_id + '/' + jogo_id
							,async: false
							,dataType: 'json'
							,success: function( dados )	{
															json_data.rodadas				=	dados.rodadas;
															json_data.maior_classificacao	=	dados.maior_classificacao;
															json_data.dados_gerais			=	dados.dados_gerais;
															json_data.chutes_kiker			=	dados.chutes_kiker;
														}
						}
				);

			// Elimina o working
			$( 'container_1-' + jogo_id ).html( null );

			// Build the data arrays
			var	 dadosEquipe = []
				,dadosChute = []
				,dadosLine = []
				,nomes_equipe = []
				,titulo_jogo
				,maior_classif = json_data.maior_classificacao;
			for ( var i = 0; i < json_data.dados_gerais.length; i++) // 3
			{
				if ( i == 0 || i == 1 )
				{
					if ( titulo_jogo == null )
					{
						titulo_jogo = json_data.dados_gerais[i].equipe_nome;
					}
					else
					{
						titulo_jogo = titulo_jogo + '   X   ' + json_data.dados_gerais[i].equipe_nome;
					}
				}

				if ( json_data.dados_gerais[i].equipe_nome != 'Empate' )
				{
					nomes_equipe.push( json_data.dados_gerais[i].equipe_nome );
				}

				dadosEquipe.push(	{
									 name: json_data.dados_gerais[i].equipe_sigla
									,y: json_data.dados_gerais[i].total_chutes
									,color: colors[color_ind[i]]
									}
								);
				dadosLine.push	(	{
									 name: json_data.dados_gerais[i].equipe_sigla
									,data: json_data.dados_gerais[i].posicao_rodada
									,color: colors[color_ind[i]]
									}
								);
	
				// Dados dos chutes
				for ( var j = 0; j < json_data.dados_gerais[i].donut.qtde_chute.length; j++)
				{
					var brightness = 0.2 - (j / json_data.dados_gerais[i].donut.qtde_chute.length ) / 5 ;
					dadosChute.push(	{
										 name: json_data.dados_gerais[i].donut.chutes[j]
										,y: json_data.dados_gerais[i].donut.qtde_chute[j]
										,color: Highcharts.Color(colors[color_ind[i]]).brighten(brightness).get()
										}
									);
				}
			}
			
			dadosChuteKiker = json_data.chutes_kiker;
			/*
			[
     	 	 {
				 name: 'Vitória'
				,data: [24, 4]
				,color: colors[2]
			 }
     	 	,{
				 name: 'Empate'
				,data: [7, 24]
				,color: colors[6]
			 }
     	 	,{
				 name: 'Derrota'
				,data: [8, 16]
				,color: colors[3]
			 }
     	 ]
     	 */
			for ( var jj = 0; jj < dadosChuteKiker.length; jj++)
			{
				dadosChuteKiker[jj].color = colors[color_ind2[jj]];
			}
			// Desempenho
			chart_line	=	new Highcharts.Chart(
							{
								 chart: {
											 renderTo: 'container_1-' + jogo_id
											,type: 'spline'
											,height:       150
											,width:        590
											,marginRight:  80
											,marginBottom: 30
										}
								,title: {
											 text: titulo_jogo
											,x: -20 //center
										}
								,xAxis: {
										 categories: json_data.rodadas
										,labels:	{
														 rotation: -35
														,align: 'right'
														,style:	{
																 fontSize: '8px'
																,fontFamily: 'Verdana, sans-serif'
															}
													}
										}
								,yAxis: {
										title:		{
														text: 'Posição'
													}
										,max:		maior_classif
										,reversed: true
										,plotLines:	[
										           	 	{
															 value: 0
															,width: 1
															,color: '#808080'
														}
										           	 ]
										}
								,tooltip:	{
												formatter: function()	{
																			return '<b>'+ this.series.name +'</b><br/>'+ this.x + ': posição ' + this.y;
																		}
											}
								,legend:  {
											 layout: 'vertical'
											,align: 'right'
											,verticalAlign: 'top'
											,x: -10
											,y: 10
											,borderWidth: 0
										}
								,series:	dadosLine//data_line
							}
						);
	
			// Create the chart
			chart_pie	=	new Highcharts.Chart(
							{
							 chart:		{
										 renderTo: 'container_2-' + jogo_id
										,type: 'pie'
										,shadown: true
										,height:	240
										,width:		305
										}
							,title:		{
										 text: 'Favorito entre os Kikers'
										}
							,yAxis:		{
											title: {
													 text: ''
													}
										}
							,plotOptions:	{
											 pie:	{
													 shadow: true
													,center: ['50%', '50%']
													,allowPointSelect: false
													,cursor: 'pointer'
													}
											}
							,tooltip:	{
										 valueSuffix: '%'
										}
							,series:	[	 {
												 name: 'Chutes'
												,data: dadosEquipe
												,size: '55%'
												,dataLabels:	{
																 formatter: function()	{
																							return this.y >= 5 ? this.point.name : null;
																						}
																,color: 'white'
																,distance: -30
																}
											 }
											,{
												 name: 'Chutes'
												,data: dadosChute
												,size: '80%'
												,innerSize: '60%'
												,dataLabels:	{
																formatter: function()	{
																							// display only if larger than 1
																							return this.y >= 5 ? '<b>'+ this.point.name +':</b> '+ this.y +'%'  : null;
																						}
																}
											}
										]
							}
						);
			// Create the chart
			chart_bar	=	new Highcharts.Chart	(
														{
														 chart:		{
																	 renderTo: 'container_3-' + jogo_id
																	,type: 'column'
																	,height:	240
																	,width:		280
																	}
														,title: {
																	text: 'Seus chutes nos Times'
																}
														,xAxis:	{
																	categories: nomes_equipe
																}
														,yAxis:	{
																	 min: 0
																	,title: {
																				text: ''
																			}
																}
														,tooltip:	{
																		 pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>'
																		,shared: true
																	}
														,plotOptions:{
																		column: {
																					stacking: 'percent'
																				}
																	}
														,series: 	dadosChuteKiker
													}
												);
		}

		$( icon ).removeClass( "icon-signal" );
		$( icon ).addClass( "icon-ok" );
	}
	else
	{
		$( "tr.chute-chart#chart-" + jogo_id ).hide();

		$( icon ).removeClass( "icon-ok" );
		$( icon ).addClass( "icon-signal" );
	}
}

$('tr.chute button.chart_chute').click	(
			function(event){
						event.preventDefault();
						
						showChartChute( this );
					}
		);

$( 'div.campeonatos button.sel-tipo-camp' ).click	(
			function(event){
						event.preventDefault();
						if ( check_for_change() )
						{
							tipo_camp = $( this ).attr( 'value' );
							window.open( '/chute/crono/null/' + tipo_camp, "_self" );
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

$( "a.videoajuda" ).click(
		function(event){
			event.preventDefault();

			$video = $( this ).attr( "video" );
			$modal_body = $( "div.videoajuda div.modal-body" );
			$html = '<iframe width="640" height="420" src="' + $video + '" frameborder="0" allowfullscreen=""></iframe>';
			$( $modal_body ).html( $html );
		}
	);

$( "div.videoajuda button.close" ).click(
		function(event){
			event.preventDefault();

			$modal_body = $( "div.videoajuda div.modal-body" );
			$html = '<i></i>';
			$( $modal_body ).html( $html );
		}
	);

$(window).load(function(event) {
	$save_button	=	$( 'button.action.save-chute' );
	$save_button.removeClass( 'btn-danger' );
	$save_button.removeClass( 'btn-warning' );
	$save_button.addClass( 'btn-success' );
	$save_img		=	$( 'div.save-chute img' );
	$save_button.html( 'Consultado' );
	testScroll( $( "section#conteudo" ) );

	$qtde = 0;
	$input_kick	= $( "input.kick" );
	$.each( $input_kick, function(event)	{
												$qtde = $qtde + 1;
												$( this ).attr( 'tabIndex', $qtde );
												if ( $qtde == 1 )
												{
													$( this ).addClass( "primeiro" );
												}
											}
		);
	$( 'input.kick[tabIndex="' + $qtde + '"]' ).addClass( "ultimo" );
	$( 'input.kick[tabIndex="1"]' ).focus().select();
});

$(window).load(function(event) {
	testScroll( $( "section#conteudo" ) );
});

$(window).resize(function(event)
{
	testScroll( $( "section#conteudo" ) );
});

