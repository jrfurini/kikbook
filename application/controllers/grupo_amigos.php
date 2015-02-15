<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Controller principal de grupo de amigos.
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/grupo_amigos.php
 * 
 * $Id: grupo_amigos.php,v 1.6 2012-09-20 09:46:10 junior Exp $
 * 
 */

class Grupo_amigos extends JX_Page
{
	protected $_revision	=	'$Id: grupo_amigos.php,v 1.6 2012-09-20 09:46:10 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'grupo_amigos'			=>	array	(
													 'read_write'		=>	'write'
													,'master'		=>	TRUE
													,'r_table_name'		=>	''
													,'hide_columns'		=>	'id_facebook'
													,'seq_columns'		=>	'nome,descr,id_facebook,privacy,icon,email'
													,'readonly_columns'	=>	'id_facebook,nome,descr,privacy,icon,email'
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'where'		=>	'id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'grupo_amigos_pessoa'		=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	'grupo_amigos'
													,'hide_columns'		=>	''
													,'seq_columns'		=>	''
													,'readonly_columns'	=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'grid'
													,'where'		=>	'grupo_amigos_pessoa.grupo_amigos_id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'grupo_amigos_fase'		=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	'grupo_amigos'
													,'hide_columns'		=>	''
													,'seq_columns'		=>	''
													,'readonly_columns'	=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'grid'
													,'where'		=>	'grupo_amigos_fase.grupo_amigos_id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'grupo_amigos_fase_rodadas'	=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	'grupo_amigos,grupo_amigos_fase'
													,'hide_columns'		=>	'campeonato_versao_id'
													,'seq_columns'		=>	''
													,'readonly_columns'	=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'grid'
													,'where'		=>	'grupo_amigos_fase_rodadas.grupo_amigos_fase_id in ( select grpamgfas.id from grupo_amigos_fase grpamgfas where grpamgfas.grupo_amigos_id = ##id## )'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							);
		
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
	/**
	 * Acrescenta os grupos do facebook à lista de dados.
	 */
	protected function get_facebook_groups(  )
	{
		// Carrega as variáveis que já estavam disponíveis para o HTML.
		$rows			=	$this->load->get_var( "rows" );
		$total_rows		=	$this->load->get_var( "total_rows" );

		$groups_info		=	array	(
							 'id'
							,'name'
							,'description'
							,'privacy'
							,'picture'
							,'email'
							);
		$groups_rows		=	$this->singlepack->get_groups_info( $groups_info );
		
		if ( $groups_rows
		&&   is_array( $groups_rows )
		   )
		{
			foreach( $groups_rows as $info )
			{
				$not_exists			=	TRUE;
				
				foreach( $rows as $row )
				{
					if ( !isset( $info->id )
					||   $info->id == $row->id_facebook
					   )
					{
						$not_exists	=	FALSE;
					   	break;
					}
				}
				
				if ( $not_exists )
				{
					$new_row		=	new stdClass();
					$new_row->id		=	NULL;
					$new_row->id_facebook	=	( isset( $info->id ) ) ? $info->id : NULL;
					$new_row->nome		=	( isset( $info->name ) ) ? $info->name : NULL;
					$new_row->descr		=	( isset( $info->description ) ) ? $info->description : NULL;
					$new_row->privacy	=	( isset( $info->privacy ) ) ? $info->privacy : NULL;
					$new_row->icon		=	( isset( $info->picture ) ) ? $info->picture : NULL;
					$new_row->email		=	( isset( $info->email ) ) ? $info->email : NULL;
					
					$new_row->title		=	$new_row->nome . ( ( $new_row->descr ) ? ', ' : NULL ) . $new_row->descr . " (" . $info->id. ")";
					$new_row->when_field	=	now();
					
					$rows[]			=	$new_row;
					$total_rows		=	$total_rows + 1;
					unset( $new_row );
				}
			}
		}

		// Devolve as variáveis para o HTML usar.
		$data			= 	array	(
							 'rows'			=> $rows
							,'total_rows'		=> $total_rows
							);
		$this->load->vars( $data );
	}
	
	/**
	 * Página princial do site.
	 */
	public function index()
	{
		if ( $this->singlepack->user_connected() )
		{
			$this->_prep_index();
			$this->get_facebook_groups();
			$this->load->view( 'grupo_amigos_index.html' );
		}
		else
		{
			$this->load->view( 'grupo_amigos_not_connect_index.html' );
		}
	}
	
	/**
	 * Monta a página de edição de grupos.
	 */
	public function edit()
	{
		$this->_prep_edit();

		$this->load->view( 'grupo_amigos_edit.html' );
	}
}
/* End of file grupo_amigos.php */
/* Location: /application/controllers/grupo_amigos.php */
