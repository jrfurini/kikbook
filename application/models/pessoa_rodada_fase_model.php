<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Pessoa Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/pessoa_rodada_fase_model.php
 * 
 * $Id: pessoa_rodada_fase_model.php,v 1.7 2013-02-25 15:21:20 junior Exp $
 * 
 */

class Pessoa_rodada_fase_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_rodada_fase_model.php,v 1.7 2013-02-25 15:21:20 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{

		return	"
			 pessoa_rodada_fase.*
			,pes.email
			,pes.nome
			,pes.sobrenome
			,pes.sexo
			,pes.imagem_facebook
			,usr.id_facebook
			,usr.ativo
			,pes.data_hora_inscricao								AS	user_data_hora_inscricao
			,rod.id											AS	rod_id
			,rod.data_inicio									AS	rodada_data_inicio
			,rod.data_fim										AS	rodada_data_fim
			,ver.descr										AS	descr_campeonato
			,concat( rod.cod, ' rodada de ', date_format( rod.data_inicio, '%e/%m' ) )		AS	rodada_fase_title
			,concat( ver.descr, ' - ', rod.cod )							AS	rodada_nome
			,concat( pes.nome, ' ', pes.sobrenome )							AS	nome_completo
			,pesver.cadastrado_para_jogar								AS	cadastrado_para_jogar
			,concat( pes.nome, ' ', pes.sobrenome, ' ', ver.descr, ' - ', rod.cod )			AS	title
			,curdate()										AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'pessoa_rodada_fase' );
		$this->db->join( 'pessoa			AS	pes',         'pes.id = pessoa_rodada_fase.pessoa_id', '' );
		$this->db->join( 'user				AS	usr',         "usr.pessoa_id = pes.id", '' );
		$this->db->join( 'rodada_fase			AS	rod',         "rod.id = pessoa_rodada_fase.rodada_fase_id", '' );
		$this->db->join( 'campeonato_versao		AS	ver',         "ver.id = rod.campeonato_versao_id", '' );
		$this->db->join( 'pessoa_campeonato_versao	AS	pesver',      "pesver.pessoa_id = pes.id and pesver.campeonato_versao_id = ver.id", 'LEFT' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
	
	public function _prep_order_by( $selection = null, $direction = null )
	{
//		return $this->get_order_by();
		$order_by			=	'';
		if ( $selection == 'Nome' )	{ $order_by	=	$order_by." concat( pes.nome, ' ', pes.sobrenome )"; }
		if ( $selection == 'P' )	{ $order_by	=	$order_by." ( pessoa_rodada_fase.pontos_kick + pessoa_rodada_fase.pontos_power )"; }
		if ( $selection == 'Vt' )	{ $order_by	=	$order_by." pessoa_rodada_fase.qtde_acertou_vitoria_tudo"; }
		if ( $selection == 'V1' )	{ $order_by	=	$order_by." pessoa_rodada_fase.qtde_acertou_vitoria_gol_1_equipe"; }
		if ( $selection == 'V' )	{ $order_by	=	$order_by." pessoa_rodada_fase.qtde_acertou_vitoria"; }
		if ( $selection == 'Et' )	{ $order_by	=	$order_by." pessoa_rodada_fase.qtde_acertou_empate_tudo"; }
		if ( $selection == 'E' )	{ $order_by	=	$order_by." pessoa_rodada_fase.qtde_acertou_empate"; }
		if ( $selection == '1g' )	{ $order_by	=	$order_by." pessoa_rodada_fase.qtde_acertou_apenas_gol_1_equipe"; }
		if ( $selection == 'N' )	{ $order_by	=	$order_by." pessoa_rodada_fase.qtde_errou_tudo"; }
		
		if ( $order_by )
		{
			if ( $direction == "+" )
			{
				$order_by		=	$order_by." ASC, concat( pes.nome, ' ', pes.sobrenome ) ASC";
			}
			else 
			{
				$order_by		=	$order_by." DESC, concat( pes.nome, ' ', pes.sobrenome ) ASC";
			}
		}
		
		$pers_orderby			=	$this->get_order_by();
		
		if ( $pers_orderby )
		{
			if ( $order_by )
			{
				$return		=	$order_by . ', ' . $pers_orderby;
			}
			else
			{
				$return		=	$pers_orderby;
			}
		}
		else
		{
			$return			=	$order_by;
		}
		return $return;		
	}
	
	function get_order_by( $selection = null, $direction = null )
	{
		return "( pessoa_rodada_fase.pontos_kick + pessoa_rodada_fase.pontos_gols + pessoa_rodada_fase.pontos_power ) DESC, pessoa_rodada_fase.pontos_kick DESC, pessoa_rodada_fase.pontos_power DESC, pessoa_rodada_fase.pontos_gols DESC, pessoa_rodada_fase.qtde_acertou_vitoria_tudo DESC, pessoa_rodada_fase.qtde_acertou_empate_tudo DESC, pessoa_rodada_fase.qtde_acertou_vitoria_gol_1_equipe DESC, pessoa_rodada_fase.qtde_acertou_vitoria DESC, concat( pes.nome, ' ', pes.sobrenome ) ASC";
	}
	public function get_column_title()
	{
		return "concat( pes.nome, ' ', pes.sobrenome )";
	}
}

/* End of file pessoa_rodada_fase_model.php */