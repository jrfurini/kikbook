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
 * $Id: notificar.php,v 1.2 2013-01-17 01:35:28 junior Exp $
 * 
 */

class Notificar extends JX_Process
{
	protected $_revision		=	'$Id: notificar.php,v 1.2 2013-01-17 01:35:28 junior Exp $';

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
							,'notificacao_profile'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'notificacao'
														,'show'			=>	FALSE
														,'show_style'		=>	'grid'
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
							);
		parent::__construct( $_config );
		
		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
	
	public function f( $not_pes )
	{
		$redir							=	"/classificacao";

		$notificacao_pessoa_bas					=	NULL;
		if ( is_numeric( $not_pes ) )
		{
			$notificacao_pessoa_bas				=	$this->notificacao_pessoa->get_one_by_id( $not_pes );
			
			if ( $notificacao_pessoa_bas )
			{
				$notificacao_bas			=	$this->notificacao->get_one_by_id( $notificacao_pessoa_bas->notificacao_id );
				$redir					=	$notificacao_bas->pagina;
				
				$ar_hist				=	array	(
											 'id'				=>	NULL
											,'notificacao_pessoa_id'	=>	$notificacao_pessoa_bas->id
											,'data_hora_exibicao'		=>	date( 'Y-m-d H:i:s' )
											,'acao'				=>	'L'
											);
				$this->notificacao_pessoa_historia->update( $ar_hist );
			}
		}

		redirect( $redir );
	}
	
	/**
	 * Avisa a falta de chutes para uma rodada.
	 */
	public function falta_chute()
	{
		// Retorna a lista de kikers sem chute para amanhã.
		$select	=	"
				select	 /*rod.id				AS	rodada_fase_id
					,rod.cod			AS	rodada_cod
					,*/pes.id				AS	pessoa_id
					,pes.nome			AS	pessoa_nome
					,usr.id_facebook		AS	id_facebook
					,pes.email			AS	email
					,usrcfg.lembrar_via_facebook	AS	lembrar_via_facebook
					,usrcfg.lembrar_via_email	AS	lembrar_via_email
					,usrcfg.idioma			AS	idioma
				from	jogo			AS	jogo
				join	rodada_fase		AS	rod	ON	rod.id  = jogo.rodada_fase_id
				join	pessoa			AS	pes
				join	user			AS	usr	ON	usr.pessoa_id = pes.id
										and	usr.ativo = 'S'
				join	user_cfg		AS	usrcfg	ON	usrcfg.user_id = usr.id
				where	jogo.data_hora			between	now()
									and	date( DATE_ADD( now(), INTERVAL '2' DAY ) )
				and	not exists	(
							select	kick2.id
							from	kick			AS	kick2
							where	kick2.jogo_id		=	jogo.id
							and	kick2.pessoa_id		=	pes.id
							)
				and	exists		(
							select	pescamp.pessoa_id
							from	pessoa_campeonato_versao	AS	pescamp
							where	pescamp.pessoa_id		=	pes.id
							and	rod.campeonato_versao_id	=	pescamp.campeonato_versao_id
							and	pescamp.cadastrado_para_jogar	=	'S'
							)
				group by	 /*rod.id
						,rod.cod
						,*/pes.id
						,usr.id_facebook
				";

		$query_kikers					=	$this->db->query( $select );
		foreach( $query_kikers->result_object() as $kiker )
		{
			//ok. localizar os jogos que ocorrem amanhã
			//ok. buscar as pesssoas que estão cadastradas nos campeonatos e ativas nele.
			//ok. Verificar se a pessoa autorizou a comunicação.
			// ver se já não avisamos ao usuário
			// enviar a notificação.
			// Registrar o envio se deu certo.
			
			// ID TEM QUE SER VARCHAR2

			if ( $kiker->lembrar_via_facebook == 'S' )
			{
//				echo ( $kiker->id_facebook . '  http://kikbook.com/chute/rodada/'.$kiker->rodada_fase_id . $kiker->pessoa_nome . ", amanhã tem jogo e você ainda não fez os seus chutes.\n" );
//				$this->singlepack->send_facebook_notification( $kiker->id_facebook, 'http://kikbook.com/chute/rodada/'.$kiker->rodada_fase_id, $kiker->pessoa_nome . ", amanhã tem jogo e você ainda não fez os seus chutes." );
				echo ( $kiker->id_facebook . '  http://kikbook.com/chute/rodada/' . $kiker->pessoa_nome . ", amanhã tem jogo e você ainda não fez os seus chutes.\n" );
				$this->singlepack->send_facebook_notification( $kiker->id_facebook, 'http://kikbook.com/chute/rodada/', $kiker->pessoa_nome . ", amanhã tem jogo e você ainda não fez os seus chutes." );
			}
			
			// O servidor do EC2 tem limites de envio de e-mails. Então não vamos usar este recurso agora, vamos só notificar.
			if ( $kiker->lembrar_via_facebook == 'S' )
			{
				//$this->singlepack->send_email( $kiker->email, 'Falta de Chute', "Você não fez chutes para a rodada 34." );
			}
		}
	}
	
	/**
	 * Envia notificação cadastrada.
	 */
	public function novidade( $id )
	{
		// Retorna a lista de kikers sem chute para amanhã.
		$select	=	"
				select	 /*rod.id				AS	rodada_fase_id
					,rod.cod			AS	rodada_cod
					,*/pes.id				AS	pessoa_id
					,pes.nome			AS	pessoa_nome
					,usr.id_facebook		AS	id_facebook
					,pes.email			AS	email
					,usrcfg.lembrar_via_facebook	AS	lembrar_via_facebook
					,usrcfg.lembrar_via_email	AS	lembrar_via_email
					,usrcfg.idioma			AS	idioma
				from	jogo			AS	jogo
				join	rodada_fase		AS	rod	ON	rod.id  = jogo.rodada_fase_id
				join	pessoa			AS	pes
				join	user			AS	usr	ON	usr.pessoa_id = pes.id
										and	usr.ativo = 'S'
				join	user_cfg		AS	usrcfg	ON	usrcfg.user_id = usr.id
				where	jogo.data_hora			between	date( DATE_ADD( now(), INTERVAL '1' DAY ) )
									and	date( DATE_ADD( now(), INTERVAL '2' DAY ) )
				and	not exists	(
							select	kick2.id
							from	kick			AS	kick2
							where	kick2.jogo_id		=	jogo.id
							and	kick2.pessoa_id		=	pes.id
							)
				and	exists		(
							select	pescamp.pessoa_id
							from	pessoa_campeonato_versao	AS	pescamp
							where	pescamp.pessoa_id		=	pes.id
							and	rod.campeonato_versao_id	=	pescamp.campeonato_versao_id
							and	pescamp.cadastrado_para_jogar	=	'S'
							)
				group by	 /*rod.id
						,rod.cod
						,*/pes.id
						,usr.id_facebook
				";

		$query_kikers					=	$this->db->query( $select );
		foreach( $query_kikers->result_object() as $kiker )
		{
			//ok. localizar os jogos que ocorrem amanhã
			//ok. buscar as pesssoas que estão cadastradas nos campeonatos e ativas nele.
			//ok. Verificar se a pessoa autorizou a comunicação.
			// ver se já não avisamos ao usuário
			// enviar a notificação.
			// Registrar o envio se deu certo.
			
			// ID TEM QUE SER VARCHAR2

			if ( $kiker->lembrar_via_facebook == 'S' )
			{
//				echo ( $kiker->id_facebook . '  http://kikbook.com/chute/rodada/'.$kiker->rodada_fase_id . $kiker->pessoa_nome . ", amanhã tem jogo e você ainda não fez os seus chutes.\n" );
//				$this->singlepack->send_facebook_notification( $kiker->id_facebook, 'http://kikbook.com/chute/rodada/'.$kiker->rodada_fase_id, $kiker->pessoa_nome . ", amanhã tem jogo e você ainda não fez os seus chutes." );
				echo ( $kiker->id_facebook . '  http://kikbook.com/chute/rodada/' . $kiker->pessoa_nome . ", amanhã tem jogo e você ainda não fez os seus chutes.\n" );
				$this->singlepack->send_facebook_notification( $kiker->id_facebook, 'http://kikbook.com/chute/rodada/', $kiker->pessoa_nome . ", amanhã tem jogo e você ainda não fez os seus chutes." );
			}
			
			// O servidor do EC2 tem limites de envio de e-mails. Então não vamos usar este recurso agora, vamos só notificar.
			if ( $kiker->lembrar_via_facebook == 'S' )
			{
				//$this->singlepack->send_email( $kiker->email, 'Falta de Chute', "Você não fez chutes para a rodada 34." );
			}
		}
	}
}
/* End of file Notificar.php */
/* Location: /application/controllers/Notificar.php */
