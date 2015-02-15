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
 * @filesource		/application/models/jx/pessoa_relacionamento_model.php
 * 
 * $Id: pessoa_relacionamento_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $
 * 
 */

class Pessoa_relacionamento_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_relacionamento_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 pessoa_relacionamento.*
			,pessoa_relacionamento.pessoa_id_eu		AS	title
			,pessoa_relacionamento.data_inicio		AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'pessoa_relacionamento' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file pessoa_relacionamento_model.php */