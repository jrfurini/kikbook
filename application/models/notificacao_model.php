<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Kicks Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/notificacao_model.php
 * 
 * $Id: notificacao_model.php,v 1.11 2013-04-14 12:58:25 junior Exp $
 * 
 */

class Notificacao_model extends JX_Model
{
	protected $_revision		=	'$Id: notificacao_model.php,v 1.11 2013-04-14 12:58:25 junior Exp $';
	
	// Notificar
	protected $template_base	=	NULL;
	protected $template_have_send	=	FALSE;
	protected $notificacao_base	=	NULL;
	protected $notif_pessoas	=	NULL;
	protected $date_ref		=	NULL;
	
	// Notificar: fim
	
	function __construct()
	{
		$_config		=	array	(
							 'notificacao_pessoa'			=>	array	(
														 'model_name'	=>	'notificacao_pessoa'
												 		)
							,'notificacao'				=>	array	(
														 'model_name'	=>	'notificacao'
												 		)
							,'notificacao_template'			=>	array	(
														 'model_name'	=>	'notificacao_template'
												 		)
							,'pessoa'				=>	array	(
														 'model_name'	=>	'pessoa'
												 		)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 notificacao.*
			,notificacao.nome		AS	title
			,notificacao.data_inicio	AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'notificacao' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
	
	/**
	 * Retorna a lista de passoa já cadastradas na notificacação.
	 */
	public function get_pessoa_list( $notificacao_id )
	{
		$pes_list			=	$this->notificacao_pessoa->get_all_by_where( 'notificacao_id = ' . $notificacao_id );
		$ret				=	array();
		foreach( $pes_list as $pes )
		{
			$ret[ $pes->pessoa_id ]			=	$pes;
		}
		return $ret;
	}
	
	/**
	 * Retorna a primeira notificação que deve ser exibida nas páginas para a pessoa conectada.
	 */
	public function get_my_page_notification( $pessoa_id = NULL )
	{
		if ( !$pessoa_id )
		{
			if ( $this->singlepack->user_connected() )
			{
				$pessoa_id			=	$this->singlepack->get_pessoa_id();
			}
			else
			{
				$pessoa_id			=	NULL;
			}
		}
		if ( $pessoa_id )
		{
//			$notificacao				=	new stdClass();
//			$notificacao->texto_enviado		=	'<h4>Parabéns Junior!</h4><br><div style="margin: 5px auto 0px auto; line-height: 16px;">Você conquistou 100 Kiks na campanha "Seja bem-vindo ao Kikbook".</div>';
			$notificacao				=	$this->notificacao_pessoa->get_one_by_where( 	"	metodo = 'P'
															and	pessoa_id = {$pessoa_id}
															and	( acao = 'N'
															or        ( acao = 'A'
															and         data_hora_envio <= now()
																  )
																)
															" );
			if ( !isset( $notificacao )
			||   !is_object( $notificacao )
			||   count( $notificacao ) == 0 )
			{
				$notificacao			=	NULL;
			}
		}
		else
		{
			$notificacao				=	NULL;
		}
		return $notificacao;
	}

	/**
	 * 
	 * Retorna a última notificação gerada para o template.
	 *	Analisamos se a última notificação gerada, a que tem a mesma data que a data do último envio, está sem nenhum envio.
	 *	Se estiver, mantemos esta notificação como sendo a notificação que será utilizada pelo processo que está solicitando.
	 * 
	 * @param int $template_id
	 */
	public function get_last_by_template( $template_id, $template_base = NULL )
	{
		if ( !$template_base
		||   !is_object(  $template_base )
		   )
		{
			$template_base				=	$this->notificacao_template->get_one_by_id( $template_id );
		}

		return $this->notificacao->get_one_by_where	( 
								"	notificacao_template_id		=	{$template_base->id}
								and	data_inicio			=	'{$template_base->ultimo_envio}'
								and	data_fim			>=	now()
								and	( qtde_pes_facebook_enviada	=	0
								and	  qtde_pes_email_enviada	=	0
								and       qtde_pes_pagina_enviada	=	0
									)
								");
	}
	
	/**
	 * Conjunto de functions para enviar as notificações aos usuários.
	 */
	/*
	 * Altera as variáveis enviada pelos valores enviados.
	 * 
	 * 	Todos os campos devem estar entre chaves "{}". Exemplo: {nome}
	 */
	public function translate_vars( $ar_values, $text )
	{
		$ret_text					=	$text;
		foreach( $ar_values as $fld => $value )
		{
			if ( !is_array( $value ) )
			{
				$ret_text				=	preg_replace( "/{".$fld."}/", $value, $ret_text );
			}
		}
		return $ret_text;
	}
	
	/* 
	 * Função que registra a envia a notificação para a pessoa.
	 * 	Trabalha em conjunto com notificar.
	 * 
	 * 	$metodo
	 * 		F - facebook
	 * 		E - e-mail
	 * 		P - página
	 * 
	 * 	$pessoa
	 * 		Objecto contendo os dados da pessoa.
	 * 
	 * 	$ar_values
	 * 		Array de campos e valores.
	 * 		
	 */
	public function send_notif( $metodo, $pessoa, $ar_values, $show_log = FALSE, $notif_type = 'kikbook' )
	{
		// Verifica se a notificação já existe na base de dados.
		$notif_pes_base						=	$this->notificacao_pessoa->get_one_by_where	(
																"	notificacao_pessoa.pessoa_id		=	{$pessoa->id}
																and	notificacao_pessoa.notificacao_id	=	{$this->notificacao_base->id}
																and	notificacao_pessoa.metodo		=	'$metodo'
																"
																);
		if ( $notif_pes_base )
		{
			$nova_notif					=	FALSE;

			// Se já foi enviado antes, anulamos tudo para enviar novamente.
			$notif_pes_base->data_hora_envio		=	'CURRENT_TIMESTAMP';
			$notif_pes_base->acao				=	'N';
			
			// Acumula todos dos textos enviados. Na notificação de página, forçará a pessoa a ler todas de uma vez.
			// Já as notificações de Face e e-mail serão enviadas N vezes com textos separados.
		}
		else
		{
			$nova_notif					=	TRUE;

			$notif_pes_base					=	new stdClass();
			$notif_pes_base->id				=	NULL;
			$notif_pes_base->pessoa_id			=	$pessoa->id;
			$notif_pes_base->notificacao_id			=	$this->notificacao_base->id;
			$notif_pes_base->data_hora_envio		=	'CURRENT_TIMESTAMP';
			$notif_pes_base->metodo				=	$metodo;
			$notif_pes_base->acao				=	'N';
			$notif_pes_base->texto_enviado			=	NULL;

			$notif_pes_base->id				=	$this->notificacao_pessoa->update( $notif_pes_base );
		}
		
		// Prepara a URL para o feedback do usuário.
		$notificacao_url					=	NULL;
		if ( $metodo == 'F' )
		{
//TODO:			$notificacao_url				=	$this->config->item( 'base_url' ) . "notificar/feedback/{$notif_pes_base->id}/facebook";
			$notificacao_url				=	'http://kikbook.com/' . "notificar/feedback/{$notif_pes_base->id}/facebook";
		}
		elseif ( $metodo == 'E' )
		{
//TODO:			$notificacao_url				=	$this->config->item( 'base_url' ) . "notificar/feedback/{$notif_pes_base->id}/email";
			$notificacao_url				=	'http://kikbook.com/' . "notificar/feedback/{$notif_pes_base->id}/email";
		}
		elseif ( $metodo == 'P' )
		{
//TODO:			$notificacao_url				=	$this->config->item( 'base_url' ) . "notificar/feedback/{$notif_pes_base->id}/page";
			$notificacao_url				=	'http://kikbook.com/' . "notificar/feedback/{$notif_pes_base->id}/page";
		}
		
		$notificacao_text						=	NULL;
		if ( $metodo == 'F' )
		{
			if ( $pessoa->lembrar_via_facebook != 'S'
			||   $this->notificacao_base->via_facebook != 'S'
			||   !$pessoa->id_facebook
			   )
			{
				// Eliminamos a notificação para a pessoa.
				$notif_pes_base->id				=	$notif_pes_base->id * (-1);
				$this->notificacao_pessoa->delete( $notif_pes_base->id );
				
				return FALSE; // Notificação não usa o facebook ou a pessoa não quer receber via facebook.
			}
			elseif ( !$nova_notif ) // Só enviamos por e-mail se a notificação for nova.
			{
				return FALSE; // Notificação não usa o e-mail ou a pessoa não auer receber via e-mail.
			}
			$this->notificacao_base->qtde_pes_facebook_enviada	+=	1;
			$notificacao_text					=	$this->notificacao_base->texto_facebook;
			if ( $show_log ) echo ( '......Via facebook=' . $pessoa->id_facebook . ' ' );
		}
		elseif ( $metodo == 'E' )
		{
			if ( $pessoa->lembrar_via_email != 'S'
			||   $this->notificacao_base->via_email != 'S'
			||   !$pessoa->email
			   )
			{
				// Eliminamos a notificação para a pessoa.
				$notif_pes_base->id				=	$notif_pes_base->id * (-1);
				$this->notificacao_pessoa->delete( $notif_pes_base->id );
				
				return FALSE; // Notificação não usa o e-mail ou a pessoa não auer receber via e-mail.
			}
			elseif ( !$nova_notif ) // Só enviamos por e-mail se a notificação for nova.
			{
				return FALSE; // Notificação não usa o e-mail ou a pessoa não auer receber via e-mail.
			}
			else
			{
				$this->notificacao_base->qtde_pes_email_enviada		+=	1;
				$notificacao_text					=	$this->notificacao_base->texto_email;
			}
			if ( $show_log ) echo ( '......Via email=' . $pessoa->email . ' ' );
		}
		elseif ( $metodo == 'P' )
		{
			if ( $this->notificacao_base->via_pagina != 'S'
			   )
			{
				// Eliminamos a notificação para a pessoa.
				$notif_pes_base->id				=	$notif_pes_base->id * (-1);
				$this->notificacao_pessoa->delete( $notif_pes_base->id );

				return FALSE; // Notificação não usa a página.
			}
			$this->notificacao_base->qtde_pes_pagina_enviada	+=	1;
			$notificacao_text					=	$this->notificacao_base->texto_pagina;
			if ( $show_log ) echo ( '......Via pagina ' );
		}

		$notificacao_text						=	$this->notificacao->translate_vars	(
																 array_merge	(
																		 $ar_values
																		,array	(
																	 		 'url'	=>	$notificacao_url
																		 	)
																		)
																,$notificacao_text
																);
//echo "\n(1) $notificacao_text \n";
		// Cria arquivo com o conteúdo.
		$file_name							=	"__notificacao_temp.html";
		$f_notif							=	@fopen( "../application/views/$file_name", 'w' );
		if ( $f_notif )
		{
			$bytes							=	fwrite( $f_notif, $notificacao_text );
			fclose( $f_notif );
			$notificacao_text					=	$this->load->view	(
														 $file_name
														,array_merge	(
																 $ar_values
																,array	(
																	 'url'	=>	$notificacao_url
																 	)
																)
														,true // Retorna o resultado para uma variável.
														);
		}
	
		// Se existir a string .html dentro do texto do template/notific indica que é um nome de arquivo que devemos usar.
		if ( strpos( strtolower( $notificacao_text ), '.html' ) !== FALSE )
		{
			$notificacao_text					=	$this->load->view	(
														 $notificacao_text
														,array_merge	(
																 $ar_values
																,array	(
																	 'url'	=>	$notificacao_url
																 	)
																)
														,true // Retorna o resultado para uma variável.
														);
//echo "\n $notificacao_text \n";
		}
//echo "\n(2) $notificacao_text \n";

		if ( $show_log ) echo ( $notificacao_url . "{$this->config->item( 'facebook_login' )}\n" );
		$ret							=	FALSE;

		if ( $notificacao_text ) // Só enviamos se existir o texto.
		{
			if ( $metodo == 'F'
//TODO:			&&   $this->config->item( 'facebook_login' ) == TRUE
//TODO: o config não está funcionando aqui, está vazio.
			   )
			{
				$ret					=	$this->singlepack->send_facebook_notification( $pessoa->id_facebook, $notificacao_url, $notificacao_text, $notif_type );
			}
			elseif ( $metodo == 'E' )
			{
				$ret					=	$this->singlepack->send_email( $pessoa->email, $this->notificacao_base->nome, $notificacao_text );
			}
			elseif ( $metodo == 'P' )
			{
				$ret					=	TRUE;
				// O envio da página se dará quando o usuário se conectar novamente ou trocar de página.
			}

			// Acumula todos dos textos enviados. Na notificação de página, forçará a pessoa a ler todas de uma vez.
			// Já as notificações de Face e e-mail serão enviadas N vezes com textos separados.
			$notif_pes_base->texto_enviado			=	$notif_pes_base->texto_enviado . $notificacao_text;
	
			if ( $ret !== TRUE )
			{ // Erro
				$notif_pes_base->acao			=	'E';
			}
			
			$this->notificacao_pessoa->update( $notif_pes_base );
		}
		else
		{
			$ret						=	TRUE;
		}
		return $ret;
	}
	/*
	 * Notificar, processo que gera a notificação dentro do template enviado.
	 * 
	 * 	$template_id
	 * 		id do template que será utilizado para enviar a notificação.
	 * 
	 * 	$pessoa_id
	 * 		Id da pessoa que receberá a notificação.
	 * 
	 * 	$ar_values
	 * 		Array contendo os campos e os respectivos valores que serão substituídos no envio.
	 */
	public function notificar( $template_id, $pessoa_id, $ar_values, $show_log = FALSE, $notif_id = NULL, $notif_type = 'kikbook' )
	{
		// Verificamos se estamos enviado via template ou notificação direta.
		if ( $notif_id ) // Via notificação direta.
		{
			if ( !$this->notificacao_base
			||   $this->notificacao_base->id != $notif_id
			   )
			{
				$this->notificacao_base					=	$this->notificacao->get_one_by_id( $notif_id );
		
				// Pegamos a lista de pessoas que já foram notificadas.
				$this->notif_pessoas					=	$this->notificacao->get_pessoa_list( $this->notificacao_base->id );
	
				$this->template_have_send				=	TRUE; // Neste tipo de envio SEMPRE "temos que enviar".
			}
		}
		else
		{
			// Não temos um template lido ou o lido é diferente do template enviado, então buscamos as informações do template.
			// 		Em processamento de múltiplas pessoas, usaremos a informação obtida pela pessoa anterior para agilizar o processo das demais.
			if ( !$this->template_base
			||   $this->template_base->id != $template_id
			   )
			{
				$this->template_have_send			=	$this->notificacao_template->have_send( $template_id );
				// Retorna o template base selecionado em have_send.
				$this->template_base				=	$this->notificacao_template->get_template_base( $template_id );
	
				// Retorna a notifiação quer usada.
				$this->notificacao_base				=	$this->notificacao_template->get_new_notificacao( $template_id );
	
				// Pegamos a lista de pessoas que já foram notificadas.
				$this->notif_pessoas				=	$this->notificacao->get_pessoa_list( $this->notificacao_base->id );
			}
		}
		$this->date_ref							=	new DateTime( 'now' );
		$this->date_ref->sub( new DateInterval( 'P1M' ) );
		
		if ( $this->template_have_send )
		{
			// Criamos/recuperamos a notificação que será enviada.

			if ( $this->notificacao_base )
			{
				$this->notificacao_template->set_to_sent(); // Marca o template como enviado logo no início.
				
				// Recuperamos a pessoa.
				$pessoa_base					=	$this->pessoa->get_one_by_id( $pessoa_id );

				// Prepara as datas para teste de atividades no Kikbook.
				if ( !$pessoa_base->data_hora_ultima_atualizacao )
				{
					if ( !$pessoa_base->data_hora_inscricao ) // Sem atualização olhamos para a inscrição.
					{
						$data_hora_ultima_atualizacao		=	DateTime::createFromFormat( 'Y-m-d H:i:s', $pessoa_base->data_hora_inscricao );
					}
					else // Sem os dois, colocamos a 2 meses.
					{
						$data_hora_ultima_atualizacao		=	new DateTime( 'now' );
						$data_hora_ultima_atualizacao->sub( new DateInterval( 'P2M' ) ); // Se nunca atualizou é sinal que nunca voltou ao kik. Não avisamos.
					}
				}
				else
				{
					$data_hora_ultima_atualizacao		=	DateTime::createFromFormat( 'Y-m-d H:i:s', $pessoa_base->data_hora_ultima_atualizacao );
				}
				if ( !$pessoa_base->data_hora_ultimo_chute )
				{
					$data_hora_ultimo_chute			=	$data_hora_ultima_atualizacao; // Sem chute, usamos a data acima.
				}
				else
				{
					$data_hora_ultimo_chute			=	DateTime::createFromFormat( 'Y-m-d H:i:s', $pessoa_base->data_hora_ultimo_chute );
					$data_hora_ultima_atualizacao		=	$data_hora_ultimo_chute; // Vale mais o chute que a atualizacao.
				}
				
				if ( key_exists( $pessoa_base->id, $this->notif_pessoas ) )
				{
					if ( $show_log ) echo "......Já notificaca.\n";
					$notif_pessoa_base			=	$this->notificacao_pessoa->get_one_by_where	( "	notificacao_pessoa.notificacao_id = {$this->notificacao_base->id}
																	and	notificacao_pessoa.pessoa_id = {$pessoa_base->id}"
																	);

					if ( $notif_pessoa_base
					&&   $notif_pessoa_base->acao == 'L' // Já lida.
					   )
					{
						// Marca como não lido.
						$notif_pessoa_base->acao	=	'N';
						$this->notificacao_pessoa->update( $notif_pessoa_base );
					}
				}
				/* só avisa pessoas ativas */
				elseif ( $pessoa_base->ativa != 'S' )
				{
					if ( $show_log ) echo "......Pessoa não está ativa {$pessoa_base->ativa}.\n";
				}
				/* só avisa pessoas que estão jogando ou jogaram nos últimos 30 dias */
				elseif ( /*$data_hora_ultima_atualizacao	<=	$this->date_ref )
				||       */$data_hora_ultimo_chute		<=	$this->date_ref
				       )
				{
					if ( $show_log ) echo "......Pessoa a muito tempo sem jogar.\n";
				}
				else
				{
					$ar_values_notif			=	array_merge	(
													 $ar_values
													, array	(
												 		 'subject'	=>	$this->notificacao_base->nome
													 	)
													);
					// FACEBOOK
					$this->send_notif( 'F', $pessoa_base, $ar_values_notif, $show_log, $notif_type );

					// E-MAIL
					$this->send_notif( 'E', $pessoa_base, $ar_values_notif, $show_log, $notif_type );

					// PAGINA
					$this->send_notif( 'P', $pessoa_base, $ar_values_notif, $show_log, $notif_type );
				}

				// Atualiza a notificação, em cada envio.
				$this->notificacao->update( $this->notificacao_base );
			}
			else
			{
				if ( $show_log ) echo "......Não foi possível criar uma nova notificação para ser enviada.\n";
			}
		}
		else
		{
			if ( $show_log ) echo "......Template já enviado dentro do período configurado. NADA A ENVIAR.\n";
		}
		
		return TRUE;
	}
}
/* End of file notificacao_model.php */
