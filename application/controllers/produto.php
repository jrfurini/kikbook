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
 * @filesource		/application/controler/produto.php
 *
 * $Id: produto.php,v 1.2 2013-03-13 21:09:57 junior Exp $
 *
 */

class Produto extends JX_Page
{
	protected $_revision	=	'$Id: produto.php,v 1.2 2013-03-13 21:09:57 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'produto'			=>	array	(
													 'read_write'		=>	'write'
													,'master'		=>	TRUE
													,'r_table_name'		=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'where'		=>	'produto.id = ##id##'
													,'orderby'		=>	'descr'
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'produto_tamanho'		=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	'produto'
													,'show'			=>	TRUE
													,'show_style'		=>	'grid'
													,'hide_columns'		=>	''
													,'where'		=>	'produto_tamanho.produto_id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													,'force_copy_from'	=>	FALSE
													)
							,'produto_preco'		=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	'produto'
													,'show'			=>	TRUE
													,'show_style'		=>	'grid'
													,'hide_columns'		=>	''
													,'where'		=>	'produto_preco.produto_id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													,'force_copy_from'	=>	FALSE
													)
							,'produto_detalhe'		=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	'produto'
													,'show'			=>	TRUE
													,'show_style'		=>	'grid'
													,'hide_columns'		=>	''
													,'where'		=>	'produto_detalhe.produto_id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													,'force_copy_from'	=>	FALSE
													)
							,'vw_produto_imagem'		=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'grid'
													,'hide_columns'		=>	'size,mime_type,file_extension,produto_imagem_id,imagem_id,produto_id,versao'
													,'where'		=>	'vw_produto_imagem.produto_id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													,'force_copy_from'	=>	FALSE
													)
							);
		
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
}
/* End of file produto.php */
