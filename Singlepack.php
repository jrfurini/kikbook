<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require '../application/libraries/facebook-php-sdk/facebook.php';

/**
 * Jarvix Plus
 *
 *	Single Pack
 *		- Controle de Acesso
 *		- Controle de Menus
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Jarvix Plus.
 * @license		http://jarvixplus.com/license.html
 * @link		http://jarvixplus.com
 * @since		Version 0.0.1
 * @filesource		/application/libraries/Singlepack.php
 * 
 * $Id: Singlepack.php,v 1.22 2013-01-28 23:59:22 junior Exp $
 * 
 */

class Singlepack
{
	protected $CI;
	/**
	 * Variáveis gerais de controle de visualização.
	 */
	// Parametros.
	var $prg_controller		=	'home';
	var $prg_controller_method	=	'index';
	protected $email_initialized	=	FALSE;

	var $last_url			=	'';
	// Preferências.
	var $theme			=	'dflt';
	var $idioma			=	'pt_BR';
	
	/**
	 * Controle de Acesso.
	 */
	var $sistema_id_atual		=	1;
	var $sistema_atual;
	var $controller_id_atual	=	1;
	var $controller_atual;
	var $method_id_atual		=	1;
	var $method_atual;
	
	/**
	 * Variáveis de controle de acesso.
	 */
	var $user_id;
	var $user_info;
	var $user_cfg;
	var $methods_access;
	var $system_granted;
	var $facebook; // Instancia da classe Facebook. Veja user_connected();
	var $facebook_id;
	var $facebook_user_profile;
	var $facebook_friends;
	var $facebook_groups;
	var $facebook_friends_installed;
	var $facebook_login_url		=	NULL;
	var $facebook_logout_url	=	NULL;
	var $facebook_login_fail	=	'NOT_CHECK';
	var $facebook_access_token	=	NULL;

	/**
	 * Variáveis internas.
	 */
	protected $year;
	protected $month;
	protected $day;
	protected $hour;
	protected $min;
	protected $sec;
	
	protected $count_fields		=	0; // Esta variável é usada no JX_Field
		
	protected $initalized		=	FALSE;
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	public function __construct( $params = array() )
	{
		log_message('debug', "Singlepack(star).");
		$this->CI =& get_instance();

		$this->set_theme( $this->CI->config->item( 'default_theme' ) );
		log_message('debug', "Singlepack loaded.");
	}

	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	public function initialize( $config = array() )
	{
		if ( !$this->initalized )
		{
			log_message('debug', 'Singlepack.initialize (start).' );
			
			if ( session_id() == "" )
			{
				session_start(); // Ativa controle de sessão do CI.
			}
	
			/**
			 * Carrega os models necessários para o Single Pack.
			 */
			// Menus
			$this->load_model_file( 'sistema',           'sistema' );
			$this->load_model_file( 'sistema_ctrl',      'sistema_ctrl' );
			$this->load_model_file( 'sistema_ctrl_meth', 'sistema_ctrl_meth' );
			// Controle de Acesso
			$this->load_model_file( 'user',              'user' );
			$this->load_model_file( 'user_nav',          'user_nav' );
	
			// Carrega as variáveis com os valores passados como parâmetros.
			if ( count($config) > 0 )
			{
				foreach ($config as $key => $val)
				{
					log_message( 'debug', "Singlepack params: {$key}." );
					if ( isset( $this->$key ) )
					{
						$this->$key = $val;
						log_message( 'debug', "....value: {$val} ." );
					}
				}
			}
			/**
			 * Verifica se há usuário conectado ou não.
			 */
			$this->try_connect();
	
			if ( !$this->user_connected() )
			{
				log_message('debug', "NÃO CONECTADO." );
				if ( get_parent_class( $this->CI->router->class ) == 'JX_Process'
				&&   $this->CI->router->method == 'batch' // Paro processos em background quebramos o processo aqui.
				   )
				{
				   	exit;
				}
			}
			else
			{
				log_message('debug', "CONECTADO." );
				$this->CI->lang->reload_all();
			}
			/**
			 * Registra, a partir do controller, qual é o sistema que está sendo ativado.
			 */
			$this->set_sistema();
	
			/**
			 * Obtém os dados para controle dos menus.
			 */
			$where_sis			=	NULL;
			$sis_used			=	array();
			foreach( $this->system_granted as $row )
			{
				if ( !key_exists( $row->sistema_id, $sis_used )
				&&   $row->system_controller != 'autocomplete'
				   )
				{
				   	if ( $row->sistema_id )
				   	{
					   	$sis_used[ $row->sistema_id ]	=	$row->sistema_id;
						if ( !$where_sis )
						{
							$where_sis		=	$where_sis.'sistema.id in ( '.$row->sistema_id;
						}
						else
						{
							$where_sis		=	$where_sis.', '.$row->sistema_id;
						}
				   	}
				}
			}
			if ( $where_sis )
			{
				$where_sis		=	$where_sis.' ) and ( sistema.show_menu = "S" or sistema.show_menu_footer = "S" )';
			}
			else // Não tem acesso a nada.
			{
				$where_sis		=	'( 1 = 2 ) and ( sistema.show_menu = "S" or sistema.show_menu_footer = "S" )'; // não mostra nenhum sistema no topmenu.
			}
			$this->CI->sistema->select_all( $where_sis, 'seq_exibicao ASC' );
			$sistemas			=	$this->CI->sistema->get_query_rows();
			
			// Acrescenta os menus (controllers) aos sistemas.
			$new_sistemas			=	array();	
			foreach( $sistemas as $sistema )
			{
				$sistema->menus			=	$this->get_menus( $sistema->id );
				$sistema->menus_count_show	=	0;
				foreach( $sistema->menus as $menu )
				{
					if ( ( $menu->ctrl_show_menu_footer == 'S'
					||       $menu->meth_show_menu_footer == 'S'
					     )
					&&   ( $menu->method != 'edit'
					&&     $this->has_access_prg( $menu->prg_controller . '.' . $menu->prg_controller_method, 2 )
					     )
					   )
      					{
					   	$sistema->menus_count_show	+=	1;
					}
				}
				$new_sistemas[ $sistema->id ]		=	$sistema;
			}
			$sistemas					=	$new_sistemas;

	/*		if ( $this->CI->config->item( 'facebook_login' ) == TRUE )
			{
				$this->facebook_login_url	=	$this->facebook->getLoginUrl();
				$this->facebook_logout_url	=	str_replace( '%2Flogout', '', $this->facebook->getLogoutUrl() );
			}
			else
	*/		{
				$this->facebook_login_url	=	NULL;
				$this->facebook_logout_url	=	NULL;
			}
	
			/**
			 * Carrega as variáveis do Single Pack para a área do view.
			 */
			$data				=	array	(
									 'sistemas'		=>	$sistemas
									,'sistema_id_atual'	=>	$this->sistema_id_atual
									,'sistema_atual'	=>	$this->sistema_atual
									,'controller_id_atual'	=>	$this->controller_id_atual
									,'controller_atual'	=>	$this->controller_atual
									,'method_id_atual'	=>	$this->method_id_atual
									,'method_atual'		=>	$this->method_atual
									,'facebook_app_id'	=>	$this->CI->config->item( 'facebook_appid' )
									,'facebook_app_secret'	=>	$this->CI->config->item( 'facebook_appsecret' )
									,'facebook_login'	=>	( $this->CI->config->item( 'facebook_login' ) ) ? TRUE : FALSE
									,'facebook_id'		=>	$this->facebook_id
									,'facebook_login_url'	=>	$this->facebook_login_url
									,'facebook_logout_url'	=>	$this->facebook_logout_url
									);
			$this->CI->load->vars( $data );
			/**
			 * Altera o idioma padrão do sistema para o idioma do usuário atual.
			 */
			if ( isset( $this->user_cfg->idioma ) )
			{
				$this->CI->config->set_item( 'language', $this->user_cfg->idioma );
			}
			
			/**
			 * Controle retorno de página.
			 * 	Usado em páginas de edição que são chamadas de diversos pontos do sistema.
			 */
			$this->set_history_url();
			
			/**
			 * Carrega as variáveis de usuário do Single Pack para a área do view.
			 */
			$data				=	array	(
									 'theme'		=>	$this->theme
									,'user_info'		=>	$this->user_info
									,'user_cfg'		=>	$this->user_cfg
									,'back_url'		=>	$this->last_url
									);
			$this->CI->load->vars( $data );
	
			log_message( 'debug', "Single Pack initialized (end)." );
			$this->initalized		=	TRUE;
		}
		return TRUE;
	}

