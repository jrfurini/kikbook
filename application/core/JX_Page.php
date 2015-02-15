<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Classe para controller que será um CRUD em uma ou N tabelas.
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/core/JX_Page.php
 *
 * $Id: JX_Page.php,v 1.14 2013-04-07 13:24:43 junior Exp $
 *
 */

class JX_Page extends JX_Controller
{
	/**
	 * Variáveis gerais de controle de visualização.
	 */
	var $jx_order_selection			=	'title';
	var $jx_order_direction			=	'+';
	var $jx_search_what			=	'';
	var $jx_filter_parent			=	'';
	var $jx_pagina_atual			=	1;
	var $first_time				=	FALSE;

	/**
	 * 
	 * Variáveis de controle da edição.
	 */
	var $dialog				=	'FALSE';
	var $show_header			=	'TRUE';
	var $grid				=	'FALSE';
	var $print				=	'FALSE';
	var $next_edit_action			=	'update';
	var $actual_edit_action			=	'query';
	var $edit_id				=	NULL;
	var $delete_ret_array			=	array();

	/**
	 * Variáveis de controle visual.
	 */
	var $index_html				=	'jx/index.html';
	var $edit_html				=	'jx/edit.html';
	var $edit_form_html			=	'jx/edit_form_style.html';
	var $edit_grid_html			=	'jx/edit_grid_style.html';
	var $edit_grid_button_html		=	'jx/edit_button_grid.html';

	/**
	 * Construtor da classe.
	 */
	public function __construct( $_config_table = NULL, $_config_visual = NULL )
	{
		log_message( 'debug', "JX_Page.(start)." );
		parent::__construct( $_config_table, $_config_visual );
		
		/**
		 * Lê as variáveis comuns de controle das páginas.
		 */
		$this->jx_order_selection		=	$this->input->post_multi( 'jx-order-selection' );
		if ( !$this->jx_order_selection )
		{
			$this->first_time		=	TRUE; // Sempre retorna valor aqui, se não retornar é porque estamos entrando pela primera vez na página.
			$this->jx_order_selection	=	'title';
		}
		$this->jx_order_direction		=	$this->input->post_multi( 'jx-order-direction' );
		if ( !$this->jx_order_direction )
		{
			$this->jx_order_direction	=	'+';
		}
		$this->jx_search_what			=	$this->input->post_multi( 'jx-search-what' );
		$this->jx_filter_parent			=	$this->input->post_multi( 'jx-filter-parent' );
		$this->jx_pagina_atual			=	$this->input->post_multi( 'jx_pagina_atual' );
		if ( !$this->jx_pagina_atual 
		OR   $this->jx_pagina_atual == ''
		   )
		{
			$this->jx_pagina_atual		=	1;
		}

		$data					=	array	(
									 'jx_order_selection'	=> $this->jx_order_selection
									,'jx_order_direction'	=> $this->jx_order_direction
									,'jx_search_what'	=> $this->jx_search_what
									,'jx_filter_parent'	=> $this->jx_filter_parent
									,'rows'			=> ''
									,'start_line'		=> ''
									,'last_line'		=> ''
									,'total_lines'		=> ''
									);
		$this->load->vars( $data );

		log_message( 'debug', "JX_Page subclass({$this->router->class}.{$this->router->method}) initialized." );
	}

