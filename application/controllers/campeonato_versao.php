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
 * @filesource		/application/controler/campeonato_versao.php
 *
 * $Id: campeonato_versao.php,v 1.6 2013-01-17 01:35:28 junior Exp $
 *
 */

class Campeonato_versao extends JX_Page
{
	protected $_revision	=	'$Id: campeonato_versao.php,v 1.6 2013-01-17 01:35:28 junior Exp $';

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
														,'where'		=>	'imagem.id in ( select cmpimg.imagem_id from campeonato_versao_imagem cmpimg where cmpimg.campeonato_versao_id = ##id## )'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'campeonato_versao'			=>	array	(
														 'read_write'		=>	'write'
														,'master'		=>	TRUE
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	'campeonato_versao.id = ##id##'
														,'orderby'		=>	'campeonato_versao.data_inicio'
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'campeonato_versao_imagem'		=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'campeonato_versao,imagem'
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	'campeonato_versao_imagem.campeonato_versao_id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'force_copy_from'	=>	TRUE
														)
							,'campeonato_versao_equipe'		=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'campeonato_versao'
														,'show'			=>	TRUE
														,'show_style'		=>	'grid'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	'campeonato_versao_equipe.campeonato_versao_id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							);
		parent::__construct( $_config );
		
		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file campeonato_versao.php */