	/**
	 * CONTROLE DE ACESSO
	 */
	/**
	 * O usuário está conectado?
	 * 
	 * Desta pergunta montamos todas as variáveis para o usuário ou, simplesmente, levamos o usuário para a página de conexão.
	 * 
	 */
	public function user_connected()
	{
		if ( ( !isset( $_SESSION[ 'user_id' ] )
		||     !$this->get_user()
		     )
		   )
		{
			// Não estando conectado:
				// Montamos a string para retorno após o login. Retormamos o ponto de onde o usuário foi levado antes do login.
				$this->unset_user();
				return FALSE;
		}
		else
		{
			// Estando conectado:
				// Monta as variáveis do usuário e, se tudo OK, retorna TRUE;
				$this->set_user( $this->get_user() );
				return TRUE;
		}
	}

	protected function set_new_facebook()
	{
		if ( !is_object( $this->facebook ) )
		{
			$this->facebook = new Facebook	(
								array	(
									 'appId'  => $this->CI->config->item( 'facebook_appid' )
									,'secret' => $this->CI->config->item( 'facebook_appsecret' )
									)
							);
		}
	}
	
	protected function facebook_get_user_data()
	{
		if ( $this->CI->config->item( 'facebook_login' ) == TRUE )
		{
//			$this->set_new_facebook();
	
			// Verifica se o usuário já está conectado.
//			$user = $this->facebook->getUser();
//if ($_SESSION['state'] && ($_SESSION['state'] === $_REQUEST['state'])) {
	$myurl		=	$this->CI->config->item( 'base_url' ) . $this->CI->config->item( 'first_controller_connected' );
//echo $myurl;
//echo  $this->CI->config->item( 'facebook_appid' );
//echo  urlencode( $myurl );
//echo  $this->CI->config->item( 'facebook_appsecret' );

	$token_url	=	"https://graph.facebook.com/oauth/access_token?"
									. "client_id="		. $this->CI->config->item( 'facebook_appid' ) 
									. "&redirect_uri="	. urlencode( $myurl )
									. "&client_secret="	. $this->CI->config->item( 'facebook_appsecret' )
									. "&code=" . '';
//echo $token_url;

	$response = file_get_contents($token_url);
	$params = null;
	parse_str($response, $params);
print_r( $params );
//}
//else
//{
//	echo("The state does not match. You may be a victim of CSRF.");
//}
$user = FALSE;
			if ( $user )
			{
				if ( key_exists( 'facebook_id', $this->CI->session->userdata ) )
				{
					$this->facebook_id		=	$this->get_sessao( 'facebook_id' );
				}
				else
				{
					$this->facebook_id		=	NULL;
				}
	
				if ( $this->get_sessao( 'facebook_user_profile' )
				&&   $this->facebook_id == $user
				   )
				{
					// Recupera os dados da sessão anterior.
					$this->facebook_user_profile	=	json_decode( $this->get_sessao( 'facebook_user_profile' ), TRUE );
					$this->facebook_friends		=	json_decode( $this->get_sessao( 'facebook_friends' ), TRUE );
					$this->facebook_groups		=	json_decode( $this->get_sessao( 'facebook_groups' ), TRUE );
					$this->facebook_login_fail	=	json_decode( $this->get_sessao( 'facebook_login_fail' ), TRUE );
					$this->facebook_access_token	=	json_decode( $this->get_sessao( 'facebook_access_token' ), TRUE );
	
					// Monta novamente as variáveis do facebook.
					$this->facebook_id		=	$this->facebook_user_profile[ 'id' ];
	
					$this->set_user( NULL );
					log_message( 'debug', "Single Pack.facebook_get_user_data(end TRUE 1) facebook_id($this->facebook_id)." );
					return TRUE;
if ( $this->user_info->pessoa_id == 4 )
{
print_r( $this->get_groups_info( 'name' ) );
print_r( $this->get_groups_info( 'id', NULL ) );
print_r( $this->get_groups_info( 'name', NULL ) );
print_r( $this->facebook_user_profile );
}
				}
				else
				{
					try	{
							$this->facebook_user_profile		= $this->facebook->api( '/me', 'GET', array( 'fields' => 'email,name,first_name,last_name,gender,username,id,locale,picture,location,birthday,favorite_teams' ) );
							$this->facebook_friends			= $this->facebook->api(  'me/friends?fields=id,name,picture,installed' );
							$this->facebook_groups			= $this->facebook->api(  'me/groups?fields=id,name,description,privacy,icon,email,updated_time' );
							$this->facebook_id			= $this->facebook_user_profile[ 'id' ];
							$this->facebook_access_token		= $this->facebook->getAccessToken();
	
							$this->set_sessao( 'facebook_update', 'FALSE' );
	
							$this->set_user( NULL );
							$this->facebook_login_fail		=	0;
		
							// Guarda as informações para não pegar novamente enquanto a sessão durar.
							$this->set_sessao( 'facebook_user_profile', json_encode( $this->facebook_user_profile ) );
		
							$this->set_sessao( 'facebook_friends', json_encode( $this->facebook_friends ) );
	
							$this->set_sessao( 'facebook_groups', json_encode( $this->facebook_groups ) );
	
							$this->set_sessao( 'facebook_login_fail', json_encode( $this->facebook_login_fail ) );
	
							$this->set_sessao( 'facebook_access_token', json_encode( $this->facebook_access_token ) );
							
							log_message( 'debug', "Single Pack.facebook_get_user_data(end TRUE 2) facebook_id($this->facebook_id)." );
							return TRUE;
						}
					catch ( FacebookApiException $e )
						{
// tirar os comentários abaixo para ver o retorno de erro do facebook.
//							echo '<pre>'.htmlspecialchars(print_r($e->getResult(), true)).'</pre>';
//							echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
							$user					=	NULL;
							$this->unset_user();
							$this->facebook_login_fail		=	$this->facebook_login_fail + 1;
							log_message( 'debug', "Single Pack.facebook_get_user_data(end catch FALSE 0) count_erro({$this->facebook_login_fail})." );
							return FALSE;
						}
				}
			}
			else
			{
				$this->unset_user();
				log_message( 'debug', "Single Pack.facebook_get_user_data(end FALSE 1)." );
				return FALSE;
			}
		}
	}

	protected function connect()
	{
		log_message( 'debug', "Single Pack.connect(START)." );
		if ( ( !$this->get_user()
		||     !isset( $_SESSION[ 'user_id' ] )
		     )
		||   ( $this->CI->config->item( 'facebook_login' ) == TRUE
		&&     !$this->get_sessao( 'facebook_user_profile' )
		     )
		   )
		{
			log_message( 'debug', "sem conexão ou sem PROFILE" );
			if ( $this->CI->config->item( 'facebook_login' ) == TRUE )
			{
				return $this->facebook_get_user_data();
			// Usuário não está conectado. Tenta a conexão como facebook, se configurado.
			}
			else
			{
				// Montamos a string para retorno após o login. Retormamos o ponto de onde o usuário foi levado antes do login.
				$this->unset_user();
				log_message( 'debug', "Single Pack.connect(end FALSE 2)." );
				return FALSE;
			}
		}
		else
		{
			log_message( 'debug', "conectado" );
			// Estando conectado e com facebook, recuperamos os dados da sessão.
			if ( $this->CI->config->item( 'facebook_login' ) == TRUE )
			{

				if ( key_exists( 'facebook_id', $this->CI->session->userdata ) )
				{
					$this->facebook_id		=	$this->get_sessao( 'facebook_id' );
					$this->set_new_facebook();
	
					// Verifica se o usuário já está conectado.
					$user = $this->facebook->getUser();
					if ( $user
					&&   $user != $this->facebook_id
					   )
					{
						$this->close_session();
						return $this->facebook_get_user_data();
					}
				}
				else
				{
					$this->facebook_id		=	NULL;
				}

				if ( $this->get_sessao( 'facebook_user_profile' ) )
				{
					// Recupera os dados da sessão anterior.
					$this->facebook_user_profile	=	json_decode( $this->get_sessao( 'facebook_user_profile' ), TRUE );
					$this->facebook_friends		=	json_decode( $this->get_sessao( 'facebook_friends' ), TRUE );
					$this->facebook_groups		=	json_decode( $this->get_sessao( 'facebook_groups' ), TRUE );
					$this->facebook_login_fail	=	json_decode( $this->get_sessao( 'facebook_login_fail' ), TRUE );
					$this->facebook_access_token	=	json_decode( $this->get_sessao( 'facebook_access_token' ), TRUE );
					// Monta novamente as variáveis do facebook.
					$this->facebook_id		=	$this->facebook_user_profile[ 'id' ];

					$this->set_user( NULL );
					log_message( 'debug', "Single Pack.connect(end TRUE 3) facebook_id($this->facebook_id)." );
					return TRUE;
				}
				else
				{
					$this->unset_user();
					log_message( 'debug', "Single Pack.connect(end FALSE 3)." );
					return FALSE;
				}
			}
			else
			{
				// Monta as variáveis do usuário e, se tudo OK, retorna TRUE;
				$this->set_user( $this->get_user() );
				log_message( 'debug', "Single Pack.connect(end TRUE 4) sem FACEBOOK." );
				return TRUE;
			}
		}
	}

