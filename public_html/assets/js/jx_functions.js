// $Id: jx_functions.js,v 1.29 2013-03-27 01:28:03 junior Exp $

// remap jQuery to $
(function($){})(window.jQuery);
var v_message_timer;
var v_autocomplete_timer;
var v_count_selection = 0;
var v_count_selection_edit = Array();
var v_form_changed = false;
var v_autocomplete_last_input = 'NONE';
var v_record_controller;

//TODO: Controle de autosave, rascunhos.

function show_message( $message, $type, $time ){
	clearTimeout( v_message_timer );

	$master		=	$( 'div.message_master-ctrl-group' );
	
	$up_master			=	$( $master ).parent( "section" );
	$up_master_height	=	$( $up_master ).css( 'heigth' );
	$( $up_master ).css( 'heigth', ( $up_master_height + 50 ) );

	$( $master ).slideDown( 'fast' );

	$visual		=	$( $master ).children( "div.message_master-ctrl" );
	$( $visual ).removeClass( 'alert-error' );
	$( $visual ).removeClass( 'alert-success' );

	if ( $type == 'error' )
	{
		$( $visual ).addClass( 'alert-error' );
	}
	else if ( $type == 'success' )
	{
		$( $visual ).addClass( 'alert-success' );
	}

	$text		=	$( $visual ).children( "div.text" );
	$( $text ).text( $message );

	if ( !$time && $type == 'success' )
	{
		$time = 4;
	}
	if ( $time
	&&   $time != 0
	&&   ( $type == 'success'
	||     $type == 'warning'
		 )
	   )
	{
		v_message_timer = setTimeout( function() {	$( 'div.message_master-ctrl-group' ).slideUp( "slow" );
												}, ( $time * 1000 ) );
	}
}

function show_success( $message ){
	show_message( $message, 'success', 10 );
}

function show_warning( $message ){
	show_message( $message, 'warning', 10 );
}

function show_error( $message ){
	show_message( $message, 'error' );
}

function edit_delete_button(){
	if ( v_count_selection == 0 )
	{
		$( "section.acoes button.action.delete" ).hide();
		$( "section.acoes button.action.edit" ).hide();
		$( "section.acoes button.action.unselect-all" ).hide();
		$( "section.acoes button.action.select-all" ).show();
		set_form_saved();
	}
	else
	{
		set_changed( this );
		$( "section.acoes button.action.select-all" ).hide();
		$( "section.acoes button.action.unselect-all" ).show();
		$( "section.acoes button.action.delete" ).show();
// TODO: Quando o editor de grid estiver ativo, eliminar este if.
		if ( v_count_selection > 1 )
		{
 			$( "section.acoes button.action.edit" ).hide();
		}
		else
		{
 			$( "section.acoes button.action.edit" ).show();
		}
	}
}

function select_control( $jx ){
	if ( $($jx).is( ':checked' ) )
	{
		v_count_selection++;
		$( 'tr#' + $($jx).val() + '.jx-index'  ).addClass( "selected" );
	}
	else
	{
		v_count_selection--;
		if ( v_count_selection < 0 )
		{
			v_count_selection = 0;
		}
		$( 'tr#' + $($jx).val() + '.jx-index' ).removeClass( "selected" );
	}

	edit_delete_button();
}

function edit_delete_button_edit( $jx ){
	$table = $( $jx ).attr('record_group');
	if ( v_count_selection_edit[$table] == 0 )
	{
		$( 'section.acoes-edicao[record_group="' + $table + '"] button.action.delete-grid' ).hide();
		$( 'section.acoes-edicao[record_group="' + $table + '"] button.action.edit-grid' ).hide();
		$( 'section.acoes-edicao[record_group="' + $table + '"] button.action.unselect-all-grid' ).hide();
		$( 'section.acoes-edicao[record_group="' + $table + '"] button.action.select-all-grid' ).show();
		set_form_saved();
	}
	else
	{
		set_changed( this );
		$( 'section.acoes-edicao[record_group="' + $table + '"] button.action.select-all-grid' ).hide();
		$( 'section.acoes-edicao[record_group="' + $table + '"] button.action.unselect-all-grid' ).show();
		$( 'section.acoes-edicao[record_group="' + $table + '"] button.action.delete-grid' ).show();
// TODO: Quando o editor de grid estiver ativo, eliminar este if.
		if ( v_count_selection_edit[$table] > 1 )
		{
 			$( 'section.acoes-edicao[record_group="' + $table + '"] button.action.edit-grid' ).hide();
		}
		else
		{
 			$( 'section.acoes-edicao[record_group="' + $table + '"] button.action.edit-grid' ).show();
		}
	}
}

function select_control_edit( $jx ){
	$table = $( $jx ).attr('record_group');
	
	if ( isNaN( v_count_selection_edit[$table] ) ) { v_count_selection_edit[$table] = 0 }
	
	if ( $( $jx ).is( ':checked' ) )
	{
		v_count_selection_edit[$table] = v_count_selection_edit[$table] + 1;
		$( 'tr.jx-edit-g[record_group="' + $table + '"][row_id="' + $( $jx ).val() + '"]' ).addClass( "selected" );
	}
	else
	{
		v_count_selection_edit[$table] = v_count_selection_edit[$table] - 1;
		if ( v_count_selection_edit[$table] < 0 )
		{
			v_count_selection_edit[$table] = 0;
		}
		$( 'tr.jx-edit-g[record_group="' + $table + '"][row_id="' + $( $jx ).val() + '"]' ).removeClass( "selected" );
	}

	edit_delete_button_edit( $jx );
}

