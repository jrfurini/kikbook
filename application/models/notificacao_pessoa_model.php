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
 * @filesource		/application/models/notificacao_pessoa_model.php
 * 
 * $Id: notificacao_pessoa_model.php,v 1.3 2013-02-16 21:14:45 junior Exp $
 * 
 */

class Notificacao_pessoa_model extends JX_Model
{
	protected $_revision		=	'$Id: notificacao_pessoa_model.php,v 1.3 2013-02-16 21:14:45 junior Exp $';
	
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
			,date_format( notificacao_pessoa.data_hora_envio, '%e/%m/%Y %h:%i' )	AS	data_hora_envio_format
			,now()				AS	when_field
			,notif.id			AS	notif_notificacao_id
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'notificacao_pessoa' );
		$this->db->join( 'notificacao		AS	notif',        'notif.id          = notificacao_pessoa.notificacao_id', '' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
	
	public function get_order_by()
	{
		return 'notif.prioridade ASC, data_hora_envio ASC';
	}
}
/* End of file notificacao_pessoa_model.php */
