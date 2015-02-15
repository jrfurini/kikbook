<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	Navegação de usuário Model
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/models/user_nav_model.php
 * 
 * $Id: user_nav_model.php,v 1.4 2012-06-28 22:31:54 junior Exp $
 * 
 */

class User_nav_model extends JX_Model
{
	protected $_revision	=	'$Id: user_nav_model.php,v 1.4 2012-06-28 22:31:54 junior Exp $';
	
	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 user_nav.*
			,concat( pes.nome, ' ', pes.sobrenome )					AS	nome
			,pes.email								AS	email
			,pes.sexo								AS	sexo
			,concat( pes.nome, ' ', pes.sobrenome, ' IP: ', user_nav.ip_address )	AS	title
			,user_nav.data_acesso							AS	when_field
			";
	}
	
	public function get_select_for_one()
	{
		return $this->get_select_for_index();
	}

	public function set_from_join()
	{
		$this->db->from( 'user_nav' );
		$this->db->join( 'user		AS	usr', 'usr.id = user_nav.user_id', '' );
		$this->db->join( 'pessoa	AS	pes', 'pes.id = usr.pessoa_id', '' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
	public function get_column_title()
	{
		return "concat( pes.nome, ' ', pes.sobrenome, ' IP: ', user_nav.ip_address )";
	}
}

/* End of file user_nav_model.php */
