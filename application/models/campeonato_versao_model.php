<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Campeonato Versão Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/campeonato_versao_model.php
 * 
 * $Id: campeonato_versao_model.php,v 1.10 2013-03-27 01:30:44 junior Exp $
 * 
 */

class Campeonato_versao_model extends JX_Model
{
	protected $_revision	=	'$Id: campeonato_versao_model.php,v 1.10 2013-03-27 01:30:44 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		$ret		=	"
					 campeonato_versao.id
					,campeonato_versao.campeonato_id
					,campeonato_versao.data_inicio
					,campeonato_versao.data_fim
					,campeonato_versao.descr
					,campeonato_versao.entidade_organizadora
					,campeonato_versao.ativa
					,campeonato_versao.id_externo
					,campeonato_versao.calculo_encerrado
					,campeonato_versao.url_dado_externo/*
					,campeonato_versao.dados_externos*/
					,cmp.nome					AS	campeonato_nome
					,concat( 'De ', date_format( campeonato_versao.data_inicio, '%e/%m' ), ' até ', date_format( campeonato_versao.data_fim, '%e/%m/%Y' ), ', organizado pela ', campeonato_versao.entidade_organizadora )	AS	content
					,campeonato_versao.descr			AS	title
					,campeonato_versao.data_inicio			AS	when_field
					,IFNULL( verimg.imagem_id, cmpimg.imagem_id )	AS	imagem_id
					,cmp.genero					AS	genero
					,cmpimg.imagem_id				AS	imagem_id_campeonato";
			
		if ( $this->singlepack->get_pessoa_id() )
		{
			$ret	=	$ret . 
					"
					,IFNULL( pescmp.cadastrado_para_jogar, 'N' )	AS	cadastrado_para_jogar
					";
		}
		else
		{
			$ret	=	$ret . 
					"
					,'N'						AS	cadastrado_para_jogar
					";
		}
	
		return $ret;
	}
		
	public function set_from_join()
	{
		$this->db->from( 'campeonato_versao' );
		$this->db->join( 'campeonato			AS	cmp', 'cmp.id = campeonato_versao.campeonato_id', '' );
		$this->db->join( 'campeonato_versao_imagem	AS	verimg', 'verimg.campeonato_versao_id = campeonato_versao.id', 'LEFT' );
		$this->db->join( 'campeonato_imagem		AS	cmpimg', 'cmpimg.campeonato_id = campeonato_versao.campeonato_id', 'LEFT' );
		if ( $this->singlepack->get_pessoa_id() )
		{
			$this->db->join( 'pessoa_campeonato_versao	AS	pescmp', 'pescmp.campeonato_versao_id = campeonato_versao.id' .
										' and pescmp.pessoa_id = ' . $this->singlepack->get_pessoa_id() , 'LEFT' );
		}
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	public function get_order_by()
	{
		return ( "campeonato_versao.data_inicio" );
	}
	
	/**
	 * 
	 * Devolve um array contendo
	 * @param INT $rodada_fase_id		ID da rodada atual
	 * @param STR $quem			quem usará o array
	 * @param INT $count_max		informa a qtde de linhas a ser retornada.
	 */
	public function get_campeonato_selecao()
	{
		$this->retorno_selecao_campeonato			=	array();

		$this->select_all( NULL, "campeonato_versao.descr" );
		$rows							=	$this->get_query_rows();
		foreach( $rows as $versao )
		{
			if ( isset( $versao->imagem_id )
			&&   $versao->imagem_id
			&&   isset( $versao->nome_arquivo_imagem )
			   )
			{
				$versao->imagem_src			=	$versao->nome_arquivo_imagem;
			}
			elseif ( isset( $versao->imagem_id_campeonato )
			&&       $versao->imagem_id_campeonato
			       )
			{
				$versao->imagem_src			=	$this->imagem->get_file_name( $versao->imagem_id_campeonato, TRUE );
			}
			else
			{
				$versao->imagem_src			=	'/assets/img/ajax_loader_3.gif';
			}
			$this->retorno_selecao_campeonato[]	=	$versao;
		}

		return $this->retorno_selecao_campeonato;
	}

	/**
	 * 
	 * Permiter regisrar na sessão o ID do campeonato selecionado pelo usuário. Isso será usado para que na troca de página o campeonato permaneça.
	 * @param unknown_type $campeonato_versao_id
	 */
	public function set_id_sessao( $campeonato_versao_id )
	{
		$this->singlepack->set_sessao( 'campeonato_versao_id', $campeonato_versao_id );
	}
	public function get_id_sessao()
	{
		return $this->singlepack->get_sessao( 'campeonato_versao_id' );
	}
	
	// Retorna o campeonato selecionado pelo usuário. Comparando Sessão, informado e da rodada.
	public function get_id_selecionado( $campeonato_versao_id = NULL, $rodada_fase_id = NULL )
	{
		if ( !is_numeric( $campeonato_versao_id ) )
		{
			$campeonato_versao_id			=	NULL;
		}

		$campeonato_versao_id_sess			=	$this->campeonato_versao->get_id_sessao();

		// Não foi selecionado nada, então buscamos o campeonato da sessão.
		//    Se a rodada foi informada, então deixamos NULL para que seja buscado o campeonato da rodada.
		if ( !$campeonato_versao_id
		&&   !$rodada_fase_id
		   )
		{
			$campeonato_versao_id_form		=	$this->input->post_multi( 'campeonato_versao_id_selecionada' );
			if ( $campeonato_versao_id_form )
			{
				$campeonato_versao_id		=	$campeonato_versao_id_form;
			}
			else
			{
				$campeonato_versao_id		=	( is_numeric( str_replace( '"', '', $campeonato_versao_id_sess ) ) ) ? str_replace( '"', '', $campeonato_versao_id_sess ) : NULL;
			}
		}

		$this->campeonato_versao->set_id_sessao( $campeonato_versao_id );
		
		return $campeonato_versao_id;
	}
}

/* End of file campeonato_versao_model.php */