<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Jogo Controller
 *
 * @package		Kik book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Kikbook.com.br
 * @license		http://kikbook.com.br/licence
 * @link		http://kikbook.com.br
 * @since		Version 0.1
 * @filesource		/application/controler/jogo.php
 *
 * $Id: jogo.php,v 1.4 2012-09-06 10:17:29 junior Exp $
 *
 */

class Jogo extends JX_Page
{
	protected $_revision	=	'$Id: jogo.php,v 1.4 2012-09-06 10:17:29 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'imagem'			=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'none'
													,'hide_columns'		=>	'mime_type,file_extension'
													,'readonly_columns'	=>	'size'
													,'where'		=>	'imagem.id in ( select eqpimg.imagem_id from equipe_imagem eqpimg where eqpimg.equipe_id = ##id## )'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							);
		
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file jogo.php */
