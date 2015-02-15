<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Jarvix Plus
 *
 *	Controller principal do sistema. Porta de acesso.
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Jarvix Plus.
 * @license		http://jarvixplus.com/license.html
 * @link		http://jarvixplus.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/jx/home.php
 * 
 * $Id: home.php,v 1.6 2013-04-14 12:44:43 junior Exp $
 * 
 */

class Home extends JX_Page
{
	protected $_revision	=	'$Id: home.php,v 1.6 2013-04-14 12:44:43 junior Exp $';
	
	var $force_redirect	=	NULL;
	var $try_connnect_count	=	0;
	var $user_status	=	NULL;
	
	function __construct()
	{
		parent::__construct();

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	/**
	 * Página princial do site.
	 */
	public function index()
	{
		if ( key_exists( "force_url", $_REQUEST ) )
		{
			$force_redirect		=	$_REQUEST[ "force_url" ];
		}
		else
		{
			$force_redirect		=	NULL;
		}
		
		if ( $this->config->item( 'first_controller_connected' )
		&&   $this->singlepack->user_connected()
		   )
		{
			if ( $force_redirect )
			{
				redirect( $force_redirect );
			}
			else
			{
				redirect( $this->config->item( 'first_controller_connected' ) );
			}
		}
		else if ( $this->config->item( 'first_controller' ) )
		{
			redirect( $this->config->item( 'first_controller' ) );
		}
		else
		{
			$this->load->view( $this->config->item( 'start_page' ) );
		}
	}
	
	/**
	 * Controle de abertura e fechamento de sessão de usuário.
	 */
	public function login()
	{
		if ( key_exists( "force_url", $_REQUEST ) )
		{
			$force_redirect		=	$_REQUEST[ "force_url" ];
		}
		else
		{
			$force_redirect		=	NULL;
		}
		
		$data	=	array	(
					'userid'	=>	''
					);
		$this->load->vars( $data );
		if ( $this->singlepack->user_connected()
		&&   $this->config->item( 'first_controller_connected' )
		   )
		{
			if ( $force_redirect )
			{
				redirect( $force_redirect );
			}
			else
			{
				redirect( $this->config->item( 'first_controller_connected' ) );
			}
		}
		else
		{
			// Se estamos usando o login do facebook, nunca exibimos a página de login e sim levamos a pessoa à página inicial do sistema.
			if ( $this->config->item( 'facebook_login' ) )
			{
				if ( $this->config->item( 'first_controller' ) )
				{
					redirect( $this->config->item( 'first_controller' ) );
				}
				else
				{
					redirect( "/" );
				}
			}
			else
			{
//				if ( $this->input->post( 'login' ) &&
//				     $this->input->post( 'password' )
//				   )
				{
					$data	=	array	(
								'userid'	=>	$this->input->post_multi( 'login' )
								);
					$this->load->vars( $data );
					$this->load->library('form_validation');
					$this->form_validation->set_rules( 'login',    'Nome de Usuário', 'trim|valid_email|required');
					$this->form_validation->set_rules( 'password', 'Senha',           'trim|required|min_length[4]');
					if ( $this->form_validation->run() !== false )
					{
						if ( $this->singlepack->user_valid( $this->input->post_multi( 'login' ), $this->input->post_multi( 'password' ) ) )
						{
							redirect( $this->session->userdata( 'jx_continue' ) );
						}
						else
						{
							$data	=	array	(
										'message_error'		=>	'Usuário ou senha inválido.'
										);
							$this->load->vars( $data );
						}
					}
					$this->load->view( $this->config->item( 'start_page' ) );
				}
			}
		}
	}

	public function logout()
	{
		if ( $this->singlepack->close_session() )
		{
			if ( $this->config->item( 'facebook_login' ) == TRUE )
			{
				if ( $this->singlepack->inside_facebook() )
				{
					print( '<html>
							<body>
								Bye!
								<script>top.location.href="' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . '://www.facebook.com/";</script>
							</body>
						</html>
						' );
				}
				else
				{
					$this->load->view( 'logout_facebook.html' );
				}
			}
			else
			{
				redirect( "/" );
			}
		}
		else
		{
			redirect( "/" );
		}
	}

	public function cancel()
	{
		$this->load->view( 'home_cancel.html' );
	}

	// Para o facebook
	public function channel()
	{
		$this->load->view( 'home_channel.html' );
	}

	// Para o facebook acessar quando estiver em CANVAS.
	public function canvas()
	{
		$this->singlepack->set_inside_facebook();
		
		// Se temos um force é sinal que estamos retornando para o canvas
		$this->force_redirect			=	$this->singlepack->get_sessao( 'force_redirect' );
		if ( $this->force_redirect )
		{
			$this->singlepack->unset_sessao( 'force_redirect' );
			$this->force_redirect		=	NULL;
			redirect( $this->force_redirect );
		}

		/*
		 * Controla o retorno de um convite para jogar.
		 */
log_message( 'debug', "notif_facebook.ini(1)" );
		$fb_source		=	NULL;
		$request_id		=	array();
		$app_request_type	=	NULL;
		$notif_t		=	NULL;
		$fb_ref			=	NULL;

		if ( isset( $_REQUEST )
		&&   key_exists( 'fb_source', $_REQUEST )
		   )
		{
			if ( key_exists( 'fb_source', $_REQUEST ) )
			{
				$fb_source			=	$_REQUEST[ 'fb_source' ]; // notification
			}
			
			if ( $fb_source == 'notification' ) // Estamos recebendo o retorno de uma notificação.
			{
				if ( key_exists( 'request_ids', $_REQUEST ) )
				{
					$request_id		=	explode( ',', $_REQUEST[ 'request_ids' ] );
				}
				if ( key_exists( 'app_request_type', $_REQUEST ) )
				{
					$app_request_type	=	$_REQUEST[ 'app_request_type' ]; // NULL OR user_to_user
				}
				if ( key_exists( 'notif_t', $_REQUEST ) )
				{
					$notif_t		=	$_REQUEST[ 'notif_t' ]; // app_notification OR app_invite
				}
				if ( key_exists( 'fb_ref', $_REQUEST ) )
				{
					$fb_ref			=	$_REQUEST[ 'fb_ref' ]; // falta_chutes OR novidade OR kik_vencer
				}
			}
log_message( 'debug', "notif_facebook.registrou_sessao(2)" );
			
			$this->singlepack->set_sessao( 'facenotif_fb_source', $fb_source );
			$this->singlepack->set_sessao( 'facenotif_request_ids', json_encode( $request_id ) );
			$this->singlepack->set_sessao( 'facenotif_app_request_type', $app_request_type );
			$this->singlepack->set_sessao( 'facenotif_notif_t', $notif_t );
			$this->singlepack->set_sessao( 'facenotif_fb_ref', $fb_ref );
		}
		elseif ( $this->singlepack->get_sessao( 'facenotif_fb_source' ) )
		{
log_message( 'debug', "notif_facebook.recuperou_sessao(3)" );
			$fb_source				=	$this->singlepack->get_sessao( 'facenotif_fb_source' );
			$request_id				=	json_decode( $this->singlepack->get_sessao( 'facenotif_request_ids' ) );
			$app_request_type			=	$this->singlepack->get_sessao( 'facenotif_app_request_type' );
			$notif_t				=	$this->singlepack->get_sessao( 'facenotif_notif_t' );
			$fb_ref					=	$this->singlepack->get_sessao( 'facenotif_fb_ref' );
		}
		
		if ( $fb_source // Estamos recebendo uma notificação.
		&&   $this->singlepack->get_pessoa_id() // E estamos conectados.
		   )
		{
log_message( 'debug', "notif_facebook.tem dados e esta conectado(4)" );
			$this->singlepack->load_lang_model_files( 'pessoa_convite', 'pessoa_convite', 'pessoa_convite' );
			$this->singlepack->load_lang_model_files( 'pessoa_relacionamento', 'pessoa_relacionamento', 'pessoa_relacionamento' );
			$facebook_id				=	$this->singlepack->get_facebook_id();
			$pessoa_id				=	$this->singlepack->get_pessoa_id();
			
			foreach( $request_id as $req_id )
			{
				$convite_base			=	$this->pessoa_convite->get_one_by_where( "	pessoa_convite.facebook_id_apprequest = $req_id
														and	pessoa_convite.facebook_id_convidado = $facebook_id
														" );
				
				if ( $convite_base )
				{
					$convite_base->data_hora_retorno	=	'CURRENT_TIMESTAMP';
					$this->pessoa_convite->update( $convite_base );
					
					$relac_base		=	$this->pessoa_relacionamento->get_one_by_where( "	pessoa_relacionamento.pessoa_id_eu = {$convite_base->pessoa_id}
															and	pessoa_relacionamento.pessoa_id_amigo = $pessoa_id
															and	pessoa_relacionamento.tipo_relacionamento_id = 1 /*amigo*/
															" );
					if ( $relac_base )
					{
						$relac_base->indiquei	=	'S';
					}
					else
					{
						$relac_base				=	new stdClass();
						$relac_base->pessoa_id_eu		=	(int) $convite_base->pessoa_id; // pessoa que fez o convite.
						$relac_base->tipo_relacionamento_id	=	1; // amigo
						$relac_base->pessoa_id_amigo		=	(int) $pessoa_id; // pessoa atual.
						$relac_base->indiquei			=	'S';
						$relac_base->data_inicio		=	'CURRENT_TIMESTAMP';
						$relac_base->data_fim			=	NULL;
					}
					
					$this->pessoa_relacionamento->update( $relac_base );
				}
				
log_message( 'debug', "notif_facebook.eliminando a solicitacao(4).$req_id" );
				$this->singlepack->delete_request_app( $req_id );
			}
			
			// Retira a informação da sessão para que não tratemos a notificação mais de uma vez.
			$this->singlepack->unset_sessao( 'facenotif_fb_source' );
			$this->singlepack->unset_sessao( 'facenotif_request_ids' );
			$this->singlepack->unset_sessao( 'facenotif_app_request_type' );
			$this->singlepack->unset_sessao( 'facenotif_notif_t' );
			$this->singlepack->unset_sessao( 'facenotif_fb_ref' );
		}
		else
		{
log_message( 'debug', "notif_facebook.nao conectado ou sem dados()" );				
		}
		
		$this->index();
	}
	
	/**
	 * AS DUAS FUNCTIONS ABAIXO TRABALHAM SINCRONIZADAS COM O SINGLEPACK.
	 */
	protected function get_URI_facebook()
	{
		// Obtém o redirect da sessão.
		// Este redirect pode ser usado por qualquer controller para forçar um controller inicial sem ser o que está em config.php.
		$this->force_redirect			=	$this->singlepack->get_sessao( 'force_redirect' );
		if ( !$this->force_redirect
		&&   key_exists( "force_url", $_REQUEST )
		   )
		{ // Não tendo na sessão busca no request.
			$this->force_redirect		=	$_REQUEST[ "force_url" ];
		}
		else
		{
			$this->force_redirect		=	NULL;
		}
		if ( !$this->force_redirect
		&&   key_exists( "force_url", $_SESSION )
		   )
		{ // Não tendo na sessão busca no request.
			$this->force_redirect		=	$_SESSION[ "force_url" ];
		}

		if ( key_exists( "tcc", $_SESSION ) )
		{
			$this->try_connnect_count	=	$_SESSION[ "tcc" ];
			$this->try_connnect_count	+=	1;
		}
		else
		{
			$this->try_connnect_count	=	0;
		}
		$_SESSION['tcc']			=	$this->try_connnect_count;
		
		$url_OK					=	$this->config->item( 'base_url' ) . 'facebook_login';
		
		if ( $this->force_redirect )
		{
//			$url_OK				=	$url_OK . '?force_url=' . $this->force_redirect;
			$_SESSION['force_url']		=	$this->force_redirect;
		}
		
		return $url_OK;
	}

	/*
	 * Realiza o login no facebook.
	 */
	public function facebook_login()
	{
//log_message( 'debug', "facebook_login.ini(" . session_id() . ")<br>" );
		if ( key_exists( "code", $_REQUEST ) )
		{
			$code_request			=	$_REQUEST[ "code" ];
		}
		else
		{
			$code_request			=	NULL;
		}
//log_message( 'debug', "facebook_login.ini code($code_request)<br>" );
		
		$this->user_status			=	NULL;
		if ( key_exists( "ust", $_REQUEST ) )
		{
			$this->user_status		=	$_REQUEST[ "ust" ];
		}
		if ( $this->user_status == 'diff' ) // Trocou de usuário, temos que inicializar tudo.
		{
			$this->singlepack->facebook_new_user();
		}
//log_message( 'debug', "facebook_login.ini ust($this->user_status)<br>" );

		if( empty( $code_request ) )
		{
//log_message( 'debug', "facebook_login.2 sem código, vamos ao facebook.<br>" );
//			$this->singlepack->close_session();

			$_SESSION['state']		=	md5( uniqid( rand(), TRUE ) ); // CSRF protection
			$dialog_url			=	"https://www.facebook.com/dialog/oauth"
									. "?client_id="		. $this->config->item( 'facebook_appid' )
									. "&redirect_uri="	. urlencode( $this->get_URI_facebook() )
//									. "&client_secret="	. $this->config->item( 'facebook_appsecret' )
									. "&state="		. $_SESSION['state']
									. "&scope=email,user_birthday,user_groups,friends_groups,publish_actions"; // Solicita permissão.
			//echo("<script>document.location.href='" . $dialog_url . "'</script>");
//log_message( 'debug', "facebook_login.redirect=" . $dialog_url . "<br>");
			redirect( $dialog_url );
		}
		else
		{
//log_message( 'debug', "facebook_login.3 temos o código" );
			$access_token						=	NULL;
			$state_request						=	NULL;
			$state_session						=	NULL;
			$code_request						=	NULL;
	
			if ( key_exists( "code", $_REQUEST ) )
			{
				$code_request				=	$_REQUEST[ "code" ];
			}
			if ( key_exists( "state", $_REQUEST ) )
			{
				$state_request				=	$_REQUEST[ "state" ];
			}
			if ( key_exists( "state", $_SESSION ) )
			{
				$state_session				=	$_SESSION[ "state" ];
			}
	
			$access_token					=	$this->singlepack->get_facebook_access_token();
			if ( empty( $access_token )
			&&   key_exists( "access_token", $_REQUEST )
			   )
			{
				$access_token				=	$_REQUEST[ "access_token" ];
			}
	
			$url_redirect_face				=	$this->get_URI_facebook();
//log_message( 'debug', "facebook_login url($url_redirect_face)" );
			
			// se o REQUEST contiver state e code, indica que estamos em processo de login.
			// Se já temos um access_token, pula esta parte também.
			if ( !empty( $code_request )
			&&   !empty( $state_request )
			&&   empty( $access_token )
			   )
			{
//log_message( 'debug', "facebook_login.4." );
				if ( !empty( $state_session ) && ( $state_session !== $state_request ) )
				{
//log_message( 'debug', "facebook_login.4.1 The state does not match. You may be a victim of CSRF." );
					$this->close_session();
					show_error( "The state does not match. You may be a victim of CSRF." );
				}
				else
				{
//log_message( 'debug', "facebook_login.4.2" );
					$token_url		=	"https://graph.facebook.com/oauth/access_token?"
													. "client_id="		. $this->config->item( 'facebook_appid' ) 
													. "&redirect_uri="	. urlencode( $url_redirect_face )
													. "&client_secret="	. $this->config->item( 'facebook_appsecret' )
													. "&code="		. $code_request;
					$response		=	file_get_contents( $token_url );
					parse_str( $response, $params );
	
					if ( isset( $params )
					&&   key_exists( 'access_token', $params )
					   )
					{
						$access_token	=	$params[ 'access_token' ];
					}
					else
					{
						$access_token	=	NULL;
					}
				
					if ( $access_token )
					{
						$this->singlepack->set_facebook_access_token( $access_token );
					}
				}
			}
	
			if ( $access_token )
			{
//log_message( 'debug', "facebook_login.5." );
				if ( $this->singlepack->inside_facebook() )
				{
//log_message( 'debug', "facebook_login.5.1" );
					redirect( ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . '://apps.facebook.com/' . $this->config->item( 'facebook_namespace' ) );
				}
				else
				{
//log_message( 'debug', "facebook_login.5.2" );
					if ( $this->force_redirect )
					{
						$this->singlepack->unset_sessao( 'force_redirect' );
						redirect( $this->force_redirect );
					}
					else
					{
						redirect( $this->config->item( 'first_controller_connected' ) );
					}
				}
			}
			else
			{
//log_message( 'debug', "facebook_login.6." );
				// Não está logado ainda, então tenta novamente.
				$facebook_login_fail	=	$this->get_sessao( 'facebook_login_fail' );
				$facebook_login_fail	=	$facebook_login_fail + 1;
				$this->set_sessao( 'facebook_login_fail', json_encode( $this->facebook_login_fail ) );
				if ( $facebook_login_fail <= 2 )
				{
					redirect( "/facebook_login" );
				}
				elseif ( $this->force_redirect )
				{
					redirect( $this->config->item( 'first_controller' ) . '?force_url=' . $this->force_redirect . '&getl=' );
				}
				else
				{
					redirect( $this->config->item( 'first_controller' ) );
				}
			}
		}
	}
	
	/**
	 * Executa o login para os robos do google
	 */
	public function robot()
	{
		$check_login			=	$this->input->get_multi( 'p' );
		$redirect			=	$this->input->get_multi( 'to' );

		if ( $check_login == $this->config->item( 'robot_login' ) )
		{
			$this->config->set_item( 'facebook_login', FALSE );
			$this->singlepack->set_sessao( 'robot_mode', 'ON' );
	
			if ( $this->singlepack->user_valid( $this->config->item( 'robot_user' ), $this->config->item( 'robot_pass' ) ) )
			{
				redirect( $redirect );
			}
			else
			{
				redirect( $this->config->item( 'first_controller' ) );
			}
		}
		else
		{
			redirect( $this->config->item( 'first_controller' ) );
		}
	}
}

/* End of file home.php */
/* Location: /application/controllers/home.php */
