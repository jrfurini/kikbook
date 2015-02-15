<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Tipo de endereço Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/tipo_endereco_model.php
 * 
 * $Id: tipo_endereco_model.php,v 1.1 2012-05-11 11:07:35 junior Exp $
 * 
 */

class Tipo_endereco_model extends JX_Model
{
	protected $_revision	=	'$Id: tipo_endereco_model.php,v 1.1 2012-05-11 11:07:35 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 tipo_endereco.*
			,tipo_endereco.nome		AS	title
			,now()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'tipo_endereco' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file tipo_endereco_model.php */