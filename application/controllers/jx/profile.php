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
 * @filesource		/application/controllers/profile.php
 * 
 * $Id: profile.php,v 1.2 2012-09-20 09:51:51 junior Exp $
 * 
 */

class Profile extends JX_Page
{
	protected $_revision	=	'$Id: profile.php,v 1.2 2012-09-20 09:51:51 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'profile_meth'			=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	'profile'
													,'show'		=>	TRUE
													,'show_style'	=>	'grid'
													,'hide_columns'	=>	''
													,'where'	=>	'profile_meth.profile_id = ##id##'
													,'orderby'	=>	''
													,'max_rows'	=>	999999
													,'delete_rule'	=>	'restrict'
													)
							);
		
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}

/* End of file profile.php */
/* Location: /application/controllers/profile.php */
