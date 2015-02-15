<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Jogo Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/jogo_model.php
 * 
 * $Id: jogo_model.php,v 1.7 2013-03-27 01:30:44 junior Exp $
 * 
 */

class Jogo_model extends JX_Model
{
	protected $_revision	=	'$Id: jogo_model.php,v 1.7 2013-03-27 01:30:44 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 jogo.*
			,IFNULL( eqp_casa.nome, jogo.titulo_casa )					AS	nome_equipe_casa
			,IFNULL( eqp_vis.nome, jogo.titulo_visitante )					AS	nome_equipe_visitante
			,eqp_casa.sigla									AS	sigla_equipe_casa
			,eqp_vis.sigla									AS	sigla_equipe_visitante
			,arena.nome									AS	nome_arena
			,concat( eqp_casa.nome, ' {imagem_id=', eqpimg_casa.imagem_id, '} ', cast( IFNULL( jogo.resultado_casa, '' ) AS CHAR ), ' X ', cast( IFNULL( jogo.resultado_visitante, '' ) AS CHAR ), '{imagem_id=', eqpimg_vis.imagem_id, '}', ' ', eqp_vis.nome, ' (Rodada ', rod.cod, ') ', ' ', date_format( jogo.data_hora, '%a %e/%m/%Y %H:%i' ), ' ', case when IFNULL( jogo.resultado_visitante, '-1' ) = -1 then 'Em aberto' else 'Realizado' end )		AS	title
			,date_format( jogo.data_hora, '%e/%m %H:%i' )					AS	dd_mm_jogo
			,date_format( jogo.data_hora, '%e/%m/%Y %H:%i' )				AS	when_field
			,rod.campeonato_versao_id							AS	campeonato_versao_id
			,verimg.imagem_id								AS	campeonato_versao_imagem_id
			,eqpimg_casa.imagem_id								AS	equipe_casa_imagem_id
			,eqpimg_vis.imagem_id								AS	equipe_visitante_imagem_id
			";
	}
	
	public function set_from_join()
	{
		$this->db->from( 'jogo' );
		$this->db->join( 'rodada_fase			AS	rod',         'rod.id  = jogo.rodada_fase_id' );
		$this->db->join( 'campeonato_versao_imagem	AS	verimg',      'verimg.campeonato_versao_id  = rod.campeonato_versao_id' );
		$this->db->join( 'equipe			AS	eqp_casa',    'eqp_casa.id = jogo.equipe_id_casa', 'LEFT' );
		$this->db->join( 'equipe			AS	eqp_vis',     'eqp_vis.id  = jogo.equipe_id_visitante', 'LEFT' );
		$this->db->join( 'equipe_imagem			AS	eqpimg_casa', 'eqpimg_casa.equipe_id = eqp_casa.id', 'LEFT' );
		$this->db->join( 'equipe_imagem			AS	eqpimg_vis',  'eqpimg_vis.equipe_id = eqp_vis.id', 'LEFT' );
		$this->db->join( 'arena				AS	arena',       'arena.id = jogo.arena_id', 'LEFT' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	function get_order_by( $selection = null, $direction = null )
	{
		return "jogo.data_hora, concat( eqp_casa.nome, ' X ', eqp_vis.nome, ' (', jogo.cod, ')' )";
	}
	public function get_column_title()
	{
		return "concat( eqp_casa.nome, ' ', cast( IFNULL( jogo.resultado_casa, ' ' ) AS CHAR ), ' X ', cast( IFNULL( jogo.resultado_visitante, ' ' ) AS CHAR ), ' ', eqp_vis.nome, ' (Rodada ', rod.cod, ') ', ' ', date_format( jogo.data_hora, '%a %e/%m/%Y %H:%i' ), ' ', case when IFNULL( jogo.resultado_visitante, '-1' ) = -1 then 'Em aberto' else 'Realizado' end )";
	}
	
	/**
	 * Retorna o próxima data e hora de jogo não realizado.
	 */
	public function get_next_date( $campeonato_versao_id = NULL )
	{
		return NULL;
	}
}
/* End of file jogo_model.php */