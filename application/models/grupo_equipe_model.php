<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Equipes do Grupo Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/grupo_equipe_model.php
 * 
 * $Id: grupo_equipe_model.php,v 1.2 2012-06-03 18:47:54 junior Exp $
 * 
 */

class Grupo_equipe_model extends JX_Model
{
	protected $_revision	=	'$Id: grupo_equipe_model.php,v 1.2 2012-06-03 18:47:54 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 grupo_equipe.*
			,concat( grupo.descr, ' / ', equipe.nome )	AS	title
			,now()						AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'grupo_equipe' );
		$this->db->join( 'grupo		AS	grupo',  'grupo.id = grupo_equipe.grupo_id' );
		$this->db->join( 'equipe	AS	equipe', 'equipe.id = grupo_equipe.equipe_id' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file grupo_equipe_model.php */