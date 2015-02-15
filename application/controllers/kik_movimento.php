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
 * @filesource		/application/controllers/kik_movimento.php
 * 
 * $Id: kik_movimento.php,v 1.5 2013-04-14 12:51:37 junior Exp $
 * 
 */

class Kik_movimento extends JX_Page
{
	protected $_revision	=	'$Id: kik_movimento.php,v 1.5 2013-04-14 12:51:37 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'kik_movimento'	=>	array	(
												 'read_write'		=>	'readonly'
												,'r_table_name'		=>	''
												,'show'			=>	TRUE
												,'show_style'		=>	'none'
												,'hide_columns'		=>	''
												,'readonly_columns'	=>	''
												,'where'		=>	''
												,'orderby'		=>	''
												,'max_rows'		=>	999999
												,'delete_rule'		=>	'restrict'
												,'master'		=>	TRUE
												)
							,'kik_saldo'		=>	array	(
												 'read_write'		=>	'readonly'
												,'r_table_name'		=>	''
												,'show'			=>	TRUE
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
	
	public function get_movto()
	{
		$ret_movtos					=	array();

		// Pega os movimentos em ordem crescente de DATA e ID. Inverteremos isso abaixo.
		if ( $this->singlepack->get_pessoa_id() )
		{
			$movtos						=	$this->kik_movimento->get_all_by_where	(
															 "sld.pessoa_id = {$this->singlepack->get_pessoa_id()}"
															,"kik_movimento.data_hora ASC, kik_movimento.id DESC" // order by
															);
	
			if ( count( $movtos ) > 0 )
			{
				$saldo					=	0;
				foreach( $movtos as $movto )
				{
					if ( $movto->tipo == 'S' ) // Saída, não pode deixar o saldo menor que zero.
					{
						$saldo			=	( ( $saldo - ( $movto->qtde - $movto->qtde_perda ) ) < 0 ) ? 0 : ( $saldo - ( $movto->qtde - $movto->qtde_perda ) );
						$movto->qtde		=	$movto->qtde * (-1);
						$movto->qtde_final	=	$movto->qtde_final * (-1);
					}
					else
					{
						$saldo			=	( $saldo + ( $movto->qtde - $movto->qtde_perda ) );
					}
					
					if ( $movto->qtde_perda > 0 )
					{
						$movto->saldo		=	NULL;
					}
					else
					{
						$movto->saldo		=	number_format(             $saldo, 2, ',', '.' );
					}
					$movto->qtde			=	( $movto->qtde       != 0 ) ? number_format(       $movto->qtde, 2, ',', '.' ) : '';
					$movto->qtde_final		=	( $movto->qtde_final != 0 ) ? number_format( $movto->qtde_final, 2, ',', '.' ) : '';
					$movto->qtde_perda		=	( $movto->qtde_perda != 0 ) ? number_format( $movto->qtde_perda, 2, ',', '.' ) : '';
					$movto->qtde_usada		=	( $movto->qtde_usada != 0 ) ? number_format( $movto->qtde_usada, 2, ',', '.' ) : '';

					$ret_movtos[]			=	$movto;		
				}
			}
	
			$movtos_final					=	array_reverse( $ret_movtos );
	
			$data						=	array	(
											 'movtos'		=> $movtos_final
											);
			$this->load->vars( $data );
	
			// Busca a qtde de kiks que está para vencer em menos de 1 mês.
			$kik_a_vencer					=	new stdClass();
			$kik_a_vencer->qtde				=	0;
	
			foreach( $this->kik_movimento->get_kik_vencer() as $kik )
			{
				$kik_a_vencer->qtde			=	$kik->total_kik;
	
				break;
			}
			$this->load->vars( array( "kiks_hist"	=>	$kik_a_vencer ) );
		}
		else
		{
			$kik_a_vencer					=	new stdClass();
			$kik_a_vencer->qtde				=	0;
			
			$this->load->vars( array( "kiks_hist"	=>	$kik_a_vencer
						 ,"movtos"	=>	array()
						)
					);
		}
		$this->load->view( "portlet/kik_movto.html" );
	}
}
/* End of file kik_movimento.php */
