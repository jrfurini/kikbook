<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Jarvix Plus
 *
 *     Controller principal do sistema de Controle de Acesso.
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Jarvix Plus.
 * @license		http://jarvixplus.com/license.html
 * @link		http://jarvixplus.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/auth_ctl.php
 * 
 * $Id: auth_ctl.php,v 1.2 2012-09-06 10:13:14 junior Exp $
 * 
 */

class Auth_ctl extends JX_Page
{
	protected $_revision	=	'$Id: auth_ctl.php,v 1.2 2012-09-06 10:13:14 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	/**
	 * Página princial do site.
	 */
	public function index()
	{
		$this->load->view( 'jx/index.html' );
	}
	
	/**
	 * Função de controle de search
	 */
	public function search()
	{
		return null;
	}
}
/* End of file auth_ctl.php */
/* Location: /application/controllers/auth_ctl.php */
