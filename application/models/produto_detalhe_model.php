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
 * @filesource		/application/models/produto_detalhe_model.php
 * 
 * $Id: produto_detalhe_model.php,v 1.1 2013-03-13 21:13:17 junior Exp $
 * 
 */

class Produto_detalhe_model extends JX_Model
{
	protected $_revision	=	'$Id: produto_detalhe_model.php,v 1.1 2013-03-13 21:13:17 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message( 'debug', "(jx)Model ".$this->table." initialized." );
	}

	public function get_select_for_index()
	{
		return	"
			 produto_detalhe.*
			,prd.descr		AS	title
			,now()			AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'produto_detalhe' );
		$this->db->join( 'produto			AS	prd', 'produto_detalhe.produto_id	=	prd.id', 'LEFT' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file produto_detalhe_model.php */