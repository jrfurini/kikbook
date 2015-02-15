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
 * @filesource		/application/controllers/scheduller.php
 * 
 * $Id: scheduller.php,v 1.1 2013-02-28 18:02:50 junior Exp $
 * 
 */

class Scheduller extends JX_Process
{
	protected $_revision				=	'$Id: scheduller.php,v 1.1 2013-02-28 18:02:50 junior Exp $';

	protected $notificacao_template_id_falta_chute	=	1;
	
	function __construct()
	{
		$_config		=	array	(
							 'process'				=>	array	(
														 'read_write'		=>	'write'
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
							,'process_history'			=>	array	(
														 'read_write'		=>	'write'
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
	 * Método de execução principal.
	 * 
	 */
	public function main()
	{
		/*
		 * Lista de processos
		 */
		$processos				=	$this->process->get_all_by_where;

		/*
		 * Determina o tempo para dormir.
		 */
		
		/*
		 * Dorme por 60 segundos.
		 */
		sleep( 60 );
		
	}
	
	/**
	 * Executa os processos determinados pelo processo anterior.
	 */
	public function process( $command, $id_process )
	{
		
	}
	
	/**
	 * 
	 * Executa novamente o processo indicado após o tempo indicado.
	 * 
	 * @param unknown_type $id_history
	 */
	public function repeat( $id_process, $time )
	{
		
	}
	
	/**
	 * 
	 * Cancela um processo que esteja em execução.
	 * @param int		$id_process.
	 */
	public function cancel( $id_process )
	{
		// TODO:
	}
}
/* End of file scheduller.php */
