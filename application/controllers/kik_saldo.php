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
 * @filesource		/application/controllers/kik_saldo.php
 * 
 * $Id: kik_saldo.php,v 1.1 2013-02-28 18:09:12 junior Exp $
 * 
 */

class Kik_saldo extends JX_Page
{
	protected $_revision	=	'$Id: kik_saldo.php,v 1.1 2013-02-28 18:09:12 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file kik_saldo.php */
