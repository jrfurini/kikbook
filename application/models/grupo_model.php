<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Grupo Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/grupo_model.php
 * 
 * $Id: grupo_model.php,v 1.1 2012-05-11 11:07:36 junior Exp $
 * 
 */

class Grupo_model extends JX_Model
{
	protected $_revision	=	'$Id: grupo_model.php,v 1.1 2012-05-11 11:07:36 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 grupo.*
			,grupo.descr			AS	title
			,now()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'grupo' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file grupo_model.php */