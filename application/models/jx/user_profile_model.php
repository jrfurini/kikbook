<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	Navegação de usuário Model
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/models/user_profile_model.php
 * 
 * $Id: user_profile_model.php,v 1.1 2012-06-15 02:15:21 junior Exp $
 * 
 */

class User_profile_model extends JX_Model
{
	protected $_revision	=	'$Id: user_profile_model.php,v 1.1 2012-06-15 02:15:21 junior Exp $';
	
	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}
}

/* End of file user_profile_model.php */
