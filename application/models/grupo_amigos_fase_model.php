<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Grupo de Amigos FASE Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/grupo_amigos_fase_model.php
 * 
 * $Id: grupo_amigos_fase_model.php,v 1.2 2012-08-29 14:45:52 junior Exp $
 * 
 */

class Grupo_amigos_fase_model extends JX_Model
{
	protected $_revision	=	'$Id: grupo_amigos_fase_model.php,v 1.2 2012-08-29 14:45:52 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 grupo_amigos_fase.*
			,grupo_amigos_fase.nome										AS	title
			,date_format( now(), '%e/%m/%Y' )					AS	when_field
			,min( rod.rodada_fase_id_inicio )								AS	rodada_fase_id_inicio
			,max( rod.rodada_fase_id_fim )									AS	rodada_fase_id_fim
			";
	}
	
	public function set_from_join()
	{
		$this->db->from( 'grupo_amigos_fase' );
		$this->db->join( 'grupo_amigos_fase_rodadas	AS	rod',  'rod.grupo_amigos_fase_id = grupo_amigos_fase.id' );
	}
	
	public function get_group_by()
	{
		return	"
			 grupo_amigos_fase.id
			,grupo_amigos_fase.grupo_amigos_id
			,grupo_amigos_fase.cod
			,grupo_amigos_fase.nome
			,grupo_amigos_fase.descr
			";		
	}
}

/* End of file grupo_amigos_fase_model.php */