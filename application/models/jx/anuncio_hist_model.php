<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Anuncio Histórico Model
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/jx/anuncio_hist_model.php
 * 
 * $Id: anuncio_hist_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $
 * 
 */

class Anuncio_hist_model extends JX_Model
{
	protected $_revision	=	'$Id: anuncio_hist_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 anuncio_hist.*
			,anuncio_hist.data_hora_view		AS	title
			,anuncio_hist.data_hora_view		AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'anuncio_hist' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file anuncio_hist_model.php */