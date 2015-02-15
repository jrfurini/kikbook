<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jarvix Plus
 *
 *	Users Model
 *
 * @package		Jarvix Plus / Main
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/models/user_model.php
 * 
 * $Id: user_model.php,v 1.12 2012-11-02 12:50:31 junior Exp $
 * 
 */

class User_model extends JX_Model
{
	protected $_revision	=	'$Id: user_model.php,v 1.12 2012-11-02 12:50:31 junior Exp $';
	
	var $user_id;
	var $_is_admin;
	
	function __construct()
	{
		$_config		=	array	(
							 'pessoa'			=>	array	(
													 'model_name'	=>	'pessoa'
													)
							,'user_cfg'			=>	array	(
													 'model_name'	=>	'user_cfg'
													)
							,'user_profile'			=>	array	(
													 'model_name'	=>	'user_profile'
													)
							);
		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}
	
	/**
	 * Retorna os dados do usuário a partir do e-mail/id/id_facebook informado.
	 */
	public function get_by_email( $email )
	{
		$query		=	$this
						->db
						->query( "	select	 pes.email
									,pes.nome
									,pes.sobrenome
									,concat( pes.nome, ' ', pes.sobrenome ) AS nome_completo
									,pes.aniversario
									,pes.sexo
									,pes.fone_1
									,pes.fone_2
									,pes.id						AS pessoa_id
									,pes.imagem_facebook
									,usr.username
									,usr.ativo
									,usr.password
									,usr.id						AS user_id
									,img.imagem_id
									,usr.id_facebook				AS id_facebook
								from	   pessoa		AS	pes
								inner join user			AS	usr	ON	usr.pessoa_id	=	pes.id
								left join  pessoa_imagem	AS	img	ON	img.pessoa_id	=	pes.id
								where	pes.email							=	'{$email}'
							 "
							);
		if ( $query->num_rows > 0 )
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}
	public function get_by_id( $id )
	{
		$query		=	$this
						->db
						->query( "	select	 pes.email
									,pes.nome
									,pes.sobrenome
									,concat( pes.nome, ' ', pes.sobrenome ) AS nome_completo
									,pes.aniversario
									,pes.sexo
									,pes.fone_1
									,pes.fone_2
									,pes.id						AS pessoa_id
									,pes.imagem_facebook
									,usr.username
									,usr.ativo
									,usr.password
									,usr.id						AS user_id
									,img.imagem_id
									,usr.id_facebook				AS id_facebook
								from	   user			AS	usr
								inner join pessoa		AS	pes	ON	pes.id		=	usr.pessoa_id
								left join  pessoa_imagem	AS	img	ON	img.pessoa_id	=	pes.id
								where	usr.id								=	'{$id}'
							 "
							);
		if ( $query->num_rows > 0 )
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}
	public function get_by_facebook_id( $id )
	{
		$query		=	$this
						->db
						->query( "	select	 pes.email
									,pes.nome
									,pes.sobrenome
									,concat( pes.nome, ' ', pes.sobrenome ) AS nome_completo
									,pes.aniversario
									,pes.sexo
									,pes.fone_1
									,pes.fone_2
									,pes.id						AS pessoa_id
									,pes.imagem_facebook
									,usr.id_facebook
									,usr.username
									,usr.ativo
									,usr.password
									,usr.id						AS user_id
								from	   pessoa	AS	pes
								inner join user		AS	usr	ON	usr.pessoa_id	=	pes.id
								where	usr.id_facebook						=	'{$id}'
							 "
							);
		if ( $query->num_rows > 0 )
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Retorna a configuração do usuário.
	 */
	public function get_cfg( $user_id )
	{
		$query		=	$this
						->db
						->where( 'user_id', $user_id )
						->limit( 1 )
						->get( 'user_cfg' );
		if ( $query->num_rows > 0 )
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Retorna a configuração do usuário.
	 */
	public function is_admin()
	{
		return $this->_is_admin;
	}

	public function get_access( $user_id )
	{
		$access_ret				=	array();
		
		// Verifica se o usuário é ADMIN.
		$this->_is_admin			=	FALSE;
		$admin_select				=	"
								select	prf.admin
								from		user			AS	usr
								inner join	user_profile		AS	usrprf	ON	usrprf.user_id			=	usr.id
								inner join	profile			AS	prf	ON	prf.id				=	usrprf.profile_id
															AND	prf.admin			=	'S'
								where	usr.id			=	'{$user_id}'
								";
		foreach( $this->db->query( $admin_select )->result_object() as $row )
		{
			if ( $row->admin == 'S' )
			{
				$this->_is_admin	=	TRUE;
				break;
			}
		}

		if ( $this->_is_admin )
		{
			$select				=	"
								select	 ctrl.prg_controller
									,meth.prg_controller_method
									,ctrl.sistema_id
									,meth.sistema_ctrl_id
									,meth.id			AS	sistema_ctrl_meth_id
									,sis.nome			AS	nome_sistema
									,sis.descr			AS	descr_sistema
									,ctrl.nome			AS	nome_controller
									,ctrl.descr			AS	descr_controller
									,meth.nome			AS	nome_method
									,meth.descr			AS	descr_method
									,sis.seq_exibicao		AS	seq_exibicao_sistema
									,ctrl.seq_exibicao		AS	seq_exibicao_controller
									,meth.seq_exibicao		AS	seq_exibicao_method
									,'C'				AS	access
								from		sistema			AS	sis
								left join	sistema_ctrl		AS	ctrl	ON	ctrl.sistema_id		=	sis.id
								left join 	sistema_ctrl_meth	AS	meth	ON	meth.sistema_ctrl_id	=	ctrl.id
								order by 12, 13, 14
								";
		}
		else
		{
			// Métodos, páginas, liberadas para todos, inclusive não conectados.
			$select				=	"
								select	 ctrl.prg_controller
									,meth.prg_controller_method
									,ctrl.sistema_id
									,meth.sistema_ctrl_id
									,meth.id			AS	sistema_ctrl_meth_id
									,sis.nome			AS	nome_sistema
									,sis.descr			AS	descr_sistema
									,ctrl.nome			AS	nome_controller
									,ctrl.descr			AS	descr_controller
									,meth.nome			AS	nome_method
									,meth.descr			AS	descr_method
									,sis.seq_exibicao		AS	seq_exibicao_sistema
									,ctrl.seq_exibicao		AS	seq_exibicao_controller
									,meth.seq_exibicao		AS	seq_exibicao_method
									,'C'				AS	access
								from	sistema				AS	sis
								join	sistema_ctrl			AS	ctrl	ON	( ctrl.sistema_id		=	sis.id
																)
								join 	sistema_ctrl_meth		AS	meth	ON	( meth.sistema_ctrl_id		=	ctrl.id
															and	  ( meth.liberado_user_pessoa	=	'S'
															or	    meth.prg_controller_method	=	'autocomplete'
															or	    ( ctrl.liberado_user_pessoa	=	'S'
															or	      sis.liberado_user_pessoa	=	'S'
																    )
																  )
																)
								";

			if ( $user_id ) // não é ANONYMOUS
			{
				$select			=	$select.
								" UNION
								select	 ctrl.prg_controller
									,meth.prg_controller_method
									,ctrl.sistema_id
									,meth.sistema_ctrl_id
									,meth.id			AS	sistema_ctrl_meth_id
									,sis.nome			AS	nome_sistema
									,sis.descr			AS	descr_sistema
									,ctrl.nome			AS	nome_controller
									,ctrl.descr			AS	descr_controller
									,meth.nome			AS	nome_method
									,meth.descr			AS	descr_method
									,sis.seq_exibicao		AS	seq_exibicao_sistema
									,ctrl.seq_exibicao		AS	seq_exibicao_controller
									,meth.seq_exibicao		AS	seq_exibicao_method
									,prfmeth.access			AS	access
								from		user			AS	usr
								inner join	user_profile		AS	usrprf	ON	usrprf.user_id			=	usr.id
								inner join	profile_meth		AS	prfmeth	ON	prfmeth.profile_id		=	usrprf.profile_id
								inner join	sistema_ctrl_meth	AS	meth	ON	meth.id				=	prfmeth.sistema_ctrl_meth_id
								inner join	sistema_ctrl		AS	ctrl	ON	ctrl.id				=	meth.sistema_ctrl_id
								inner join	sistema			AS	sis	ON	sis.id				=	ctrl.sistema_id
								where	usr.id				=	'{$user_id}'
								order by 12, 13, 14
								";
			}
		}

		$query				=	$this->db->query( $select ); 
		if ( $query->num_rows > 0 )
		{
			foreach( $query->result_array() as $row )
			{
				$M			=	new stdClass();
				foreach( $row as $field => $value )
				{
//echo 'field='.$field.' value='.$value.'<br/>';
					$M->$field	=	$value;
				}

				/* Acesso a ser testado.
				 * 	1 - Não pode nada
				 * 	2 - Pode consultar
				 * 	3 - Pode Alterar/Deletar e etc.
				 */ 
				$M->access_level	=	( $M->access == 'T' ) ? 3 : ( ( $M->access == 'C' ) ? 2 : 1 );
				$key			=	$M->prg_controller.'.'.$M->prg_controller_method;
				$access_ret[ $key ]	=	$M;
//echo 'key='.$key.'<br/>';
				unset( $M );
			}
			return $access_ret;
		}
		else
		{
			return array();//FALSE;
		}
	}

	public function get_system_granted( $user_id )
	{
		$access_ret			=	array();
		
		// Verifica se o usuário é ADMIN.
		$this->_is_admin		=	FALSE;
		$admin_select			=	"
							select	prf.admin
							from		user			AS	usr
							inner join	user_profile		AS	usrprf	ON	usrprf.user_id			=	usr.id
							inner join	profile			AS	prf	ON	prf.id				=	usrprf.profile_id
														AND	prf.admin			=	'S'
							where	usr.id			=	'{$user_id}'
							";
		foreach( $this->db->query( $admin_select )->result_object() as $row )
		{
			if ( $row->admin == 'S' )
			{
				$this->_is_admin	=	TRUE;

				log_message('debug', "Singlepack.get_system_granted (É ADMIN)." );
				
				break;
			}
		}

		if ( $this->_is_admin )
		{
			$select				=	"
								select	 sis.nome			AS	nome_sistema
									,sis.id				AS	sistema_id
									,sis.descr			AS	descr_sistema
									,sis.seq_exibicao		AS	seq_exibicao_sistema
									,sis.prg_controller		AS	system_controller
									,2				AS	access
								from		sistema			AS	sis
								";
		}
		else
		{
			// Métodos, páginas, liberadas para todos, inclusive não conectados.
			$select				=	"
								select	 sis.nome			AS	nome_sistema
									,sis.id				AS	sistema_id
									,sis.descr			AS	descr_sistema
									,sis.seq_exibicao		AS	seq_exibicao_sistema
									,sis.prg_controller		AS	system_controller
									,2				AS	access
								from		sistema			AS	sis
								where	sis.liberado_user_pessoa	=	'S'
								";

			if ( $user_id ) // não é ANONYMOUS
			{
				$select			=	$select.
								" UNION
								select	 sis.nome			AS	nome_sistema
									,sis.id				AS	sistema_id
									,sis.descr			AS	descr_sistema
									,sis.seq_exibicao		AS	seq_exibicao_sistema
									,sis.prg_controller		AS	system_controller
									,prfmeth.access			AS	access
								from		user			AS	usr
								inner join	user_profile		AS	usrprf	ON	usrprf.user_id			=	usr.id
								inner join	profile_meth		AS	prfmeth	ON	prfmeth.profile_id		=	usrprf.profile_id
								inner join	sistema_ctrl_meth	AS	meth	ON	meth.id				=	prfmeth.sistema_ctrl_meth_id
								inner join	sistema_ctrl		AS	ctrl	ON	ctrl.id				=	meth.sistema_ctrl_id
								inner join	sistema			AS	sis	ON	sis.id				=	ctrl.sistema_id
								where	usr.id				=	'{$user_id}'
								";
			}
		}

		$query				=	$this->db->query( $select ); 
		if ( $query->num_rows > 0 )
		{
			foreach( $query->result_array() as $row )
			{
				$M			=	new stdClass();
				foreach( $row as $field => $value )
				{
//echo 'field='.$field.' value='.$value.'<br/>';
					$M->$field	=	$value;
				}
				$key			=	$M->system_controller;
				$access_ret[ $key ]	=	$M;
//echo 'key='.$key.'<br/>';
				unset( $M );
			}
			return $access_ret;
		}
		else
		{
			return array();//FALSE;
		}
	}
/*
	function select_all( $where = null, $orderby = null, $row_offset = null, $row_count = null )
	{
		$this->_prep_query( $where, $orderby, $row_offset, $row_count );

		$this->query = $this->db->get( $this->table );

		return $this->query;
	}
*/
	public function get_select_for_index()
	{
		return	"
			 user.*
			,concat( pes.nome, ' ', pes.sobrenome )			AS	nome_completo
			,pes.email						AS	email
			,pes.sexo						AS	sexo
			,img.imagem_id						AS	imagem_id
			,concat( pes.nome, ' ', pes.sobrenome )			AS	title
			,now()							AS	when_field
			,img.imagem_id						AS	imagem_id
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'user' );
		$this->db->join( 'pessoa	AS	pes', 'pes.id = user.pessoa_id', 'LEFT' );
		$this->db->join( 'pessoa_imagem	AS	img', 'img.pessoa_id = pes.id', 'LEFT' );
	}

	public function get_column_title()
	{
		return "concat( pes.nome, ' ', pes.sobrenome, ' (', pes.email, ')' )";
	}

	/**
	 * Cria um novo usuário a partir do FACEBOOK
	 */
	public function create_by_facebook( $user_profile )
	{
//TODO: Criar o código usar o "favorite_teams".
//echo "user->id=".$user_profile['id'].'<br/>';
		
		if ( $user_profile )
		{
			//ID
			if ( array_key_exists( 'id', $user_profile ) )
			{
				$facebook_id		=	$user_profile[ 'id' ];
			}
			else
			{
				$facebook_id		=	'';
			}
	
			//EMAIL
			if ( array_key_exists( 'email', $user_profile ) )
			{
				$email			=	$user_profile[ 'email' ];
			}
			else
			{
				$email			=	$facebook_id;// E-MAIL é obrigatório.
			}
	
			//Primeiro NOME
			if ( array_key_exists( 'first_name', $user_profile ) )
			{
				$nome			=	$user_profile[ 'first_name' ];
			}
			else
			{
				$nome			=	'';
			}
	
			//SOBRENOME
			if ( array_key_exists( 'last_name', $user_profile ) )
			{
				$sobrenome		=	$user_profile[ 'last_name' ];
			}
			else
			{
				$sobrenome		=	'';
			}
	
			//SEXO
			if ( array_key_exists( 'gender', $user_profile ) )
			{
				$sexo			=	($user_profile[ 'gender' ] == 'male') ? 'M' : 'F';
			}
			else
			{
				$sexo			=	'M';
			}
	
			//FOTO
			if ( array_key_exists( 'picture', $user_profile ) )
			{
				if ( is_object( $user_profile[ 'picture' ] ) )
				{
								
					$foto		=	$user_profile[ 'picture' ]->data->url;
				}
				else
				{
					$foto		=	$user_profile[ 'picture' ]['data']['url'];
				}
			}
			else
			{
				$foto			=	"http://graph.facebook.com/{$facebook_id}/picture";
			}
			
			//Aniversario
			if ( array_key_exists( 'birthday', $user_profile ) )
			{
				$birthday_split		=	explode( '/', $user_profile[ 'birthday' ] );//'MM/DD/YYYY'
				$_date			=	new DateTime( $birthday_split['2'] .'-'. $birthday_split['0'] .'-'. $birthday_split['1'] .' 00:00 GMT' );
				$aniversario		=	$_date->format( 'Y-m-d H:i:s' );
			}
			else
			{
				$aniversario		=	'';
			}
	
			//Username
			if ( array_key_exists( 'username', $user_profile ) )
			{
				$username		=	$user_profile[ 'username' ];
			}
			else
			{
				$username		=	$facebook_id;
			}
	
			//Username
			if ( array_key_exists( 'locale', $user_profile ) )
			{
				$idioma			=	$user_profile[ 'locale' ];
			}
			else
			{
				$idioma			=	'pt_BR';
			}
			/*
			 * TABELA USER
			 */
			$user_data			=	$this->get_one_by_where( "id_facebook = {$facebook_id}" );
			if ( is_object( $user_data ) )
			{
				$user_id		=	$user_data->id;
				$pessoa_id		=	$user_data->pessoa_id;
			}
			else
			{
				$user_id		=	NULL;
				$pessoa_id		=	NULL;
			}

			/*
			 * TABELA PESSOA
			 */
//			$pessoa_data			=	$this->pessoa->get_one_by_where( "email = '{$email}'" );
/*			if ( is_object( $pessoa_data ) )
			{
				$pessoa_id		=	$pessoa_data->id;
			}
			else
			{
				$pessoa_id		=	NULL;
			}
*/
			if ( $pessoa_id )
			{
				$pessoa_data			=	array	(
										 'id'				=>	$pessoa_id
										,'email'			=>	$email // UK
										,'nome'				=>	$nome
										,'sobrenome'			=>	$sobrenome
										,'aniversario'			=>	$aniversario
										,'sexo'				=>	$sexo
										,'fone_1'			=>	''
										,'fone_2'			=>	''
										,'imagem_facebook'		=>	( $foto ) ? $foto : 'http://profile.ak.fbcdn.net/static-ak/rsrc.php/v2/yo/r/UlIqmHJn-SK.gif'
										,'data_ultima_atualizacao'	=>	'CURRENT_TIMESTAMP'
										);
			}
			else
			{
				$pessoa_data			=	array	(
										 'id'				=>	$pessoa_id
										,'email'			=>	$email // UK
										,'nome'				=>	$nome
										,'sobrenome'			=>	$sobrenome
										,'aniversario'			=>	$aniversario
										,'sexo'				=>	$sexo
										,'fone_1'			=>	''
										,'fone_2'			=>	''
										,'imagem_facebook'		=>	( $foto ) ? $foto : 'http://profile.ak.fbcdn.net/static-ak/rsrc.php/v2/yo/r/UlIqmHJn-SK.gif'
										,'data_inscricao'		=>	'CURRENT_TIMESTAMP'
										);
			}
			$pessoa_id = $this->pessoa->update( $pessoa_data );
	
			$user_data			=	array	(
									 'id'				=>	$user_id
									,'pessoa_id'			=>	$pessoa_id
									,'username'			=>	$username // UK
									,'ativo'			=>	'S'
									,'password'			=>	sha1( $username )
									,'id_facebook'			=>	$facebook_id
									);
			$user_id = $this->update( $user_data );
	
			/*
			 * TABELA CONFIGURACAO
			 */
			$user_cfg_data			=	$this->user_cfg->get_one_by_where( "user_id = {$user_id}" );
			if ( is_object( $user_cfg_data ) )
			{
				$user_cfg_id		=	$user_cfg_data->id;
			}
			else
			{
				$user_cfg_id		=	NULL;
			}
			$user_cfg_data			=	array	(
									 'id'				=>	$user_cfg_id
									,'user_id'			=>	$user_id // UK
									,'data_autorizacao_facebook'	=>	NULL
									,'theme'			=>	'kik'
									,'lines_per_page'		=>	'30'
									,'idioma'			=>	$idioma // pt_BR
									,'lembrar_via_facebook'		=>	'S'
									,'lembrar_via_email'		=>	'S'
									);
			$this->user_cfg->update( $user_cfg_data );
			
			/*
			 * TABELA PROFILE
			 */
			$user_profile_data		=	$this->user_profile->get_one_by_where( "user_id = {$user_id}" );
			if ( is_object( $user_profile_data ) )
			{
				$user_profile_id	=	$user_profile_data->id;
				$profile_id		=	$user_profile_data->profile_id;
			}
			else
			{
				$user_profile_id	=	NULL;
				$profile_id		=	3;
			}
			$user_profile_data		=	array	(
									 'id'				=>	$user_profile_id
									,'user_id'			=>	$user_id // UK
									,'profile_id'			=>	$profile_id
									);
			$this->user_profile->update( $user_profile_data );
			
			return $this->get_by_facebook_id( $facebook_id );
		}
		else
		{
			return NULL;
		}
	}
}

/* End of file user_model.php */