function initialze_page(){
	// Prepara a variável para o controle de aviso de alteração não salva.
    v_form_changed = false;

	// Seleciona novamente o que estava selecionado antes da volta da página, se for o caso.
	v_count_selection = 0;
    checks = $('.jx-select :checked' ).toArray();
    for ( i=0; i < checks.length; i++ )
    {
		select_control( checks[i] );
	}
    $( '.working' ).css( 'display', 'none' );
	$( '.conteudo' ).css( 'display', 'block' );
	
	// Copia todos os valores dos campos de controle de linhas.
	var v_record_controller   = $( 'input[name*="[jx_record_control]"]' );
	$.each( v_record_controller, function()	{
		value = $( this ).val();
		$( this ).attr( 'database_control', value );
		});
}

function form_is_changed()
{
	return ( v_form_changed == true );
}
function check_for_change(){
	if ( form_is_changed() )
	{
		show_warning( 'Há alterações não salvas. Abandone ou salve as alterações antes de sair.' ); // has_change_save_it
		return false;
	}
	else
	{
		return true;
	}
}

function set_changed( $obj ) {
	if ( $( $obj ).attr( 'name' ) != 'jx-search-what' // search não provoca changed.
	   )
	{
		v_form_changed = true;
		$( 'button.action.save' ).addClass( 'btn-danger' );
		$( 'button.action.reset' ).css( 'display', 'inline-block' );
		
		// Altera o controle de registro para alterado.
		var v_record_groups		=	$( $obj ).parents( 'div [record_group], tr [record_group], p [record_group]' );
		v_record_groups.each( function( i ) {
				v_table_name		=	$( this ).attr( "record_group" );
				v_record_ctrl		=	$( this ).find( 'input[name*="' + v_table_name + '[jx_record_control"]' );

				v_ctrl				=	"(" + v_record_ctrl.val() + ")";
				//alert( v_table_name +  " - " + v_ctrl );
				v_ctrl				=	eval( v_ctrl );
				//alert( v_table_name +  " - " + v_ctrl.STATUS );

				if ( v_ctrl.STATUS == 'NEW' || v_ctrl.STATUS == 'QUERY' ) 
				{
					v_ctrl.STATUS	=	'CHANGED';
				}
				v_ctrl.VALID		=	'FALSE';
				v_record_ctrl.val( JSON.stringify( v_ctrl ).replace( '[', '' ).replace( ']', '' ) );
			});
	}
}

function set_form_saved() {
	if ( v_form_changed )
	{
		v_form_changed = false;
		$( 'button.action.save' ).removeClass( 'btn-danger' );
		$( 'button.action.reset' ).css( 'display', 'none' );
	}
}

function unset_changed() {
	if ( v_form_changed )
	{
		location.reload();
/*		v_form_changed = false;
		$( 'button.action.save' ).removeClass( 'btn-danger' );
		$( 'button.action.reset' ).css( 'display', 'none' );
		$form[ 0 ].reset();
		//TODO: Encontrar um método que faça o undo de toda a página.
*/
	}
}

function setAutocomplete()
{
		return	{
					 source: $('.jx-autocomplete').attr( 'jx_autocomplete_source' )
					,minLenght: 2
					,search: function(){
						$(this).autocomplete( "option", "source", $(this).attr('jx_autocomplete_source') );
					}
					,select: function( event, ui ){
						field_id = $(this).attr('id');
						$('input[id="' + field_id + '"]').val( ui.item.value );
						$('input[id="' + field_id.replace( 'titleFK_', '' ) + '"]').val( ui.item.id );
						set_changed( this );
					}
					,change: function( event, ui ){
						/* copiar o código abaixo para o "click" do título de FK */
						if ( $(this).val() == "" )
						{
							field_id = $(this).attr('id');
							$('input[id="' + field_id.replace( 'titleFK_', '' ) + '"]').val( null );
						}
						/* fim da cópia */
					}
				};
}

function showAllAutoComplete( f )
{
	clearTimeout( v_autocomplete_timer );
	if ( v_autocomplete_last_input != 'NONE'
	&&   v_autocomplete_last_input.autocomplete( "widget" ).is( ":visible" )
	&&   v_autocomplete_last_input.attr( 'name' ) == $( f ).parent( "div.campo-borda" ).children( "input.input.jx-autocomplete" ).attr( 'name' )
	   )
	{
		v_autocomplete_last_input.autocomplete( "close" );
		v_autocomplete_last_input = 'NONE';
	}
	else
	{
		$( ".jx-autocomplete" ).autocomplete( "close" );
		v_autocomplete_last_input = $( f ).parent( "div.campo-borda" ).children( "input.input.jx-autocomplete" );
		v_autocomplete_last_input.autocomplete( "search", '$@#$' );
		v_autocomplete_timer = setTimeout( function() {	$( ".jx-autocomplete" ).autocomplete( "close" );
													  }, 15000 );
	}
}

function createEdit( f )
{
	urlCreate = $(f).attr( 'create_url' );
	fieldFK = $(f).attr( 'field_key' );
	
	//Força a alteração do título.
	if ( $('input[name="titleFK_' + fieldFK + '"]').val() == "" )
	{
		fieldFK = $(f).attr('name');
		$('input[name="' + fieldFK + '"]').val( null );
	}
	/* fim cópia */
	
	idFK = $('input[id="' + fieldFK + '"]').val();
	
	if ( idFK > 0 )
	{
		urlCreate = urlCreate + '/' + idFK + '/dialog';
	}
	else
	{
		urlCreate = urlCreate + '/0/dialog';
	}

	$( 'iframe#iframe_fk' ).remove();
	$( '#dialog_fk').append( "<iframe id='iframe_fk'></iframe>" );
	$( 'iframe#iframe_fk' ).attr( 'src', urlCreate ).attr( 'height', '580' ).attr( 'width', '783' );
	$( "#dialog_fk:ui-dialog" ).dialog( "destroy" );
	$( "#dialog_fk" ).dialog(
			{
				 resizable: false
				,height:600
				,width: 800
				,modal: true
				,buttons:	{
								"Fechar": function(){
														$( this ).dialog( "close" );
														$( 'iframe#iframe_fk' ).remove();
													}
							}
			}
		);
}

