<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Campeonato Imagem Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/vw_kick_power_model.php
 * 
 * $Id: vw_kick_power_model.php,v 1.1 2013-03-15 15:01:40 junior Exp $
 * 
 */

class Vw_kick_power_model extends JX_Model
{
	protected $_revision	=	'$Id: vw_kick_power_model.php,v 1.1 2013-03-15 15:01:40 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'kick'		=>	array	(
											 'model_name'	=>	'kick'
											,'where'	=>	'kick.id = ##id##'
											,'r_table_name'	=>	''
											)
							,'kick_power'	=>	array	(
											 'model_name'	=>	'kick_power'
											,'where'	=>	'kick_power.kick_id = ##id##'
											,'r_table_name'	=>	'kick'
									 		)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 vw_kick_power.*
			,vw_kick_power.id	AS	title
			,now()			AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'vw_kick_power' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file vw_kick_power_model.php */