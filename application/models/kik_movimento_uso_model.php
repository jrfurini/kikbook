<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Movimentos de Kiks Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/kik_movimento_uso_model.php
 * 
 * $Id: kik_movimento_uso_model.php,v 1.8 2013-04-07 13:59:54 junior Exp $
 * 
 */

class Kik_movimento_uso_model extends JX_Model
{
	protected $_revision				=	'$Id: kik_movimento_uso_model.php,v 1.8 2013-04-07 13:59:54 junior Exp $';
	
	function __construct()
	{
		$_config		=	array	(
							 'kik_movimento_suo'			=>	array	(
														 'model_name'	=>	'kik_movimento_uso'
												 		)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 kik_movimento_uso.*
			,kik_movimento_uso.id								AS	title
			,kik_movimento_uso.data_hora							AS	when_field
			 ";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'kik_movimento_uso' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}
/* End of file kik_movimento_uso_model.php */
