<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Ranking por Pessoa por Grupo de Amigos Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/pessoa_ranking_grupo_amigos_model.php
 * 
 * $Id: pessoa_ranking_grupo_amigos_model.php,v 1.6 2013-01-17 01:38:24 junior Exp $
 * 
 */

class Pessoa_ranking_grupo_amigos_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_ranking_grupo_amigos_model.php,v 1.6 2013-01-17 01:38:24 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{

		return	"
			 pessoa_ranking_grupo_amigos.*
			,pes.email
			,pes.nome
			,pes.sobrenome
			,pes.sexo
			,pes.imagem_facebook
			,usr.id_facebook
			,usr.ativo
			,concat( pes.nome, ' ', pes.sobrenome )	AS	nome_completo
			,concat( pes.nome, ' ', pes.sobrenome )	AS	title
			,curdate()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'pessoa_ranking_grupo_amigos' );
		$this->db->join( 'pessoa		AS	pes',         'pes.id = pessoa_ranking_grupo_amigos.pessoa_id', '' );
		$this->db->join( 'user			AS	usr',         "usr.pessoa_id = pes.id", '' );
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
		if ( $selection == 'P' )	{ $order_by	=	$order_by." ( pessoa_ranking_grupo_amigos.pontos_kick + pessoa_ranking_grupo_amigos.pontos_power )"; }
		if ( $selection == 'Vt' )	{ $order_by	=	$order_by." pessoa_ranking_grupo_amigos.qtde_acertou_vitoria_tudo"; }
		if ( $selection == 'V1' )	{ $order_by	=	$order_by." pessoa_ranking_grupo_amigos.qtde_acertou_vitoria_gol_1_equipe"; }
		if ( $selection == 'V' )	{ $order_by	=	$order_by." pessoa_ranking_grupo_amigos.qtde_acertou_vitoria"; }
		if ( $selection == 'Et' )	{ $order_by	=	$order_by." pessoa_ranking_grupo_amigos.qtde_acertou_empate_tudo"; }
		if ( $selection == 'E' )	{ $order_by	=	$order_by." pessoa_ranking_grupo_amigos.qtde_acertou_empate"; }
		if ( $selection == '1g' )	{ $order_by	=	$order_by." pessoa_ranking_grupo_amigos.qtde_acertou_apenas_gol_1_equipe"; }
		if ( $selection == 'N' )	{ $order_by	=	$order_by." pessoa_ranking_grupo_amigos.qtde_errou_tudo"; }
		
		if ( $order_by )
		{
			if ( $direction == "+" )
			{
				$order_by	=	$order_by." ASC, concat( pes.nome, ' ', pes.sobrenome ) ASC";
			}
			else 
			{
				$order_by	=	$order_by." DESC, concat( pes.nome, ' ', pes.sobrenome ) ASC";
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
		return "( pessoa_ranking_grupo_amigos.pontos_kick + pessoa_ranking_grupo_amigos.pontos_gols + pessoa_ranking_grupo_amigos.pontos_power ) DESC, pessoa_ranking_grupo_amigos.qtde_acertou_vitoria_tudo DESC, pessoa_ranking_grupo_amigos.qtde_acertou_empate_tudo DESC, pessoa_ranking_grupo_amigos.qtde_acertou_vitoria_gol_1_equipe DESC, pessoa_ranking_grupo_amigos.qtde_acertou_vitoria DESC, pessoa_ranking_grupo_amigos.qtde_jogos_com_chute DESC, pessoa_ranking_grupo_amigos.qtde_rodada_jogada DESC, concat( pes.nome, ' ', pes.sobrenome ) ASC";
	}
	public function get_column_title()
	{
		return "concat( pes.nome, ' ', pes.sobrenome )";
	}
	
	public function get_ranking_rodada( $pessoa_id, $rodada_fase_id, $campeonato_versao_id )
	{
		$select				=	"
							select		 pesrk.id
									,IFNULL( pesrk.pessoa_id, grppes.pessoa_id )		AS	pessoa_id
									,IFNULL( pesrk.grupo_amigos_fase_id, grpfas.id )	AS	grupo_amigos_fase_id
									,pesrk.pessoa_rodada_fase_id				AS	pessoa_rodada_fase_id
									,IFNULL( pesrk.pontos_kick, 0 )				AS	pontos_kick
									,IFNULL( pesrk.pontos_gols, 0 )				AS	pontos_gols
									,IFNULL( pesrk.pontos_power, 0 )			AS	pontos_power
									,IFNULL( pesrk.qtde_jogos_com_chute, 0 )		AS	qtde_jogos_com_chute
									,IFNULL( pesrk.qtde_jogos_sem_chute, 0 )		AS	qtde_jogos_sem_chute
									,IFNULL( pesrk.qtde_acertou_vitoria_tudo, 0 )		AS	qtde_acertou_vitoria_tudo
									,IFNULL( pesrk.qtde_acertou_vitoria_gol_1_equipe, 0 )	AS	qtde_acertou_vitoria_gol_1_equipe
									,IFNULL( pesrk.qtde_acertou_vitoria, 0 )		AS	qtde_acertou_vitoria
									,IFNULL( pesrk.qtde_acertou_empate_tudo, 0 )		AS	qtde_acertou_empate_tudo
									,IFNULL( pesrk.qtde_acertou_empate, 0 )			AS	qtde_acertou_empate
									,IFNULL( pesrk.qtde_acertou_apenas_gol_1_equipe, 0 )	AS	qtde_acertou_apenas_gol_1_equipe
									,IFNULL( pesrk.qtde_errou_tudo, 0 )			AS	qtde_errou_tudo
									,IFNULL( pesrk.qtde_rodada_jogada, 0 )			AS	qtde_rodada_jogada
									,IFNULL( pesrk.posicao_geral, 0 )			AS	posicao_geral
									,grpfas.usar_poderes					AS	usar_poderes
							from		grupo_amigos_fase_rodadas	AS	grprods
							join		grupo_amigos_fase		AS	grpfas	ON	grpfas.id			=	grprods.grupo_amigos_fase_id
							join		grupo_amigos_pessoa		AS	grppes	ON	grppes.pessoa_id		=	{$pessoa_id}
															AND	grppes.grupo_amigos_id		=	grpfas.grupo_amigos_id
							join		rodada_fase			AS	rod	ON	rod.id				=	{$rodada_fase_id}
															AND	rod.data_inicio			>=	grprods.data_inicio
															AND	rod.data_fim			<=	grprods.data_fim
							left join	pessoa_ranking_grupo_amigos	AS	pesrk	ON	pesrk.pessoa_id			=	grppes.pessoa_id
															AND	pesrk.grupo_amigos_fase_id	=	grpfas.id
							where		grprods.campeonato_versao_id	=	{$campeonato_versao_id}
							";

		$query				=	$this->db->query( $select );
		return $query->result_object();
	}
	
	public function get_ranking_rodada_existente( $rodada_fase_id, $campeonato_versao_id )
	{
		$select				=	"
							select		pesrk.*
							from		grupo_amigos_fase_rodadas	AS	grprods
							join		rodada_fase			AS	rod_ini	ON	rod_ini.id			=	grprods.rodada_fase_id_inicio
							join		rodada_fase			AS	rod_fim	ON	rod_fim.id			=	grprods.rodada_fase_id_fim
							join		grupo_amigos_fase		AS	grpfas	ON	grpfas.id			=	grprods.grupo_amigos_fase_id
							join		grupo_amigos_pessoa		AS	grppes	ON	grppes.grupo_amigos_id		=	grpfas.grupo_amigos_id
							join		rodada_fase			AS	rod	ON	rod.id				=	{$rodada_fase_id}
															AND	rod.data_inicio			>=	rod_ini.data_inicio
															AND	rod.data_fim			<=	rod_fim.data_fim
							join		pessoa_ranking_grupo_amigos	AS	pesrk	ON	pesrk.pessoa_id			=	grppes.pessoa_id
															AND	pesrk.grupo_amigos_fase_id	=	grpfas.id
							join		user				AS	usr	ON	usr.pessoa_id			=	pesrk.pessoa_id
															AND	usr.ativo			=	'S'
							where		grprods.campeonato_versao_id	=	{$campeonato_versao_id}
							order by	pesrk.grupo_amigos_fase_id ASC, ( pesrk.pontos_kick + pesrk.pontos_gols + pesrk.pontos_power ) DESC, pesrk.qtde_acertou_vitoria_tudo DESC, pesrk.qtde_acertou_empate_tudo DESC, pesrk.qtde_acertou_vitoria_gol_1_equipe DESC, pesrk.qtde_acertou_vitoria DESC, pesrk.qtde_jogos_com_chute DESC, pesrk.qtde_rodada_jogada DESC
							";

		$query				=	$this->db->query( $select );
		return $query->result_object();
	}
}

/* End of file pessoa_ranking_grupo_amigos_model.php */