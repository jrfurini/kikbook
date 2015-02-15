<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	Tamanho do Anuncio Controller
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/modules/main/controler/jx/area_anuncio.php
 
  $Id: area_anuncio.php,v 1.1 2013-03-10 20:02:48 junior Exp $
 
 */

class Area_anuncio extends JX_Page
{
	protected $_revision	=	'$Id: area_anuncio.php,v 1.1 2013-03-10 20:02:48 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'area_anuncio'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	'carimbo_exibicao'
														,'where'		=>	'area_anuncio.id = ##id##'
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

/* End of file jx/area_anuncio.php */
