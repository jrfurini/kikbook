<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	User Config Model
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/models/user_cfg_model.php
 * 
 * $Id: user_cfg_model.php,v 1.2 2012-05-11 11:06:27 junior Exp $
 * 
 */

class User_cfg_model extends JX_Model
{
	protected $_revision	=	'$Id: user_cfg_model.php,v 1.2 2012-05-11 11:06:27 junior Exp $';
	
	var $user_id;

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 user_cfg.*
			,concat( pes.nome, ' ', pes.sobrenome )			AS	nome_completo
			,pes.email						AS	email
			,pes.sexo						AS	sexo
			,concat( pes.nome, ' ', pes.sobrenome )			AS	title
			,now()							AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'user_cfg' );
		$this->db->join( 'user		AS	usr', 'usr.id = user_cfg.user_id' );
		$this->db->join( 'pessoa	AS	pes', 'pes.id = usr.pessoa_id', '' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file user_cfg_model.php */