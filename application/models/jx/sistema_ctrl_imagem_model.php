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
 * @filesource		/application/models/sistema_ctrl_imagem_model.php
 * 
 * $Id: sistema_ctrl_imagem_model.php,v 1.1 2012-12-08 00:18:23 junior Exp $
 * 
 */

class Sistema_ctrl_imagem_model extends JX_Model
{
	protected $_revision	=	'$Id: sistema_ctrl_imagem_model.php,v 1.1 2012-12-08 00:18:23 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 sistema_ctrl_imagem.*
			,sistema_ctrl_imagem.id	AS	title
			,now()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'sistema_ctrl_imagem' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file sistema_ctrl_imagem_model.php */