	/**
	 * Retorna todas as linhas da tabela.
	 */
	public function _prep_index( $master_model_name = 'master_model', $where_external = FALSE, $orderby_external = FALSE, $set_parents = TRUE, $use_pagination = TRUE )
	{
		log_message( 'debug', "JX_Page._prep_index()." );
		//TODO: Verificar permissão do usuário antes;

		//TODO: Na montagem das linhas, verificar permissão do usuário para executar determinada função do sistema.

		// Se o campo search foi preenchido na página, restringimos a consulta usando esta informacao.
		$where						=	$where_external;
		$count_table					=	0;

		if ( is_object( $this->$master_model_name ) )
		{
			if ( $this->jx_search_what
			||   $this->jx_filter_parent
			   )
			{
				foreach( $this->tables as $table )
				{
					if ( strtoupper( $table->show_style ) != 'NONE' )
					{
if ( $table->master )
{
						if ( $count_table == 0 )
						{
							if ( $where )
							{
								$where		=	$where . ' AND (';
							}
							else
							{
								$where		=	'(';
							}
						}
		
						if ( $table->master )
						{
							$model_name		=	$master_model_name;
						}
						else
						{
							$model_name		=	$table->model_name;
							/*
							$where			=	" OR EXISTS( select 'S' ".
											$this->$model_name->
							*/
						}
// TODO: Reestruturar o set_from_all do JX_Model para não usar o db->from e o db->join. Com o uso destas funções não conseguimos usar a montagem da query para outros fins a não ser o index da página princial.
						if ( $this->jx_search_what )
						{
							if ( $count_table == 0 )
							{
								$where		=	$where." ( ".$this->$model_name->get_where_search_all( $this->jx_search_what ).")";
							}
							else
							{
								$where		=	$where." OR ( ".$this->$model_name->get_where_search_all( $this->jx_search_what ).")";
							}
						}
		
						// Se ha algum filtro de FK, montamos a query para isso.
						if ( $this->jx_filter_parent )
						{
							if ( $count_table == 0 )
							{
								$where		=	$where." ( ".$this->$model_name->get_where_filter_parent( $this->jx_filter_parent ).")";
							}
							else
							{
								$where		=	$where." and ( ".$this->$model_name->get_where_filter_parent( $this->jx_filter_parent ).")";
							}
						}
						$count_table			+=	1;
}
					}
				}
			}

			if ( $where && $count_table > 0 )
			{
				$where			=	$where . ' ) ';
				$where			=	str_replace( " = -1", " IS NULL", $where );
			}
			
			// Usa informacoes da página para montar o order by.
			if ( $orderby_external )
			{
				$order_by	= $orderby_external;
				
			}
			else
			{
				$order_by	= $this->$master_model_name->_prep_order_by( $this->jx_order_selection, $this->jx_order_direction );
			}

			// Obtém o número total de linhas.
			$total_rows		= $this->$master_model_name->count_all( $where );
	
			// Ativamos a paginação.
			if ( !$this->singlepack->user_connected() )
			{
				// Se o usuário não está conectado, mostramos apenas 6 linhas para ele.
				$this->jx_pagination->initialize(
									array	(
										 'total_rows'		=> 6
										,'cur_page'		=> 1
										,'use_pagination'	=> $use_pagination
										)
								);
			}
			else
			{
				$this->jx_pagination->initialize(
									array	(
										 'total_rows'		=> $total_rows
										,'cur_page'		=> $this->jx_pagina_atual
										,'use_pagination'	=> $use_pagination
										)
								);
			}
			
			// Executa a consulta.
			log_message( 'debug', "JX_Page._prep_index( Queries.select_for_index ).start({$this->jx_pagination->get_start_line()}).last({$this->jx_pagination->get_lines_per_page()}).total({$this->jx_pagination->get_total_lines()})." );
			$this->$master_model_name->select_for_index( $where, $order_by, $this->jx_pagination->get_start_line(), $this->jx_pagination->get_lines_per_page() );
			
			log_message( 'debug', "JX_Page._prep_index( Queries.get_query_rows )." );
			$rows			= $this->$master_model_name->get_query_rows( $set_parents = $set_parents );
			
			log_message( 'debug', "JX_Page._prep_index( Queries.get_rows_parents )." );
			if ( $set_parents )
			{
				$parents_info	= $this->$master_model_name->get_rows_parents();
			}
			else
			{
				$parents_info	= array();
			}
			
			log_message( 'debug', "JX_Page._prep_index( Queries.get_fields_info )." );
			$fields			= $this->$master_model_name->get_fields_info( 'INDEX', $rows );
			$master_table_name	= $this->master_table;
		}
		else
		{
			log_message( 'debug', "JX_Page._prep_index( sem consulta )." );
			$rows			=	array();
			$fields			=	array();
			$parents_info		=	array();
			$master_table_name	=	NULL;
			$total_rows		=	0;
		}

		$data			= array	(
						// Obtém as linhas que serão exibidas.
						 'rows'			=> $rows
						,'fields'		=> $fields
						,'master_table'		=> $master_table_name
						,'parents_info'		=> $parents_info
						,'total_rows'		=> $total_rows
						,'jx_pagina_atual'	=> $this->jx_pagination->get_cur_page()
						,'start_line'		=> $this->jx_pagination->get_start_line()
						,'last_line'		=> $this->jx_pagination->get_last_line()
						,'total_lines'		=> $this->jx_pagination->get_total_lines()
						);
		$this->load->vars( $data );

		log_message( 'debug', "JX_Page._prep_index(fim)." );

		return TRUE;
	}

