<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Rodada / Fase Controller
 *
 * @package		Kik book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Kikbook.com.br
 * @license		http://kikbook.com.br/licence
 * @link		http://kikbook.com.br
 * @since		Version 0.1
 * @filesource		/application/controler/shop.php
 *
 * $Id: shop.php,v 1.2 2013-03-27 01:31:19 junior Exp $
 *
 */
class Shop extends JX_Page
{
	protected $_revision	=	'$Id: shop.php,v 1.2 2013-03-27 01:31:19 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'campra'			=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	''
													,'hide_columns'		=>	''
													,'seq_columns'		=>	''
													,'readonly_columns'	=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'where'		=>	'campra.id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'none'
													,'master'		=>	TRUE
													)
							);

		$_config_visual			=	array	(
								'index_html'		=>	'jx/index_empty.html'
								);

		parent::__construct( $_config,$_config_visual );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
	
	public function index()
	{
		if ( $this->singlepack->is_admin() )
		{
			parent::index();
		}
		else
		{
			echo 'Aguarde...';
		}
	}
}
/* End of file shop.php */
