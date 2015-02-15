<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Arena Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/power_model.php
 * 
 * $Id: power_model.php,v 1.1 2012-06-30 14:47:45 junior Exp $
 * 
 */

class Power_model extends JX_Model
{
	protected $_revision	=	'$Id: power_model.php,v 1.1 2012-06-30 14:47:45 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 power.*
			,power.nome						AS	title
			,now()							AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'power' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file power_model.php */