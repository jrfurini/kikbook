<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Campeonato Versão  Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/campeonato_versao_equipe_model.php
 * 
 * $Id: campeonato_versao_equipe_model.php,v 1.2 2012-09-06 10:17:28 junior Exp $
 * 
 */

class Campeonato_versao_equipe_model extends JX_Model
{
	protected $_revision	=	'$Id: campeonato_versao_equipe_model.php,v 1.2 2012-09-06 10:17:28 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 campeonato_versao_equipe.*
			,eqp.nome				AS	title
			,now()					AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'campeonato_versao_equipe' );
		$this->db->join( 'equipe		AS	eqp', 'eqp.id = campeonato_versao_equipe.equipe_id', '' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	public function get_order_by()
	{
		return ( "eqp.nome" );
	}
}

/* End of file campeonato_versao_equipe_model.php */