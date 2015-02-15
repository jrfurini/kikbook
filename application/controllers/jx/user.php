<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Jarvix Plus
 *
 *     Controller de Usuários.
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Jarvix Plus.
 * @license		http://jarvixplus.com/license.html
 * @link		http://jarvixplus.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/user.php
 * 
 * $Id: user.php,v 1.5 2013-04-14 12:44:43 junior Exp $
 * 
 */

class User extends JX_Page
{
	protected $_revision	=	'$Id: user.php,v 1.5 2013-04-14 12:44:43 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'imagem'			=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	''
													,'show'		=>	TRUE
													,'show_style'	=>	'form'
													,'hide_columns'	=>	'mime_type,file_extension'
													,'where'	=>	'imagem.id in ( select pesimg.imagem_id from pessoa_imagem pesimg where pesimg.pessoa_id = ##id## )'
													,'orderby'	=>	''
													,'max_rows'	=>	1
													,'delete_rule'	=>	'restrict'
													)
							,'pessoa'			=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	''
													,'show'		=>	TRUE
													,'show_style'	=>	'form'
													,'max_rows'	=>	1
													,'master'	=>	TRUE
													,'where'	=>	'pessoa.id = ##id##'
													,'orderby'	=>	''
													)
							,'user'				=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	'pessoa'
													,'show'		=>	TRUE
													,'show_style'	=>	'form'
													,'hide_columns'	=>	'id_facebook'
													,'max_rows'	=>	1
													,'master'	=>	FALSE
													,'where'	=>	'user.pessoa_id = ##id##'
													,'orderby'	=>	''
													)
							,'pessoa_imagem'		=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	'pessoa,imagem'
													,'show'		=>	TRUE
													,'show_style'	=>	'form'
													,'where'	=>	'pessoa_imagem.pessoa_id = ##id##'
													,'orderby'	=>	''
													,'max_rows'	=>	1
													,'delete_rule'	=>	'cascade'
													)
							,'user_cfg'			=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	'user'
													,'show'		=>	TRUE
													,'max_rows'	=>	1
													,'where'	=>	'user_cfg.user_id = ( select user.id from user where user.pessoa_id = ##id## )'
													,'orderby'	=>	''
													)
							,'pessoa_convite'		=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	''
													,'show'		=>	TRUE
													,'max_rows'	=>	1
													,'where'	=>	'pessoa_convite.pessoa_id = ##id##'
													,'orderby'	=>	''
													)
							);
		
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	/**
	 * Cria um novo usuário via página.
	 */
	public function create_by_page()
	{
		/*
		INSERT INTO
			pessoa
			user
			user_cfg
			imagem
			pessoa_imagem
		*/
		$this->load->view( $this->config->item( 'start_page' ) );
	}
	
	/**
	 * Gera uma nova senha e envia ao e-mail solicitante.
	 */
	public function reminder_password()
	{
		$this->load->view( $this->config->item( 'start_page' ) );
	}
	
	/**
	 * Bloqueia um usuário.
	 */
	public function block_user( $email )
	{
		// TODO: set ativo = 'N'
		return null;
	}
	
	/**
	 * Atualiza via facebook. Webservice
	 */
	public function faceupdate()
	{
		$hub_mode			=	$this->input->get_multi( 'hub_mode' );
		$hub_challenge			=	$this->input->get_multi( 'hub_challenge' );
		$hub_verify_token		=	$this->input->get_multi( 'hub_verify_token' );

		// Pegando os dados alterados.
//		$data = file_get_contents("php://input");
//		$json = json_decode($data);

//echo "mode=$hub_mode chal=$hub_challenge token=$hub_verify_token";
//Response does not match challenge, expected value = '2066742490', received='mode=subscribe chal=...
log_message('error', "FACEBOOK UPDATE.");

		if ( $hub_verify_token == $this->config->item( 'facebook_update_token' ) )
		{
			echo $hub_challenge;
		}
		else
		{
			echo 'fail';
		}
	}
	
	/**
	 * Registra o envio de convite para jogar.
	 */
	public function invite()
	{
		$req_id					=	$this->input->get_post_multi( 'reqId' );
		$ar_user				=	$this->input->get_post_multi( 'users' );
		$pessoa_id				=	$this->singlepack->get_pessoa_id();
		$ret					=	array();

		if ( $req_id
		&&   is_array( $ar_user )
		&&   $pessoa_id
		   )
		{
			foreach( $ar_user as $user_id )
			{
				$obj_convite				=	new stdClass();
				$obj_convite->id			=	NULL;
				$obj_convite->data_hora			=	'CURRENT_TIMESTAMP';
				$obj_convite->pessoa_id			=	$pessoa_id;
				$obj_convite->facebook_id_convidado	=	$user_id;
				$obj_convite->facebook_id_apprequest	=	$req_id;
				$obj_convite->data_hora_retorno		=	NULL;
				
				$obj_convite->id			=	$this->pessoa_convite->update( $obj_convite );
				$ret[]					=	$obj_convite;
			}
		}
		echo json_encode( $ret );
	}
}
/* End of file user.php */
/* Location: /application/controllers/user.php */
