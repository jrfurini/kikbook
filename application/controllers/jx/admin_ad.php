<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Administração de Anuncios
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/jx/admin_ad.php
 * 
 * $Id: admin_ad.php,v 1.1 2013-03-10 20:02:48 junior Exp $
 * 
 */

class Admin_ad extends JX_Page
{
	protected $_revision	=	'$Id: admin_ad.php,v 1.1 2013-03-10 20:02:48 junior Exp $';

	function __construct()
	{
		$_config_visual			=	array	(
								'index_html'		=>	'jx/index_empty.html'
								);

		parent::__construct( NULL, $_config_visual );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file admin_ad.php */