	public function index()
	{
		$this->_prep_index();
		$this->load->view( $this->index_html );
	}

	/**
	 * Abre a pagina completa de edição de uma linha.
	 */
	public function _prep_edit( $force_id = NULL, $force_edit_type = NULL )
	{
		/**
		 * Se receber o ID via parâmetro, forçamos a leitura por este ID.
		 */
		if ( $force_id )
		{
//echo ' FORCE ID='.$force_id.'<br>';
			if ( is_numeric( $force_id ) )
			{
				$this->edit_id			= $force_id;
			}
			else
			{
				$this->edit_id			= NULL;
			}
			$edit_type				= ( $force_edit_type ) ? $force_edit_type : 'EDIT';
		}
		else
		{
			/**
			 * ID veio pela URL da página e o campo ACTION não está preenchido.
			 * 	Edição, 	abre a página com os dados do ID informado.
			 */
			$this->edit_id				= $this->uri->segment( 3 );
			// confirmamos aqui se o o ID, posição 3, não está com o dialog ou grid.
			if ( is_numeric( $this->edit_id ) )
			{
				/**
				 * Pegamos na sequencia 4 da URL se queremos uma edição com grid ou dialog.
				 */
				$edit_type			= $this->uri->segment( 4 );
			}
			else
			{
				/**
				 *  Não temos id, mas sim um possível informação do estilo de exibição.
				 */
				$edit_type			= $this->uri->segment( 3 );
				$this->edit_id			= NULL;
			}
//echo ' URL ID='.$this->edit_id.'<br>';
		}
		/**
		 * Tratamos, se existir, um novo método de exibição da página.
		 */
		$this->dialog					= 'FALSE';
		$this->show_header				= 'TRUE';
		$this->grid					= 'FALSE';
		$this->print					= 'FALSE';
		/**
		 * Edita em uma página que não contenha os headers e footers do site.
		 */
		if ( strtoupper( $edit_type ) == 'DIALOG' )
		{
			$this->dialog				= 'TRUE';
			$this->show_header			= 'FALSE';
		}
		/**
		 * Edita várias linhas ao mesmo tempo.
		 */
		elseif  ( strtoupper( $edit_type ) == 'GRID' )
		{
			$this->grid				= 'TRUE';
		}
		/**
		 * Imprime em PDF a linha enviada.
		 */
		elseif  ( strtoupper( $edit_type ) == 'PRINT' )
		{
			$this->print				= 'TRUE';
		}
		
// TODO: Criar, automaticamente, JS que validam os campos na página;
		$this->actual_edit_action			= $this->input->post_multi( 'jx_action' );
		/*
		 * Não há informação de action.
		 */
		if ( ! $this->actual_edit_action )
		{
			/*
			 * Foi informado um ID.
			 * Assumimos que é a primeira entrada da página e o ID foi enviado via URL.
			 */
			if ( $this->edit_id )
			{
				$this->actual_edit_action	= 'query';
			}
			/*
			 * Não foi informado um ID.
			 * Assumimos abriremos a página vazia para ser preenchida.
			 */
			else
			{
				$this->actual_edit_action	= 'new';
			}
		}
		
		/*
		 * Se action estiver insert ou update, pegamos os dados da página e formamos um cubo com eles.
		 */
		$message_text					= NULL;
		$message_type					= NULL;

		if ( in_array( $this->actual_edit_action, array( 'insert', 'update' ) ) )
		{
			$id					= NULL;
// TODO: reativar este comando. Hoje ele dá erro de LOCK quando uma das tabelas de update dá erro. No calculo de kiks deu LOCK mesmo sem erro de db.
//			$this->db->trans_begin();

			foreach( $this->tables as $table )
			{
				if ( $table->read_write == 'write' )
				{
//echo 'atualizando tabela ('.$table->name.') <br/>';
					$model_name		= $table->model_name;
					$row_name		= $table->name;
					
					// Chama a atualização das linhas no model e recupera o ID para consulta. Caso seja insert o update decide isso.
					$id			= $this->$model_name->update(); // Aqui forçamos uma atribuição de valor ao $id.
					if ( $id === FALSE ) // Só quando der erro mesmo vem FALSE
					{
						$message_text	= 'Alteração dos dados falhou.';
						$message_type	= 'error';
						log_message( 'debug', "JX_Page.(update) FALHA." );
//TODO: mudar o comando abaixo.
$this->$model_name->select_one( NULL, $this->edit_id );
$data		= array	(
			 $row_name	=> $this->$model_name->get_query_rows( $set_parents = TRUE, $max_rows = 1 )
			);
/*						
						$data					= array	(
												 $row_name	=> $this->input->_return_input_data( $table->name )// retorna os dados que enviados.
												);
*/
						$this->load->vars( $data );
						break; // Saímos do loop após ocorrer o primeiro erro.
					}
					else
					{
						if ( $table->master )
						{
							$this->edit_id	= ( $id ) ? $id : $this->edit_id;
						}

						$message_text	= 'Dados alterados.';
						$message_type	= 'success';
						log_message( 'debug', "JX_Page.(update) SUCESSO." );
						
						$this->$model_name->get_one_by_id( $this->edit_id );
						$data					= array	(
												 $row_name	=> $this->$model_name->get_query_rows( $set_parents = TRUE, $max_rows = 1 )
												);
						$this->load->vars( $data );
					}
				}
			}

			if ( $message_type == 'error' )
			{
				$this->db->trans_rollback();
			}
			else
			{
				$this->db->trans_commit();
			}

			// Após atualizar os dados, voltamos a consultar eles.
			$this->actual_edit_action		= 'query';
		}

		/*
		 * Retorna a linha de edição.
		 */
		$rows						= new stdClass();
		if ( $this->actual_edit_action == 'query'
		||   $this->actual_edit_action == 'new'
		   )
		{
			$this->next_edit_action			= 'update';
			$master_id				= $this->edit_id;

			foreach( $this->tables as $table )
			{
//echo ' table='.$table->name.'<br>';
//echo ' id='.$this->edit_id.'<br>';

				$model_name			= $table->model_name;
				$table_name			= $table->name;
				// Carrega as linhas se show=TRUE;
				if ( strtoupper( $table->show_style ) != 'NONE' )
				{
					if ( $this->edit_id )
					{
						$where			= str_replace( '##id##', $this->edit_id, $table->where );
//TODO: Verificar a hierarquia de tabelas para montar o where completamentar.
//log_message( 'debug', 'where='.$where );
//log_message( 'debug', 'MODEL->'.$model_name );
						if ( $table->max_rows == 1 )
						{
							if ( $table->master )
							{
								// Master é igual ao controller.
								if ( $table->name == $this->router->class )
								{
//log_message( 'debug', 'rows(m1) table->'.$table_name.'<br/>' );
									$this->$model_name->select_one( $where, $this->edit_id );
									$rows->$table_name	= $this->$model_name->get_query_rows( $set_parents = TRUE );
								}
								else
								{
									// O where da tabela master, sem ser o mesmo do controller, deve conter um where que retorne o ID da tabela do controller.
//log_message( 'debug', 'rows(m2) table->'.$table_name.'<br/>' );
									$this->$model_name->select_one( $where, NULL );
									$rows->$table_name	= $this->$model_name->get_query_rows( $set_parents = TRUE );
								}
								foreach( $rows->$table_name as $row )
								{
									$master_id	= $row->id;
									break;
								}
//echo '...master_id='.$master_id.'<br>';
							}
							else
							{
/*
 * ESTA LOGICA DE AMARRAÇAO ENTRE VÁRIAS TABELAS AUTOMATICAMENTE NÃO ESTÁ FUNCIONANDO.
								$r_table_id			= NULL;
								$r_table_name			= $table->r_table_name;
								if ( $table->r_table_name
								&&   isset( $rows->$r_table_name )
								   )
								{
									$r_table_id		= $rows->$r_table_name->id;
echo '...$r_table_id='.$r_table_id.'<br>';
								}
								else
								{
									$r_table_id		= $master_id;
echo '...$r_table_id(2)='.$r_table_id.'<br>';
								}
*/
//log_message( 'debug', 'rows(1) ->'.$table_name.' where='.$where );
								$this->$model_name->select_all( $where, $table->orderby );
								$rows->$table_name		= $this->$model_name->get_query_rows( $set_parents = TRUE );
							}
						}
						else
						{
//log_message( 'debug', 'rows(2) ->'.$table_name.' where='.$where );
							$this->$model_name->select_all( $where, $table->orderby );
							$rows->$table_name			= $this->$model_name->get_query_rows( $set_parents = TRUE );
						}
					}
				}

				// Sem dados para a tabela, colocamos uma linha com o ID.
				if ( !isset( $rows->$table_name ) || !$rows->$table_name )
				{
//log_message( 'debug', 'rows(3) ->'.$table_name.' vazio' );
					$rows->$table_name			= array( array( 'id' => NULL ) );
				}
//log_message( 'debug', ' '.$table->name.'.fim' );
			}
		}

		/*
		 * Não existe ID informado, abriremos a página para criação de uma nova linha.
		 */
		else // new
		{
			$this->next_edit_action			= 'insert';
			foreach( $this->tables as $table )
			{
				$table_name			= $table->name;
				$rows->$table_name		= array();
			}
		}

//TODO: Criar métodos que permitam ordenar os itens de edição. "Alfabético" e "Com erro"

		$data						= array	(
									 'jx_action'		=> $this->next_edit_action
									,'jx_message'		=> $message_text
									,'jx_message_type'	=> $message_type
									,'show_header'		=> $this->show_header
									);
		$this->load->vars( $data );

		/*
		 * Carrega variáveis de página para as tabelas associadas.
		 */
		$table_fields					= new stdClass();
		foreach( $this->tables as $table )
		{
			if ( strtoupper( $table->show_style ) != 'NONE' )
			{
//echo 'Carrega Campos para '.$table->name.' model='.$table->model_name.'<br>';
				$model_name				= $table->model_name;
				$table_name				= $table->name;
	
				// Carrega o array de campos (fields) para cada tabela.
				$table_fields->$table_name->fields	= $this->$model_name->get_fields_info( 'EDIT', NULL, $table->seq_columns );
				$table_fields->$table_name->style	= $table->show_style;
				$table_fields->$table_name->table_name	= $table_name;
			}
		}
		$data				= array	(
							 'table_fields'				=>	$table_fields
							,'rows'					=>	$rows
							,'edit_id'				=>	$this->edit_id
							);
		$this->load->vars( $data );
	}

