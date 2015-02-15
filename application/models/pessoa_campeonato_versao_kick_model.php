<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/pessoa_campeonato_versao_kick_model.php
 * 
 * $Id: pessoa_campeonato_versao_kick_model.php,v 1.1 2012-09-20 09:46:11 junior Exp $
 * 
 */

class Pessoa_campeonato_versao_kick_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_campeonato_versao_kick_model.php,v 1.1 2012-09-20 09:46:11 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}
}

/* End of file pessoa_campeonato_versao_kick_model.php */