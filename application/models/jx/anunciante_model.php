<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Grupo Model
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/jx/anunciante_model.php
 * 
 * $Id: anunciante_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $
 * 
 */

class Anunciante_model extends JX_Model
{
	protected $_revision	=	'$Id: anunciante_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 anunciante.*
			,anunciante.nome		AS	title
			,now()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'anunciante' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file anunciante_model.php */