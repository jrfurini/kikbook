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
 * @filesource		/application/controllers/regra.php
 * 
 * $Id: regra.php,v 1.6 2013-03-02 14:37:24 junior Exp $
 * 
 */

class Regra extends JX_Page
{
	protected $_revision	=	'$Id: regra.php,v 1.6 2013-03-02 14:37:24 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'kick'					=>	array	(
														 'read_write'		=>	'readonly'
														,'master_table'		=>	TRUE
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	TRUE
														)
							,'campeonato_versao'			=>	array	(
														 'read_write'		=>	'readonly'
														,'master_table'		=>	FALSE
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	TRUE
														)
							,'campeonato_versao_classificacao'	=>	array	(
														 'read_write'		=>	'readonly'
														,'master_table'		=>	FALSE
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	TRUE
														)
							);
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	/**
	 * Página princial do site.
	 */
	public function index()
	{
		$this->rules();
	}

	public function privacy()
	{
		$data						=	array	(
										 'kiker_info'			=> $this->kick->kiker_info()
										,'rows_campeonato'		=> $this->campeonato_versao->get_campeonato_selecao()
										,'campeonato_versao_atual'	=> $this->campeonato_versao->get_one_by_id( NULL )
										);
		$this->load->vars( $data );

		$this->load->view( 'regra_privacy.html' );
	}
	
	public function terms()
	{
		$data						=	array	(
										 'kiker_info'			=> $this->kick->kiker_info()
										,'rows_campeonato'		=> $this->campeonato_versao->get_campeonato_selecao()
										,'campeonato_versao_atual'	=> $this->campeonato_versao->get_one_by_id( NULL )
										);
		$this->load->vars( $data );
		
		$this->load->view( 'regra_terms.html' );
	}

	public function rules()
	{
		$data						=	array	(
										 'kiker_info'			=> $this->kick->kiker_info()
										,'rows_campeonato'		=> $this->campeonato_versao->get_campeonato_selecao()
										,'campeonato_versao_atual'	=> $this->campeonato_versao->get_one_by_id( NULL )
										);
		$this->load->vars( $data );
		
		$this->load->view( 'regra_rules.html' );
	}
	
	public function kiks()
	{
		$data						=	array	(
										 'kiker_info'			=> $this->kick->kiker_info()
										,'rows_campeonato'		=> $this->campeonato_versao->get_campeonato_selecao()
										,'campeonato_versao_atual'	=> $this->campeonato_versao->get_one_by_id( NULL )
										);
		$this->load->vars( $data );
		
		$this->load->view( 'regra_kiks.html' );
	}
	
	public function support()
	{
		$data						=	array	(
										 'kiker_info'			=> $this->kick->kiker_info()
										,'rows_campeonato'		=> $this->campeonato_versao->get_campeonato_selecao()
										,'campeonato_versao_atual'	=> $this->campeonato_versao->get_one_by_id( NULL )
										);
		$this->load->vars( $data );
		
		$this->load->view( 'regra_support.html' );
	}
}
/* End of file regra.php */
/* Location: /application/controllers/regra.php */
