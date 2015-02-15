<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Arena Histórico Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/arena_hist_model.php
 * 
 * $Id: arena_hist_model.php,v 1.1 2012-05-11 11:07:35 junior Exp $
 * 
 */

class Arena_hist_model extends JX_Model
{
	protected $_revision	=	'$Id: arena_hist_model.php,v 1.1 2012-05-11 11:07:35 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
			}

	public function get_select_for_index()
	{
		return	"
			 arena_hist.*
			,arena.nome
			,arena.descr
			,arena.nome						AS	title
			,now()							AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'arena_hist' );
		$this->db->join( 'arena		AS	arena', 'arena.id = arena_hist.arena_id' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file arena_hist_model.php */