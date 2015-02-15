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
 * @filesource		/application/controllers/kik_processo.php
 * 
 * $Id: kik_processo.php,v 1.1 2013-03-02 01:12:36 junior Exp $
 * 
 */

class Kik_processo extends JX_Process
{
	protected $_revision				=	'$Id: kik_processo.php,v 1.1 2013-03-02 01:12:36 junior Exp $';

	protected $notificacao_template_id_falta_chute	=	1;
	protected $notificacao_template_id_kik_vencer	=	8;
	
	function __construct()
	{
		$_config		=	array	(
							 'kik_movimento'				=>	array	(
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
	 * Estorna Kiks vencidos.
	 */
	public function perda_kik_vencido()
	{
		return $this->kik_movimento->set_movto_perda();
	}
}
/* End of file kik_processo.php */
