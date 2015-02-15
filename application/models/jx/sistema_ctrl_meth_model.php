<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	Sistema Controller Method Model
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/models/sistema_ctrl_meth_model.php
 * 
 * $Id: sistema_ctrl_meth_model.php,v 1.3 2012-08-04 12:38:40 junior Exp $
 * 
 */

class Sistema_ctrl_meth_model extends JX_Model
{
	protected $_revision	=	'$Id: sistema_ctrl_meth_model.php,v 1.3 2012-08-04 12:38:40 junior Exp $';
	
	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_column_title()
	{
		return 'concat( sis.nome, " / ", ctrl.nome, " / "	, sistema_ctrl_meth.nome )';
	}

	public function get_select_for_index()
	{
		// Alterei (Junior Furini) o arquivo mysql_driver.php em /system/database/driver/mysql/ para retirar o conteúdo da variável $_escape_char. Sem esta alteração o CONCAT abaixo não funciona.
		return	'
			 sistema_ctrl_meth.*
			,concat( sis.nome, " / ", ctrl.nome, " / ", sistema_ctrl_meth.nome )	AS	title
			,now()										AS	when_field
			';
	}
	
	public function get_select_for_one()
	{
		return	$this->get_select_for_index();
	}

	public function set_from_join()
	{
		$this->db->from( 'sistema_ctrl_meth' );
		$this->db->join( 'sistema_ctrl	AS	ctrl', 'ctrl.id = sistema_ctrl_meth.sistema_ctrl_id', '' );
		$this->db->join( 'sistema	AS	sis',  'sis.id = ctrl.sistema_id', '' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
//		$this->db->from( 'sistema_ctrl_meth' );
	}
}
/* End of file sistema_ctrl_meth_model.php */
