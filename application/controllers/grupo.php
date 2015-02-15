<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/grupo.php
 * 
 * $Id: grupo.php,v 1.1 2012-09-06 10:17:29 junior Exp $
 * 
 */

class Grupo extends JX_Page
{
	protected $_revision	=	'$Id: grupo.php,v 1.1 2012-09-06 10:17:29 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'grupo'				=>	array	(
														 'read_write'		=>	'write'
														,'master'		=>	TRUE
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	'grupo.id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'grupo_equipe'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'grupo'
														,'show'			=>	TRUE
														,'show_style'		=>	'grid'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	'grupo_equipe.grupo_id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							);
		parent::__construct( $_config );
		
		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file grupo.php */
/* Location: /application/controllers/grupo.php */
