<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Rodada / Fase Controller
 *
 * @package		Kik book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Kikbook.com.br
 * @license		http://kikbook.com.br/licence
 * @link		http://kikbook.com.br
 * @since		Version 0.1
 * @filesource		/application/controler/rodada_fase.php
 *
 * $Id: rodada_fase.php,v 1.9 2012-11-02 12:48:18 junior Exp $
 *
 */
class Rodada_fase extends JX_Page
{
	protected $_revision	=	'$Id: rodada_fase.php,v 1.9 2012-11-02 12:48:18 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'rodada_fase'			=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	''
													,'hide_columns'		=>	''
													,'seq_columns'		=>	''
													,'readonly_columns'	=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'where'		=>	'rodada_fase.id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'none'
													,'master'		=>	TRUE
													)
							,'jogo'				=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	''
													,'hide_columns'		=>	'rodada_fase_id,titulo_casa,titulo_visitante,penaltis_casa,penaltis_visitante,resultado_casa_prorrogacao,resultado_visitante_prorrogacao,id_externo,publico_total,publico_pagante,renda_moeda,renda_total'
													,'seq_columns'		=>	'cod,grupo_id,equipe_id_casa,resultado_casa,resultado_visitante,equipe_id_visitante,data_hora,arena_id,tipo'
													,'readonly_columns'	=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'grid'
													,'where'		=>	'jogo.rodada_fase_id = ##id##'
													,'orderby'		=>	'grupo_id,jogo.data_hora'
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							);

		$_config_visual			=	array	(
								 'index_html'		=>	'jx/index.html'
								,'edit_html'		=>	'rodada_fase_edit.html'
								);

		parent::__construct( $_config,$_config_visual );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
	
	function atualizar_inicio_fim( $id = NULL, $campeonato_versao_id = NULL )
	{
		$this->rodada_fase->set_inicio_fim( $id, $campeonato_versao_id );
	}
}
/* End of file rodada_fase.php */
