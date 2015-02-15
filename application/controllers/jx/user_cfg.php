<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Jarvix Plus
 *
 *     Controller de Configuração de Usuários.
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Jarvix Plus.
 * @license		http://jarvixplus.com/license.html
 * @link		http://jarvixplus.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/user_cfg.php
 * 
 * $Id: user_cfg.php,v 1.9 2013-03-10 20:02:48 junior Exp $
 * 
 */

class User_cfg extends JX_Page
{
	protected $_revision	=	'$Id: user_cfg.php,v 1.9 2013-03-10 20:02:48 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'user_cfg'			=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	'user'
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'hide_columns'		=>	'data_autorizacao_facebook'
													,'readonly_columns'	=>	'user_id,theme,lines_per_page,idioma'
													,'where'		=>	'user_cfg.user_id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'pessoa'			=>	array	(
													 'read_write'		=>	'read'
													,'r_table_name'		=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'hide_columns'		=>	'fone_1,fone_2,data_hora_inscricao,data_hora_ultima_atualizacao,data_hora_ultimo_chute'
													,'readonly_columns'	=>	'email,nome,sobrenome,aniversario,sexo'
													,'seq_columns'		=>	'imagem_facebook'
													,'where'		=>	'pessoa.id in( select usr.pessoa_id from user usr where usr.id = ##id## )'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'user'				=>	array	(
													 'read_write'		=>	'read'
													,'r_table_name'		=>	'pessoa'
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'hide_columns'		=>	'ativo,password'
													,'readonly_columns'	=>	'id_facebook,username'
													,'where'		=>	'user.id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													,'master'		=>	TRUE
													)
							);
		
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
	
	public function profile()
	{
		$this->_prep_edit( $this->singlepack->get_user_cfg()->user_id );
//		$this->_prep_edit( $this->singlepack->get_user_cfg()->id, 'DIALOG' );
		
/*		if ( $this->grid == 'TRUE'  )
		{
			$this->load->view( 'jx/edit_grid.html' );
		}
		elseif ( $this->print == 'TRUE'  )
		{
			$this->load->view( 'jx/edit.html' );
		}
		else
		{
			$this->load->view( 'jx/edit.html' );
		}
*/
		
		if ( $this->grid == 'TRUE'  )
		{
			if ( file_exists( '../application/views/user_cfg_edit.html' ) )
			{
				$this->load->view( 'user_cfg_edit.html' );
			}
			else
			{
				$this->load->view( 'jx/user_cfg_edit.html' );
			}
		}
		elseif ( $this->print == 'TRUE'  )
		{
			if ( file_exists(  '../application/views/user_cfg_edit.html' ) )
			{
				$this->load->view( 'user_cfg_edit.html' );
			}
			else
			{
				$this->load->view( 'jx/user_cfg_edit.html' );
			}
		}
		else
		{
			if ( file_exists(  '../application/views/user_cfg_edit.html' ) )
			{
				$this->load->view( 'user_cfg_edit.html' );
			}
			else
			{
				$this->load->view( 'jx/user_cfg_edit.html' );
			}
		}
	}
}
/* End of file user.php */
/* Location: /application/controllers/user.php */
