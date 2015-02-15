<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	"Sistema Controller" Controller
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/controler/sistema_ctrl_meth.php
 *
 * $Id: sistema_ctrl_meth.php,v 1.2 2012-09-20 09:51:51 junior Exp $
 *
 */

class Sistema_ctrl_meth extends JX_Page
{
	protected $_revision	=	'$Id: sistema_ctrl_meth.php,v 1.2 2012-09-20 09:51:51 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "Controller ".$this->router->class." initialized.");
	}
}

/**
 * Log de alterações
  
 $Log: sistema_ctrl_meth.php,v $
 Revision 1.2  2012-09-20 09:51:51  junior
 Commit de segurança.

 Revision 1.1  2012-04-18 11:06:33  junior
 Várias reestruturações para que o JX possa ser usado como link.

 Ampliação do uso do _config no constructor do controller. Agora o delete e o update seguem esta configuração.

 Revision 1.1  2012/03/09 08:24:30  cvsuser
 Insert e update funcionando.

 Revision 1.1  2012/02/28 23:06:39  cvsuser
 Controle de acesso V1.


*/
/* End of file sistema_ctrl_meth.php */
