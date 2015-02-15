<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Grupo de Amigos Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/grupo_amigos_model.php
 * 
 * $Id: grupo_amigos_model.php,v 1.2 2012-08-29 14:45:52 junior Exp $
 * 
 */

class Grupo_amigos_model extends JX_Model
{
	protected $_revision	=	'$Id: grupo_amigos_model.php,v 1.2 2012-08-29 14:45:52 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_where()
	{
		return "grupo_amigos.id in ( select pesgrp.grupo_amigos_id from grupo_amigos_pessoa pesgrp where pesgrp.pessoa_id = ". $this->singlepack->get_user_info()->pessoa_id . " )";
	}
	
	public function get_select_for_index()
	{
		return	"
			 grupo_amigos.*
			,grupo_amigos.nome						AS	title
			,date_format( now(), '%e/%m/%Y %H:%i' )				AS	when_field
			";
	}

	public function set_from_join()
	{
		$this->db->from( 'grupo_amigos' );
//		$this->db->join( 'kick			AS	kick',        'kick.jogo_id = jogo.id and kick.pessoa_id = '.$pessoa_id, 'LEFT' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	function get_order_by( $selection = null, $direction = null )
	{
		return "grupo_amigos.nome";
	}
	public function get_column_title()
	{
		return "grupo_amigos.nome";
	}
}

/* End of file grupo_amigos_model.php */