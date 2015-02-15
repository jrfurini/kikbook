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
 * @filesource		/application/modules/main/controler/jx/anunciante.php
 
  $Id: anunciante.php,v 1.1 2013-03-10 20:02:48 junior Exp $
 
 */

class Anunciante extends JX_Page
{
	protected $_revision	=	'$Id: anunciante.php,v 1.1 2013-03-10 20:02:48 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'anunciante'				=>	array	(
														 'read_write'		=>	'write'
														,'master'		=>	TRUE
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	'anunciante.id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'anuncio'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'anunciante'
														,'show'			=>	TRUE
														,'show_style'		=>	'grid'
														,'hide_columns'		=>	'data_fim,carimbo_exibicao,carimbo_peso_exibicao,peso_exibicao'
														,'readonly_columns'	=>	''
														,'where'		=>	'anuncio.anunciante_id = ##id##'
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

/* End of file jx/anunciante.php */
