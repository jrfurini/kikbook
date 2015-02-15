<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Classe para controller que será um gerenciador de processo.
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/core/JX_Process.php
 *
 * $Id: JX_Process.php,v 1.3 2012-10-27 10:29:48 junior Exp $
 *
 */

class JX_Process extends JX_Controller
{

	/**
	 * Variáveis de controle visual.
	 */
	var $index_html				=	'jx/index_process.html';
	
	/**
	 * Construtor da classe.
	 */
	public function __construct( $_config_table = NULL, $_config_visual = NULL )
	{
		log_message( 'debug', "JX_Process.(start)." );

		parent::__construct( $_config_table, $_config_visual );

		log_message( 'debug', "JX_Process subclass({$this->router->class}.{$this->router->method}) initialized." );
	}
	
	/**
	 * Página de configuração do processo.
	 */
	public function index()
	{
		$this->load->view( $this->index_html );
	}

	/**
	 * Se o processo é disparado via cron (tarefa agenda) precisaremos realizar o logon de um usuário.
	 * A function abaixo permite este logon.
	 */
	protected function logon()
	{
echo "no LOGON() \n";
		if ( $this->singlepack->user_connected()
		&&   $this->config->item( 'first_controller_connected' )
		   )
		{
			return TRUE;
		}
		else
		{
			if ( !is_null( $this->config->item( 'process_user' ) )
			&&   !is_null( $this->config->item( 'process_password' ) ) 
			   )
			{
				if ( $this->singlepack->user_valid( $this->config->item( 'process_user' ), $this->config->item( 'process_password' ) ) )
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
			else
			{
				log_message( 'error', "JX_Process.logon: Usuário (process_user) ou Senha (process_password) não estão configurados em config.php." );
				return FALSE;
			}
		}
	}

	// Fechamos a sessão.
	protected function logoff()
	{
		return $this->singlepack->close_session();
	}

	/**
	 *  Processa em background.
	 *  
	 *  Recebe N parametros, sendo, obrigatoriamente, o primeiro o método que será executado.
	 *  Os demais parametros serão colocados em um novo array e passado como argumento para o call_user_func.
	 */
	public function run_back()
	{
		log_message('debug', 'Process.run_back()' );
		
		$params			=	func_get_args();
		
		if ( func_num_args() <= 0 )
		{
			log_message( 'error', 'Process.run_back(): Número de parametros inválido para execução. Você precisa ao menos indicar qual o método a ser executado.' );
			exit;
		}
		// Seleciona o método.
		// Monta o array de parametros para o método.
		$method			=	array_shift( $params );
		log_message( 'debug', '...executando='. $method );

		call_user_func_array( array( $this, $method ), $params );
		
//		$this->logoff();
		log_message('debug', 'Process.run_back(logoff)' );
	}
}

/* End of file JX_Process.php */
/* Location: ./application/core/JX_Process.php */
