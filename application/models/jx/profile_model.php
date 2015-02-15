<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	Users Model
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/models/jx/profile_model.php
 * 
 * $Id: profile_model.php,v 1.1 2012-06-01 04:49:55 junior Exp $
 * 
 */

class Profile_model extends JX_Model
{
	protected $_revision	=	'$Id: profile_model.php,v 1.1 2012-06-01 04:49:55 junior Exp $';
	
	var $user_id;
	
	function __construct()
	{
		$_config		=	array	(
							);
		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}
		
	public function set_from_join()
	{
		$this->db->from( 'profile' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file jx/profile_model.php */