	public function edit()
	{
		$this->_prep_edit();
		
		$this->load->view( $this->edit_html, array	(
								 'edit_grid_style'  => $this->edit_grid_html
								,'edit_form_style'  => $this->edit_form_html
								,'edit_grid_button' => $this->edit_grid_button_html
								)
				);
	}

	/**
	 * Usa as informações cadastradas no controller para realizar um delete na hierarquia das tabelas.
	 */
	public function delete( $id, $table_name = NULL )
	{
		$this->db->trans_begin();

		if ( $id )
		{
			// Inicializa as variáveis.
			$delete_ret_view				=	TRUE;
			$delete_ret					=	TRUE;

			if ( !is_numeric( $id ) ) // Tratamos como json quando não for numérico.
			{
				$id_obj					=	json_decode( str_replace( "'", '"', $id ) );
			}
			else // Tornamos o ID um objeto para facilitar o processo abaixo.
			{
				$id_obj					=	new stdClass();
			}
//print_r( $id_obj );
			// Se não informamos a tabela, usamos a tabela master do controller.
			if ( !$table_name )
			{
				foreach( $this->tables as $table )
				{
					if ( $table->master )
					{
						$table_name		=	$table->name;
						break;
					}
				}
			}

			// Sempre registramos o ID para a visão informada.
			if ( key_exists( $table_name, $this->tables )
			&&   !$this->tables[ $table_name ]->is_view
			   )
			{
				$id_obj->$table_name			=	$id;
			}

			// Montamos o array de nome enviados para o delete. Se for uma visão buscamos num loop todas as tabelas contidas no model dela.
			$ar_table_name					=	array();
			if ( $this->tables[ $table_name ]->is_view )
			{
				foreach( $this->tables as $table )
				{
					if ( $table->part_of_view == $table_name )
					{
						$new_table				=	new stdClass();
						$tname					=	$table->name;
						$new_table->where			=	str_replace( '##id##', $id_obj->$tname, $table->where );
						$new_table->table_name			=	$table->name;
						$new_table->r_table_name		=	$table->r_table_name;
						$ar_table_name[ $table->name ]		=	$new_table;

						log_message( 'debug', "JX_Page.delete( tabela a deletar $tname)." );
					}
				}
			}
			else
			{
				$new_table				=	new stdClass();
				$new_table->where			=	$this->tables[ $table_name ]->where;
				$new_table->table_name			=	$this->tables[ $table_name ]->name;
				$new_table->r_table_name		=	$this->tables[ $table_name ]->r_table_name;
				$ar_table_name[ $table_name ]		=	$new_table; // Usaremos o ID para o delete. Delete direto.
			}

			foreach( array_reverse( $this->tables ) as $table )
			{
				if ( $table->read_write == 'write' )
				{
					$model_name			=	$table->model_name;
					$tname				=	$table->name;
					if ( key_exists( $table->name, $ar_table_name ) // É a tabela enviada ou uma das tabelas da visão enviada.
					||   ( key_exists( $table_name, $table->r_table_name ) // Ou é uma das tabelas filho configurada para a tabela envida.
					&&     $table->delete_rule == 'cascade'
					     )
					   )
					{
						if ( ( key_exists( $table->name, $ar_table_name ) // É a tabela enviada ou uma das tabelas da visão enviada.
						&&     empty( $ar_table_name[ $table->name ]->where ) // Não temos where, então é pelo ID.
						     )
						||   isset( $id_obj->$tname )
						   )
						{
							// Faz um delete pela PK.
							log_message( 'debug', "JX_Page.delete( deletando $model_name com ID={$id_obj->$tname})." );
							$delete_ret			=	$this->$model_name->delete( $id_obj->$tname );
						}
						elseif ( key_exists( $table_name, $table->r_table_name ) // É uma das tabelas filho configurada para a tabela envida.
						&&       $table->delete_rule == 'cascade'
						       )
						{
							// Faz um delete pela FK. Montamos o where apartir do id de FK direto.
							log_message( 'debug', "JX_Page.delete( deletando $model_name via FK." );
							$delete_ret			=	$this->$model_name->delete( NULL, array( $table_name.'_id' => $id_obj->$tname ) );
						}
						else
						{
							// No array de tabela envidas o value é o where montado acima.
							log_message( 'debug', "JX_Page.delete( deletando $model_name com where ID={$ar_table_name[ $table->name ]->where})." );
							$delete_ret			=	$this->$model_name->delete( NULL, $ar_table_name[ $table->name ]->where ); // Deleção pelo where.
						}
						
						if ( $this->tables[ $table_name ]->is_view ) // Visão tramos diferente.
						{ // se está inválido fica inválido.
							$delete_ret_view				=	( $delete_ret_view === TRUE ) ? $delete_ret : $delete_ret_view;
						}
						else
						{
							if ( $delete_ret )
							{
								$this->delete_ret_array[ 'ok' ][]	=	array	(
															 'message_type'		=>	'ok'
															,'message'		=>	$delete_ret
															,'id'			=>	$id // Para a página interessa o ID original.
															,'table_name'		=>	$table->name
															);
							}
							else
							{
	//TODO: Tradução de mensagem.
								$this->delete_ret_array[ 'fail' ][]	=	array	(
															 'message_type'		=>	'error'
															,'message'		=>	'Falha ao eliminar a linha.'
															,'id'			=>	$id // Para a página interessa o ID original.
															,'table_name'		=>	$table->name
															,'db_error_number'	=>	$this->db->_error_number()
															,'db_error_message'	=>	$this->db->_error_message()
															);
							}
						}
					}
					elseif ( $table_name == $table->r_table_name )
					{
						if ( $table->delete_rule == 'restrict' )
						{
							/*
							 * Havendo filhos ligados à linha deletada, paramos o processo e retornamos um erro.
							 */
							if ( $this->$model_name->count_all( array( $table_name.'_id' => $id_obj->$tname ) ) > 0 )
							{
								$this->delete_ret_array[ 'fail' ][]	=	array	(
															 'message_type'		=>	'error'
															,'message'		=>	'Esta linha possui "'.$this->$model_name->get_header().'".'
															,'id'			=>	$id // Para a página interessa o ID original.
															,'table_name'		=>	$table->name
															,'db_error_number'	=>	$this->db->_error_number()
															,'db_error_message'	=>	$this->db->_error_message()
															);
								break;
							}
						}
						elseif ( $table->delete_rule == 'setnull' )
						{
							/*
							 * Desliga os filhos da linha deletada através de update com NULL na FK.
							 */
							if ( $this->$model_name->update_where( array( $table_name.'_id' => $id_obj->$tname ), array( $table_name.'_id' => 'null' ) ) )
							{
								$this->delete_ret_array[ 'ok' ][]	=	array	(
															 'message_type'		=>	'ok'
															,'message'		=>	NULL
															,'id'			=>	$id // Para a página interessa o ID original.
															,'table_name'		=>	$table->name
															);
							}
							else
							{
								$this->delete_ret_array[ 'fail' ][]	=	array	(
															 'message_type'		=>	'error'
															,'message'		=>	'Falha no set_null dos filhos de "'.$this->$model_name->get_header().'".'
															,'id'			=>	$id // Para a página interessa o ID original.
															,'table_name'		=>	$table->name
															,'db_error_number'	=>	$this->db->_error_number()
															,'db_error_message'	=>	$this->db->_error_message()
															);
								break;
							}
						}
					}
				}
			}
			
			if ( $this->tables[ $table_name ]->is_view )
			{
				if ( $delete_ret_view )
				{
					$this->delete_ret_array[ 'ok' ][]	=	array	(
												 'message_type'		=>	'ok'
												,'message'		=>	$delete_ret_view
												,'id'			=>	$id // Para a página interessa o ID original.
												,'table_name'		=>	$table_name
												);
				}
				else
				{
//TODO: Tradução de mensagem.
					$this->delete_ret_array[ 'fail' ][]	=	array	(
												 'message_type'		=>	'error'
												,'message'		=>	'Falha ao eliminar a linha.'
												,'id'			=>	$id // Para a página interessa o ID original.
												,'table_name'		=>	$table_name
												,'db_error_number'	=>	$this->db->_error_number()
												,'db_error_message'	=>	$this->db->_error_message()
												);
				}
			}
		}
		else
		{
			$this->delete_ret_array[ 'fail' ][]	=	array	(
										 'message_type'		=>	'error'
										,'message'		=>	'ID nulo.'
										,'id'			=>	$id // Para a página interessa o ID original.
										,'table_name'		=>	$table_name
										,'db_error_number'	=>	$this->db->_error_number()
										,'db_error_message'	=>	$this->db->_error_message()
										);
		}

		if ( count( $this->delete_ret_array[ 'fail' ] ) != 0 )
		{
			$this->db->trans_rollback();
		}
		else
		{
			$this->db->trans_commit();
		}

		return ( count( $this->delete_ret_array[ 'fail' ] ) != 0 );
	}
	
