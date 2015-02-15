<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Cadastro de Compras.
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/compra.php
 * 
 * $Id: compra.php,v 1.2 2012-09-06 10:17:29 junior Exp $
 * 
 */

class Compra extends JX_Process
{
	protected $_revision	=	'$Id: compra.php,v 1.2 2012-09-06 10:17:29 junior Exp $';

	protected $template_notificacao_compra		=	10;
	
	function __construct()
	{
		$_config		=	array	(
							 'compra'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'compra_entrega'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'compra_produto'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'notificacao'
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'kik_movimento'		=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'notificacao_pessoa'
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'pessoa'				=>	array	(
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
							,'endereco'				=>	array	(
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
							,'produto_tamanho'			=>	array	(
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
	
	public function set_novo_produto( $pessoa_id, $produto_tamanho_id, $qtde, $valor_unit, $compra_id = NULL )
	{
		$pessoa_base					=	$this->pessoa->get_one_by_id( $pessoa_id );
		
		if ( $pessoa_base )
		{
			if ( $compra_id )
			{
				$compra_base			=	$this->compra->get_one_by_id( $compra_id );
			}
			else
			{
				$compra_base			=	NULL;
			}

			if ( !$compra_base ) // Cria uma nova compra
			{
				$endereco_base			=	$this->endereco->get_one_by_where( "pessoa_id = {$pessoa_base->id} and preferencial = 'S'" );
				if ( !$endereco_base )
				{
					echo "Pessoa sem endereço cadastrado. \n";
					$endereco_base				=	new stdClass();
					$endereco_base->id			=	NULL;
					$endereco_base->pessoa_id		=	$pessoa_base->id;
					$endereco_base->tipo_endereco_id	=	1; // Em mãos.
					$endereco_base->preferencial		=	'S';
					$endereco_base->cep_id			=	1; // Software Ltda.
					$endereco_base->numero			=	55;
					$endereco_base->complemento		=	NULL;
					$endereco_base->referencia		=	NULL;
					$endereco_base->id			=	$this->endereco->update( $endereco_base );
				}

				$compra_base			=	new stdClass();
				$compra_base->id		=	NULL;
				$compra_base->pessoa_id		=	$pessoa_id;
				$compra_base->data_hora		=	'CURRENT_TIMESTAMP';
				$compra_base->endereco_id	=	$endereco_base->id;
				$compra_base->tipo_entrega_id	=	1;
				$compra_base->valor_total	=	0;
				$compra_base->valor_desconto	=	0;
				$compra_base->id		=	$this->compra->update( $compra_base );
			}
			
			$produto_tamanho_base			=	$this->produto_tamanho->get_one_by_id( $produto_tamanho_id );
			
			if ( !$produto_tamanho_base )
			{
				echo 'Produto tamanho não cadastrado.';
				return FALSE;
			}
			
			// Associamos o produto tamanho.
			$compra_produto_base			=	$this->compra_produto->get_one_by_where( "compra_id = {$compra_base->id} and produto_tamanho_id = {$produto_tamanho_base->id}" );
			if ( !$compra_produto_base )
			{
				$compra_produto_base				=	new stdClass();
				$compra_produto_base->id			=	NULL;
				$compra_produto_base->compra_id			=	$compra_base->id;
				$compra_produto_base->produto_tamanho_id	=	$produto_tamanho_base->id;
				$compra_produto_base->qtde_compra		=	$qtde;
				$compra_produto_base->valor_unit		=	$valor_unit;
			}
			else
			{
				echo "Produto tamanho já registrado nesta compra.";
				return FALSE;
			}
			
			$compra_base->valor_total		+=	$compra_produto_base->qtde_compra * $compra_produto_base->valor_unit;
			
			// Atualiza a base de dados de compra.
			$this->compra->update( $compra_base );
			$this->compra_produto->update( $compra_produto_base );
			
			// Registra o pagamento com Kiks.
			$this->kik_movimento->set_pagamento_compra( $compra_base, $pessoa_base );

			// Notifica
			$ar_values					=	 array	(
										 		 'pessoa_nome'		=>	$pessoa_base->nome
										 		,'qtde_total_kiks'	=>	$compra_base->valor_total
										 		,'s'			=>	( ( $compra_base->valor_total == 1 ) ? '' : 's' )
										 	);
			$this->notificacao->notificar( $this->template_notificacao_compra, $pessoa_base->id, $ar_values, TRUE );
			
			return $compra_base->id; // Tudo registrado certo, retornamos o ID da compra.
		}
		else
		{
			echo "Não localizou a pessoa $pessoa_id \n";
			return FALSE;
		}
	}
}
/* End of file compra.php */
