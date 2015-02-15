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
 * @filesource		/application/models/pessoa_campeonato_versao_model.php
 * 
 * $Id: pessoa_campeonato_versao_model.php,v 1.3 2012-10-24 11:36:31 junior Exp $
 * 
 */

class Pessoa_campeonato_versao_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_campeonato_versao_model.php,v 1.3 2012-10-24 11:36:31 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{

		return	"
			 pessoa_campeonato_versao.*
			,pes.email
			,pes.nome
			,pes.sobrenome
			,pes.sexo
			,pes.imagem_facebook
			,usr.id_facebook
			,usr.ativo
			,ver.descr				AS	descr_campeonato
			,concat( pes.nome, ' ', pes.sobrenome )	AS	nome_completo
			,concat( pes.nome, ' ', pes.sobrenome )	AS	title
			,curdate()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'pessoa_campeonato_versao' );
		$this->db->join( 'pessoa		AS	pes',         'pes.id = pessoa_campeonato_versao.pessoa_id', '' );
		$this->db->join( 'user			AS	usr',         "usr.pessoa_id = pes.id", '' );
		$this->db->join( 'campeonato_versao	AS	ver',         "ver.id = pessoa_campeonato_versao.campeonato_versao_id", '' );
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
		if ( $selection == 'P' )	{ $order_by	=	$order_by." ( pessoa_campeonato_versao.pontos_kick + pessoa_campeonato_versao.pontos_power )"; }
		if ( $selection == 'Vt' )	{ $order_by	=	$order_by." pessoa_campeonato_versao.qtde_acertou_vitoria_tudo"; }
		if ( $selection == 'V1' )	{ $order_by	=	$order_by." pessoa_campeonato_versao.qtde_acertou_vitoria_gol_1_equipe"; }
		if ( $selection == 'V' )	{ $order_by	=	$order_by." pessoa_campeonato_versao.qtde_acertou_vitoria"; }
		if ( $selection == 'Et' )	{ $order_by	=	$order_by." pessoa_campeonato_versao.qtde_acertou_empate_tudo"; }
		if ( $selection == 'E' )	{ $order_by	=	$order_by." pessoa_campeonato_versao.qtde_acertou_empate"; }
		if ( $selection == '1g' )	{ $order_by	=	$order_by." pessoa_campeonato_versao.qtde_acertou_apenas_gol_1_equipe"; }
		if ( $selection == 'N' )	{ $order_by	=	$order_by." pessoa_campeonato_versao.qtde_errou_tudo"; }
		
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
		return "( pessoa_campeonato_versao.pontos_kick + pessoa_campeonato_versao.pontos_gols + pessoa_campeonato_versao.pontos_power ) DESC, pessoa_campeonato_versao.qtde_acertou_vitoria_tudo DESC, pessoa_campeonato_versao.qtde_acertou_empate_tudo DESC, pessoa_campeonato_versao.qtde_acertou_vitoria_gol_1_equipe DESC, pessoa_campeonato_versao.qtde_acertou_vitoria DESC, pessoa_campeonato_versao.qtde_jogos_com_chute DESC, pessoa_campeonato_versao.qtde_rodada_jogada DESC, concat( pes.nome, ' ', pes.sobrenome ) ASC";
	}
	public function get_column_title()
	{
		return "concat( pes.nome, ' ', pes.sobrenome )";
	}
}

/* End of file pessoa_campeonato_versao_model.php */