<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Cadastro de Poderes.
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/power.php
 * 
 * $Id: power.php,v 1.2 2012-09-06 10:17:29 junior Exp $
 * 
 */

class Power extends JX_Page
{
	protected $_revision	=	'$Id: power.php,v 1.2 2012-09-06 10:17:29 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file power.php */
/* Location: /application/controllers/power.php */
