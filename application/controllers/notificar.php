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
 * @filesource		/application/controllers/Notificar.php
 * 
 * $Id: notificar.php,v 1.11 2013-04-14 12:51:47 junior Exp $
 * 
 */

class Notificar extends JX_Process
{
	protected $_revision				=	'$Id: notificar.php,v 1.11 2013-04-14 12:51:47 junior Exp $';

	protected $notificacao_template_id_falta_chute	=	1;
	protected $notificacao_template_id_kik_vencer	=	8;
	
	function __construct()
	{
		$_config		=	array	(
							 'notificacao'				=>	array	(
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
							,'notificacao_template'			=>	array	(
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
							,'notificacao_pessoa'			=>	array	(
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
							,'notificacao_pessoa_historico'		=>	array	(
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
							,'rodada_fase'				=>	array	(
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
							,'jogo'					=>	array	(
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
	 * Enter description here ...
	 * @param int $not_pes
	 * @param string $via
	 * 		- facebook
	 * 		- email
	 * 		- page
	 */
	public function feedback( $not_pes, $via, $acao = 'L', $ajax = 'false' )
	{
		$redir							=	"/classificacao";
		$ok							=	FALSE;

		$notificacao_pessoa_bas					=	NULL;
		if ( is_numeric( $not_pes ) )
		{
			$notificacao_pessoa_bas				=	$this->notificacao_pessoa->get_one_by_id( $not_pes );
			
			if ( $notificacao_pessoa_bas )
			{
				// Marca a notificação para a pessoa como lida.
				$notificacao_pessoa_bas->acao		=	( ( strtoupper( $acao )	 == 'L' ) ? strtoupper( $acao ) : ( ( strtoupper( $acao ) == 'A' ) ? strtoupper( $acao ) : $notificacao_pessoa_bas->acao ) );
				// Registra a data e hora da leitura.
				$ar_hist				=	array	(
											 'id'				=>	NULL
											,'notificacao_pessoa_id'	=>	$notificacao_pessoa_bas->id
											,'data_hora_exibicao'		=>	date( 'Y-m-d H:i:s' )
											,'acao'				=>	$notificacao_pessoa_bas->acao
											);
				$this->notificacao_pessoa_historico->update( $ar_hist );
				$notificacao_bas			=	$this->notificacao->get_one_by_id( $notificacao_pessoa_bas->notificacao_id );
				// Prepara para redirecionar a pessoa.
				$redir					=	$notificacao_bas->pagina_redirect;
					
				
				if ( $notificacao_pessoa_bas->acao == 'L' ) // Ler
				{
					// Atualiza as qtdes de controle da notificação.
					if ( strtolower( $via ) == 'facebook' )
					{
						$notificacao_bas->qtde_pes_facebook_feedback++;
					}
					elseif ( strtolower( $via ) == 'email' )
					{
						$notificacao_bas->qtde_pes_email_feedback++;
					}
					elseif ( strtolower( $via ) == 'page' )
					{
						$notificacao_bas->qtde_pes_pagina_feedback++;
					}
				}
				elseif ( $notificacao_pessoa_bas->acao == 'A' ) // Adiar
				{
					// Adia a notificação por 1 hora.
					$date_now			=	new DateTime( 'now' );
					$date_now->add( new DateInterval( 'PT1H' ) );
					$notificacao_pessoa_bas->data_hora_envio		=	$date_now->format( 'Y-m-d H:i:s' );
					$redir				=	FALSE;
				}
				
				$this->notificacao->update( $notificacao_bas );
				$this->notificacao_pessoa->update( $notificacao_pessoa_bas );
				$ok					=	TRUE;
			}
		}
		
		if ( strtoupper( $ajax ) == 'TRUE' )
		{
			if ( $ok )
			{
				$ar_notif				=	$this->notificacao->get_my_page_notification( $notificacao_pessoa_bas->pessoa_id );
				if ( $ar_notif )
				{
					echo json_encode( array_merge( array( 'tipo' => 'new' ), get_object_vars( $ar_notif ) ) );
				}
				else
				{
					echo json_encode( array( 'tipo' => 'true', 'pes' => $this->singlepack->get_pessoa_id() ) );
				}
			}
			else
			{
				echo json_encode( array( 'tipo' => 'false' ) );
			}
		}
		else
		{
			if ( $redir )
			{
				redirect( $redir );
			}
			else
			{
				redirect( "/classificacao" );
			}
		}
	}
	
	/**
	 * Avisa a falta de chutes para os kikers.
	 */
	public function falta_chute()
	{
		// Retorna a lista de kikers sem chute para  HOJE (00:00), AMANHÃ E DEPOIS DE AMANHÃ (4 dias 00:00).
		//					between	now() e date( DATE_ADD( now(), INTERVAL '2' DAY ) )
		$select	=	"
				select	 pes.id					AS	pessoa_id
					,pes.nome				AS	pessoa_nome
					,pes.imagem_facebook			AS	pessoa_imagem
					,rod.id					AS	rodada_id
					,concat( ver.descr, ' ', rod.cod, ' (', date_format( rod.data_inicio, '%e/%m' ), ' até ', date_format( rod.data_fim, '%e/%m/%Y' ), ')' )		AS	rodada_nome
					,count( jogo.id )			AS	qtde_jogos
				from	jogo					AS	jogo
				join	rodada_fase				AS	rod	ON	rod.id  = jogo.rodada_fase_id
				join	pessoa					AS	pes
				join	pessoa_campeonato_versao		AS	pescamp ON	pescamp.pessoa_id		=	pes.id
												and	rod.campeonato_versao_id	=	pescamp.campeonato_versao_id
												and	pescamp.cadastrado_para_jogar	=	'S'
				join	campeonato_versao			AS	ver	ON	ver.id				=	rod.campeonato_versao_id
				where	jogo.data_hora			between	date( DATE_FORMAT(now(), '%Y-%m-%d') )
									and	adddate( date( DATE_FORMAT(now(), '%Y-%m-%d') ), interval 3 day )
				and	not exists	(
							select	kick2.id
							from	kick			AS	kick2
							where	kick2.jogo_id		=	jogo.id
							and	kick2.pessoa_id		=	pes.id
							and	( kick2.kick_casa	IS NOT NULL
							and	  kick2.kick_visitante	IS NOT NULL
								)
							)
/* and pes.id in( 4, 432 ) */
/* and pes.id = 4 */
/* and pes.id = 6 */
				group by pes.id
					,pes.nome
					,rod.id
					,concat( ver.descr, ' ', rod.cod, ' (', date_format( rod.data_inicio, '%e/%m' ), ' até ', date_format( rod.data_fim, '%e/%m/%Y' ), ')' )
				";
		$date_now								=	new DateTime( 'now' );

		$query_kikers								=	$this->db->query( $select );
		$kikers_rows								=	$query_kikers->result_object();
		echo "Qtde Kikers=" . count( $kikers_rows ) . "\n";
		
		if ( count( $kikers_rows ) > 0 ) // Se existe linha precisamos enviar para os kikers.
		{
			$pessoa_id_ant				=	-1;
			
			$pessoa_nome_ant			=	NULL;
			$pessoa_imagem_ant			=	NULL;
			$ar_rodadas				=	array();
			$qtde_jogos				=	0;
			// Kikers sem chute.
			foreach( $kikers_rows as $kiker )
			{
				if ( $pessoa_id_ant != $kiker->pessoa_id )
				{
					if ( $pessoa_id_ant != -1 )
					{
						$ar_values		=	 array	(
										 		 'pessoa_nome'		=>	$pessoa_nome_ant
										 		,'pessoa_imagem'	=>	$pessoa_imagem_ant
										 		,'qtde_jogos'		=>	$qtde_jogos
										 		,'s'			=>	( ( $qtde_jogos == 1 ) ? '' : 's' )
										 		,'ar_rodadas'		=>	$ar_rodadas
										 	);
						echo $pessoa_nome_ant . "\n";
						$this->notificacao->notificar( $this->notificacao_template_id_falta_chute, $pessoa_id_ant, $ar_values, TRUE );
					}

					$ar_rodadas				=	array();
					$qtde_jogos				=	0;
					$pessoa_id_ant				=	$kiker->pessoa_id;
					$pessoa_nome_ant			=	$kiker->pessoa_nome;
					$pessoa_imagem_ant			=	$kiker->pessoa_imagem;
				}

				$obj_rodada				=	new stdClass();
				$obj_rodada->id				=	$kiker->rodada_id;
				$obj_rodada->rodada_nome		=	$kiker->rodada_nome;
				$obj_rodada->qtde_jogos			=	$kiker->qtde_jogos;
				$qtde_jogos				+=	$kiker->qtde_jogos;

				$ar_rodadas[]				=	$obj_rodada;
				unset( $obj_rodada );
			}
			
			if ( $pessoa_id_ant != -1 )
			{
				$ar_values					=	 array	(
										 		 'pessoa_nome'		=>	$pessoa_nome_ant
										 		,'pessoa_imagem'	=>	$pessoa_imagem_ant
										 		,'qtde_jogos'		=>	$qtde_jogos
										 		,'s'			=>	( ( $qtde_jogos == 1 ) ? '' : 's' )
										 		,'ar_rodadas'		=>	$ar_rodadas
										 	);
				echo $pessoa_nome_ant . "\n";
				$this->notificacao->notificar( $this->notificacao_template_id_falta_chute, $pessoa_id_ant, $ar_values, TRUE, $notif_id = NULL, $notif_type = 'falta_chute' );
			}
		}
	}
	
	/**
	 * Avisa que existem Kiks que vencem em menos de 1 mês.
	 */
	public function kik_vencer()
	{
		$kiks_rows								=	$this->kik_movimento->get_kik_vencer( $all = TRUE );
		echo "Qtde Kiks a Vencer=" . count( $kiks_rows ) . "\n";
		
		if ( count( $kiks_rows ) > 0 ) // Se existe linha precisamos enviar para os kikers.
		{
			foreach( $kiks_rows as $kiker )
			{
				$ar_values		=	 array	(
							 		 'pessoa_nome'		=>	$kiker->pessoa_nome
									,'pessoa_imagem'	=>	$kiker->pessoa_imagem
							 		,'qtde_kiks'		=>	$kiker->total_kik
							 		,'s'			=>	( ( $kiker->total_kik == 1 ) ? '' : 's' )
								 	);
				echo $kiker->pessoa_nome . "\n";
				$this->notificacao->notificar( $this->notificacao_template_id_kik_vencer, $kiker->pessoa_id, $ar_values, TRUE, $notif_id = NULL, $notif_type = 'kik_vencer' );
			}
		}
	}
	
	/**
	 * Envia notificação cadastrada.
	 */
	public function novidade( $id_templ_notif, $notif_id = NULL )
	{
		if ( $id_templ_notif
		||   $notif_id
		   )
		{
			foreach( $this->pessoa->get_all_by_where( "usr.ativo = 'S'" ) as $pessoa )
//			foreach( $this->pessoa->get_all_by_where( "usr.id = 3" ) as $pessoa )
			{
				$ar_values		=	 array	(
							 		 'pessoa_nome'		=>	$pessoa->nome
									,'pessoa_imagem'	=>	$pessoa->imagem_facebook
								 	);
				echo $pessoa->nome . "\n";
				
				if ( $notif_id )
				{
					$this->notificacao->notificar( NULL, $pessoa->id, $ar_values, TRUE, $notif_id, 'novidade' );
				}
				else
				{
					$this->notificacao->notificar( $id_templ_notif, $pessoa->id, $ar_values, TRUE, NULL, 'novidade' );
				}
			}
		}
		else
		{
			echo "Não informou a notificação.";
		}
	}
}
/* End of file Notificar.php */