	/**
	 * Exclui uma linha na base de dados diretamente da página "index".
	 * Usar via AJAX.
	 */
	public function delete_index( $table = NULL )
	{
		// Prepara retorno ao json.
		$this->delete_ret_array					=	array();
		$this->delete_ret_array['ok']				=	array();
		$this->delete_ret_array['fail']				=	array();
		
		$delete_selection					=	$this->input->get_post_multi( 'checkbox_id' );
		if ( $delete_selection )
		{
			foreach( $delete_selection as $id_json )
			{
				$this->delete( $id_json, $table );
			}
		}
		else 
		{
			// Não recebeu dados para processar.
			$this->delete_ret_array[ 'fail' ][]		=	array	(
											 'id'		=>	null
											,'message_type'	=>	'warning'
											,'message'	=>	'Nenhuma linha selecionada.'
											);
		}
		
		echo json_encode( $this->delete_ret_array );
	}

	/**
	 * Retorna os dados para exibir como "parent" nas páginas index.
	 * 
	 * Entrada:
	 * 	- id da FK
	 * 	- id da PK do filho
	 * 
	 * Saída:
	 * 	- id parent
	 * 	- image parent
	 * 	- mime_type parent
	 * 	- table parent
	 * 	- header parent
	 * 	- title parent
	 * 	- email parent
	 * 
	 */
// TODO: Confirmar a real necessidade de finalizar esta idéia. O motivo é a performance para montar páginas com parents.
	public function show_fly_parent()
	{
NULL;
/*
		if ( isset( $this->input->post_multi( 'table_name ' ) ) &&
		     isset( $this->input->post_multi( 'id' ) )
		   )
		{
			$table_name	=	array	(
							 'id'
							,'image'
							,'mime_type'
							,'table'
							,'header'
							,'title'
							,'email'
							);

			$output		=	'
						<div class="jx-fly" id="parent_<?=$parent->table;?>_<?=$parent->id;?>_for_<?=$row->id;?>" style="display:none;">
							<div class="data">
								<h2><?=$parent->header;?></h2>
								<br />
								<p><?=$parent->title;?></p>
								<?php if (isset( $parent->image ) && $parent->image != "" ): ?>
									<img src="data:<?= $user_info->mime_type; ?>;base64,<?php echo base64_encode( $parent->image ); ?>"/>
								<?php endif; ?>
							</div>
							<div class="acoes">
								<?php if (isset( $parent->email ) && $parent->email != "" ): ?>
									<a class="button mail acaobutton-sumbit" title="Enviar e-mail" href="#">
										<div class="button-medium">
											<div class="button-image mail">
											</div>
										</div>
									</a>
								<?php endif; ?>
								<a class="button filter acaobutton-sumbit" title="Filtrar" href="#"  filter="<?=$parent->table;?>_id = <?=$parent->id;?>">
									<div class="button-medium">
										<div class="button-image filter">
										</div>
									</div>
								</a>
								<a class="button edit acaobutton-sumbit" title="Editar" href="/<?=$parent->table;?>/edit/<?=$parent->id;?>">
									<div class="button-medium">
										<div class="button-image edit">
										</div>
									</div>
								</a>
							</div>
						</div>
						';
		}
		else
		{
			$output		=	NULL;
		}
		
		return $output;
*/
	}

