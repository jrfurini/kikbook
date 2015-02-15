<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Campanha Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/campanha_model.php
 * 
 * $Id: campanha_model.php,v 1.2 2013-02-16 21:14:45 junior Exp $
 * 
 */

class Campanha_model extends JX_Model
{
	protected $_revision		=	'$Id: campanha_model.php,v 1.2 2013-02-16 21:14:45 junior Exp $';
	
	function __construct()
	{
		$_config		=	array	(
							 'campanha_count'				=>	array	(
															 'model_name'	=>	'campanha_count'
													 		)
							,'campanha_pessoa'				=>	array	(
															 'model_name'	=>	'campanha_pessoa'
													 		)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 campanha.*
			,campanha.short_url									AS	url_convite
			,CONCAT( campanha.descr, '( ', campanha.short_url, ' )' )		AS	title
			,campanha.data_inicio									AS	when_field
			";
	}

	public function set_from_join()
	{
		$this->db->from( 'campanha' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
	
	public function _pre_insert()
	{
		if ( !$this->_in_pre_insert )
		{
		 	$this->_in_pre_insert		=	TRUE;

		 	$this->insert_data->cod_md5	=	md5( str_pad( $this->insert_data->id, 10, '0', STR_PAD_LEFT ) . $this->insert_data->data_inicio );
		 	$this->insert_data->short_url	=	'none';

		 	$this->_in_pre_insert		=	FALSE;
		}
	}
}
/* End of file campanha_model.php */
