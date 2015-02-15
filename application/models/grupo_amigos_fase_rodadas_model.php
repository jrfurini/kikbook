<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Grupo de Amigos FASE RODADAS Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/grupo_amigos_fase_rodadas_model.php
 * 
 * $Id: grupo_amigos_fase_rodadas_model.php,v 1.1 2012-06-15 02:16:29 junior Exp $
 * 
 */

class Grupo_amigos_fase_rodadas_model extends JX_Model
{
	protected $_revision	=	'$Id: grupo_amigos_fase_rodadas_model.php,v 1.1 2012-06-15 02:16:29 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}
}

/* End of file grupo_amigos_fase_rodadas_model.php */