<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Campanha Pessoa Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/campanha_pessoa_model.php
 * 
 * $Id: campanha_pessoa_model.php,v 1.1 2013-02-08 09:14:40 junior Exp $
 * 
 */

class Campanha_pessoa_model extends JX_Model
{
	protected $_revision		=	'$Id: campanha_pessoa_model.php,v 1.1 2013-02-08 09:14:40 junior Exp $';
	
	function __construct()
	{
		$_config		=	array	(
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 campanha_pessoa.*
			,campanha_pessoa.pessoa_id	AS	title
			,now()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'campanha_pessoa' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}
/* End of file campanha_pessoa_model.php */
