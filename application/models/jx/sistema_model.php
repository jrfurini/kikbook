<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	Sistema Model
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/models/sistema_model.php
 * 
 * $Id: sistema_model.php,v 1.3 2012-12-15 22:13:02 junior Exp $
 * 
 */

class Sistema_model extends JX_Model
{
	protected $_revision	=	'$Id: sistema_model.php,v 1.3 2012-12-15 22:13:02 junior Exp $';
	
	function __construct()
	{
		$_config		=	array	(
							 'imagem'			=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	''
													,'show'		=>	TRUE
													,'where'	=>	''
													,'max_rows'	=>	99999
													,'orderby'	=>	''
													)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 sistema.*
			,sistema.nome	AS	title
			,now()			AS	when_field
			,sisimg.imagem_id	AS	imagem_id
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'sistema' );
		$this->db->join( 'sistema_imagem	AS	sisimg', 'sisimg.sistema_id = sistema.id', 'LEFT' );
	}
}
/* End of file sistema_model.php */
