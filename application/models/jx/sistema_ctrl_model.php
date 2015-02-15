<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	Sistema Controller Model
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/models/sistema_ctrl_model.php
 * 
 * $Id: sistema_ctrl_model.php,v 1.3 2012-12-08 00:18:01 junior Exp $
 * 
 */

class Sistema_ctrl_model extends JX_Model
{
	protected $_revision	=	'$Id: sistema_ctrl_model.php,v 1.3 2012-12-08 00:18:01 junior Exp $';
	
	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 sistema_ctrl.*
			,sistema_ctrl.nome	AS	title
			,now()			AS	when_field
			,ctrlimg.imagem_id	AS	imagem_id
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'sistema_ctrl' );
		$this->db->join( 'sistema_ctrl_imagem	AS	ctrlimg', 'ctrlimg.sistema_ctrl_id = sistema_ctrl.id', 'LEFT' );
	}
}
/* End of file sistema_ctrl_model.php */
