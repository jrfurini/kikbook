<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Single Pack
 *
 * @package		Single Pack
 * @author		Junior Furini
 * @copyright	Copyright (c) 2012 - 2012, Jarvix, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 0.1
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Montagem de campos em páginas
 *
 * @package		Publicidade
 * @subpackage	Library
 * @category	Ads
 * @author		Junior Furini
 */
class JX_Ads
{
	protected $CI;

	protected $count		=	array();
	protected $show			=	array();
	protected $group_lines		=	0;
	/**
	 * Constructor
	 */
	public function __construct( $field = NULL, $table_name = NULL )
	{
		$this->CI 		=& get_instance();

		$this->CI->singlepack->load_model_file( 'anuncio',	'anuncio' );
		$this->CI->singlepack->load_model_file( 'anuncio_hist',	'anuncio_hist' );
	}

	public function show( $cod_area_anuncio, $ar_values = array(), $interval = 5, $count_save = NULL, $count_repeat = 1 )
	{
		if ( empty( $this->group_lines )
		&&   $count_save
		   )
		{
			$this->group_lines				=	$count_save;
		}

		if ( !key_exists( $cod_area_anuncio, $this->count ) )
		{
			$this->count[ $cod_area_anuncio ]		=	0;
		}
		if ( !key_exists( $cod_area_anuncio, $this->show ) )
		{
			$this->show[ $cod_area_anuncio ]		=	0;
		}
		
		$this->count[ $cod_area_anuncio ]			=	$this->count[ $cod_area_anuncio ] + 1;
		if ( !empty( $this->group_lines ) )
		{
			$this->group_lines				=	$this->group_lines - 1;
		}

		     // Intervalo configurado, mas não falta apenas 1 ou 2 linhas para serem exibidas. Se sim, aguarda o final do grupo ou página.
		if ( ( $this->count[ $cod_area_anuncio ] >= $interval
		&&     ( $this->group_lines > 2
		||       ( empty( $this->group_lines )
		||         $this->group_lines == 0
		         )
		       )
		     )
		     // Está acabando as linhas a serem exibidas e estas são maiores ou igual que 4.
		||   ( $count_save >= 4
		&&     $this->count[ $cod_area_anuncio ] >= $count_save
		     )
		     // Falta apenas 1 ou linha para ser exibida.
//		&&   
		   )
		{
			for ($i = 0; $i < $count_repeat; $i++)
			{
				$html						=	$this->CI->anuncio->get_next( $cod_area_anuncio, $ar_values );
	
				$this->count[ $cod_area_anuncio ]		=	0;
//				$this->group_lines				=	$count_save;
				
				$this->show[ $cod_area_anuncio ]		=	$this->show[ $cod_area_anuncio ] + 1;
	
				echo $html;
			}
			
			return TRUE;
		}
		
		return FALSE;
	}

	public function show_if_not_show( $cod_area_anuncio, $ar_values = array(), $count_repeat = 1 )
	{
		if ( !key_exists( $cod_area_anuncio, $this->show ) )
		{
			$this->show[ $cod_area_anuncio ]	=	0;
		}

		if ( !is_array( $ar_values ) )
		{
			$ar_values				=	array();
		}
		
		if ( empty( $this->show[ $cod_area_anuncio ] ) || $this->show[ $cod_area_anuncio ] == 0 )
		{
			for ($i = 0; $i < $count_repeat; $i++)
			{
				$html			=	$this->CI->anuncio->get_next( $cod_area_anuncio, $ar_values );
	
				echo $html;
			}
			
			return TRUE;
		}

		return FALSE;
	}

	public function show_if_not_show_reset( $cod_area_anuncio, $ar_values = array(), $count_repeat = 1 )
	{
		if ( !key_exists( $cod_area_anuncio, $this->show ) )
		{
			$this->show[ $cod_area_anuncio ]	=	0;
		}
		
		if ( empty( $this->show[ $cod_area_anuncio ] ) || $this->show[ $cod_area_anuncio ] == 0 )
		{
			for ($i = 0; $i < $count_repeat; $i++)
			{
				$html			=	$this->CI->anuncio->get_next( $cod_area_anuncio, $ar_values );
	
				echo $html;
			}

			$this->count[ $cod_area_anuncio ]		=	0;
			$this->show[ $cod_area_anuncio ]		=	0;
			$this->group_lines				=	0;
		}

		return TRUE;
	}
}

/* End of file Form_validation.php */