function delete_edit()
{
	vcount_record = 0;
	$('input[record_group="' + $table + '"][name="checkbox_id[]"]').each( function()	{
		if ( $( this ).is( ':checked' ) ){ vcount_record++; }
	});

	if ( vcount_record > 0 )
	{
		$('input[record_group="' + $table + '"][name="checkbox_id[]"]').each( function()	{
			if ( $( this ).is( ':checked' ) )
			{
				$obj = $( this ).parent( "td" ).parent( "tr" ).parent( "tbody" ).parent( "table" ).parent( "li" ).parent( "ol" ).children( "div.pre-acoes" ).children( "section.acoes-edicao" ).children( "div.btn-toolbar" ).children( "div.btn-group" ).children( "button.delete-grid" );
				delete_from_edit( $obj );
			}
		});
	}
	else
	{
		$.each( $( 'input[name*="[id]"]' ), function()	{
				inputVal = $( this ).val();
				$( this ).val( inputVal * (-1) );
				$( "form" ).submit();
		});
	}
}

function delete_from_index()
{
	var url1 = window.location + "/";
	url = url1.split( "/" );
	urlDelete = "/" + url[3] + "/delete_index";
	urlDelete = urlDelete.replace( '.html', '' );
	deleteSelection = $('.jx-select :checkbox').serialize();

	$.ajax({
		 url: urlDelete
		,dataType: 'json'
		,type: 'get'
		,data: deleteSelection
		,error: function( jqXHR, textStatus, errorThrown )	{
									show_error( 'Falhou a solicitação de exclusão da(s) linha(s).' );
									//show_error( 'jqXHR=' + jqXHR + 'textStatus=' +  textStatus + 'errorThrown=' + errorThrown );
								}
		,success: function( ret ) {
									var v_count_ok = 0;
									var v_count_fail = 0;

									if ( ret.ok.length != 0 )
									{
										for ( i=0; i < ret.ok.length; i++ )
										{
											v_count_ok++;
											$( 'tr#' + ret.ok[i].id + '.jx-index' ).remove();
										}
									}

									if ( ret.fail.length != 0 )
									{
										for ( i=0; i < ret.fail.length; i++ )
										{
											v_count_fail++;
											$( 'tr#' + ret.fail[i].id + '.jx-index' ).addClass( 'error' );
											//ret.fail[i].message
											//ret.fail[i].message_type
										}
									}
									v_count_selection = 0;

									if ( v_count_ok > 0 )
									{
										if ( v_count_ok == 1 )
										{
											show_success( 'Foi excluída 1 linha.' ); // one_line_deleted
										}
										else
										{
											show_success( 'Foram excluídas ' + v_count_ok + ' linhas.' ); // many_lines_deleted
										}
									}
									v_count_selection = v_count_fail;
									if ( v_count_fail == 1 )
									{
										show_error( 'Uma linha falhou ao ser eliminada.' ); // one_line_deleted_fail
									}
									else if ( v_count_fail > 1 )
									{
										show_error( 'Algumas linhas falharam ao serem eliminadas.' ); // many_lines_deleted_fail
									}
									else if ( v_count_ok == 0 )
									{
										show_error( 'Nenhuma linha excluída.' ); // no_line_deleted
									}
									edit_delete_button();
									set_form_saved();
								}
			});
}

function create_new_grid( $obj )
{
	$table = $( $obj ).parent( "div.btn-group" ).parent( "div.btn-toolbar" ).parent( "section.acoes-edicao" ).attr('record_group');
	
	$tr_sample  = $( 'tr.jx-edit-g[record_group="' + $table + '"][row_id="new_sample"]' );
	$tr_new     = $( $tr_sample ).clone();
	$( $tr_new ).css( 'display', '' );
	$( $tr_new ).attr( 'row_id', 'temp' );
	
	$( $tr_new ).appendTo( 'ol[table_name="' + $table + '"] li.jx-edit-f table tbody' );
	$tr_new		= null;
	
	jx_set_input_edit_functions( 'tr.jx-edit-g[record_group="' + $table + '"][row_id="temp"]' );
	$( 'tr.jx-edit-g[record_group="' + $table + '"][row_id="temp"]' ).attr( 'row_id', '' );
}

