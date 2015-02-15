<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Esta classe extende o Controller do CodeIgniter. Não use esta classe direto, escolha entre: JX_Page para páginas ou JX_Process para processos.
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/core/JX_Controller.php
 *
 * $Id: JX_Controller.php,v 1.36 2013-04-07 13:24:14 junior Exp $
 *
 */
$main_controller				=	NULL;

class JX_Controller extends CI_Controller
{
	/**
	 * Variáveis de controle de multi-tabelas.
	 */
	var $tables				=	array();
	var $master_table			=	'';
	var $master_model_str			=	'';
	public $master_model;

	/**
	 * Construtor da classe.
	 */
	public function __construct( $_config_table = NULL, $_config_visual = NULL )
	{
		log_message( 'debug', "JX_Controller.(start)." );
		parent::__construct();

		global $main_controller;
		$main_controller			=	$this;
		
		/**
		 * Controle de sistema e permissões.
		 */
		$config_single				=	array	(
									 'prg_controller'		=>	$this->router->class
									,'prg_controller_method'	=>	$this->router->method
									);

		$this->load->library( 'singlepack' );
		$this->singlepack->initialize( $config_single );
		$this->load->library( 'JX_Ads', null, 'ads' );
//		$this->ads = new JX_Ads();
		
		// Verifica se o browser do usuário é muito antigo.
		if ( get_parent_class( $this->router->class ) != 'JX_Process'
		&&   $this->router->method != 'batch' // Não testamos paro processos em background.
		&&   !$this->singlepack->test_browser()
		   )
		{
			include( APPPATH.'views/jx/browser_to_old.html' );
			exit;
		}
		if ( $this->singlepack->get_facebook_login_status() == 3 )
		{
			include( APPPATH.'views/jx/facebook_login_fail.html' );
			exit;
		}
		
		/**
		 * O comando abaixo tem que ser o primeiro do __construct para que o construtor do "pai" receba a informação.
		 * 	Coloque as tabelas na ordem em que devem ser gravadas.
		 * 	- Para excluir usaremos a ordem contrária.
		 * 
		 * Atributos:
		 * 	- name			- Nome da tabela.
		 * 
		 * 	- model_name		- Apelido do model que tratará a tabela.
		 * 
		 * 	- master		- Indica se a tabela é a tabela principal do controller.
		 * 				- Normalmente apenas a tabela do controller assume este valor.
		 * 
		 * 	- read_write
		 * 		- write		- Tabelas que serão lidas e gravadas.
		 * 		- read		- Tabelas com este método será carrega apenas para que possamos ler para auxiliar a página, mas não serão usadas para atualização de dados.
		 * 
		 * 	- r_table_name		- Nome da tabela relacionada. Usamos a última enviada para associar caso seja omitido.
		 * 				- Havendo mais de uma, basta separar por , (virgula).
		 * 				- Usaremos o formato <table_name>_id para criar a ligação entre as tabelas.
		 * 
		 * 	- delete_rule		- Indica se devemos usar um delete cascade ou restrict em caso de exclusão do pai.
		 * 					- cascade	- apaga as linhas
		 * 					- restrict	- evita que o pai seja apagado
		 * 					- setnull	- atualiza as linhas dos filhos com nulo.
		 * 
		 * 	- show			- TRUE ou FALSE indica se devemos carregar as linhas da tabela para uso na página usando o where e o order by.
		 * 	- show_style		- Determina o estilo de exibição dos dados da tabela.
		 * 					- form, estilo padrão;
		 * 					- grid, coloca as colunas em linha só.
		 * 					- none, não exibe a tabela para edição.
		 * 
		 * 	- hide_columns		- Array de colunas que não devem ser exibidas.
		 * 
		 * 	- seq_columns		- Array de colunas que serão exibidas na sequencia de exibição.
		 * 
		 * 	- max_rows		- "N"úmero de linhas a serem retornadas pela tabela.
		 * 
		 * 	- where			- Contém, se necessário, o where para restringir a consulta da tabela.
		 * 
		 * 	- orderby		- Contém, se necessário, a ordenação da tabela.
		 * 
//TODO:		 * 	- orderby_index		- Contém a coluna que será usada por padrão para ordenar a página de INDEX.
		 * 
		 * 	- edit_html		- edit.html
		 * 
		 * 	- edit_form_html	- edit_form_style.html
		 * 
		 * 	- edit_grid_html	- edit_grid-style.html
		 * 
		 * 	- force_copy_from	- Indica que a tabela, mesmo sem alteração, deve ser forçada pelo copy_from do JX_Input para ser gravada.
		 * 				  Usamos este recurso, normalmente, para tabelas que são relacionamento entre duas tabelas pais que estão sendo inseridas pela página.
		 * 
		 *	- part_of_view		- Esta propriedade é marcada automaticamente pela função que é usada pelo MODEL quando usamos uma visão.
		 * 
		 */
		if ( is_array( $_config_table )
		&&   ! empty( $_config_table )
		   )
		{
			$table_ctrl_used				=	FALSE;
			$other_master					=	FALSE;
			foreach ( $_config_table as $table => $methods ) // Procura pelo master (==controller) ou outra determinação de master.
			{
				if ( $table == $this->router->class )
				{
					$table_ctrl_used		=	TRUE;
				}

				$master					=	FALSE;
				foreach( $methods as $method => $value )
				{
					if ( $method == 'master' && $value )
					{
						$master		=	$value;
						break;
					}
				}

				if ( $master
				&&   $table != $this->router->class
				   )
				{
					$other_master			=	TRUE;
				}
			}

			$last_table_name				=	$this->router->class;
			
			if ( ! $table_ctrl_used ) // Não foi configurada a tabela do controller dentro do array. Então colocamos automaticamente a tabela no topo do array.
			{
				$new_table				=	new stdClass();
				$new_table->name			=	$this->router->class;
				$new_table->model_name			=	$new_table->name;
				if ( ! $other_master )
				{
					$new_table->master		=	TRUE;
					$this->master_table		=	$new_table->name;
					$this->master_model_str		=	$new_table->model_name;
				}
				else
				{
					$new_table->master		=	FALSE;
				}
				$new_table->read_write			=	'write';
				$new_table->r_table_name		=	array();
				$new_table->show			=	TRUE;
				$new_table->show_style			=	'form';
				$new_table->hide_columns		=	array();
				$new_table->seq_columns			=	array();
				$new_table->readonly_columns		=	array();
				$new_table->max_rows			=	1;
				$new_table->where			=	NULL;
				$new_table->orderby			=	NULL;
				$new_table->delete_rule			=	'restrict';
				$new_table->force_copy_from		=	FALSE;
				$new_table->part_of_view		=	FALSE;
				$new_table->is_view			=	FALSE;
				$this->tables[ $this->router->class ]	=	$new_table;
				$last_table_name			=	$this->router->class;
				unset( $new_table );
			}

			foreach ( $_config_table as $table => $methods )
			{
				// Seta os valores default para todas as tabelas.
				$new_table				=	new stdClass();
				$new_table->name			=	$table;
				$new_table->model_name			=	$new_table->name;
				$new_table->read_write			=	'read';
				$new_table->show			=	TRUE;
				$new_table->show_style			=	'form';
				$new_table->hide_columns		=	'';
				$new_table->seq_columns			=	'';
				$new_table->readonly_columns		=	'';
				$new_table->max_rows			=	1;
				$new_table->master			=	FALSE;
				$new_table->where			=	NULL;
				$new_table->orderby			=	NULL;
				$new_table->delete_rule			=	'restrict';
				$new_table->force_copy_from		=	FALSE;
				$new_table->part_of_view		=	FALSE;
				$new_table->is_view			=	FALSE;

				// Personaliza a configuracão de cada tabela.
				foreach( $methods as $method => $value )
				{
					if ( $method == 'r_table_name' ) // este método pode receber um valor nulo.
					{
						$new_table->$method	=	explode( ',', $value );
					}
					elseif ( $value ) // Força manter o default se não foi enviado nada como valor.
					{
						$new_table->$method	=	$value;
					}
				}

				if ( !isset( $new_table->r_table_name ) ) // Não foi informado nada neste método, então usamos a sequencia de tabelas para criar a hierarquia.
				{				
					$new_table->r_table_name	=	explode( ',', $last_table_name );
				}

				$new_table->hide_columns		=	explode( ',', str_replace( ' ', '', $new_table->hide_columns ) );
				$new_table->seq_columns			=	explode( ',', str_replace( ' ', '', $new_table->seq_columns ) );
				$new_table->readonly_columns		=	explode( ',', str_replace( ' ', '', $new_table->readonly_columns ) );
				
				// Define o master
				if ( ! $other_master
				&&   $table == $this->router->class
				   )
				{
					$new_table->master		=	TRUE;
				}
				if ( $new_table->master ) // Sendo a tabela master mudamos o nome do model para "model_master" que será a base do controller.
				{
					$this->master_table		=	$new_table->name;
					$this->master_model_str		=	$new_table->model_name;
				}
				
				$this->tables[ $table ]			=	$new_table;
				unset( $new_table );
				$last_table_name			=	$table;
			}
		}
		else
		{
			$new_table					=	new stdClass();
			$new_table->name				=	$this->router->class;
			$new_table->model_name				=	$new_table->name;
			$new_table->master				=	TRUE;
			$this->master_table				=	$new_table->name;
			$this->master_model_str				=	$new_table->model_name;
			$new_table->read_write				=	'write';
			$new_table->r_table_name			=	array();
			$new_table->show				=	TRUE;
			$new_table->show_style				=	'form';
			$new_table->hide_columns			=	array();
			$new_table->seq_columns				=	array();
			$new_table->readonly_columns			=	array();
			$new_table->max_rows				=	1;
			$new_table->where				=	NULL;
			$new_table->orderby				=	NULL;
			$new_table->delete_rule				=	'restrict';
			$new_table->force_copy_from			=	FALSE;
			$new_table->part_of_view			=	FALSE;
			$new_table->is_view				=	FALSE;
			$this->tables[ $this->router->class ]		=	$new_table;
			$last_table_name				=	$this->router->class;
			unset( $new_table );
		}
		
		/*
		 * Carregar o model e o arquivo de linguagem para as tabelas configuradas para este controller.
		 */
		foreach( array_reverse( $this->tables ) as $table )
		{
			$this->singlepack->load_lang_model_files( $table->name, $table->model_name, $table->name );
			if ( $this->master_model_str == $table->model_name )
			{
				$model			=	$table->model_name;
				$this->master_model	=&	$this->$model; // Criamos um novo apontamento para o model master da tabela master do controller.
				log_message( 'debug', "JX_Page. Carga da tabela '{$table->name}' para '{$table->read_write}' com model '{$model}' realizada como MASTER." );
				if ( is_object( $this->master_model ) )
				{
					log_message( 'debug', "...OBJECT_MASTER master_model." );
				}
				else
				{
					log_message( 'debug', "...NÃO TEM OBJECT_MASTER master." );
				}
				if ( is_object( $this->$model ) )
				{
					log_message( 'debug', "...OBJECT_MASTER com model '{$model}'." );
				}
				else
				{
					log_message( 'debug', "...NÃO TEM OBJECT_MASTER com model '{$model}." );
				}
			}
			else
			{
				$model			=	$table->model_name;
				if ( isset( $this->$model )
				&&   is_object( $this->$model )
				   )
				{
					log_message( 'debug', "...OBJECT com model '{$model}'." );
				}
				else
				{
					log_message( 'debug', "...NÃO TEM OBJECT com model '{$model}." );
				}
				
			}
		}
		
		/**
		 * Configuração visual do controller
		 * 
		 * Atributos:
		 * 	index_html		- Nome do arquivo HTML que será usado para exibir a página do método index().
		 * 				  Valor padrão "jx/index.html".
		 * 
		 *	edit_html		- Nome do arquivo HTML que será usado pelo método edit();
		 *				  Valor padrão jx/edit.html
		 *
		 *	edit_form_html		- Nome do arquivo HTML que será usado pelo arquivo "edit_html" para chamar edição do tipo form.
		 *				  Valor padrão jx/edit_form_style.html
		 *
		 *	edit_grid_html		- Nome do arquivo HTML que será usado pelo arquivo "edit_html" para chamar edição do tipo grids.
		 *				  Valor padrão jx/edit_grid-style.html
		 * 
		 * 	edit_grid_button_html	- Nome do arquivo HTML que será usado pelo arquivo "edit_html" para construir os botões de grid.
		 *				  Valor padrão jx/edit_button_grid.html

		 */
		if ( is_array( $_config_visual )
		&&   ! empty( $_config_visual )
		   )
		{
			foreach( $_config_visual as $var => $value )
			{
				$this->$var	=	$value;
			}
		}
		
		/*
		 * Carrega as lang de controle geral das páginas.
		 */
		$this->singlepack->load_lang_file( 'jx/button' );
		$this->singlepack->load_lang_file( 'jx/message' );
		$this->singlepack->load_lang_file( 'jx/misc' );

		/*
		 * Carrega o primeiro conjunto de variáveis comuns para Páginas e Processos.
		 */
		$data					=	array	(
									 'base_uri'		=> $this->uri->segment(1).$this->uri->slash_segment(2, 'leading')
									,'base_url'		=> $this->config->site_url().'/'.$this->uri->segment(1).$this->uri->slash_segment(2, 'both')
									);
		$this->load->vars( $data );
		
		// Inicializa as variáveis que os outros controllers devem enviar.
		$data					=	array	(
									// Obtém as linhas que serão exibidas.
									 'rows'			=> NULL
									,'fields'		=> NULL
									,'master_table'		=> $this->master_table
									,'total_rows'		=> NULL
									,'jx_pagina_atual'	=> NULL
									,'start_line'		=> NULL
									,'last_line'		=> NULL
									,'total_lines'		=> NULL
									,'jx_action'		=> NULL
									,'jx_message'		=> NULL
									,'jx_message_type'	=> NULL
									,'show_header'		=> NULL
									,'jx_order_selection'	=> NULL
									,'jx_order_direction'	=> NULL
									,'jx_search_what'	=> NULL
									,'jx_filter_parent'	=> NULL
									);
		$this->load->vars( $data );

		/*
		 * Carrega, se existir, a notificação para a pessoa.
		 */
		$this->singlepack->load_lang_model_files( 'notificacao', 'notificacao', 'notificacao' ); // Forçamos a carga do model notificação.
		$notificacao_header			=	$this->notificacao->get_my_page_notification();
		$data					=	array	(
									 'notificacao_header'		=> $notificacao_header
									);
		$this->load->vars( $data );

		log_message( 'debug', "JX_Controller subclass({$this->router->class}.{$this->router->method}) initialized." );
	}

