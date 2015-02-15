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
 * @filesource		/application/models/notificacao_template_model.php
 * 
 * $Id: notificacao_template_model.php,v 1.4 2013-03-02 13:51:51 junior Exp $
 * 
 */

class Notificacao_template_model extends JX_Model
{
	protected $_revision		=	'$Id: notificacao_template_model.php,v 1.4 2013-03-02 13:51:51 junior Exp $';

	var $new_notificacao		=	NULL;
	var $template_base		=	NULL;
	
	function __construct()
	{
		$_config		=	array	(
							 'notificacao'					=>	array	(
															 'model_name'	=>	'notificacao'
													 		)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 notificacao_template.*
			,DATE_FORMAT(notificacao_template.ultimo_envio, '%Y-%m-%d')	AS	ultimo_envio_trunc
			,notificacao_template.nome					AS	title
			,notificacao_template.ultimo_envio				AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'notificacao_template' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
	
	/**
	 * Verifica se o template foi enviado ou não dentro do período configurado.
	 */
	public function have_send( $template_id )
	{
		$ret							=	FALSE;

		$this->template_base					=	$this->notificacao_template->get_one_by_id( $template_id );
		
		if ( $this->template_base
		&&   is_object( $this->template_base )
		   )
		{
			$date_now					=	new DateTime( 'now' );
			$last_sent					=	new DateTime( $this->template_base->ultimo_envio_trunc ); // Não analisaremos a hora para definir a próxima execução.
			$date_diff					=	$last_sent->diff( $date_now );
			$diff_envio_dia					=	$date_diff->format('%a');
			$diff_envio_mes					=	$date_diff->format('%y');

//echo "now=". $date_now->format( 'Y-m-d' ) . " end=" . $last_sent->format( 'Y-m-d' ) . " diff_dia=". $diff_envio_dia . " diff_mes=". $diff_envio_mes . "\n";
			
			if ( !$this->template_base->ultimo_envio // Nunca foi enviado.
			||   ( $this->template_base->repeticao == 'D' /*Diário*/
			&&     $diff_envio_dia >= 1 // dia
			     )
			||   ( $this->template_base->repeticao == 'S' /*Semanal*/
			&&     $diff_envio_dia >= 7 // dias
			     )
			||   ( $this->template_base->repeticao == 'M' /*Mensal*/
			&&     $diff_envio_mes >= 1 // mês
			     )
			||   ( $this->template_base->repeticao == 'I' /*Intervalo de dias*/
			&&     $diff_envio_dia >= $this->template_base->qtde_dias_repeticao
			     )
			||   ( $this->template_base->repeticao == 'E' /*Eventual / Manual, sempre manda */
			     )
			   )
			{
				// Analisamos se a última notificação gerada, a que tem a mesma data que a data do último envio, está sem nenhum envio.
				//	Se estiver, mantemos esta notificação como sendo a notificação que será utilizada pelo processo que está solicitando.
				$this->new_notificacao			=	$this->notificacao->get_last_by_template( $this->template_base->id, $this->template_base );
				$ret					=	TRUE;
			}
			else
			{
				$ret					=	FALSE;
			}
		}
		else
		{
			$ret						=	FALSE;
		}
//echo 'enviar 222.\n';
		
		return $ret;
	}
	
	public function set_to_sent()
	{
		if ( $this->template_base )
		{
			$this->template_base->ultimo_envio		=	'CURRENT_TIMESTAMP';
			$this->update( $this->template_base );
		}
	}
	
	public function get_template_base( $template_id = NULL )
	{
		if ( $template_id )
		{
			$this->template_base				=	$this->notificacao_template->get_one_by_id( $template_id );
		}
		return $this->template_base;
	}
	
	/**
	 * Dado um template verifica se há alguma notificação criada dentro do período configurado não tendo já cria uma nova.
	 * Sempre retorna uma notificaçao.
	 */
	public function get_new_notificacao( $template_id )
	{
		// Criamos uma nova notificação se já não criamos uma ou se a criada não é do mesmo tipo solicitado.
		if ( !$this->new_notificacao
		||   ( $this->new_notificacao 
		&&     $this->new_notificacao->notificacao_template_id != $template_id
		     )
		   )
		{
			$notif_template_base				=	$this->notificacao_template->get_one_by_id( $template_id );
			
			$notif_base					=	$this->notificacao->get_last_by_template( $template_id, $notif_template_base );
			if ( !$notif_base ) // Nova notificação.
			{
				$n					=	new stdClass();
				$n->id					=	NULL;
				$n->notificacao_template_id		=	$template_id;
				$n->cod					=	$notif_template_base->cod;
				$n->nome				=	$notif_template_base->nome;
				$n->descr				=	$notif_template_base->descr;
	
				$date_now				=	new DateTime( 'now' );

				$n->data_inicio				=	'CURRENT_TIMESTAMP';
				if ( $notif_template_base->qtde_dia_fim == 0
				&&   $notif_template_base->repeticao == 'E' // Eventual, sempre registramos como 0.
				   )
				{
					$n->data_fim			=	$n->data_inicio; // Finaliza junto com o início.
				}
				else
				{
					$n->data_fim			=	$date_now;
					$n->data_fim->add( new DateInterval( 'P' .$notif_template_base->qtde_dia_fim. 'D' ) )->format( 'Y-m-d H:i:s' );
					$n->data_fim			=	$n->data_fim->format( 'Y-m-d H:i:s' );
				}

				$n->texto_facebook			=	$notif_template_base->texto_facebook;
				$n->texto_email				=	$notif_template_base->texto_email;
				$n->texto_pagina			=	$notif_template_base->texto_pagina;
				$n->pagina_redirect			=	$notif_template_base->pagina_redirect;
				$n->via_facebook			=	$notif_template_base->via_facebook;
				$n->via_email				=	$notif_template_base->via_email;
				$n->via_pagina				=	$notif_template_base->via_pagina;
				$n->prioridade				=	$notif_template_base->prioridade;
				// Inicializa contagem.
				$n->qtde_pes_facebook_enviada		=	0;
				$n->qtde_pes_facebook_feedbak		=	0;
				$n->qtde_pes_email_enviada		=	0;
				$n->qtde_pes_email_feedback		=	0;
				$n->qtde_pes_pagina_enviada		=	0;
				$n->qtde_pes_pagina_feedback		=	0;
				
				$n->id					=	$this->notificacao->update( $n );
				
				$this->new_notificacao			=	$n;
			}
			else
			{
				// Existindo na base, alteramos as informações para que o template seja mais forte.
				$notif_base->cod			=	$notif_template_base->cod;
				$notif_base->nome			=	$notif_template_base->nome;
				$notif_base->descr			=	$notif_template_base->descr;
	
				$date_now				=	new DateTime( 'now' );
				if ( $notif_template_base->qtde_dia_fim == 0
				&&   $notif_template_base->repeticao == 'E' // Eventual, sempre registramos como 0.
				   )
				{
					$notif_base->data_fim		=	'CURRENT_TIMESTAMP'; // Finaliza junto com o início.
				}
				else
				{
					$notif_base->data_fim		=	$date_now;
					$notif_base->data_fim->add( new DateInterval( 'P' .$notif_template_base->qtde_dia_fim. 'D' ) )->format( 'Y-m-d H:i:s' );
				}
				
				$notif_base->texto_facebook		=	$notif_template_base->texto_facebook;
				$notif_base->texto_email		=	$notif_template_base->texto_email;
				$notif_base->texto_pagina		=	$notif_template_base->texto_pagina;
				$notif_base->pagina_redirect		=	$notif_template_base->pagina_redirect;
				$notif_base->via_facebook		=	$notif_template_base->via_facebook;
				$notif_base->via_email			=	$notif_template_base->via_email;
				$notif_base->via_pagina			=	$notif_template_base->via_pagina;
				$notif_base->prioridade			=	$notif_template_base->prioridade;

				$notif_base->id				=	$this->notificacao->update( $notif_base );
				
				$this->new_notificacao			=	$notif_base;
			}
		}

		return $this->new_notificacao;
	}
}
/* End of file kick_model.php */
