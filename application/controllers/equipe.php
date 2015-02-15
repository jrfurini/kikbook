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
 * @filesource		/application/controllers/equipes.php
 * 
 * $Id: equipe.php,v 1.5 2013-01-17 01:35:28 junior Exp $
 * 
 */

class Equipe extends JX_Page
{
	protected $_revision	=	'$Id: equipe.php,v 1.5 2013-01-17 01:35:28 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'imagem'			=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'hide_columns'		=>	'mime_type,file_extension'
													,'readonly_columns'	=>	'size'
													,'where'		=>	'imagem.id in ( select eqpimg.imagem_id from equipe_imagem eqpimg where eqpimg.equipe_id = ##id## )'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'equipe'			=>	array	(
													 'read_write'		=>	'write'
													,'master'		=>	TRUE
													,'r_table_name'		=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'where'		=>	'equipe.id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'equipe_imagem'		=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	'equipe,imagem'
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'hide_columns'		=>	'tamanho'
													,'where'		=>	'equipe_imagem.equipe_id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													,'force_copy_from'	=>	TRUE
													)
/*							,'equipe_hist'			=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	'equipe'
													,'show'		=>	TRUE
													,'show_style'	=>	'grid'
													,'where'	=>	'equipe_hist.equipe_id = ##id##'
													,'orderby'	=>	'data_hora DESC'
													,'max_rows'	=>	999999
													,'delete_rule'	=>	'restrict'
													)
*/							);
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file equipes.php */
/* Location: /application/controllers/equipes.php */
