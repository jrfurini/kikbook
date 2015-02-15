<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Jarvix Plus
 *
 *     Controller de Usuários.
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Jarvix Plus.
 * @license		http://jarvixplus.com/license.html
 * @link		http://jarvixplus.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/process.php
 * 
 * $Id: process.php,v 1.1 2013-02-28 18:02:50 junior Exp $
 * 
 */

class Process extends JX_Page
{
	protected $_revision	=	'$Id: process.php,v 1.1 2013-02-28 18:02:50 junior Exp $';

	function __construct()
	{
		$_config		=	array	();
		
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}

/* End of file process.php */
