<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Jarvix Plus
 *
 *     Controller de Navegação de usuários Usuários.
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Jarvix Plus.
 * @license		http://jarvixplus.com/license.html
 * @link		http://jarvixplus.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/user_nav.php
 * 
 * $Id: user_nav.php,v 1.2 2012-09-06 10:13:14 junior Exp $
 * 
 */

class User_nav extends JX_Page
{
	protected $_revision	=	'$Id: user_nav.php,v 1.2 2012-09-06 10:13:14 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}

/**
 * Log de alterações
  
 $Log: user_nav.php,v $
 Revision 1.2  2012-09-06 10:13:14  junior
 Evolução dos controles de tabelas e visualização.

 Revision 1.1  2012-04-18 11:06:33  junior
 Várias reestruturações para que o JX possa ser usado como link.

 Ampliação do uso do _config no constructor do controller. Agora o delete e o update seguem esta configuração.

 Revision 1.1  2012/03/09 08:24:30  cvsuser
 Insert e update funcionando.


*/
/* End of file user.php */
/* Location: /application/controllers/user_nav.php */
