<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Produto tamanho Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/produto_tamanho_model.php
 * 
 * $Id: produto_tamanho_model.php,v 1.1 2013-03-10 21:01:47 junior Exp $
 * 
 */

class Produto_tamanho_model extends JX_Model
{
	protected $_revision	=	'$Id: produto_tamanho_model.php,v 1.1 2013-03-10 21:01:47 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message( 'debug', "(jx)Model ".$this->table." initialized." );
	}

	public function get_select_for_index()
	{
		return	"
			 produto_tamanho.*
			,prd.descr		AS	title
			,now()			AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'produto_tamanho' );
		$this->db->join( 'produto			AS	prd', 'produto_tamanho.produto_id	=	prd.id', 'LEFT' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file produto_tamanho_model.php */