	/*
	 * Criado para visões para que possamos, de dentro do MODEL, copiar a configuração da visão para as tabelas configuradas em seu model.
	 */
	public function _copy_table_config( $view_origem, $table_destino, $where, $r_table_name )
	{
		log_message( 'debug', "JX_Controller _copy_table_config $view_origem, $table_destino, $where, $r_table_name )." );
		$new_table					=	new stdClass();
		$new_table->name				=	$table_destino;
		$new_table->model_name				=	$table_destino;
		$new_table->master				=	FALSE;

		// Mantemos os seguintes valores iguais ao modelo.
		$new_table->read_write				=	$this->tables[ $view_origem ]->read_write;
		$new_table->hide_columns			=	$this->tables[ $view_origem ]->hide_columns;
		$new_table->orderby				=	$this->tables[ $view_origem ]->orderby;
		$new_table->delete_rule				=	$this->tables[ $view_origem ]->delete_rule;
		$new_table->force_copy_from			=	$this->tables[ $view_origem ]->force_copy_from;

		// Nunca exibiremos estas novas tabelas
		$new_table->show				=	FALSE;
		$new_table->show_style				=	'none';
		$new_table->seq_columns				=	array();
		$new_table->readonly_columns			=	array();
		$new_table->max_rows				=	999999;
		
		// Alteramos o where trocando o nome do modelo pelo nome do destino
		if ( $r_table_name )
		{
			$new_table->r_table_name		=	explode( ',', $r_table_name );
		}
		else
		{
			$new_table->r_table_name		=	$this->tables[ $view_origem ]->r_table_name;
		}
		$new_table->where				=	( ( $where ) ? $where : $this->tables[ $view_origem ]->where );
		
		// Avisamos ao resto do sistema que estas tabelas são de uma visão.
		$new_table->part_of_view			=	$view_origem;
		$new_table->is_view				=	FALSE;
		
		$this->tables[ $new_table->name ]		=	$new_table;

		// Setamos a visão como sendo visão.
		$this->tables[ $view_origem ]->is_view		=	TRUE;
		
		return TRUE;
	}
	
