<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Campeonato versão classificação.
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/campeonato_versao_classificacao.php
 * 
 * $Id: campeonato_versao_classificacao.php,v 1.8 2013-02-25 15:17:23 junior Exp $
 * 
 */

class Campeonato_versao_classificacao extends JX_Process
{
	protected $_revision	=	'$Id: campeonato_versao_classificacao.php,v 1.8 2013-02-25 15:17:23 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'campeonato_versao_classificacao'	=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
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

		log_message('debug', 'Controller '.get_class( $this ).' initialized.' );
	}

	public function classificar( $rodada_fase_id = NULL, $campeonato_versao_id = NULL )
	{
		log_message( 'debug', 'classificar..' );
		
		$this->master_model->classificar( $rodada_fase_id, $campeonato_versao_id );
		
		log_message( 'debug', 'classificar.2' );
		return TRUE;
	}

	public function estorna_calculo( $campeonato_versao_id = NULL )
	{
		log_message( 'debug', 'estornar' );
		if ( !$campeonato_versao_id )
		{
			$camps_base			=	$this->campeonato_versao->get_all_by_where( "1 = 1" );
			
			foreach( $camps_base as $camp )
			{
				$this->kick->estorna_calculo( $camp->id );
			}
			
			log_message( 'debug', 'estorna.2' );
			return TRUE;
		}
		else
		{
			$this->kick->estorna_calculo( $campeonato_versao_id );
		}

		log_message( 'debug', 'estornar.3' );
		return TRUE;
	}
	
	public function recalcular_tudo( $campeonato_versao_id = NULL )
	{
		log_message( 'debug', 'recalcular_tudo' );
		
		if ( !$campeonato_versao_id )
		{
			$camps_base			=	$this->campeonato_versao->get_all_by_where( "1 = 1" );
			
			foreach( $camps_base as $camp )
			{
				$this->kick->estorna_calculo( $camp->id );
		
				$this->master_model->classificar( NULL, $camp->id );
			}
			
			log_message( 'debug', 'recalcular_tudo.2' );
			return TRUE;
		}
		else
		{
			$this->kick->estorna_calculo( $campeonato_versao_id );
	
			$this->master_model->classificar( NULL, $campeonato_versao_id );
			
			log_message( 'debug', 'recalcular_tudo.3' );
			return TRUE;
		}
	}
}
/* End of file campeonato_versao_classificacao.php */
/* Location: /application/controllers/campeonato_versao_classificacao.php */
