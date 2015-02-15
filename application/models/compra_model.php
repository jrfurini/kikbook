<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Compra Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/compra_model.php
 * 
 * $Id: compra_model.php,v 1.1 2013-03-10 21:01:47 junior Exp $
 * 
 */

class Compra_model extends JX_Model
{
	protected $_revision	=	'$Id: compra_model.php,v 1.1 2013-03-10 21:01:47 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'compra'				=>	array	(
															 'model_name'	=>	'compra'
													 	)
							,'pessoa'				=>	array	(
															 'model_name'	=>	'pessoa'
														)
							,'kik_movimento'			=>	array	(
															 'model_name'	=>	'kik_movimento'
														)
							);
		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 compra.*
			,concat( pes.nome, ' ', pes.sobrenome )		AS	title
			,compra.data_hora				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'compra' );
		$this->db->join( 'pessoa	AS	pes', 'pes.id	=	compra.pessoa_id', '' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file compra_model.php */