	public function _get_tables()
	{
		return $this->tables;
	}
	public function _get_master_model()
	{
		return $this->master_model;
	}
	
	/**
	 * Retorna informações da revision do módulo.
	 * O que retorna:
	 * 	- rev			- Último número
	 * 	- author		- quem fez a última alteração
	 * 	- when			- quando foi feita a última alteração
	 * 	- name			- nome do fonte.
	 * 	- all			- retorna toda a string.
	 * 
	 * Copiar esta lógica para o JX_Model.
	 */
	public function get_version( $what = 'all' )
	{
		if ($this->_revision)
		{
			if ( $what == 'all' )
			{
				return $this->_revision;
			}
			elseif ( $what == 'rev' )
			{
				return $this->_revision;
			}
			elseif ( $what == 'author' )
			{
				return $this->_revision;
			}
			elseif ( $what == 'when' )
			{
				return $this->_revision;
			}
			elseif ( $what == 'name' )
			{
				return $this->_revision;
			}
		}
		else
		{
			return "no version.";
		}
	}
}

/**
 * Aqui lemos as subclasses do controller principal que contém personalizações por estilo de uso.
 */

// Classe para páginas de atualização e consulta de dados
require_once APPPATH.'core/JX_Page.php';

// Classe para processos em geral. Contém controles e páginas de controles de processos.
require_once APPPATH.'core/JX_Process.php';


/* End of file JX_Controller.php */
/* Location: ./application/core/JX_Controller.php */
