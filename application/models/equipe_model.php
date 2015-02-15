<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Equipe Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/equipe_model.php
 * 
 * $Id: equipe_model.php,v 1.2 2012-05-22 18:44:24 junior Exp $
 * 
 */

class Equipe_model extends JX_Model
{
	protected $_revision	=	'$Id: equipe_model.php,v 1.2 2012-05-22 18:44:24 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 equipe.*
			,equipe.nome		AS	title
			,now()			AS	when_field
			,eqpimg.imagem_id	AS	imagem_id
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'equipe' );
		$this->db->join( 'equipe_imagem	AS	eqpimg', 'eqpimg.equipe_id = equipe.id', 'LEFT' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file equipe_model.php */