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
 * @filesource		/application/controllers/avalia_dados.php
 * 
 * $Id: avalia_dados.php,v 1.1 2012-11-02 12:48:18 junior Exp $
 * 
 */

class Avalia_dados extends JX_Process
{
	protected $_revision		=	'$Id: avalia_dados.php,v 1.1 2012-11-02 12:48:18 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'rodada_fase'				=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'kick'					=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							);
		parent::__construct( $_config );
		
		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
	
	/**
	 * Confirma se a autorização do usuário no facebook ainda está ativa
	 */
	public function usuario_ativo()
	{
		
	}
}
/* End of file avalia_dados.php */
/* Location: /application/controllers/avalia_dados.php */
