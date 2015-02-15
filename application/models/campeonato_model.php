<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Campeonato Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/campeonato_model.php
 * 
 * $Id: campeonato_model.php,v 1.3 2012-09-20 09:46:10 junior Exp $
 * 
 */

class Campeonato_model extends JX_Model
{
	protected $_revision	=	'$Id: campeonato_model.php,v 1.3 2012-09-20 09:46:10 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 campeonato.*
			,campeonato.nome	AS	title
			,now()			AS	when_field
			,cmpimg.imagem_id	AS	imagem_id
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'campeonato' );
		$this->db->join( 'campeonato_imagem	AS	cmpimg', 'cmpimg.campeonato_id = campeonato.id', 'LEFT' );
	}
}

/* End of file campeonato_model.php */