<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	Sistema Controller
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/modules/main/controler/sistema.php
 
  $Id: sistema.php,v 1.5 2013-02-08 08:47:33 junior Exp $
 
 */

class Sistema extends JX_Page
{
	protected $_revision	=	'$Id: sistema.php,v 1.5 2013-02-08 08:47:33 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'imagem'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	'mime_type,file_extension'
														,'readonly_columns'	=>	'size'
														,'where'		=>	'imagem.id in ( select sisimg.imagem_id from sistema_imagem sisimg where sisimg.sistema_id = ##id## )'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'sistema'				=>	array	(
														 'read_write'		=>	'write'
														,'master'		=>	TRUE
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	'sistema.id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'sistema_imagem'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'sistema,imagem'
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	'sistema_imagem.sistema_id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'force_copy_from'	=>	TRUE
														)
							);
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}

/* End of file sistema.php */
