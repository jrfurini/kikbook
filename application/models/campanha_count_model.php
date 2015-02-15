<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Campanha Count Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/campanha_count_model.php
 * 
 * $Id: campanha_count_model.php,v 1.1 2013-02-08 09:14:41 junior Exp $
 * 
 */

class Campanha_count_model extends JX_Model
{
	protected $_revision		=	'$Id: campanha_count_model.php,v 1.1 2013-02-08 09:14:41 junior Exp $';
	
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
			 campanha_count.*
			,campanha_count.campanha_id	AS	title
			,now()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'campanha_count' );
	}
	
	public function _pre_insert()
	{
		if ( !$this->_in_pre_insert )
		{
		 	$this->_in_pre_insert			=	TRUE;
		 	
			$this->insert_data->countdown_inicio	=	$this->insert_data->countdown;

		 	$this->_in_pre_insert			=	FALSE;
		}
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}
/* End of file campanha_count_model.php */