	/**
	 * Retorna dados para "auto"completar campos em páginas.
	 */
	public function autocomplete()
	{
		$where							=	NULL;
		$term							=	strtoupper( $this->input->get_post_multi( 'term' ) );
		if ( $term )
		{
			$title_column	= $this->master_model->get_column_title();
			if ( $term != '$@#$' ) // esta string é enviada quando queremos retornar todas as linhas da tabela. Será limitado a 100 pelo select_all abaixo
			{
				if ( $where )
				{
					$where				=	$where."AND ( upper( $title_column ) like '%". $term ."%' )";
				}
				else
				{
					$where				=	"( upper( $title_column ) like '%". $term ."%' )";
				}
			}

			$query						=	$this->master_model->select_all( $where, $title_column, 0, 100, $this->master_model->get_column_id() . ' as id, '. $title_column .' as title ' );
			$json_array					=	array();
			foreach( $query->result_array() as $row )
			{
				$row[ 'value' ]				=	$row['title'];
				$row[ 'label' ]				=	$row['title'];
				$json_array[]				=	$row;
			}

			if ( count( $json_array ) > 0 )
			{
//TODO: Verificar se o campo é obrigatório para inserir ou não a linha abaixo.
				$row					=	$json_array[0];
				
				// deixa a linha em branco.
				$row[ 'id' ] 				=	null;
				$row[ 'value' ] 			=	null;
				$row[ 'label' ]				=	'(nenhum)';
				
				$json_array[]				=	$row;
			}

			echo json_encode( $json_array );
		}
		else
		{
			echo null;
		}
	}
}

/* End of file JX_Page.php */
/* Location: ./application/core/JX_Page.php */