	public function try_connect()
	{
		log_message('debug', "Singlepack.try_connect (start)." );

		if ( get_parent_class( $this->prg_controller ) == 'JX_Process' )
		{
			$this->CI->config->set_item( 'facebook_login', FALSE ); // desliga a autenticacao com o facebook.
			if ( !is_null( $this->CI->config->item( 'process_user' ) )
			&&   !is_null( $this->CI->config->item( 'process_password' ) ) 
			   )
			{
				if ( !$this->user_valid( $this->CI->config->item( 'process_user' ), $this->CI->config->item( 'process_password' ) ) )
				{
					log_message( 'error', 'Singlepack.try_connect: Usuário (process_user) ou Senha (process_password) não estão configurados em config.php.' );
					return FALSE;
				}
			}
			else
			{
				log_message( 'error', 'Singlepack.try_connect: Usuário (process_user) e/ou Senha (process_password) não estão configurados em config.php.' );
				return FALSE;
			}
		}
		
		$user_connected		=	$this->connect();
		// Carrega o nível de acesso do usuário.
		log_message('debug', "Singlepack.try_connect (methods_access)");
		$this->set_user_access();

		if ( !$this->has_access( $user_connected ) )
		{
			if ( $this->CI->input->is_ajax_request() )
			{
				log_message('debug', "Singlepack.try_connect (FAIL ajax array fail)." );

				$ret_array		=	array();
				$ret_array[ 'ok' ]	=	array();
				$ret_array[ 'fail' ]	=	array();
				$ret_array[ 'fail' ][]	=	array	(
									 'message_type'		=>	'error'
									,'message'		=>	'Você não possui autorização para executar esta operação.'
									,'id'			=>	NULL
									,'table_name'		=>	NULL
									,'db_error_number'	=>	NULL
									,'db_error_message'	=>	NULL
									);
				echo json_encode( $ret_array );
				die;
			}
			else
			{
				log_message('debug', "Singlepack.try_connect (FAIL redirect)." );

				// Regristra a URL de retorno após o login.
				$this->set_sessao( 'jx_continue', $this->CI->uri->uri_string() );
				
				// Redireciona para a página de login.
				redirect( 'login', 'refresh' );
			}
		}
	}
	
	/**
	 * Verifica permissão de acesso ao programa que está sendo chamado agora.
	 */
	public function has_access( $connected )
	{
/*
		log_message('debug', ( is_array( $this->methods_access ) ) ? 'is array' : 'não é array' );
		log_message('debug', ( isset( $this->prg_controller ) ) ? 'isset TRUE (1)': 'not isset (1)' );
		log_message('debug', ( isset( $this->prg_controller_method ) ) ? 'isset TRUE (2)': 'not isset (2)' );
		log_message('debug', ( key_exists( $this->prg_controller.'.'.$this->prg_controller_method, $this->methods_access ) ) ? 'key exists': 'not key exists' );
		log_message('debug', ( key_exists( $this->prg_controller.'.'.$this->prg_controller_method, $this->methods_access ) && $this->methods_access[ $this->prg_controller.'.'.$this->prg_controller_method ]->access != 0 ) ? 'method TRUE' : 'not method' );
		
		foreach( $this->methods_access as $key => $values )
		{
			log_message('debug', 'Key='.$key . ' Nome=' . $values->descr_controller );
		}
*/
//		return TRUE;
		return	(  ( $connected
			&&   $this->CI->user->is_admin()
			   )
			|| ( is_array( $this->methods_access )
			&&   isset( $this->prg_controller )
			&&   isset( $this->prg_controller_method )
			&&   key_exists( $this->prg_controller.'.'.$this->prg_controller_method, $this->methods_access )
			&&   $this->methods_access[ $this->prg_controller.'.'.$this->prg_controller_method ]->access != 'N'
			   )
			|| ( isset( $this->prg_controller )
			&&   in_array( $this->prg_controller, array( 'home' ) )
			&&   isset( $this->prg_controller_method )
			&&   in_array( $this->prg_controller_method, array( 'index', 'login', 'logout' ) )
			   )
			);
	}
	/*
	 * Verifica o acesso ao programa enviado.
	 * 
	 * @prg		-	Programa a ser testado.
	 * 				controller . method
	 * 
	 * @min_access	-	Menor acesso a ser testado.
	 * 				1 - Não pode nada
	 * 				2 - Pode consultar
	 * 				3 - Pode Alterar/Deletar e etc.
	 * 
	 */
	public function has_access_prg( $prg, $min_access = 1 )
	{
	//	return TRUE;
		$arg_prg		=	explode( '.', $prg );
		if ( key_exists( 0, $arg_prg ) )
		{
			$controller	=	$arg_prg[0];
		}
		
		if ( key_exists( 1, $arg_prg ) )
		{
			$method		=	$arg_prg[1];
		}
/*
echo 'AcessoMin='.$min_access.'<BR/>';
if ( $this->CI->user->is_admin() )
{
	echo 'ADMIN<BR/>';
}
else
{
	echo $controller.'.'.$method.'<BR/>';
}
foreach( $this->methods_access as $key => $values )
{
	echo '...'.$key.' acc='.$values->access_level.'<br/>';
}
*/
//return false;
		return	(  $this->CI->user->is_admin()
			|| ( is_array( $this->methods_access )
			&&   key_exists( $prg, $this->methods_access )
			&&   $this->methods_access[ $prg ]->access_level >= $min_access
			   )
			|| ( in_array( $controller, array( 'home' ) )
			&&   in_array( $method, array( 'index', 'login', 'logout' ) )
			   )
			);
	}
	
	/**
	 *  Verifica se a senha informada é válida.
	 *  Usado pela página de conexão (login).
	 */
	public function user_valid( $email, $password )
	{
//echo 'USER_VALID<br/>';
		if ( !$this->user_id
		&&   $email
		   )
		{
			$this->set_user( NULL, $email );
		}

//echo 'pag='.sha1( $password ).' digitado='.$password.'<br/>';
//echo 'db='.$this->user_info->password.'<br/>';

		if ( isset( $this->user_info->password )
		&&   $this->user_info->password == sha1( $password )
//		&&   $this->user_info->password == $password
		   )
		{
//echo 'TRUE<br/>';
			return TRUE;
		}
//echo 'FALSE<br/>';

		$this->unset_user();
		return FALSE;
	}

	// Fecha a sessão atual.
	public function close_session()
	{
		$this->CI->session->sess_destroy();
		session_destroy();
		
		// Cria uma nova sessão para seguir como anonimo.
		session_start();
		$this->CI->session->sess_create(); // Ativa controle de sessão do CI.
		
		$this->prg_controller			=	'home';
		$this->prg_controller_method		=	'index';
		
		// Variáveis montados na lógica abaixo.
		$this->set_theme( $this->CI->config->item( 'default_theme' ) );
		$this->sistema_id_atual			=	1;
		$this->sistema_atual			=	NULL;
		$this->controller_id_atual		=	1;
		$this->controller_atual			=	NULL;
		$this->method_id_atual			=	1;
		$this->method_atual			=	NULL;

		$this->unset_user();

		if ( $this->CI->config->item( 'facebook_login' ) == TRUE )
		{
			$this->set_new_facebook();
			// Verifica se o usuário já está conectado.
			$user = $this->facebook->getUser();
			if ( $user )
			{
				$this->facebook_login_url	=	$this->facebook->getLoginUrl();
				if ( $this->CI->config->item( 'first_controller' ) )
				{
					$this->facebook_logout_url	=	str_replace( '%2Flogout', '', $this->facebook->getLogoutUrl( array( 'access_token' => $this->get_facebook_access_token(), "next" => $this->CI->config->item( 'first_controller' ) ) ) );
				}
				else
				{
					$this->facebook_logout_url	=	str_replace( '%2Flogout', '', $this->facebook->getLogoutUrl( array( 'access_token' => $this->get_facebook_access_token() ) ) );
				}
				return $this->facebook_logout_url;
			}
			else
			{
				if ( $this->CI->config->item( 'first_controller' ) )
				{
					return $this->CI->config->item( 'first_controller' );
				}
				else
				{
					return 'home/index';
				}
			}
		}
		else
		{
			return 'home/index';
		}
	}

