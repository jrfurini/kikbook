<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Tamanho do Anuncio Model
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/jx/area_anuncio_model.php
 * 
 * $Id: area_anuncio_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $
 * 
 */

class Area_anuncio_model extends JX_Model
{
	protected $_revision	=	'$Id: area_anuncio_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 area_anuncio.*
			,area_anuncio.descr		AS	title
			,now()				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'area_anuncio' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file area_anuncio_model.php */