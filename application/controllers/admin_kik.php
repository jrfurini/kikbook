<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Administração do Kikbook
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/admin_kik.php
 * 
 * $Id: admin_kik.php,v 1.1 2012-09-06 10:17:29 junior Exp $
 * 
 */

class Admin_kik extends JX_Page
{
	protected $_revision	=	'$Id: admin_kik.php,v 1.1 2012-09-06 10:17:29 junior Exp $';

	function __construct()
	{
		$_config_visual			=	array	(
								'index_html'		=>	'jx/index_empty.html'
								);

		parent::__construct( NULL, $_config_visual );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file admin.php */
/* Location: /application/controllers/admin_kik.php */
