<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Poderes de uma rodada da pessoa Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/pessoa_rodada_fase_power_model.php
 * 
 * $Id: pessoa_rodada_fase_power_model.php,v 1.4 2013-01-28 22:39:39 junior Exp $
 * 
 */

class Pessoa_rodada_fase_power_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_rodada_fase_power_model.php,v 1.4 2013-01-28 22:39:39 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'pessoa_rodada_fase'		=>	array	(
													 'model_name'	=>	'pessoa_rodada_fase'
													)
							,'kick_power'			=>	array	(
													 'model_name'	=>	'kick_power'
													)
							);
		
		parent::__construct( $_config );
		
		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 pessoa_rodada_fase_power.*
			,power.cod						AS	cod_power
			,power.nome						AS	nome_power
			,power.descr						AS	descr_power
			,power.css_class					AS	css_class
			,power.nome						AS	title
			,now()							AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'pessoa_rodada_fase_power' );
		$this->db->join( 'power		AS	power',         'power.id = pessoa_rodada_fase_power.power_id', '' );
	}

	/**
	 * Sempre precisamos criar a qtde. inicial de poderes para uma pessoa em uma rodada, por exemplo, a primeira vez que a pessoa acessa a página de chutes.
	 * O cálculo de ranking também precisa criar os poderes para a próxima rodada.
	 * 
	 * Esta função faz esta inicialização de poderes.
	 */
	public function libera_poderes( $rodada_fase_id, $pessoa_id )
	{
		// Prepara para calcular os poderes disponíveis para a rodada seguinte.
		$pessoa_rodada_data							=	$this->pessoa_rodada_fase->get_one_by_where	(
																		"	pessoa_rodada_fase.rodada_fase_id = {$rodada_fase_id}
																		and	pessoa_rodada_fase.pessoa_id = {$pessoa_id}
																		"																					
																		);
		if ( !$pessoa_rodada_data ) // Se não existe a pessoa_rodada_fase, criamos uma.
		{
			$pessoa_rodada_data						=	new stdClass();
			$pessoa_rodada_data->id						=	NULL;
			$pessoa_rodada_data->pessoa_id					=	$pessoa_id;
			$pessoa_rodada_data->rodada_fase_id				=	$rodada_fase_id;
			$pessoa_rodada_data->pontos_kick				=	0;
			$pessoa_rodada_data->pontos_gols				=	0;
			$pessoa_rodada_data->pontos_power				=	0;
			$pessoa_rodada_data->qtde_jogos_com_chute			=	0;
			$pessoa_rodada_data->qtde_jogos_sem_chute			=	0;
			$pessoa_rodada_data->qtde_acertou_vitoria_tudo			=	0;
			$pessoa_rodada_data->qtde_acertou_vitoria_gol_1_equipe		=	0;
			$pessoa_rodada_data->qtde_acertou_vitoria			=	0;
			$pessoa_rodada_data->qtde_acertou_empate_tudo			=	0;
			$pessoa_rodada_data->qtde_acertou_empate			=	0;
			$pessoa_rodada_data->qtde_acertou_apenas_gol_1_equipe		=	0;
			$pessoa_rodada_data->qtde_errou_tudo				=	0;
			$pessoa_rodada_data->jogou_rodada				=	'N';
			
			// Cria a linha para a próxima rodada da pessoa.
			$pessoa_rodada_data->id						=	$this->pessoa_rodada_fase->update( $pessoa_rodada_data );
		}

		// Prepara para calcular os poderes disponíveis para a rodada atual.
		$qry_powers								=	$this->get_all_by_where( "pessoa_rodada_fase_power.pessoa_rodada_fase_id = {$pessoa_rodada_data->id}" );
		$ar_powers_rodada							=	array();
		foreach( $qry_powers as $power )
		{
			$ar_powers_rodada[ $power->power_id ]				=	$power;
			if ( $ar_powers_rodada[ $power->power_id ]->qtde_usada == 0 )
			{
				$ar_powers_rodada[ $power->power_id ]->qtde_usada	=	$this->kick_power->get_qtde_usada( $pessoa_id, $power->power_id, $rodada_fase_id );
			}
		}
	
		for ( $power_id = 1; $power_id <= 7; $power_id ++ )
		{
			// Inicialza os poderes.
			$pessoa_rodada_power_new					=	new stdClass();
			$pessoa_rodada_power_new->id					=	NULL;
			$pessoa_rodada_power_new->pessoa_rodada_fase_id			=	$pessoa_rodada_data->id;
			$pessoa_rodada_power_new->power_id				=	NULL; // Anulamos para evitar sobreposição de poderes já gravados.
			$pessoa_rodada_power_new->pontos				=	0;
			$pessoa_rodada_power_new->qtde_liberado				=	0;
			$pessoa_rodada_power_new->qtde_usada				=	0;
			$pessoa_rodada_power_new->qtde_usada_user			=	0;
	
			if ( !key_exists( $power_id, $ar_powers_rodada ) )
			{
				if ( $power_id == QQI )
				{
					$pessoa_rodada_power_new->power_id			=	QQI;
					$pessoa_rodada_power_new->qtde_liberado			=	1;
					$pessoa_rodada_power_new->qtde_usada			=	$this->kick_power->get_qtde_usada( $pessoa_id, $pessoa_rodada_power_new->power_id, $rodada_fase_id );
					$pessoa_rodada_power_new->qtde_usada_user		=	$pessoa_rodada_power_new->qtde_usada;
				}
				elseif ( $power_id == GURU )
				{
					$pessoa_rodada_power_new->power_id			=	GURU;
					$pessoa_rodada_power_new->qtde_liberado			=	1;
					$pessoa_rodada_power_new->qtde_usada			=	$this->kick_power->get_qtde_usada( $pessoa_id, $pessoa_rodada_power_new->power_id, $rodada_fase_id );
					$pessoa_rodada_power_new->qtde_usada_user		=	$pessoa_rodada_power_new->qtde_usada;
				}
				elseif ( $power_id == DUELO )
				{
					$pessoa_rodada_power_new->power_id			=	DUELO;
					$pessoa_rodada_power_new->qtde_liberado			=	0;
					$pessoa_rodada_power_new->qtde_usada			=	$this->kick_power->get_qtde_usada( $pessoa_id, $pessoa_rodada_power_new->power_id, $rodada_fase_id );
					$pessoa_rodada_power_new->qtde_usada_user		=	$pessoa_rodada_power_new->qtde_usada;
				}
				elseif ( $power_id == TJUNTO )
				{
					$pessoa_rodada_power_new->power_id			=	TJUNTO;
					$pessoa_rodada_power_new->qtde_liberado			=	0;
					$pessoa_rodada_power_new->qtde_usada			=	$this->kick_power->get_qtde_usada( $pessoa_id, $pessoa_rodada_power_new->power_id, $rodada_fase_id );
					$pessoa_rodada_power_new->qtde_usada_user		=	$pessoa_rodada_power_new->qtde_usada;
				}
				elseif ( $power_id == ESPIAO )
				{
					$pessoa_rodada_power_new->power_id			=	ESPIAO;
					$pessoa_rodada_power_new->qtde_liberado			=	0;
					$pessoa_rodada_power_new->qtde_usada			=	$this->kick_power->get_qtde_usada( $pessoa_id, $pessoa_rodada_power_new->power_id, $rodada_fase_id );
					$pessoa_rodada_power_new->qtde_usada_user		=	$pessoa_rodada_power_new->qtde_usada;
				}
				elseif ( $power_id == BARBADA )
				{
					$pessoa_rodada_power_new->power_id			=	BARBADA;
					$pessoa_rodada_power_new->qtde_liberado			=	1;
					$pessoa_rodada_power_new->qtde_usada			=	$this->kick_power->get_qtde_usada( $pessoa_id, $pessoa_rodada_power_new->power_id, $rodada_fase_id );
					$pessoa_rodada_power_new->qtde_usada_user		=	$pessoa_rodada_power_new->qtde_usada;
				}
				elseif ( $power_id == ZEBRA )
				{
					$pessoa_rodada_power_new->power_id			=	ZEBRA;
					$pessoa_rodada_power_new->qtde_liberado			=	2;
					$pessoa_rodada_power_new->qtde_usada			=	$this->kick_power->get_qtde_usada( $pessoa_id, $pessoa_rodada_power_new->power_id, $rodada_fase_id );
					$pessoa_rodada_power_new->qtde_usada_user		=	$pessoa_rodada_power_new->qtde_usada;
				}
			}	
			if ( $pessoa_rodada_power_new->power_id
			&&   $pessoa_rodada_power_new->qtde_liberado > 0
			   )
			{
				$this->update( $pessoa_rodada_power_new );
				$ar_powers_rodada[ $pessoa_rodada_power_new->power_id ]	=	$pessoa_rodada_power_new;
			}
			unset( $pessoa_rodada_power_new );
		}
	}
}

/* End of file pessoa_rodada_fase_power_model.php */