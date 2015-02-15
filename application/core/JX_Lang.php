<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Extendendo o CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/library/JX_Lang.php
 *
 * $Id: JX_Lang.php,v 1.8 2013-01-28 22:22:49 junior Exp $
 *
 */
class JX_Lang extends CI_Lang
{
	public function __construct()
	{
		parent::__construct();
		log_message('debug', "JX_Language Class Initialized");
	}

	/**
	 * Complementa a função do CI_lang->line.
	 * Aqui quando não encontramos a informação no arquivo ou o arquivo não existe, retornamos a KEY para ser usada.
	 * 
	 */
	public function get_line( $line, $file = NULL )
	{
		if ( ! $file )
		{
			// Pega a informação apenas com line
			$value	= $this->line( $line );
			if (!$value)
			{
				$value	= $line;
				log_message('debug', 'JX_Lang not found "'.$line.'"');
			}
		}
		else
		{
			// Pega a informação com file + line
			$value	= $this->line( $file.'.'.$line );
			if (!$value)
			{
				// Pega a informação apenas com line
				$value	= $this->line( $line );
				if (!$value)
				{
					$value	= $line;
					log_message('debug', 'JX_Lang not found "'.$line.'"');
				}
			}
		}

		return $value;
	}
	
	/**
	 * Complementa a função do CI_lang->line.
	 * Aqui quando não encontramos a informação no arquivo ou o arquivo não existe, retornamos a KEY para ser usada.
	 * 
	 */
	public function get_ck_array( $line )
	{
		$value					=	$this->line( $line );
		$ck_value				=	array();
		if ( !$value )
		{
			$ck_value			=	NULL;
			log_message('debug', 'JX_Lang not found "'.$line.'"');
		}
		else
		{
			$ar_value			=	NULL;
			$ar_label			=	NULL;
			foreach ( explode( "|", $value ) as $ret )
			{
				if ( !$ar_value )
				{
					$ar_value	=	$ret;
				}
				else
				{
					$ar_label	=	$ret;
				}

				if ( $ar_label && $ar_value )
				{
					$ck_value[ $ar_value ]	=	array( "value" => $ar_value, "label" => $ar_label );
					$ar_value		=	NULL;
					$ar_label		=	NULL;
				}
			}
		}

		return $ck_value;
	}
	public function get_ck_line( $line, $value = NULL )
	{
		$ck_array	=	$this->get_ck_array( $line );
//return $ck_array;
		if ( $ck_array && is_array( $ck_array ) )
		{
			if ( $value )
			{
				return( $ck_array[ $value ][ 'label' ] );
			}
			else
			{
				return $ck_array;
			}
		}
		else
		{
			return $value;
		}
	}
	
	// Cria novos recursos para LOAD.
	public function load($langfile = '', $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '', $force = FALSE, $file_prefix = NULL, $from_reload_all = FALSE )
	{
		$langfile = str_replace('.php', '', $langfile);

		if ( $add_suffix == TRUE )
		{
			$langfile = str_replace('_lang.', '', $langfile).'_lang';
		}

		$langfile .= '.php';
		if ( in_array( $langfile, $this->is_loaded, TRUE )
		&&  !$force
		   )
		{
			return;
		}

		$config =& get_config();

		if ($idiom == '')
		{
			$idiom = ( !isset( $config[ 'language' ] ) ) ? 'pt_BR' : $config['language'];
		}
		$deft_lang = ( !isset( $config[ 'default_language' ] ) ) ? 'pt_BR' : $config['default_language'];
		
		// Determine where the language file is and load it
		if ($alt_path != '' && file_exists($alt_path.'language/'.$idiom.'/'.$langfile))
		{
			include($alt_path.'language/'.$idiom.'/'.$langfile);
		}
		else
		{
			// Tenta a primeira leitura pela lingua do usuário.
			$found = FALSE;
			foreach ( get_instance()->load->get_package_paths(TRUE) as $package_path )
			{
				if ( file_exists( $package_path.'language/'.$idiom.'/'.$langfile ) )
				{
					include($package_path.'language/'.$idiom.'/'.$langfile);
					$found = TRUE;
					break;
				}
			}
			
			// Tenta pela lingua default do sistema.
			if ($found !== TRUE)
			{
				foreach (get_instance()->load->get_package_paths(TRUE) as $package_path)
				{
					if (file_exists($package_path.'language/'.$deft_lang.'/'.$langfile))
					{
						include($package_path.'language/'.$deft_lang.'/'.$langfile);
						$found = TRUE;
						break;
					}
				}
			}
			
			if ($found !== TRUE)
			{
				if ( $from_reload_all )
				{
					log_message( 'error', 'JX_Language Unable to load the requested language file: language/'.$idiom.' ou '.$deft_lang.'/'.$langfile);
					return FALSE;	
				}
				else
				{
					// enviava SHOW_ERRO, isso travava alguns métodos criando um loop. Email.php era um deles.
					log_message( 'error', 'Unable to load the requested language file: language/'.$idiom.' ou '.$deft_lang.'/'.$langfile );
					return FALSE;
				}
			}
		}

		if ( !isset( $lang ) )
		{
			log_message('error', 'JX_Language file contains no data: language/'.$idiom.'/'.$langfile);
			return;
		}

		/*
		 * Acrescenta o prefixo a todos os valores do arquivo lido.
		 */
		if ( $file_prefix )
		{
			$new_lang		=	array();
			foreach ( $lang as $key => $value )
			{
				$new_lang[ $file_prefix.'.'.$key ]	=	$value;
			}
			$lang			=	$new_lang;
		}

		if ($return == TRUE)
		{
			return $lang;
		}

		$this->is_loaded[] = $langfile;
		$this->language = array_merge($this->language, $lang);
		unset($lang);

		if ( $from_reload_all )
		{
			log_message('debug', 'JX_Language file RELOADED: language/'.$idiom.'/'.$langfile);
		}
		else
		{
			log_message('debug', 'JX_Language file loaded: language/'.$idiom.'/'.$langfile);
		}
		return TRUE;
	}
	
	public function reload_all()
	{
		$config =& get_config();
		log_message( 'debug', "JX_Lang.reload_all (". $config['language']. ")." );
		foreach( $this->is_loaded as $langfile )
		{
			log_message( 'debug', "... (". $langfile . ") " . str_replace( '.php', '', str_replace( 'jx/', '', str_replace('_lang', '', $langfile ) ) ) );
			$this->load( $langfile = $langfile, $idiom = ( ! isset($config['language'] ) ) ? 'pt_BR' : $config['language'], $return = FALSE, $add_suffix = FALSE, $alt_path = '', $force = TRUE, $file_prefix = str_replace( '.php', '', str_replace( 'jx/', '', str_replace('_lang', '', $langfile ) ) ), $from_reload_all = TRUE );
		}
	}
	
}
/* End of file JX_Lang.php */
/* Location: ./application/library/JX_Lang.php */