function delete_from_edit( $obj )
{
	$table = $( $obj ).parent( "div.btn-group" ).parent( "div.btn-toolbar" ).parent( "section.acoes-edicao" ).attr('record_group');

	var url1 = window.location + "/";
	url = url1.split( "/" );
	urlDelete = "/" + url[3] + "/delete_index/" + $table;
	urlDelete = urlDelete.replace( '.html', '' );
	
	$li_edit = $( '' );
	
	deleteSelection = $( 'ol[table_name="' + $table + '"] li.jx-edit-f .jx-select :checkbox' ).serialize();

	$.ajax({
		 url: urlDelete
		,dataType: 'json'
		,type: 'get'
		,data: deleteSelection
		,error: function( jqXHR, textStatus, errorThrown )	{
									show_error( 'Falhou a solicitação de exclusão da(s) linha(s).' );
									//show_error( 'jqXHR=' + jqXHR + 'textStatus=' +  textStatus + 'errorThrown=' + errorThrown );
								}
		,success: function( ret ) {
									var v_count_ok = 0;
									var v_count_fail = 0;

									if ( ret.ok.length != 0 )
									{
										for ( i=0; i < ret.ok.length; i++ )
										{
											v_count_ok++;
											$('tr.jx-edit-g[record_group="' + ret.ok[i].table_name + '"][row_id="' + ret.ok[i].id + '"] input[name="checkbox_id[]"]').each( function()	{
												$( this ).attr( 'checked', false );
												select_control_edit( this );
											});
											$( 'tr.jx-edit-g[record_group="' + ret.ok[i].table_name + '"][row_id="' + ret.ok[i].id + '"]' ).remove();
										}
									}

									if ( ret.fail.length != 0 )
									{
										for ( i=0; i < ret.fail.length; i++ )
										{
											v_count_fail++;
											$( 'tr.jx-edit-g[record_group="' + ret.fail[i].table_name + '"][row_id="' + ret.fail[i].id + '"]' ).addClass( 'error' );
											//ret.fail[i].message
											//ret.fail[i].message_type
										}
									}
									
									if ( v_count_ok > 0 )
									{
										if ( v_count_ok == 1 )
										{
											show_success( 'Foi excluída 1 linha.' ); // one_line_deleted
										}
										else
										{
											show_success( 'Foram excluídas ' + v_count_ok + ' linhas.' ); // many_lines_deleted
										}
									}
									if ( v_count_fail == 1 )
									{
										show_error( 'Uma linha falhou ao ser eliminada.' ); // one_line_deleted_fail
									}
									else if ( v_count_fail > 1 )
									{
										show_error( 'Algumas linhas falharam ao serem eliminadas.' ); // many_lines_deleted_fail
									}
									else if ( v_count_ok == 0 )
									{
										show_error( 'Nenhuma linha excluída.' ); // no_line_deleted
									}
									edit_delete_button();
									set_form_saved();
									
									vcount_record = 0;
									$('input[record_group="' + $table + '"][name="checkbox_id[]"]').each( function()	{
										vcount_record++;
									});
									if ( vcount_record == 1 )
									{
										create_new_grid( $obj );
									}
								}
			});
}

function jx_set_input_edit_functions( selector_parent )
{
	var sel_parent = null;
	if ( selector_parent != null )
	{
		sel_parent = selector_parent || ' ';
	}
	else
	{
		sel_parent = '';
	}

	$( " " + sel_parent + ' input[datatype="date"]' ).datepicker({
		 showOn: "button"
		,buttonImage: "/assets/img/calendar.gif"
		,buttonImageOnly: true
		,changeMonth: true
		,changeYear: true
		,yearRange: '1900:c+10'
		,numberOfMonths: 2
		,showButtonPanel: true
	});
//TODO: Ao executar este comando abaixo, a data com 4 digitos no ano some.
//	$( 'input[datatype="date"]' ).datepicker( "option", $.datepicker.regional[ "pt-BR" ] );

	// controle das bordas de input.
	$( " " + sel_parent + " div.campo-borda input" ).focus (
				function(){
							$(this).parent( "div.campo-borda" ).addClass( 'light-border' );
							$(this).parent( "div.campo-borda" ).removeClass( 'normal-border' );
						}
		);
	$( " " + sel_parent + " div.campo-borda input" ).focusout (
			function(){
						$(this).parent( "div.campo-borda" ).addClass( 'normal-border' );
						$(this).parent( "div.campo-borda" ).removeClass( 'light-border' );
					}
	);

	// AUTOCOMPLETE
	$( " " + sel_parent + ' .jx-autocomplete' ).autocomplete( setAutocomplete() );

	$( 'section#edicao ' + sel_parent + ' div.campo-borda' ).on( 'click', 'a.show_all', 
			function(){
						showAllAutoComplete( this );
					}
		);
	// AUTOCOMPLETE: fim
	
	// Abre a página de edição para criar uma nova FK.
	$( 'section#edicao ' + sel_parent + ' div.campo-borda' ).on( 'click', 'a.create_edit',
			function() {
							createEdit( this );
						}
			);

	// CHECK CONSTRAINTS
	$( "button.radioset_ck" ).click(
			 function(event)	{
							event.preventDefault();
							$group_btn	=	$( this ).parent( 'div.radioset_ck' );
							$group	=	$( $group_btn ).parent( 'div.radiogroup_ck' );
							$input	=	$( $group ).children( 'input' );
							set_changed( this );
							$input.val( $( this ).attr( 'value-ck' ) );
			 			}
			);
	// CHECK CONSTRAINTS: fim

	// Marca o form como alterado.
	$( " " + sel_parent + ' input' ).on( 'change',
				function(){
							set_changed( this );
						}
			);
	$( " " + sel_parent + ' textarea' ).on( 'change',
			function(){
						set_changed( this );
					}
		);

	// CONTROLES DA LINHA EDIT
	// Seleciona a linha.
	$('tr.jx-edit-g td.jx-select :checkbox').on( 'click',
		function()	{
				 		select_control_edit( this );
					}
		);
	
}

