<?php (defined('BASEPATH')) OR exit('No direct script access allowed');
/**
 * Extendendo o CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/core/JX_Exceptions.php
 *
 * $Id: JX_Exceptions.php,v 1.1 2013-01-28 22:21:45 junior Exp $
 *
 */

class JX_Exceptions extends CI_Exceptions
{
	protected $CI;
	
	public function __construct()
	{
		log_message( 'debug', "JX_Exceptions.(start)." );
		parent::__construct();
		$this->CI =& get_instance();
		
		/**
		 * Controle de sistema e permissões.
		 */
		$config_single				=	array	(
									 'prg_controller'		=>	$this->CI->router->class
									,'prg_controller_method'	=>	$this->CI->router->method
									);

		$this->CI->load->library( 'singlepack' );
		$this->CI->singlepack->initialize( $config_single );

		log_message( 'debug', "JX_Exceptions.(fim)." );
	}
	/**
	 * General Error Page
	 *
	 * This function takes an error message as input
	 * (either as a string or an array) and displays
	 * it using the specified template.
	 *
	 * @access	private
	 * @param	string	the heading
	 * @param	string	the message
	 * @param	string	the template name
	 * @param 	int		the status code
	 * @return	string
	 */
	function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		set_status_header($status_code);

		$message = '<p> '.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';
		$this->CI->singlepack->send_email( 'jrfurini@gmail.com', 'DB ERRO: Kikbook ', "Heading: >>>>$heading<<<<\nMsg: >>>>$message<<<<" );
		
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include(APPPATH.'errors/'.$template.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	// --------------------------------------------------------------------

	/**
	 * Native PHP error handler
	 *
	 * @access	private
	 * @param	string	the error severity
	 * @param	string	the error string
	 * @param	string	the error filepath
	 * @param	string	the error line number
	 * @return	string
	 */
	function show_php_error($severity, $message, $filepath, $line)
	{
		$severity = ( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity];

		$filepath = str_replace("\\", "/", $filepath);

		$this->CI->singlepack->send_email( 'jrfurini@gmail.com', 'PHP ERRO: Kikbook ', "Severity: >>>$severity<<<\nMsg: >>>$message<<<\nFile: >>>$filepath<<<\n Line: >>>$line<<<" );

		// For safety reasons we do not show the full file path
		if (FALSE !== strpos($filepath, '/'))
		{
			$x = explode('/', $filepath);
			$filepath = $x[count($x)-2].'/'.end($x);
		}

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include(APPPATH.'errors/error_php.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}
}
/* End of file JX_Exceptions.php */
/* Location: ./application/core/JX_Exceptions.php */