	// Elimina todas as informações do usuário da sessão.
	public function unset_user()
	{
		$this->user_id				=	NULL;
		$this->unset_sessao( 'user_id' );

		$this->facebook_user_profile		=	NULL;
		$this->unset_sessao( 'facebook_user_profile' );

		$this->facebook_friends			=	NULL;
		$this->unset_sessao( 'facebook_friends' );

		$this->facebook_groups			=	NULL; 
		$this->unset_sessao( 'facebook_groups' );

		$this->facebook_login_fail		=	NULL; 
		$this->unset_sessao( 'facebook_login_fail' );
		
		$this->facebook_access_token		=	NULL;
		$this->unset_sessao( 'facebook_access_token' );
		
		$this->unset_user_info();
		$this->unset_user_cfg();
	}

	// A partir do e-mail, registra as informações do usuário.
	public function set_user( $user_id, $email = NULL )
	{
		log_message( 'debug', "Single Pack.set_user($user_id)." );
		if ( !$this->user_id
		||   !$user_id
		   )
		{
			if ( $this->CI->config->item( 'facebook_login' ) == TRUE ) // Conectando via facebook.
			{
				log_message( 'debug', "...via facebook" );
				if ( $this->set_user_info_facebook() )
				{
					log_message( 'debug', "......Encontrou" );
					$this->user_id			=	$this->user_info->user_id;

					$this->set_sessao( 'user_id', $this->user_id );

					$this->set_sessao( 'facebook_id', $this->facebook_id );
					
					$this->set_user_cfg();
				}
				else
				{
					$this->unset_user();
				}
			}
			else
			{
				if ( $user_id )
				{
					$this->user_id			=	$user_id;
					$this->set_sessao( 'user_id', $this->user_id );
		
					if ( $this->set_user_info() )
					{
						$this->set_user_cfg();
					}
					else
					{
						$this->unset_user();
					}
				}
				elseif ( $email )
				{
					if ( $this->set_user_info( $email ) )
					{
						$this->user_id			=	$this->user_info->user_id;
						$this->set_sessao( 'user_id', $this->user_id );
			
						$this->set_user_cfg();
					}
					else
					{
						$this->unset_user();
					}
				}
			}
		}
//echo 'set_user().99 user='.$this->user_id.'<br/>';
		log_message( 'debug', "Single Pack.set_user(fim)." );
		
		return $this->user_id;
	}
	
	// Completa as informações do usuário.
	//	É chamada de dentro de set_user().
	public function unset_user_info()
	{
		$this->user_info			=	NULL;
		return NULL;
	}
	public function set_user_info( $email = NULL )
	{
		if ( $email )
		{
			$this->user_info		=	$this->CI->user->get_by_email( $email );
		}
		else
		{
			$this->user_info		=	$this->CI->user->get_by_id( $this->user_id );
		}

		return $this->user_info; // ->row()
	}
	public function set_user_info_facebook()
	{
//echo 'set_user_info_facebook().1<br/>';
		$this->user_info			=	$this->CI->user->get_by_facebook_id( $this->facebook_id );

		if ( !key_exists( 'facebook_update', $this->CI->session->userdata )
		||   $this->get_sessao( 'facebook_update' ) == 'FALSE'
		   )
		{
//echo 'set_user_info_facebook().2<br/>';
			$this->set_sessao( 'facebook_update', 'TRUE' );
			$this->user_info		=	$this->CI->user->create_by_facebook( $this->facebook_user_profile );
		}
//echo 'set_user_info_facebook().3<br/>';

		return $this->user_info; // ->row()
	}
	// Carrega os perfis do usuário.
	public function set_user_access()
	{
		$this->system_granted			=	$this->CI->user->get_system_granted( $this->user_id );
		$this->methods_access			=	$this->CI->user->get_access( $this->user_id );
		
		// Acrescenta os controller.métodos dos sistemas para que este sejam acessados corretamente.
		foreach( $this->system_granted as $key => $values )
		{
			$method					=	new stdClass();
			$method->sistema_ctrl_meth_id		=	$values->sistema_id;
			$method->nome_sistema			=	$values->nome_sistema;
			$method->descr_sistema			=	$values->descr_sistema;
			$method->nome_controller		=	$values->system_controller;
			$method->descr_controller		=	'home system controller';
			$method->nome_method			=	'index';
			$method->descr_method			=	'home system method';
			$method->seq_exibicao_sistema		=	0;
			$method->seq_exibicao_controller	=	0;
			$method->seq_exibicao_method		=	0;
			$method->access				=	2;
			$this->methods_access[ $values->system_controller . '.index' ]	=	$method;
		}		
		
		return $this->methods_access;
	}
	
	// Carrega a configuração do usuário.
	public function unset_user_cfg()
	{
		$this->user_cfg				=	NULL;
		return NULL;
	}
	public function set_user_cfg()
	{
		log_message( 'debug', "Single Pack.set_user_cfg()." );
		$this->user_cfg				=	$this->CI->user->get_cfg( $this->user_info->user_id );
		
		if ( $this->user_cfg )
		{
			$this->set_theme( $this->user_cfg->theme );
			$this->set_idioma( $this->user_cfg->idioma );
		}
		
		return $this->user_cfg; // ->row()
	}

	// Carrega os perfis do usuário.
	public function get_user_access()
	{
		return $this->methods_access;
	}
	// Retorna o USER_ID da sessão atual.
	public function get_user()
	{
//		log_message( 'debug', "Single Pack.get_user($this->user_id).1" );
		if ( !$this->user_id )
		{
			if ( $this->CI->config->item( 'facebook_login' ) == TRUE // Conectando via facebook.
			&&   isset( $_SESSION[ 'facebook_id' ] )
			   )
			{
//				log_message( 'debug', "Single Pack.get_user($this->user_id).2" );
				$this->facebook_id		=	$this->get_sessao( 'facebook_id' );
				$this->user_id			=	$this->get_sessao( 'user_id' );
			}
//			log_message( 'debug', "Single Pack.get_user($this->user_id).3" );
			$this->set_user( $this->get_sessao( 'user_id' ) );
		}
//		log_message( 'debug', "Single Pack.get_user($this->user_id).4" );
		return $this->user_id;
	}

	// Retorna as informações do usuário.
	public function get_user_info()
	{
		if ( !$this->user_info )
		{
			$this->set_user_info();
		}
		return $this->user_info;
	}
	
	public function get_pessoa_id()
	{
		if ( is_object( $this->get_user_info() ) )
		{
			return $this->get_user_info()->pessoa_id;
		}
		else
		{
			return NULL;
		}
	}
	
	// Retorna as informações do usuário no facebook
	public function get_face_user_profile()
	{
		return $this->facebook_user_profile;
	}
	
	// Retorna o ID do facebook do usuário atual.
	public function get_facebook_id()
	{
		return $this->facebook_id;
	}

	// Retorna o facebook login status.
	public function get_facebook_login_status()
	{
		return 	$this->facebook_login_fail;
	}

	// Retorna o facebook login status.
	public function get_facebook_access_token()
	{
		return 	$this->facebook_access_token;
	}
	