function jx_set_all_functions()
{
	// CONTROLES DA PÁGINA
	// Havendo um filtro de FK ativo, mostramos o botão para eliminar este filtro.
	if ( $( 'input[name$="jx-filter-parent"]' ).val() != '' )
	{
		$( "section.acoes button.action.clear_filter" ).show();
	}
	// CONTROLES DA PÁGINA: fim

	
	// Anuncios, feedback;
	$( "div.ads-feedback a.ads-feedback").click(
			function(event)	{
								event.preventDefault();
								var $hist_id = $( this ).parent( "div.ads-feedback" ).attr( "ad_hist_id" );

								$.ajax({
									 url: "/anuncio/click/" + $hist_id
									,dataType: 'json'
									,type: 'post'
									,error: function( jqXHR, textStatus, errorThrown )	{
															}
									,success: function( ret ) {
															}
										});

								var url = $( this ).attr( 'href' );
								window.open( url, "_blank" );
							}
			);
	
	// Todos os links devem verificar se há alteração antes de enviar para nova página.
	$( "a" ).not('.accordion-toggle').not('.show_all').not('.create_edit').not( ".ads-feedback" ).click( //show_all create_edit
			function(event)	{
								event.preventDefault();
								if ( check_for_change()
								   )
								{
									var url = $( this ).attr( 'href' );
									window.open( url, "_self" );
								}
							}
			);

	// CONTROLES DA LINHA INDEX
	// Seleciona a linha.
	$('tr.jx-index td.jx-select :checkbox').on( 'click',
		function()	{
				 	select_control( this );
					}
		);
	// Filtra as linhas a partir da FK
	$( 'a.action.filter' ).click(
			 function(event)	{
									event.preventDefault();
									if ( check_for_change() )
									{
										var filter_value = $(this).attr( "filter" );
										var filter_now   = $( 'input[name$="jx-filter-parent"]' ).val();
										if ( filter_now )
										{
											filter_now   = filter_now + ' and ' + filter_value;
										}
										else
										{
											filter_now   = filter_value;
										}
							 			$( 'input[name$="jx-filter-parent"]' ).val( filter_now );
										$( "form" ).submit();
									}
					 			}
			);

	// Exibe informacoes detalhadas das FKs associadas a linha atual.
	$( 'span.jx-parent' ).on( 'click',
			 function(){
				 			$(this).popover( 'toggle' );
				 			
				 			$( 'button.action.close-popover' ).click(
				 					 function(event)	{
				 						 					$( 'span.jx-parent' ).popover( 'hide' );
				 							 			}
				 					);
				 			$( 'div.jx-parent-fly button.action.filter' ).click(
				 					 function(event)	{
				 											event.preventDefault();
				 											if ( check_for_change() )
				 											{
				 												var filter_value = $(this).attr( "filter" );
				 												var filter_now   = $( 'input[name$="jx-filter-parent"]' ).val();
				 												if ( filter_now )
				 												{
				 													filter_now   = filter_now + ' and ' + filter_value;
				 												}
				 												else
				 												{
				 													filter_now   = filter_value;
				 												}
				 									 			$( 'input[name$="jx-filter-parent"]' ).val( filter_now );
				 												$( "form" ).submit();
				 											}
				 							 			}
				 					);

				 			$( 'div.jx-parent-fly button.action.edit' ).click(
				 					function(event) {
				 									event.preventDefault();
				 									if ( check_for_change() )
				 									{
				 										urlEdit = $(this).attr( 'edit_url' );
				 										window.open( urlEdit, "_self" );
				 									}
				 								}
				 					);

				 			$( 'div.jx-parent-fly button.action.facebook' ).click(
				 					function(event) {
				 									event.preventDefault();
				 									if ( check_for_change() )
				 									{
				 										face_id = $(this).attr( 'id_facebook' );
				 										window.open( 'http://www.facebook.com/' + face_id, "_blank" );
				 									}
				 								}
				 					);

				 			$( 'div.jx-parent-fly button.action.mail' ).click(
				 					function(event) {
				 									event.preventDefault();
				 									if ( check_for_change() )
				 									{
				 										show_message( 'Função em desenvolvimento', 'warning', 3 );
				 									}
				 								}
				 					);

				 			$( 'button.action.close-popover' ).tooltip( { placement : 'bottom', delay: { show: 500, hide: 100 } } );
				 			$( 'button.action.filter' ).tooltip( { placement : 'bottom', delay: { show: 500, hide: 100 } } );
				 			$( 'button.action.edit' ).tooltip( { placement : 'bottom', delay: { show: 500, hide: 100 } } );
				 			$( 'button.action.mail' ).tooltip( { placement : 'bottom', delay: { show: 500, hide: 100 } } );
						}
			);

	// Filtra a pagina atual com a FK selecionada.
	$('div.jx-parent-fly button.action.filter' ).click(
			 function(event)	{
// TODO: fazer o filtro ficar incremental com várias FKs combinadas.
									event.preventDefault();
									if ( check_for_change() )
									{
										var filter_value = $(this).attr( "filter" );
							 			$( 'input[name$="jx-filter-parent"]' ).val( filter_value );
										$( "form" ).submit();
									}
					 			}
			);

	// Elimina filtro de pagina pela FK.
	$('section.acoes button.action.clear_filter' ).click(
			 function(event)	{
									event.preventDefault();
									if ( check_for_change() )
									{
									 	$( 'input[name$="jx-filter-parent"]' ).val( '' );
										$( "form" ).submit();
									}
			 					}
			);
	
	// Lança pagina de edição de FK.
	$( 'div.jx-parent-fly button.action.edit' ).click(
			function(event) {
							event.preventDefault();
							if ( check_for_change() )
							{
								urlEdit = $(this).attr( 'edit_url' );
								window.open( urlEdit, "_self" );
							}
						}
			);

	// Lança o delete;
	$( 'section.acoes button.action.delete' ).click(
			function(event) {
							event.preventDefault();
							$( "#confirm-delete" ).dialog({
								resizable: false,
								width: 400,
								height: 170,
								modal: true,
								title: "Confirmação",
								buttons: {
									"Excluir": function() {
										$( this ).dialog( "close" );
										
										delete_from_index();
									},
									"Cancelar": function() {
										$( this ).dialog( "close" );
									}
								}
							});
						}
			);

	// Marcar todas as linhas;
	$( 'section.acoes button.action.select-all' ).click(
			function(event) {
							event.preventDefault();
						    $('td.jx-select input[name="checkbox_id[]"]' ).each( function()	{
						    																	$( this ).attr( 'checked', true );
						    																	select_control( this );
						    																});
						}
			);
	// Desmarcar todas as linhas;
	$( 'section.acoes button.action.unselect-all' ).click(
			function(event) {
							event.preventDefault();
						    $('td.jx-select input[name="checkbox_id[]"]' ).each( function()	{
						    																	$( this ).attr( 'checked', false );
						    																	select_control( this );
						    																});
						}
			);
	// CONTROLES DA LINHA INDEX: fim
	
	// CONTROLES DE BOTÕES
	// Lança o delete do form (edit).	// Lança o edição;
	$("section.acoes button.action.edit" ).click(
			function(event) {
							event.preventDefault();
							v_count = 0;
							editSelection = $('.jx-select :checkbox').serializeArray();

							jQuery.each	(	 editSelection
											,function( i, line )
											{
												if ( line.value != null )
												{
													if ( line.value )
													{
														urlEdit = $( 'tr#' + line.value + '.jx-index td.jx-index-line' ).children( "a.jx-acao-edit" ).attr( 'href' );
														v_count++;
													}
												}
											}
										);

							if ( v_count == 0)
							{
								show_error( 'Nenhuma linha selecionada.' ); // no_line_selected
							}
							else
							{
								window.open( urlEdit, "_self" );
							}
						}
	);

	// Envia a pagina para alterar a ordenacao da exibicao.
	$('.jx-table-header th' ).click(
			 function()	{
				 			classes_this = $(this).attr( "class" );
					 		if ( classes_this.indexOf( "order" ) != -1 )
					 		{
					 			if( $( 'input[name="jx-order-direction"]' ).val() == "+" )
					 			{
					 				$( 'input[name="jx-order-direction"]' ).val( "-" );
					 			}
					 			else
					 			{
					 				$( 'input[name="jx-order-direction"]' ).val( "+" );
					 			}
							}
					 		else
					 		{
					 			$( 'input[name="jx-order-direction"]' ).val( "+" );
					 		}

					 		var order_field = $(this).attr( "id" );
					 		order_field     = order_field.slice( order_field.search( '-' ) +1 );
				 			$( 'input[name$="jx-order-selection"]' ).val( order_field );
				 			$( "tr.jx-table-header th.order" ).removeClass( "order" );
				 			$( "tr.jx-table-header th." + order_field ).addClass( "order" );

							$( "form" ).submit();
			 			}
			);

	// Salva a página de edição.
	$( 'button.action.save' ).click (
			function(event) {
							event.preventDefault();
							if ( form_is_changed() )
							{
								$( "#confirm-save" ).dialog({
									resizable: false,
									width: 400,
									height: 170,
									modal: true,
									title: "Confirmação",
									buttons: {
										"Salvar": function() {
																$( this ).dialog( "close" );
																$( "#confirm-working" ).dialog({
																	resizable: false,
																	width: 400,
																	height: 170,
																	modal: true
																});
																$( "form" ).submit();
										},
										"Cancelar": function() {
																$( this ).dialog( "close" );
										}
									}
								});
							}
							else
							{
								show_warning( 'Não há alterações para salvar.' ); // has_no_change_to_save
							}
						}
			);
	
	// Ativa a página de edição atual com um novo registro.
	$( 'button.action.create_new' ).click(
			function(event) {
							event.preventDefault();
							if ( check_for_change() )
							{
								var url1 = window.location + "/";
								url = url1.split( "/" )
								urlCreate = "/" + url[3] + "/" + url[4];
								if ( $( 'span.show_header' ).attr( "show_header" ) == "TRUE" )
								{
									window.open( urlCreate, "_self" );
								}
								else
								{
									window.open( urlCreate + "/dialog", "_self" );
								}
							}
						}
			);

	// Tela Inteira
	// Retira menus e rodapés para deixar a tela maior.
	$( 'button.action.full_screen' ).click(
			function(event)	{
							event.preventDefault();
							$( 'header' ).css( 'display', 'none' );
							$( 'footer' ).css( 'display', 'none' );
							$( 'aside' ).css( 'display', 'none' );
							$( 'button.action.normal_screen' ).css( 'display', 'inline-block' );
							$( 'button.action.full_screen' ).css( 'display', 'none' );
							resize_areas();
						}
			);
	$( 'button.action.normal_screen' ).click(
			function(event)	{
							event.preventDefault();
							$( 'header' ).css( 'display', 'block' );
							$( 'footer' ).css( 'display', 'block' );
							$( 'aside' ).css( 'display', 'block' );
							$( 'button.action.normal_screen' ).css( 'display', 'none' );
							$( 'button.action.full_screen' ).css( 'display', 'inline-block' );
							resize_areas();
						}
			);
	// Tela Inteira: fim
	
	// Imprimir
	$( 'button.action.print' ).click(
			function(event)	{
							event.preventDefault();
							if ( check_for_change() )
							{
								event.preventDefault();
								$( 'header' ).css( 'display', 'none' );
								$( 'footer' ).css( 'display', 'none' );
								$( 'aside' ).css( 'display', 'none' );
								resize_areas();

								window.print();

								$( 'header' ).css( 'display', 'block' );
								$( 'footer' ).css( 'display', 'block' );
								$( 'aside' ).css( 'display', 'block' );
								resize_areas();
							}
						}
			);	
	// Imprimir: fim

	$( "button.action.search" ).click(
				function(event){
							event.preventDefault();
							if ( check_for_change() )
							{
								$( 'input[name$="jx_pagina_atual"]' ).val( 1 );
								if ( $( "form" ).attr( 'action' ).indexOf( 'edit' ) != -1 ) // apenas em form do tipo edit ativa o changed.
								{
									// retorno o action do form para a posição que contenha apenas o controller. O search deve ocorrer sobre o index de cada controller.
									$( "form" ).attr( 'action', $( "form" ).attr( 'action' ).slice( 0, $( "form" ).attr( 'action' ).indexOf( 'edit' ) ) );
								}
								$( "form" ).submit();
							}
						}
			);

	// Botão Criar
	$( "button.action.criar" ).click(
				function(event){
							event.preventDefault();
							if ( check_for_change() )
							{
								window.open( '/' + $(this).attr( 'controller_atual' ) + '/edit', "_self" );
							}
						}
			);
	// Botão Criar: fim

	// Chama a pagina de edicao da linha selecionada.
	// desligado com o X antes dos nomes.
	// fica impossível usar no iPad.
	/*
	$('tr.jx-index td.jx-index-line').click	(
				function(){
							var urlEdit = $(this).children( "a.jx-acao-edit" ).attr( 'href' );
							window.open( urlEdit, "_self" );
						}
			);
	*/

	// Paginação
	$( 'button.action.previous' ).click(
				function(event){
							event.preventDefault();
							cur_page = $( 'input[name$="jx_pagina_atual"]' ).val();
							if ( !$(this).hasClass("inativo") )
							{
								cur_page--;
								$( 'input[name$="jx_pagina_atual"]' ).val( cur_page );
								$( "form" ).submit();
							}
						}
			);
	$( 'button.action.first_page' ).click(
			function(event){
						event.preventDefault();
						if ( !$(this).hasClass("inativo") )
						{
							$( 'input[name$="jx_pagina_atual"]' ).val( 1 );
							$( "form" ).submit();
						}
					}
		);
	$( 'button.action.next' ).click(
			function(event){
							event.preventDefault();
							cur_page = $( 'input[name$="jx_pagina_atual"]' ).val();
							if ( !$(this).hasClass("inativo") )
							{
								cur_page++;
								$( 'input[name$="jx_pagina_atual"]' ).val( cur_page );
								$( "form" ).submit();
							}
					}
		);
	$( 'button.action.last_page' ).click(
			function(event){
							event.preventDefault();
							if ( !$(this).hasClass("inativo") )
							{
								$( 'input[name$="jx_pagina_atual"]' ).val( -1 );
								$( "form" ).submit();
							}
					}
		);
	// Paginação: fim

	// Volta a página anterior.
	$( 'button.action.back' ).click(
				function(event){
							event.preventDefault();
							if ( check_for_change() )
							{
								urlBack = $(this).attr( 'back_url' );
								window.open( urlBack, "_self" );
//								window.history.back();
							}
			 			}
			);

	// Desfaz todas as alterações.
	$( 'button.action.reset').click(
				function(event){
							event.preventDefault();
							$( "#confirm-reset" ).dialog({
								resizable: false,
								width: 400,
								height: 170,
								modal: true,
								title: "Confirmação",
								buttons: {
									"Abandonar": function() {
										$( this ).dialog( "close" );

										$( "#confirm-working" ).dialog({
											resizable: false,
											width: 400,
											height: 170,
											modal: true
										});
										
										unset_changed();
									},
									"Cancelar": function() {
										$( this ).dialog( "close" );
									}
								}
							});
						}
			);

	// Ativa página de criação de conta
	$('.button-new-account').click(
			function(){
						window.open( '/criar_conta', "_self" );
					}
		);

	// Envia a pagina
	$( '.acaobutton-sumbit' ).click(
				function(event){
							event.preventDefault();
							if ( check_for_change() )
							{
								$( "form" ).submit();
							}
						}
			);
	// CONTROLES DE BOTÕES: fim

	// Botões de notificação
	$( "button.notif-ctrl-btn-ok" ).click(
				function(event){
							event.preventDefault();
							if ( check_for_change() )
							{
								var notif_pes_id_ant = $( this ).parent( "div.notif-ctrl-btn-group" ).attr( "notif_pes_id" );
								$.ajax({
									 url: "/notificar/feedback/" + notif_pes_id_ant + "/page/l/true"
									,dataType: 'json'
									,type: 'post'
									,error: function( jqXHR, textStatus, errorThrown )	{
															}
									,success: function( ret ) {
																	if ( ret )
																	{
																		if ( ret.tipo == "new" )
																		{
																			$alert_grp	= $( "button.notif-ctrl-btn-adiar" ).parent( "div.notif-ctrl-btn-group" ).parent( "div.notif-ctrl" ).parent( "div.notif-ctrl-group" );
																			$alert = $( $alert_grp ).children( 'div.notif-ctrl[id="' + notif_pes_id_ant + '"]' );
																			alert_html = '<div class="alert alert-success notif-ctrl notif-ctrl-btn-group" notif_pes_id="' + ret.id + '" id="' + ret.id + '">' + $( $alert ).html() + '</div>';
																			alert_html = alert_html.replace( 'notif_pes_id="' + notif_pes_id_ant +'"', 'notif_pes_id="' + ret.id +'"' );

																			$( $alert_grp ).append( alert_html );
																			$new_alert = $( "button.notif-ctrl-btn-adiar" ).parent( "div.notif-ctrl-btn-group" ).parent( 'div.notif-ctrl[id="' + ret.id + '"]' );
																			$( $new_alert ).hide();
																			
																			$( $new_alert ).children( "div.time" ).html( ret.data_hora_envio_format );
																			$( $new_alert ).children( "div.text" ).html( ret.texto_enviado );
																			jx_set_all_functions();
																			$( $alert ).slideUp("slow");
																			$( $new_alert ).slideDown("slow");
																		}
																		else
																		{
																			$( "button.notif-ctrl-btn-adiar" ).parent( "div.notif-ctrl-btn-group" ).parent( "div.notif-ctrl" ).slideUp("slow");
																		}
																	}
															}
										});
							}
						}
			);
	$( "button.notif-ctrl-btn-adiar" ).click(
			function(event){
						event.preventDefault();
						if ( check_for_change() )
						{
							var notif_pes_id_ant = $( this ).parent( "div.notif-ctrl-btn-group" ).attr( "notif_pes_id" );

							$.ajax({
								 url: "/notificar/feedback/" + notif_pes_id_ant + "/page/a/true"
								,dataType: 'json'
								,type: 'post'
								,error: function( jqXHR, textStatus, errorThrown )	{
														}
								,success: function( ret ) {
																if ( ret )
																{
																	if ( ret.tipo == 'new' )
																	{
																		$alert_grp	= $( "button.notif-ctrl-btn-adiar" ).parent( "div.notif-ctrl-btn-group" ).parent( "div.notif-ctrl" ).parent( "div.notif-ctrl-group" );
																		$alert = $( $alert_grp ).children( 'div.notif-ctrl[id="' + notif_pes_id_ant + '"]' );
																		alert_html = '<div class="alert alert-success notif-ctrl notif-ctrl-btn-group" notif_pes_id="' + ret.id + '" id="' + ret.id + '">' + $( $alert ).html() + '</div>';
																		alert_html = alert_html.replace( 'notif_pes_id="' + notif_pes_id_ant +'"', 'notif_pes_id="' + ret.id +'"' );

																		$( $alert_grp ).append( alert_html );
																		$new_alert = $( "button.notif-ctrl-btn-adiar" ).parent( "div.notif-ctrl-btn-group" ).parent( 'div.notif-ctrl[id="' + ret.id + '"]' );
																		$( $new_alert ).hide();
																		
																		$( $new_alert ).children( "div.time" ).html( ret.data_hora_envio_format );
																		$( $new_alert ).children( "div.text" ).html( ret.texto_enviado );
																		jx_set_all_functions();
																		$( $alert ).slideUp("slow");
																		$( $new_alert ).slideDown("slow");
																	}
																	else
																	{
																		$( "button.notif-ctrl-btn-adiar" ).parent( "div.notif-ctrl-btn-group" ).parent( "div.notif-ctrl" ).slideUp("slow");
																	}
																}
														}
									});
						}
					}
		);
	// fim: Botões de notificação

	// CONTROLES DA LINHA EDIT
	// Delete principal do edit.
	$( 'section.acoes-edicao button.action.delete-edit' ).click(
			function(event) {
							event.preventDefault();
							$( "#confirm-delete" ).dialog({
								resizable: false,
								width: 400,
								height: 170,
								modal: true,
								title: "Confirmação",
								buttons: {
									"Excluir": function() {
										$( this ).dialog( "close" );

										$( "#confirm-working" ).dialog({
											resizable: false,
											width: 400,
											height: 170,
											modal: true
										});
										
										delete_edit();

										$( "#confirm-working" ).dialog( "close" );
									},
									"Cancelar": function() {
										$( this ).dialog( "close" );
									}
								}
							});
						}
			);

	// Ativa a página de edição atual com um novo registro.
	$( 'section.acoes-edicao button.action.create-grid' ).click(
			function(event) {
							event.preventDefault();
							
							create_new_grid( this );
						}
			);

	// Lança o delete;
	$( 'section.acoes-edicao button.action.delete-grid' ).click(
			function(event) {
							var $who = this;
							event.preventDefault();
							$( "#confirm-delete" ).dialog({
								resizable: false,
								width: 400,
								height: 170,
								modal: true,
								title: "Confirmação",
								buttons: {
									"Excluir": function() {
										$( this ).dialog( "close" );
										
										delete_from_edit( $who );
									},
									"Cancelar": function() {
										$( this ).dialog( "close" );
									}
								}
							});
						}
			);

	// Marcar todas as linhas;
	$( 'section.acoes-edicao button.action.select-all-grid' ).click(
			function(event) {
							event.preventDefault();
							$table = $( this ).parent( "div.btn-group" ).parent( "div.btn-toolbar" ).parent( "section.acoes-edicao" ).attr('record_group');
						    $('tr.jx-edit-g[record_group="' + $table + '"] td.jx-select input[name="checkbox_id[]"]' ).each( function()	{
						    																	$( this ).attr( 'checked', true );
						    																	select_control_edit( this );
						    																});
						}
			);
	// Desmarcar todas as linhas;
	$( 'section.acoes-edicao button.action.unselect-all-grid' ).click(
			function(event) {
							event.preventDefault();
							$table = $( this ).parent( "div.btn-group" ).parent( "div.btn-toolbar" ).parent( "section.acoes-edicao" ).attr('record_group');
						    $('tr.jx-edit-g[record_group="' + $table + '"] td.jx-select input[name="checkbox_id[]"]' ).each( function()	{
						    																	$( this ).attr( 'checked', false );
						    																	select_control_edit( this );
						    																});
						}
			);
	// CONTROLES DA LINHA EDIT: fim
	
	
	
	
	
	
	
	// ativo o datapicker em todos os campos datatype='date'
	$.datepicker.setDefaults( $.datepicker.regional[ "pt-BR" ] );

	// Prepara os campos já impressos no browser.
	jx_set_input_edit_functions();
};

$(document).ready(function (){
	a_for_ad_not_prep	=	$( "div.ads-feedback a").not( ".ads-feedback" );
	$.each( a_for_ad_not_prep, function(){ $( this ).addClass( "ads-feedback" ); });

	jx_set_all_functions();

	// Havendo uma div de mensagem de edição, exibimos esta mensagem assim que a página abrir.
	if ( $( 'div.message_inicial span' ).attr( 'message_type' ) != '' )
	{
		show_message( $( 'div.message_inicial span' ).text(), $( 'div.message_inicial span' ).attr( 'message_type' ), 5 );
	}

	$( 'span.jx-parent' ).each( function() {
			 					$html = $( this ).html();
								$( this ).popover( { trigger : 'manual', placement : 'bottom', delay: { show: 500, hide: 100 }, template : $html } );
	});

	$( '[data-content]' ).popover( { placement : 'right', delay: { show: 500, hide: 100 } } );
	$( '[title]' ).tooltip( { placement : 'bottom', delay: { show: 500, hide: 100 } } );
	$( '.dropdown-toggle' ).dropdown();

	initialze_page();
});
