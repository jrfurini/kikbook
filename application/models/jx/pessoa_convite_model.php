<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Convite para usar o aplicativo Model
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/jx/pessoa_convite_model.php
 * 
 * $Id: pessoa_convite_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $
 * 
 */

class Pessoa_convite_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_convite_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 pessoa_convite.*
			,pessoa_convite.facebook_id_convidado	AS	title
			,pessoa_convite.data_hora		AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'pessoa_convite' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file pessoa_convite_model.php */