	// Retorna as a lista de amigos do facebook.
	public function get_friends( $installed = FALSE )
	{
		if ( $this->CI->config->item( 'facebook_login' ) == TRUE // Conectando via facebook.
		&&   isset( $_SESSION[ 'facebook_id' ] )
		   )
		{
			print_r( $this->facebook_friends );
			
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function get_friends_id( $installed = FALSE )
	{
		return $this->get_facebook_array_info( 'id', $where = array( 'installed' => TRUE ), NULL, $installed );
	}
	
	/**
	 * 
	 * Extrai do array de amigos ou grupos vindos do facebook a informação selecionada em $what
	 * 
	 * @param string $what, qual informação retornar.
	 * @param string $where, condiciona o retorna da informação de what quando existir a informação em where igual ao valor informação.
	 * 				Exemplo: array( 'installed' => TRUE ). Só retorna as pessoas com o aplicativo instalado.
	 * @param boolean $not_where, inverte a condição do parametro where.
	 * 				Exemplo: array( 'installed' => TRUE ). Como inverte, só retorna as pessoas não instaladas.
	 * @param boolean $group, indica se o array a ser investigado é o de grupos(true) ou de amigos(false).
	 */
	public function get_facebook_array_info( $what = 'id', $where = NULL, $not_where = FALSE, $installed = FALSE, $group = FALSE  )
	{
		if ( !is_array( $what ) )
		{
			$ar_what	=	$what;
		}
		else
		{
			$ar_what	=	$what;
		}

		if ( $this->CI->config->item( 'facebook_login' ) == TRUE // Conectando via facebook.
		&&   isset( $_SESSION[ 'facebook_id' ] )
		   )
		{
			$ar_ret						=	array();
			if ( is_array( $where ) )
			{
				foreach( $where as $key => $value )
				{
					$where_key			=	$key;
					$where_value			=	$value;
				}
			}
			else
			{
				$where_key				=	FALSE;
				$where_value				=	FALSE;
			}

			if ( $group )
			{
				$facebook_info				=	$this->facebook_groups['data'];
			}
			else
			{
				if ( $installed )
				{
					$facebook_info			=	$this->facebook_friends_installed['data'];
				}
				else
				{
					$facebook_info			=	$this->facebook_friends['data'];
				}
			}

			if ( $facebook_info )
			{
				foreach( $facebook_info as $info_values )
				{
					if ( is_array( $info_values ) ) // Em "data" sempre retorna um array.
					{
						$can_use						=	( !$where_key ) ? TRUE : FALSE;
	
						// Se o parametro de entrada é um array, retornaremos um objeto com os valores.
						if ( is_array( $ar_what ) )
						{
							$new_obj					=	new stdClass();
							foreach( $info_values as $key => $value )
							{
								$new_value				=	NULL;

								if ( is_array( $value ) )
								{
									foreach( $ar_what as $value_what )
									{
										if ( key_exists( $value_what, $value ) )
										{
											$new_value	=	$value[ $value_what ];
										}
									}
								}
								elseif ( in_array( $key, $ar_what ) )
								{
									$new_value			=	$value;
								}
								$new_obj->$key				=	$new_value;
								
								if ( !$can_use
								&&   $key == $where_key
								&&   $value == $where_value
								   )
								{
									$can_use			=	TRUE;
								}
							}

							// Verifica se a coluna no $where foi encontrada com o valor exigido.
							if ( ( ( !$not_where
							&&       $can_use
							       )
							||     ( $not_where
							&&       !$can_use
							       )
							     )
							
							   )
							{
								$ar_ret[]				=	$new_obj;
							}
							unset( $new_obj	);
						}
						else
						{
							foreach( $info_values as $key => $value )
							{
//								$new_value				=	NULL;

								if ( is_array( $value )
								&&   key_exists( $what, $value )
								   )
								{
									$new_value			=	$value[ $what ];
								}
								elseif ( $key == $what )
								{
									$new_value			=	$value;
								}
		
								if ( !$can_use
								&&   $key == $where_key
								&&   $value == $where_value
								   )
								{
									$can_use			=	TRUE;
								}
								
								// Analisa o valor encontrado contra o WHERE passado e se ele deve ser invertido.
								if ( $new_value
								&&   ( ( !$not_where
								&&       $can_use
								       )
								||     ( $not_where
								&&       !$can_use
								       )
								     )
								
								   )
								{
									$ar_ret[]			=	$new_value;
								}
							}
						}
					}
				}
				return $ar_ret;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Trata os grupos cadastrados no Facebook para o usuário atual.
	 */
	public function get_groups_id( $what = 'id', $where = array( 'administrator' => TRUE ) )
	{
		return $this->get_facebook_array_info( 'id', $where = $where, NULL, FALSE, $group = TRUE );
	}
	public function get_groups_info( $what = 'id', $where = array( 'administrator' => TRUE ) )
	{
		return $this->get_facebook_array_info( $what = $what, $where = $where, NULL, FALSE, $group = TRUE );
	}
	
	// Retorna menus que serão exibidos no aside.
	public function get_menus( $sistema_id )
	{
		$select			=	"
						select	 meth.nome			AS	method
							,ctrl.id			AS	sistema_ctrl_id
							,meth.id			AS	sistema_ctrl_meth_id
							,ctrl.prg_controller		AS	prg_controller
							,meth.prg_controller_method	AS	prg_controller_method
							,ctrl.nome			AS	nome_ctrl
							,meth.nome			AS	nome_meth
							,ctrl.descr			AS	descr_ctrl
							,meth.descr			AS	descr_meth
							,ctrl.show_menu			AS	ctrl_show_menu
							,meth.show_menu			AS	meth_show_menu
							,ctrl.show_menu_footer		AS	ctrl_show_menu_footer
							,meth.show_menu_footer		AS	meth_show_menu_footer
						from	 sistema_ctrl		AS	ctrl
							,sistema_ctrl_meth	AS	meth
						where	ctrl.sistema_id		=	{$sistema_id}
						and	meth.sistema_ctrl_id	=	ctrl.id
						and	( ctrl.show_menu	=	'S'
						or        ctrl.show_menu_footer	=	'S'
							)
						and	( meth.show_menu	=	'S'
						or        meth.show_menu_footer	=	'S'
							)
						";

		if ( !$this->CI->user->is_admin() )
		{
		}

		$select			=	$select.
						"
						order by ctrl.nome, ctrl.seq_exibicao, meth.seq_exibicao
						";

		$query			=	$this->CI->db->query( $select );

		return $query->result_object();
	}

	// Retorna as configurações do usuário.
	public function get_user_cfg()
	{
		if ( !$this->user_cfg )
		{
			$this->set_user_cfg( $this->get_user() );
		}
		return $this->user_cfg;
	}

	// Registra a navegação feita pelos usuários.
	public function registry_nav()
	{
		if ( $this->CI->config->item( 'registry_nav' )
		&&   $this->prg_controller != 'user_nav'
		   )
		{
			$user_id			=	isset( $this->user_info->user_id ) ? $this->user_info->user_id : 1;
			$user_id			=	is_null( $user_id ) ? 1 : $user_id;
			$insert_data			=	array	(
									 'id'			=>	''
									,'user_id'		=>	$user_id
									,'ip_address'		=>	$this->get_sessao( 'ip_address' )
									,'user_agent'		=>	$this->get_sessao( 'user_agent' )
									,'sistema_ctrl_meth_id'	=>	$this->method_id_atual
									);
			$nav_ret			=	$this->CI->user_nav->insert( $insert_data );
		}
	}
	
	/**
	 * CONTROLE DE PREFERÊNCIAS
	 */
	
	/**
	 * Registra/Retorna o Thema usado pelo usuário
	 * 
	 */
	public function set_theme( $theme )
	{
		$use_theme			= NULL;
		
		if ( $this->CI->config->item( 'facebook_login' ) == TRUE )
		{
			$use_theme		= $this->CI->config->item( 'facebook_theme' );
		}
		else
		{
			$use_theme		= $theme;
		}

		if ( $use_theme )
		{
			$this->theme		= $use_theme;
			// Se não existe o registro na sessão ou o registro está diferente do atual,
			// registramos no controle de sessão.
			if ( !$this->get_sessao( 'theme' ) ||
			     $this->get_sessao( 'theme' ) != $use_theme
			   )
			{
				$this->set_sessao( 'theme', $this->theme );
			}
		}
		
		return $this->theme;
	}

	public function get_theme()
	{
		if ( !$this->theme )
		{
			$this->theme		=	 $this->get_sessao( 'theme' );
		}
		return $this->theme;
	}
	/**
	 * Registra/Retorna o Idioma usado pelo usuário
	 * 
	 */
	public function set_idioma( $idioma )
	{
		log_message( 'debug', "Single Pack.set_idioma($idioma)." );
		if ( $idioma )
		{
			$this->idioma		= $idioma;
			// Se não existe o registro na sessão ou o registro está diferente do atual,
			// registramos no controle de sessão.
			if ( !$this->get_sessao( 'idioma' ) ||
			     $this->get_sessao( 'idioma' ) != $idioma
			   )
			{
				$this->set_sessao( 'idioma', $this->idioma );
			}
			
			$this->CI->config->set_item( 'language', $this->idioma );
		}
		
		return $this->idioma;
	}

	public function get_idioma()
	{
		if ( !$this->idioma )
		{
			$this->idioma		=	 $this->get_sessao( 'idioma' );
		}
		return $this->idioma;
	}
	
	/**
	 * CONTROLE DE MENUS e PERMISSÕES.
	 */
	/**
	 * Retorna o ID do sistema do controller atual.
	 */
	public function set_sistema()
	{
		log_message( 'debug', "Single Pack.set_sistema(start)." );
		$retorno							= -1;
		if ( isset( $this->prg_controller ) )
		{
			log_message( 'debug', "...prg_controller=".$this->prg_controller );
			$this->controller_atual					= $this->CI->sistema_ctrl->get_one_by_where( array( 'prg_controller' => $this->prg_controller ) );

			if ( $this->controller_atual )
			{
				log_message( 'debug', "......Localizou via Controller." );
				if ( isset( $this->controller_atual->sistema_id )
				&&   !is_null( $this->controller_atual->sistema_id )
				   )
				{
					$this->controller_id_atual		= $this->controller_atual->id;
					$retorno				= $this->controller_atual->sistema_id;
					$this->sistema_atual			= $this->CI->sistema->get_one_by_id( $this->controller_atual->sistema_id );
					$this->sistema_atual->menus		= $this->get_menus( $this->sistema_atual->id );
				}
			}
			// Não encontramos um controller, vamos verificar se há um sistema com o código do controller enviado.
			else
			{
				log_message( 'debug', "......Tentando por sistema" );
				$this->sistema_atual				= $this->CI->sistema->get_one_by_where( array( 'prg_controller' => $this->prg_controller ) );

				if ( $this->sistema_atual )
				{
					log_message( 'debug', "......Localizou via sistema" );
					if ( isset( $this->sistema_atual->id )
					&&   $this->sistema_atual->id != ''
					   )
					{
						$retorno			= $this->sistema_atual->id;
						$this->sistema_atual->menus	= $this->get_menus( $this->sistema_atual->id );
						// Forçamos o retorno NULO para a montagem do objeto. Usamos o -1 no final para isso.                                            V
						$this->controller_atual		= $this->CI->sistema_ctrl->get_one_by_where( array( 'sistema_id' => $this->sistema_atual->id, 'seq_exibicao' => -1 ) );
						$this->controller_id_atual	= 0;
					}
				}
			}
		}
		
		/**
		 * Não achamos o sistema até aqui, então assumimos o sistema com ID =1 como sistema escolhido.
		 */
		if ( $retorno == -1 )
		{
			log_message( 'debug', "......NÃO LOCALIZOU" );
			$this->sistema_atual		=	$this->CI->sistema->get_one_by_id( 1 ); // fixamos o cadastro para este tipo de inserção.
			if ( $this->sistema_atual )
			{
				$this->sistema_id_atual		=	$this->sistema_atual->id;
				$retorno			=	$this->sistema_id_atual;
				$this->sistema_atual->menus	=	$this->get_menus( $this->sistema_atual->id );
				
				/*
				 * Insere uma linha do controller que está sendo chamando, assim registramos todos os controles do sistema.
				 */
				// Controller
				$insert_data			=	array	(
										 'id'			=>	''
										,'sistema_id'		=>	$this->sistema_id_atual
										,'seq_exibicao'		=>	9999
										,'nome'			=>	$this->prg_controller
										,'descr'		=>	''
										,'ajuda'		=>	''
										,'prg_controller'	=>	$this->prg_controller
										,'liberado_user_pessoa'	=>	'N'
										,'show_menu'		=>	'N'
										);
				$ctrl_ret			=	$this->CI->sistema_ctrl->insert( $insert_data );
				log_message( 'debug', "......Inseriu novo controller." );
				$this->controller_atual		=	$this->CI->sistema_ctrl->get_one_by_where( array( 'sistema_ctrl.id' => $this->CI->db->insert_id() ) );
				if ( $this->controller_atual )
				{
					$this->controller_id_atual	=	$this->controller_atual->id;
				}
	
				if ( $retorno == -1 ) // Persistindo a não existência, chamamos a agenda.
				{
					$retorno			=	1;
					$this->sistema_atual		=	$this->CI->sistema->get_one_by_id( $retorno );
					if ( $this->sistema_atual )
					{
						$this->sistema_id_atual		=	$this->sistema_atual->id;
						$this->sistema_atual->menus	=	$this->get_menus( $this->sistema_atual->id );
						$this->controller_atual		=	$this->CI->sistema_ctrl->get_one_by_where( array( 'sistema_id' => $retorno, 'seq_exibicao' => 1 ) );
						$this->controller_id_atual	=	$this->controller_atual->id;
					}
				}
			}
		}

		/*
		 * Selecionamos a linha do método atual.
		 */
		if ( $this->CI->router->method &&
		     $this->controller_atual
		   )
		{
			log_message( 'debug', "Single Pack selecionando método." );
			$this->method_atual	=	$this->CI->sistema_ctrl_meth->get_one_by_where( 'sistema_ctrl_meth.sistema_ctrl_id = '.$this->controller_id_atual.' and sistema_ctrl_meth.prg_controller_method = "'.$this->CI->router->method .'"');
			if ( !$this->method_atual )
			{
				// Insere uma linha do método que está sendo chamando, assim registramos todos os métodos do sistema.
				$insert_data		=	array	(
									 'id'				=>	''
									,'sistema_ctrl_id'		=>	$this->controller_id_atual // pegamos o ID retornado do insert acima.
									,'seq_exibicao'			=>	999
									,'nome'				=>	$this->prg_controller_method
									,'descr'			=>	''
									,'ajuda'			=>	''
									,'prg_controller_method'	=>	$this->prg_controller_method
									,'liberado_user_pessoa'		=>	'N'
									,'show_menu'			=>	'N'
									);
				$meth_ret		=	$this->CI->sistema_ctrl_meth->insert( $insert_data );
				$this->method_atual	=	$this->CI->sistema_ctrl_meth->get_one_by_id( $this->CI->db->insert_id() );
			}
			if ( $this->method_atual )
			{
				$this->method_id_atual	=	$this->method_atual->id;
			}
		}

		if ( $retorno != -1 )
		{
			$this->sistema_id_atual	= $retorno;
			// Se não existe o registro na sessão ou o registro está diferente do atual,
			// registramos no controle de sessão.
			if ( !$this->get_sessao( 'sistema_id_atual' ) ||
			     $this->get_sessao( 'sistema_id_atual' ) != $retorno
			   )
			{
				$this->set_sessao( 'sistema_id_atual', $retorno );
			}
		}
		log_message( 'debug', "Single Pack SISTEMA({$this->sistema_id_atual}) CONTROLLER({$this->controller_id_atual}) METHOD({$this->method_id_atual})" );

		// Registra a navegação.
		$this->registry_nav();
		
		log_message( 'debug', "Single Pack.set_sistema(end)." );
		return $retorno;
	}


	/**
	 * Registra/Retorna o sistema atual
	 */
	public function set_sistema_atual( $sistema_id )
	{
		
		return $sistema_id;
	}

	public function get_sistema()
	{
		if ( !$this->$sistema_id_atual )
		{
			$this->$sistema_id_atual		=	 $this->get_sessao( 'sistema_id_atual' );
		}
		return $this->$sistema_id_atual;
	}
	
	/**
	 * Retorna descrição do controller.
	 */
	public function get_controller( $prg_controller )
	{
		return $this->CI->sistema_ctrl->get_one_by_where( "prg_controller = '$prg_controller'" );
	}

	/**
	 * Misc
	 */
	public function load_model_file( $file, $name = NULL )
	{
		if ( file_exists( APPPATH.'models/'.$file.'_model'.'.php' ) )
		{
			if ( $name == NULL )
			{
				$name	=	$file;
			}
			
			if ( ! class_exists( ucfirst( $file.'_model' ) ) ) // Verificamos se o model já foi carregado anteriormente. Se sim, não o carregamos novamente para evitar recursividade de carga.
			{
				$this->CI->load->model( $file.'_model', $name );
			}
			else
			{
				log_message( 'debug', "Singlepack - Model '/models/".$file."_model.php' já estava carregado." );
			}
			//TODO: Criar uma tabela na base de dados que registra a dependencia, FKs, entre as tabelas;
			//TODO: Fazer a carga automática dos models dos pais do model atual.
		}
		elseif ( file_exists( APPPATH.'models/jx/'.$file.'_model'.'.php' ) )
		{
			if ( $name == NULL )
			{
				$name	=	$file;
			}

			if ( ! class_exists( ucfirst( $file.'_model' ) ) ) // Verificamos se o model já foi carregado anteriormente. Se sim, não o carregamos novamente para evitar recursividade de carga.
			{
				$this->CI->load->model( 'jx/'.$file.'_model', $name );
			}
			else
			{
				log_message( 'debug', "Singlepack - Model 'jx//models/".$file."_model.php' já estava carregado." );
			}
		}
		else
		{
			log_message( 'debug', "Singlepack - Model '/models/".$file."_model.php' not exists." );
		}
	}

	public function load_lang_file( $file, $force = FALSE, $file_prefix = NULL )
	{
		$deft_lang = ( ! $this->CI->config->item( 'language' ) ) ? ( ! $this->CI->config->item( 'default_language' ) ) ? 'pt_BR' : $this->CI->config->item( 'default_language' ) : $this->CI->config->item( 'language' );
		$idiom = ($deft_lang == '') ? 'pt_BR' : $deft_lang;

		if ( file_exists( APPPATH.'language/'.$idiom.'/'.$file.'_lang'.'.php' ) )
		{
			$this->CI->lang->load( $langfile = $file, $idiom = $idiom, $return = FALSE, $add_suffix = TRUE, $alt_path = '', $force = $force, $file_prefix = $file_prefix );
		}
		elseif ( file_exists( APPPATH.'language/'.$idiom.'/jx/'.$file.'_lang'.'.php' ) )
		{
			$this->CI->lang->load( $langfile = 'jx/'.$file, $idiom = $idiom, $return = FALSE, $add_suffix = TRUE, $alt_path = '', $force = $force, $file_prefix = $file_prefix );
		}
		else
		{
			$idiom_default = ( ! $this->CI->config->item( 'default_language' ) ) ? 'pt_BR' : $this->CI->config->item( 'default_language' );

			if ( file_exists( APPPATH.'language/'.$idiom_default.'/'.$file.'_lang'.'.php' ) )
			{
				$this->CI->lang->load( $langfile = $file, $idiom = $idiom_default, $return = FALSE, $add_suffix = TRUE, $alt_path = '', $force = $force, $file_prefix = $file_prefix );
			}
			elseif ( file_exists( APPPATH.'language/'.$idiom_default.'/jx/'.$file.'_lang'.'.php' ) )
			{
				$this->CI->lang->load( $langfile = 'jx/'.$file, $idiom = $idiom_default, $return = FALSE, $add_suffix = TRUE, $alt_path = '', $force = $force, $file_prefix = $file_prefix );
			}
			else
			{
				log_message( 'debug', "JX_SinglePack - Language '/language/".$idiom.'/'.$file."_lang.php' not exists." );
			}
		}
	}
	
	public function load_lang_model_files( $file, $name = NULL, $file_prefix = NULL )
	{
		$this->load_lang_file( $file, FALSE, $file_prefix );
		$this->load_model_file( $file, $name );
	}

	/**
	 * 
	 * Trata informações de data e hora
	 * 
	 */
	protected function _prep_date( $datestr = '', $_hour = FALSE )
	{
		$explode_char				=	'/';

		$this->day				=	31;
		$this->month				=	12;
		$this->year				=	1969;
		$this->hour				=	0;
		$this->min				=	0;
		$this->sec				=	0;

		if ($datestr == '')
		{
			return NULL;
		}

		$datestr				=	trim( $datestr );

		//TODO: Usar a informação do _LANG para determinar qual o formato que está sendo cobrado do usuário. Fazer a validação abaixo focando este formato.
		$datestr = preg_replace("/\040+/", ' ', $datestr); // retira os espaços
		if ( $_hour )
		{
			// procura:          yyyy       -mm         -dd          hh        :ii         :ss            :AM ou PM
			if ( ! preg_match('/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}\s[0-9]{1,2}:[0-9]{1,2}(?::[0-9]{1,2})?(?:\s[AP]M)?$/i', $datestr))
			{
				// procura:          dd         /mm         /yyyy        hh        :ii         :ss
				if ( ! preg_match('/^[0-9]{2,2}\/[0-9]{1,2}\/[0-9]{1,4}\s[0-9]{1,2}:[0-9]{1,2}(?::[0-9]{1,2})?$/i', $datestr))
				{
					return NULL;
				}
				else
				{
					$explode_char	=	'/';
				}
			}
			else
			{
				$explode_char		=	'-';
			}
		}
		else 
		{
			// procura:          yyyy       -mm         -dd
			if ( ! preg_match('/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}/i', $datestr))
			{
				// procura:          dd         /mm         /yyyy
				if ( ! preg_match('/^[0-9]{2,2}\/[0-9]{1,2}\/[0-9]{1,4}/i', $datestr))
				{
					return NULL;
				}
				else
				{
					$explode_char	=	'/';
				}
			}
			else
			{
				$explode_char		=	'-';
			}
		}
		
		$split					=	explode( ' ', $datestr );
		
		// cria um array com os valores separados
		$ex					=	explode( $explode_char, $split['0']);

		if ( $explode_char == "-" ) // padrão estados unidos e europa
		{
			$this->year  = (strlen($ex['0']) == 2) ? '20'.$ex['0'] : $ex['0'];
			$this->month = (strlen($ex['1']) == 1) ?  '0'.$ex['1'] : $ex['1'];
			$this->day   = (strlen($ex['2']) == 1) ?  '0'.$ex['2'] : $ex['2'];
		}
		else // padrão brasileiro
		{
			$this->year  = (strlen($ex['2']) == 2) ? '20'.$ex['2'] : $ex['2'];
			$this->month = (strlen($ex['1']) == 1) ?  '0'.$ex['1'] : $ex['1'];
			$this->day   = (strlen($ex['0']) == 1) ?  '0'.$ex['0'] : $ex['0'];
		}

		if ( $_hour )
		{
			$ex = explode(":", $split['1']);
	
			$this->hour = (strlen($ex['0']) == 1) ? '0'.$ex['0'] : $ex['0'];
			$this->min  = (strlen($ex['1']) == 1) ? '0'.$ex['1'] : $ex['1'];
			if (isset($ex['2']) && preg_match('/[0-9]{1,2}/', $ex['2']))
			{
				$this->sec  = (strlen($ex['2']) == 1) ? '0'.$ex['2'] : $ex['2'];
			}
			else
			{
				// Unless specified, seconds get set to zero.
				$this->sec = '00';
			}
		}
		else
		{
			$this->hour = "00";
			$this->min  = "00";
			$this->sec  = "00";
		}

		if (isset($split['2']))
		{
			$ampm = strtolower($split['2']);

			if (substr($ampm, 0, 1) == 'p' AND $this->hour < 12)
				$this->hour = $this->hour + 12;

			if (substr($ampm, 0, 1) == 'a' AND $this->hour == 12)
				$this->hour =  '00';

			if (strlen($this->hour) == 1)
				$this->hour = '0'.$this->hour;
		}
	}

	public function print_datetime_value( $field_value, $field_type, $format_style = 'date' )
	{
		$ret_value				=	NULL;
		
		if ( $field_value == '0000-00-00'
		||   $field_value == '0000-00-00 00:00:00'
		||   $field_value == NULL
		   )
		{
			$ret_value			=	NULL;
		}
		elseif ( $format_style == 'date' )
		{
			if ( $field_type == 'datetime' )
			{
				$ret_value		=	date_format( $field_value, $this->CI->lang->get_line( 'date_format' ) );
			}
			elseif ( $field_type == 'date' )
			{
				$ret_value		=	date( $this->CI->lang->get_line( 'date_format' ), mysql_to_unix( $field_value ) );
			}
			elseif ( $field_type == 'timestamp' )
			{
				if ( $field_value == 'CURRENT_TIMESTAMP' )
				{
					$ret_value	=	date( $this->CI->lang->get_line( 'date_format' ), now() );
				}
				else
				{
					$ret_value	=	date( $this->CI->lang->get_line( 'date_format' ), mysql_to_unix( $field_value ) );
				}
			}
		}
		elseif ( $format_style == 'datetime' )
		{
			if ( $field_type == 'datetime' )
			{
				$ret_value		=	date_format( $field_value, $this->CI->lang->get_line( 'datetime_format' ) );
			}
			elseif ( $field_type == 'date' )
			{
				$ret_value		=	date_format( $field_value, $this->CI->lang->get_line( 'datetime_format' ) );
			}
			elseif ( $field_type == 'timestamp' )
			{
				if ( $field_value == 'CURRENT_TIMESTAMP' )
				{
					$ret_value	=	date( $this->CI->lang->get_line( 'datetime_format' ), now() );
				}
				else
				{
					$ret_value	=	date( $this->CI->lang->get_line( 'datetime_format' ), mysql_to_unix( $field_value ) );
				}
			}
		}
		else
		{
			$ret_value			=	$field_value;
		}
		
		return $ret_value;
	}
	
	public function input_to_date( $datestr = '', $hour = FALSE )
	{
		$this->_prep_date( $datestr, $hour );
// PHP 5.3	$_date		=	DateTime::createFromFormat( 'H i s m d Y', $this->hour .' '. $this->min .' '. $this->sec .' '. $this->month .' '. $this->day .' '. $this->year );
		$_date		=	new DateTime( $this->year .'-'. $this->month .'-'. $this->day .' '. $this->hour .':'. $this->min .' GMT' );

		return $_date;
	}

	public function set_history_url()
	{
		// Prepara a URL para retorno.
		$this->last_url				=	$this->get_sessao( 'history_atual_url' );
		$retorno_anterior			=	$this->get_sessao( 'history_last_url' );
//echo 'history_atual_url='.$this->last_url.'<br/>';
//echo 'history_last_url='.$retorno_anterior.'<br/>';
//echo 'uri_string='.preg_replace( "/[^a-zA-Z\/\_\s]/", "", $this->CI->uri->uri_string() ).'<br/>';


		// Registra a URL atual para futuro retorno.
		if ( $this->last_url == preg_replace( "/[^a-zA-Z\/\_\s]/", "", $this->CI->uri->uri_string() ) )
		{
//echo 'refresh()<br/>';
			$this->last_url			=	$retorno_anterior;
			$this->set_sessao( 'history_atual_url', $retorno_anterior );
		}
		else
		{
//echo 'sequencia normal()<br/>';
			$this->set_sessao( 'history_atual_url', preg_replace( "/[^a-zA-Z\/\_\s]/", "", $this->CI->uri->uri_string() ) );
		}

		$this->set_sessao( 'history_last_url', $this->last_url );
	}

	/**
	 * Testa o browser.
	 */
	function getBrowser() 
	{ 
		if ( isset( $_SERVER ) 
		&&   key_exists( 'HTTP_USER_AGENT', $_SERVER )
		   )
		{
		    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
		    $bname = 'Unknown';
		    $platform = 'Unknown';
		    $version= "";
		
		    //First get the platform?
		    if (preg_match('/linux/i', $u_agent)) {
		        $platform = 'linux';
		    }
		    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		        $platform = 'mac';
		    }
		    elseif (preg_match('/windows|win32/i', $u_agent)) {
		        $platform = 'windows';
		    }
		    
		    // Next get the name of the useragent yes seperately and for good reason
		    $ub = NULL;
		    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
		    { 
		        $bname = 'Internet Explorer'; 
		        $ub = "MSIE"; 
		    } 
		    elseif(preg_match('/Firefox/i',$u_agent)) 
		    { 
		        $bname = 'Mozilla Firefox'; 
		        $ub = "Firefox"; 
		    } 
		    elseif(preg_match('/Chrome/i',$u_agent)) 
		    { 
		        $bname = 'Google Chrome'; 
		        $ub = "Chrome"; 
		    } 
		    elseif(preg_match('/Safari/i',$u_agent)) 
		    { 
		        $bname = 'Apple Safari'; 
		        $ub = "Safari"; 
		    } 
		    elseif(preg_match('/Opera/i',$u_agent)) 
		    { 
		        $bname = 'Opera'; 
		        $ub = "Opera"; 
		    } 
		    elseif(preg_match('/Netscape/i',$u_agent)) 
		    { 
		        $bname = 'Netscape'; 
		        $ub = "Netscape"; 
		    } 
		    
		    // finally get the correct version number
		    $known = array('Version', $ub, 'other');
		    $pattern = '#(?<browser>' . join('|', $known) .
		    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		    if (!preg_match_all($pattern, $u_agent, $matches)) {
		        // we have no matching number just continue
		    }
		    
		    // see how many we have
		    $i = count($matches['browser']);
		    if ($i != 1) {
		        //we will have two since we are not using 'other' argument yet
		        //see if version is before or after the name
		        if (strripos($u_agent,"Version") < strripos($u_agent,$ub))
		        {
				$version		=	$matches['version'][0];
		        }
		        else {
		        	if ( key_exists( 1, $matches['version'] ) )
		        	{
					$version	=	$matches['version'][1];
		        	}
		        	else
		        	{
		        		$version	=	9999;
		        	}
		        }
		    }
		    else {
		        $version= $matches['version'][0];
		    }
		    
		    // check if we have a number
		    if ($version==null || $version=="") {$version="?";}
		}
		else
		{
			$u_agent	=	NULL;
			$bname		=	NULL;
			$version	=	NULL;
			$platform	=	NULL;
			$pattern	=	NULL;
		}
		return array	(
			        'userAgent' => $u_agent,
			        'name'      => $bname,
			        'version'   => $version,
			        'platform'  => $platform,
			        'pattern'    => $pattern
				);
	} 
	public function test_browser()
	{
		$browser		=	$this->getBrowser();
		$this->CI->load->vars	( array	(
							 'browser_name'		=>	$browser[ 'name' ]
							,'browser_version'	=>	$browser[ 'version' ]
						)
					);

		$version		=	(int) $browser[ 'version' ];
		if ( ( $browser[ 'name' ] == 'Internet Explorer'
		&&     $version < 8
		     )
		||   ( $browser[ 'name' ] == 'Google Chrome'
		&&     $version < 10
		     )
		||   ( $browser[ 'name' ] == 'Mozilla Firefox'
		&&     $version < 3.6
		     )
		||   ( $browser[ 'name' ] == 'Apple Safari'
		&&     $version < 5
		     )
		||   ( $browser[ 'name' ] == 'Opera'
		&&     $version < 10
		     )
		||   ( $browser[ 'name' ] == 'Netscape'
		&&     $version < 999
		     )
		)
		{
//print_r( $browser );
//echo '<br/>Name='.$browser[ 'name' ].'<br/>';
//echo 'Version='.$browser[ 'version' ].'<br/>';
			$this->CI->load->vars( array( 'browser_to_old'		=>	TRUE ) );
			return FALSE;
		}
		$this->CI->load->vars( array( 'browser_to_old'			=>	FALSE ) );
		return TRUE;
	}

	/**
	 * Envia e-mail
	 */
	public function send_email( $email, $assunto, $texto )
	{
		if ( !$this->email_initialized )
		{
			$this->CI->load->library( 'email' );
			
			$ar_init_email			=	array	(
									 'useragent'		=>	$this->CI->config->item( 'useragent' )
									,'protocol'		=>	$this->CI->config->item( 'protocol' )
									,'mailpath'		=>	$this->CI->config->item( 'mailpath' )
									,'smtp_host'		=>	$this->CI->config->item( 'smtp_host' )
									,'smtp_user'		=>	$this->CI->config->item( 'smtp_user' )
									,'smtp_pass'		=>	$this->CI->config->item( 'smtp_pass' )
									,'smtp_port'		=>	$this->CI->config->item( 'smtp_port' )
									,'smtp_timeout'		=>	$this->CI->config->item( 'smtp_timeout' )
									,'wordwrap'		=>	$this->CI->config->item( 'wordwrap' )
									,'wrapchars'		=>	$this->CI->config->item( 'wrapchars' )
									,'mailtype'		=>	$this->CI->config->item( 'mailtype' )
									,'charset'		=>	$this->CI->config->item( 'charset' )
									,'validate'		=>	$this->CI->config->item( 'validate' )
									,'priority'		=>	$this->CI->config->item( 'priority' )
									,'crlf'			=>	$this->CI->config->item( 'crlf' )
									,'newline'		=>	$this->CI->config->item( 'newline' )
									,'bcc_batch_mode'	=>	$this->CI->config->item( 'bcc_batch_mode' )
									,'bcc_batch_size'	=>	$this->CI->config->item( 'bcc_batch_size' )
									);
//print_r( $ar_init_email );
			$this->CI->email->initialize( $ar_init_email );
		}

		// é email válido?
		if ( preg_match( "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email ) )
		{

			$this->CI->email->from( $this->CI->config->item( 'from_email' ), $this->CI->config->item( 'from_email_name' ) );
			$this->CI->email->to( $email );

//			$this->CI->email->cc( 'another@another-example.com' );
//			$this->CI->email->bcc( 'them@their-example.com' );

			$this->CI->email->subject( $assunto );
			$this->CI->email->message( $texto );
			
			return $this->CI->email->send();

//			echo $this->CI->email->print_debugger();
		}
		else
		{
			return FALSE;
		}
		
	}
	
	/**
	 * Comunicação com via facebook.
	 */
	public function send_facebook_notification( $user_facebook_id, $destino, $message, $ref = NULL )
	{
		$this->set_new_facebook();

		$notification	=	array	(
						 'access_token'	=>	$this->facebook->getAppId() . '|' . $this->facebook->getApiSecret()
						,'href'		=>	'?notification='.$destino
						,'template'	=>	$message
						,'ref'		=>	$ref
						);
		try	{
				$fb_response	=	$this->facebook->api( '/' . $user_facebook_id . '/notifications', 'POST', $notification );
				return TRUE;
			}
		catch ( FacebookApiException $e )
			{
//				print_r( $e );
				return $e;
			}
	}

	/**
	 * 
	 * Conta e retorna a qtde de campos de uma página. É usado pelo JX_Field.
	 */
	public function get_count_field()
	{
		$this->count_fields += 1;
		return $this->count_fields;
	}
	
	/**
	 * 
	 * Permiter regisrar na sessão o ID do campeonato selecionado pelo usuário. Isso será usado para que na troca de página o campeonato permaneça.
	 * @param unknown_type $campeonato_versao_id
	 */
	public function set_sessao( $oque, $valor )
	{
		$this->CI->session->set_userdata( $oque, $valor );
		$_SESSION[ $oque ]	=	$valor;
	}
	public function unset_sessao( $oque )
	{
		$this->CI->session->unset_userdata( $oque );
		$_SESSION[ $oque ]	=	NULL;
	}
	public function get_sessao( $oque )
	{
		return $this->CI->session->userdata( $oque );
	}
}

/* End of file Singlepack.php */
/* Location: /application/libraries/Singlepack.php */