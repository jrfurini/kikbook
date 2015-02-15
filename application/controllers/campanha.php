<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Cadastro de Campanha.
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/campanha.php
 * 
 * $Id: campanha.php,v 1.4 2013-02-25 15:17:23 junior Exp $
 * 
 */

class Campanha extends JX_Page
{
	protected $_revision			=	'$Id: campanha.php,v 1.4 2013-02-25 15:17:23 junior Exp $';

	var $notificacao_template_id_OK		=	2;
	var $notificacao_template_id_NOT_OK_Q	=	3;
	var $notificacao_template_id_NOT_OK_T	=	4;
	var $notificacao_template_id_JA		=	5;
	
	/*
TODO: Analisar estes aplicativos.
		[15/02/13 19:53:50] Fábio: Mailchimp
		[15/02/13 19:54:07] Fábio: Aweber
	 */
	function __construct()
	{
		$_config		=	array	(
							 'campanha'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	'cod_md5'
														,'where'		=>	'id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'campanha_count'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'campanha'
														,'show'			=>	TRUE
														,'show_style'		=>	'form'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	'countdown_inicio'
														,'where'		=>	'campanha_id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'campanha_pessoa'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'campanha'
														,'show'			=>	TRUE
														,'show_style'		=>	'grid'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	'pessoa_id,seq,data_hora'
														,'where'		=>	'campanha_id = ##id##'
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'kik_movimento'			=>	array	(
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
							,'kik_saldo'				=>	array	(
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
							,'notificacao'				=>	array	(
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
							);
		parent::__construct( $_config );
		
		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
	
	/**
	 * 
	 * Recebe o código da campanha para registrar a pessoa como beneficiária dos bonus.
	 * 
	 * @param string $cod_md5
	 * 		- UK da tabela campanha.
	 */
	public function feedback( $cod_md5 = NULL )
	{
		$redir										=	"/classificacao";

		$status_pessoa									=	NULL;
		
		$campanha_base									=	NULL;
		if ( $cod_md5 )
		{
			$pessoa_id								=	$this->singlepack->get_pessoa_id();

			// Não estando conectado, vamos levar a pessoa à página de conexão e sair da sequencia de código.
			if ( !$pessoa_id )
			{
				$redir_url			=	"campanha/feedback/{$cod_md5}";
				$this->singlepack->set_sessao( 'force_url', $redir_url );
				
				if ( $this->config->item( 'facebook_login' ) == TRUE )
				{
					redirect( "/facebook_login?force_url=$redir_url" );
				}
				else
				{
					redirect( "/login?force_url=$redir_url" );
				}
			}
			
			$campanha_base								=	$this->campanha->get_one_by_where( "cod_md5 = '$cod_md5'" );

			// Campanha existe
			if ( $campanha_base
			&&   $pessoa_id
			   )
			{
				// Verificamos se a pessoa já está na campanha.
				$campanha_pessoa_lista					=	$this->campanha_pessoa->get_all_by_where( "campanha_id = {$campanha_base->id}" );
				$campanha_pessoa_base					=	NULL;
				$pessoa_ja_na_lista					=	FALSE;
				if ( $campanha_pessoa_lista )
				{
					foreach( $campanha_pessoa_lista as $pessoa )
					{
						if ( $pessoa->pessoa_id == $pessoa_id )
						{
							$pessoa_ja_na_lista		=	TRUE;
							$status_pessoa			=	'A';
							$campanha_pessoa_base		=	$pessoa;
							break;
						}
					}
				}
				$pessoa_base						=	$this->pessoa->get_one_by_id( $pessoa_id );

				
				if ( $pessoa_ja_na_lista === FALSE ) // Só quem não está na lista.
				{
					// Atualizamos o countdown da campanha.
					$countdown_base					=	$this->campanha_count->get_one_by_where( "campanha_id = {$campanha_base->id}" );
					if ( $countdown_base )
					{
						if ( $countdown_base->countdown > 0 )
						{
							$status_pessoa			=	'A';
						}
						else
						{
							$status_pessoa			=	'Q';
						}
					}
					else
					{ // Primeira pessoa, ainda não tinhamos definido o countdown da campanha. Assumimos que serão 100.
						$status_pessoa				=	'A';

						$status_pessoa				=	TRUE;
						$countdown_base				=	new stdClass();
						$countdown_base->id			=	NULL;
						$countdown_base->campanha_id		=	$campanha_base->id;
						$countdown_base->countdown		=	100;
						$countdown_base->countdown_inicio	=	100;
					}
					$countdown_base->countdown			=	$countdown_base->countdown - 1; // Continha contando mesmo depois que acaba o número de pessoas.
					if ( $countdown_base->countdown === 0 ) // corrige problema de gravar zero na base de dados.
					{
						$countdown_base->countdown		=	'0';
					}
					$this->campanha_count->update( $countdown_base );
					
					// Campanha está ativa / dentro do período de cadastro.
					$campanha_data_inicio				=	DateTime::createFromFormat( 'Y-m-d H:i:s', $campanha_base->data_inicio );
					$campanha_data_fim				=	DateTime::createFromFormat( 'Y-m-d H:i:s', $campanha_base->data_fim );
					$data_now					=	new DateTime( 'now' );

					if ( $campanha_data_inicio <= $data_now
					&&   ( !$campanha_data_fim
					||     $campanha_data_fim >= $data_now
					     )
					   )
					{
						$status_pessoa				=	$status_pessoa;
					}
					else // Fora do período de ativação da campanha.
					{
						$status_pessoa				=	'T';
					}
					
					// Registra a pessoa à campanha.
					$campanha_pessoa_base				=	new stdClass();
					$campanha_pessoa_base->id			=	NULL;
					$campanha_pessoa_base->campanha_id		=	$campanha_base->id;
					$campanha_pessoa_base->pessoa_id		=	$pessoa_id;
					$campanha_pessoa_base->seq			=	( $countdown_base->countdown >= 0 )	? $countdown_base->countdown_inicio - $countdown_base->countdown // Contagem até o limite da campanha.
																	: $countdown_base->countdown_inicio + ( $countdown_base->countdown * (-1) ); // Contagem após o limite da campanha.
					$campanha_pessoa_base->data_hora		=	date( 'Y-m-d H:i:s' );
					$campanha_pessoa_base->status			=	$status_pessoa;
					$this->campanha_pessoa->update( $campanha_pessoa_base );
					// Campanha atualizada.
					
					// Cria a notificação para a pessoa.
					if ( $status_pessoa == 'A' ) // Pessoa conseguiu entrar na campanha.
					{
						// Atualizar saldo e movimentação da pessoa.
						$this->kik_movimento->add_movto( $pessoa_id, $campanha_base->id, 'C', $campanha_base->kik_bonus, $campanha_base->descr, 'E' );
/*						$saldo_base					=	$this->kik_saldo->get_one_by_where	(
																		"pessoa_id = {$pessoa_id}"
																		);
						if ( !$saldo_base )
						{ // Pessoa sem saldo na base, criamos um novo saldo.
							$saldo_base				=	new stdClass();
							$saldo_base->id				=	NULL;
							$saldo_base->pessoa_id			=	$pessoa_id;
							$saldo_base->saldo_kik			=	0;
							$saldo_base->data_hora_atualizacao	=	date( 'Y-m-d H:i:s' );
						}
						// Acrescenta os bonus da campanha ao saldo da pessoa.
						$saldo_base->saldo_kik				=	$saldo_base->saldo_kik + $campanha_base->kik_bonus;
						$saldo_base->data_hora_atualizacao		=	date( 'Y-m-d H:i:s' );
						$saldo_base->id					=	$this->kik_saldo->update( $saldo_base );
						
						// Registra o movimento de kiks.
						$movimento_base					=	new stdClass();
						$movimento_base->id				=	NULL;
						$movimento_base->kik_saldo_id			=	$saldo_base->id;
						$movimento_base->data_hora			=	date( 'Y-m-d H:i:s' );
						$movimento_base->tipo				=	'E';
						$movimento_base->qtde				=	$campanha_base->kik_bonus;
						$movimento_base->descr				=	$campanha_base->descr;
						$movimento_base->pessoa_rodada_fase_id		=	NULL;
						$movimento_base->campanha_id			=	$campanha_base->id;
						$this->kik_movimento->update( $movimento_base );
						// Saldo e movimento Kiks atualizado.
*/
						$notif_template					=	$this->notificacao_template_id_OK;
					}
					elseif ( $status_pessoa == 'Q' ) // não conseguiu entrar pela qtde. de pessoas.
					{
						$notif_template					=	$this->notificacao_template_id_NOT_OK_Q;
					}
					elseif ( $status_pessoa == 'T' ) // não conseguiu pelo prazo de validade da campanha.
					{
						$notif_template					=	$this->notificacao_template_id_NOT_OK_T;
					}
					$ar_values						=	 array	(
													 	 'pessoa_nome'		=>	$pessoa_base->nome
														,'nome_campanha'	=>	$campanha_base->descr
														,'qtde_bonus'		=>	$campanha_base->kik_bonus
													 	);
					$this->notificacao->notificar( $notif_template, $pessoa_id, $ar_values );
				}
				else
				{
					$ar_values						=	 array	(
													 	 'pessoa_nome'		=>	$pessoa_base->nome
														,'nome_campanha'	=>	$campanha_base->descr
														,'qtde_bonus'		=>	$campanha_base->kik_bonus
													 	);
					$this->notificacao->notificar( $this->notificacao_template_id_JA, $pessoa_id, $ar_values );
				} // fim: Pessoa na lista.
			} // fim: campanha_base
		}

		redirect( $redir );
	}
}
/* End of file campanha.php */
/* Location: /application/controllers/campanha.php */
