<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Controller principal do sistema de Cadastro.
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/team.php
 * 
 * $Id: team.php,v 1.3 2012-09-06 10:17:29 junior Exp $
 * 
 */

class Team extends JX_Page
{
	protected $_revision	=	'$Id: team.php,v 1.3 2012-09-06 10:17:29 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file team.php */
/* Location: /application/controllers/team.php */
