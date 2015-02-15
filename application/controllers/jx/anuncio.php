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
 * @filesource		/application/modules/main/controler/jx/anuncio.php
 
  $Id: anuncio.php,v 1.2 2013-04-14 12:45:12 junior Exp $
 
 */

class Anuncio extends JX_Page
{
	protected $_revision	=	'$Id: anuncio.php,v 1.2 2013-04-14 12:45:12 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'anuncio'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	'carimbo_exibicao,carimbo_peso_exibicao,peso_exibicao'
														,'where'		=>	'anuncio.id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'force_copy_from'	=>	TRUE
														)/*
							,'anuncio_hist'				=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	'anuncio'
														,'show'			=>	TRUE
														,'show_style'		=>	'grid'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	'anuncio_hist.anuncio_id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'force_copy_from'	=>	TRUE
														)*/
							);
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
	
	/**
	 * Registra o clique no anuncio.
	 */
	public function click( $id_hist )
	{
		if ( is_numeric( $id_hist ) )
		{
			$this->anuncio->set_click( $id_hist );
		}

		return TRUE;
	}
}

/* End of file jx/anuncio.php */
