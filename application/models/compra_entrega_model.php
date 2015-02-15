<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Compra Entrega Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/compra_entrega_model.php
 * 
 * $Id: compra_entrega_model.php,v 1.1 2013-03-10 21:01:47 junior Exp $
 * 
 */

class Compra_entrega_model extends JX_Model
{
	protected $_revision	=	'$Id: compra_entrega_model.php,v 1.1 2013-03-10 21:01:47 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message( 'debug', "(jx)Model ".$this->table." initialized." );
	}

	public function get_select_for_index()
	{
		return	"
			 compra_entrega.*
			,concat( pessoa.nome, ' ', pessoa.sobrenome )	AS	title
			,compra_entrega.data_hora_envio											AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'compra_entrega' );
		$this->db->join( 'compra	AS	cpr', 'cpr.id	=	compra_entrega.compra_id', 'LEFT' );
		$this->db->join( 'pessoa	AS	pes', 'pes.id	=	cpr.pessoa_id', 'LEFT' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file compra_entrega_model.php */