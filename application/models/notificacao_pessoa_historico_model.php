<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Notificacao pessoa Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/notificacao_pessoa_historico_model.php
 * 
 * $Id: notificacao_pessoa_historico_model.php,v 1.1 2013-02-08 09:14:40 junior Exp $
 * 
 */

class Notificacao_pessoa_historico_model extends JX_Model
{
	protected $_revision		=	'$Id: notificacao_pessoa_historico_model.php,v 1.1 2013-02-08 09:14:40 junior Exp $';
	
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
			 notificacao_pessoa.*
			,notificacao_pessoa.id		AS	title
			,now()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'notificacao_pessoa' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}
/* End of file notificacao_pessoa_historico_model.php */
