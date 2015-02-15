<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Extendendo o CodeIgniter
 *
 *	Classe para controle de acesso ao banco de dados o Jarvix Plus.
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/core/JX_Model.php
 *
 * $Id: JX_Model.php,v 1.36 2013-03-07 21:29:15 junior Exp $
 *
 */

/**
 * Personalização do MODEL.
 * 
 * Entenda para que ser cada função para decidir como deve fazer para personalizar um model.
 * 
 * 					Os que estão com ** são mais frequentes de serem modificados.
 * 
 * 	** - get_base_column()
 * 
 * 	** - get_detail_column()
 * 
 * 	- get_fields_fk()
 * 
 * 	- get_fields_info()
 * 
 * 	- get_fields_name()
 * 
 * 	- get_fields_pk()
 * 
 * 	- get_fields_text()
 * 
 * 	** - get_from()
 * 
 * 	- get_order_by()
 * 
 * 	- get_parents()
 *
 * 	- get_tables_pk()
 * 
 * 	** - get_title_column()
 * 
 * 	** - get_where()
 * 
 * 	** - get_when_column()
 * 
 * 	** - get_where_filter_parent()
 * 
 * 	** - get_where_search_all()
 * 
 */

class JX_Model extends CI_Model
{
	// Contém o nome da tabela. É carregado pelo controller automaticamente.
	var $table			=	"";

 	var $table_config		=	array();
 	
 	var $fields;
 	
 	var $is_view			=	FALSE;
 	
 	/**
 	 *  Controle de validação;
 	 */
	var $record_valid		=	TRUE;

	var $cons_disable		=	array(); // Lista de constraints desativadas.
	
	// UK
	var $uk_cons_name		=	NULL;
	var $uk_cons_columns		=	array();
	var $uk_force_old_id		=	FALSE;
	var $uk_error_msg		=	'Já existe...';
	var $uk_select			=	'select';
	var $goback_to_update		=	FALSE;
	
	// CK
	var $ck_cons_name		=	NULL;
	var $ck_cons_columns		=	array();
	var $ck_condition_sql		=	NULL;
	var $ck_condition_php		=	NULL;
	var $ck_error_msg		=	'Valor inválido...';

 	/**
 	 *  Controle de mensagens.
 	 */
 	var $curr_id;
 	var $curr_seq;
	var $ar_field_msg 		=	array(); // error, warning, ok

	/**
	 *  Variáveis para evitar a recursividade.
	 */
 	var $_in_pre_insert		=	FALSE;
 	var $_in_pre_update		=	FALSE;
 	var $_in_pre_delete		=	FALSE;
 	var $_in_post_insert		=	FALSE;
 	var $_in_post_update		=	FALSE;
 	var $_in_post_delete		=	FALSE;
 	
	// Mantém o resultado da query da última execução. 	
 	var $query;

 	protected $insert_data;
 	protected $update_data;
 	protected $delete_data;
  	
	function __construct( $_table_config = NULL, $_fields_config = NULL )
	{
		log_message( 'debug', "JX_Model(start) class=".substr( strtolower( get_class( $this ) ), 0, strrpos( strtolower( get_class( $this ) ), "_" , -1 ) ) );
		parent::__construct();

		// Esta variável está montada em JX_Controller para que possamos usar funções do controller em todos os models.
		global $main_controller;

		/*
		 * Registra os relacionamento da tabela deste model (pk) com outras tabelas (fks).
		 * 
		 * 	- name			- Nome da tabela, chave do array. Se informado como atributo será usado antes da chave do array.
		 * 
		 * 	- model_name		- Apelido do model que tratará a tabela.
		 * 
		 * Para visões
		 * 	- where			- Clausula where que liga as tabelas em uma visão.
		 * 
		 * 	- r_table_name		- Nome da tabela relacionada. Usamos a última enviada para associar caso seja omitido.
		 * 				- Havendo mais de uma, basta separar por , (virgula).
		 * 				- Usaremos o formato <table_name>_id para criar a ligação entre as tabelas.
		 * 
		 * 	- ar_constraint		- Contém as regras de validação da tabela.
	 	 *  
	 	 *  Composição do array "ar_constraint"
	 	 *  		cons_type	UK
	 	 *  				PK -- Se a tabela tiver esta constraint ativa na base, não precisa colocar no array.
	 	 *  				CK -- Se a tabela tiver esta constraint ativa na base, não precisa colocar no array. Coloque se quiser melhorar a mensagem de erro.
	 	 *  				FK
	 	 *  				FK_CH (validação de delele. FK Child)
	 	 *  
	 	 *  		cons_columns	array( coluna, coluna, coluna, ... )
	 	 *  				Contém as colunas que fazem parte da constraint.
	 	 *
	 	 *		force_old_id	true ou false
	 	 *				Usada apenas em UK e indica se a validação deve retornar o ID já existente na base para a nova operação ou retornar o ERRO indicado em error_msg.
	 	 *
	 	 *		condition_sql	string com o SELECT
	 	 *				Escreva um SELECT que será executado por diretamente na base de dados.
	 	 *				Use {nome_da_coluna} para se referenciar à uma informação vinda da tabela.
	 	 *				O comando select será executado.
	 	 *					TRUE = Se contiver linhas.
	 	 *					FALSE = Se não retornar linha.
	 	 *
	 	 *		condition_php	string com o IF em PHP
	 	 *				Escreva uma IF que será executado por eval() no PHP.
	 	 *				Use {nome_da_coluna} para se referenciar à uma informação vinda da tabela.
	 	 *
	 	 *		cons_tab_ref	string com o nome_tabela pai da FK
	 	 *
	 	 *		cons_cols_ref	array( coluna_tabela_pai_da_FK, coluna_tabela_pai_da_FK, coluna_tabela_pai_da_FK, ... )
	 	 *				Usar SEMPRE a mesma sequencia da "cons_columns", pois montaremos a query desta forma.
	 	 *
	 	 *		delete_rule	string
	 	 * 				Usado apenas em FK_CH e indica se devemos usar um delete cascade ou restrict em caso de exclusão do pai.
		 * 					- cascade	- apaga as linhas dos filhos. Não retorna erro na existência de filhos.
		 * 					- restrict	- evita que o pai seja apagado. Retorna a error_msg quando encontra filhos.
		 * 					- setnull	- atualiza as linhas dos filhos com nulo. Não retorna erro na existência de filhos.
	 	 *  
	 	 *  		error_msg	string contendo a mensagem de erro de retorno quando a constraint falhar. Para UK observe o force_old_id.
	 	 *  
	 	 * 	Exemplo:
	 	 * 		CK
	 	 * 		'ar_constraint'		=>	array	(
		 *								'cons_name'	=>	array	(
	 	 * 												 'cons_type'		=>	'CK'
	 	 * 												,'cons_columns'		=>	array( 'nome_coluna_a', 'nome_coluna_b' )
	 	 * 												,'condition_sql'	=>	'select id from tabela where nome_coluna_a >= $data->nome_coluna_a'
	 	 * 												,'condition_php'	=>	'$data->nome_coluna_a >= $data->nome_coluna_b'
	 	 * 												,'error_msg'		=>	'A coluna "A" tem que ser maior que a coluna "B"'
	 	 * 												)
	 	 * 							)
	 	 * 
	 	 * 		UK
	 	 * 		'ar_constraint'		=>	array	(
		 *								'cons_name'	=>	array	(
	 	 * 												 'cons_type'		=>	'UK'
	 	 * 												,'cons_columns'		=>	array( 'nome_coluna_a', 'nome_coluna_b' )
	 	 * 												,'force_old_id'		=>	TRUE
	 	 * 												,'error_msg'		=>	'Já existe um chute para este jogo.'
	 	 * 												)
	 	 * 							)
	 	 * 
	 	 * 
	 	 * 		FK
	 	 * 		'ar_constraint'		=>	array	(
		 *								'cons_name'	=>	array	(
	 	 * 												 'cons_type'		=>	'FK'
	 	 * 												,'cons_columns'		=>	array( 'nome_tabela_pai_id' )
	 	 * 												,'cons_tab_ref'		=>	'tabela_a'
	 	 * 												,'cons_cols_ref'	=>	array( 'id' )
	 	 * 												,'error_msg'		=>	'Não existe o PAI informado.'
	 	 * 												)
	 	 * 							)
	 	 * 
	 	 * 		FK_CH
	 	 * 		'ar_constraint'		=>	array	(
		 *								'cons_name'	=>	array	(
	 	 * 												 'cons_type'		=>	'FK_CH'
	 	 * 												,'cons_columns'		=>	array( 'id' )
	 	 * 												,'cons_tab_ref'		=>	'tabela_filho_a'
	 	 * 												,'cons_cols_ref'	=>	array( 'nome_tabela_pai_id' )
	 	 * 												,'delete_rule'		=>	'restrict'
	 	 * 												,'error_msg'		=>	'Não pode ser excluída esta linha, pois existem NOME_TABELA_FILHO ligados à ela.'
	 	 * 												)
	 	 * 							)
	 	 * 
	 	 * 		Usando duas ou mais.
	 	 * 
	 	 * 		FK e FK_CH
	 	 * 		'ar_constraint'		=>	array	(
		 *								 'cons_name_FK'	=>	array	(
	 	 * 												 'cons_type'		=>	'FK'
	 	 * 												,'cons_columns'		=>	array( 'nome_tabela_pai_id' )
	 	 * 												,'cons_tab_ref'		=>	'tabela_a'
	 	 * 												,'cons_cols_ref'	=>	array( 'id' )
	 	 * 												,'error_msg'		=>	'Não existe o PAI informado.'
	 	 * 												)
		 *								,'cons_name_FK_CH'=>	array	(
	 	 * 												 'cons_type'		=>	'FK_CH'
	 	 * 												,'cons_columns'		=>	array( 'id' )
	 	 * 												,'cons_tab_ref'		=>	'tabela_filho_a'
	 	 * 												,'cons_cols_ref'	=>	array( 'nome_tabela_pai_id' )
	 	 * 												,'delete_rule'		=>	'restrict'
	 	 * 												,'error_msg'		=>	'Não pode ser excluída esta linha, pois existem NOME_TABELA_FILHO ligados à ela.'
	 	 * 												)
	 	 * 							)
		 */
		if ( is_array( $_table_config ) > 0
		&&   ! empty( $_table_config )
		   )
		{
			foreach ( $_table_config as $table => $methods )
			{
				$new_table				=	new stdClass();
				$new_table->name			=	$table;
				$new_table->model_name			=	$table;
				$new_table->where			=	NULL;
				$new_table->r_table_name		=	NULL;
				$new_table->ar_constraint		=	array();
				
				// Personaliza a configuracão de cada tabela.
				foreach( $methods as $method => $value )
				{
					$new_table->$method		=	$value;
				}
				
				$this->table_config[ $table ]		=	$new_table;
				unset( $new_table );
			}
		}

		/*
		 * Registra as colunas da tabela via configuração no model e não via estrutura de dados da base de dados.
		 * TODO: montar esta lógica e alterar o _get_fields para usá-la.
		 * 	- xxx			- xxx.
		 * 
		 */
		if ( is_array( $_fields_config ) > 0
		&&   ! empty( $_fields_config )
		   )
		{
			foreach ( $_fields_config as $field => $methods )
			{
				$new_field				=	new stdClass();
				$new_field->name			=	$table;
				
				// Personaliza a configuracão de cada tabela.
				foreach( $methods as $method => $value )
				{
					$new_field->$method		=	$value;
				}
				
				unset( $new_field );
			}
		}
		
		/**
		 * Se não carregamos ainda o valor de $table, então corregamos a partir do nome da classe que está chamando o JX_Model.
		 */
		if ( !$this->table &&
		     strtolower( get_class( $this ) ) != 'jx_model' )
		{
			//$this->table = str_replace("_model", "", strtolower( get_class( $this ) ) );
			$this->table = substr( strtolower( get_class( $this ) ), 0, strrpos( strtolower( get_class( $this ) ), "_" , -1 ) );
			log_message('debug', "JX_Model initialized for table {$this->table}.");
		}
		else
		{
			log_message('debug', "JX_Model reloaded for table {$this->table}.");
		}
		/*
		 * Carregar o model para as tabelas configuradas para este controller.
		 */
		foreach( $this->table_config as $table )
		{
			log_message('debug', ">>>> tabela da class={$this->table} relation_table={$table->name}.");
			if ( $this->table != $table->name )
			{
				$this->singlepack->load_lang_model_files( $table->name, $table->model_name, $table->name );
			}
		}
		/*
		 * Define se estamos trabalhando com uma visão.
		 */
		$vw_pos						=	stripos( strtolower( $this->table ), 'vw_' );
		if ( $vw_pos !== FALSE
		&&   $vw_pos == 0
		   )
		{
			$this->is_view				=	TRUE;

			log_message( 'debug', "JX_Model é VIEW {$this->table}.");

			foreach( $this->table_config as $table )
			{
				$this->input->duplicate_data_vw( $this->table, $table->name );
				
				// Prepara para incluir as tabelas da visões às lista de tabelas do Controller.
				$main_controller->_copy_table_config( $table_modelo = $this->table, $table_destino = $table->name, $where = $table->where, $table->r_table_name );
			}
			
log_message( 'debug', "JX_MODEL.APOS A DUPLICACAO DE DADOS" );
if ( $this->input->input_cube )
{
	foreach( $this->input->input_cube as $cube_key => $cube_value )
	{
		log_message( 'debug', "JX_MODEL.cubo_de_dados  key={$cube_key}" );
		$this->input->_open_object( $cube_value );
	}
}
		}
	}
	
	/**
	 * Pega, uma única vez, as informações da tabela na base de dados.
	 */
	protected function _get_fields()
	{
		if ( !$this->fields )
		{
			$this->fields		=	$this->db->field_data( $this->table );
		}
		return $this->fields;
	}
	/**
	 * Retorna o header para este model.
	 */
	public function get_header()
	{
		$header		=	$this->lang->get_line( "controller_title", $this->table );
		if ( !$header )
		{
			$header		=	( substr( strtolower( get_class( $this ) ), 0, strrpos( strtolower( get_class( $this ) ), "_" , -1 ) ) );
			$controller	=	$this->singlepack->get_controller( $header );
			if ( !$controller )
			{
				return ucfirst( substr( strtolower( get_class( $this ) ), 0, strrpos( strtolower( get_class( $this ) ), "_" , -1 ) ) );
			}
			else
			{
				return $controller->nome; // str_replace( '_', ' ', $header );
			}
		}
		else
		{
			return $header;
		}
	}

	/**
	 * Retona os nomes de todas as colunas da tabela.
	 */
	function get_fields_name()
	{
		return $this->db->list_fields( $this->table );
	}

	/**
	 * Retona as informacoes de todas as colunas da tabela.
	 */
	function get_fields_info( $for_html = NULL, $rows = NULL, $seq_fields = NULL )
	{
//		$field_property	=	$this->set_field_property();
		$ret_fields	=	array();
		
		if ( $for_html )
		{
			if ( $for_html == 'INDEX' )
			{
				if ( $rows
				&&   property_exists( $rows[0], 'imagem_id' )
				   )
				{
					$index_array		=	array( 'when_field' => 'text', 'title' => 'text', 'imagem_id' => 'img' );
				}
				else
				{
					$index_array		=	array( 'when_field' => 'text', 'title' => 'text' );
				}
				foreach( $index_array as $field => $type )
				{
					$F			=	new stdClass();
					$F->name		=	$field;
					$F->type		=	$type;
					$F->default		=	NULL;
					$F->max_length		=	NULL;
					$F->primary_key 	=	NULL;
					$F->nullable    	=	NULL;
					$F->index		=	new JX_Field( $F, $this->table );
					$F->db_message		=	NULL; // Mensagem de validação.
					$ret_fields[ $field ]	=	$F;
					unset( $F );
				}
			}
			else
			{
				foreach( $this->_get_fields() as $field )
				{
					$F			=	new stdClass();
					$F->name		=	$field->name;
					$F->type		=	$field->type;
					$F->default		=	$field->default;
					$F->max_length		=	$field->max_length;
					$F->primary_key 	=	$field->primary_key;
					$F->nullable    	=	$field->nullable;
					$F->db_message		=	NULL; // Mensagem de validação.
					//Acrescenta os campos abaixo para as páginas.
					if ( $for_html == 'EDIT' )
					{
						$F->edit	=	new JX_Field( $F, $this->table );
					}
					$ret_fields[ $F->name ]	=	$F;
					unset( $F );
				}
			}
			
			$fields					=	$ret_fields;
		}
		else
		{
			$fields					=	$this->_get_fields();
			$new_fields				=	array();
			foreach( $fields as $field )
			{
				$field->db_message		=	NULL; // Mensagem de validação.
				$new_fields[ $field->name ]	=	$field;
			}
			$fields					=	$new_fields;
		}

		// Muda a sequencia das colunas se o parametro foi enviado.
		$ret_fields					=	array();
		if ( $seq_fields )
		{
			foreach( $seq_fields as $field_name )
			{
				if ( key_exists( $field_name, $fields ) )
				{
					$ret_fields[ $field_name ]	=	$fields[ $field_name ];
				}
			}
			foreach( $fields as $field_name => $fields_data )
			{
				if ( !key_exists( $field_name, $ret_fields )
				&&   key_exists( $field_name, $fields )
				    )
				{
					$ret_fields[ $field_name ]	=	$fields[ $field_name ];
				}
			}
			$fields					=	$ret_fields;
		}

		return $fields;
	}
	
	/**
	 * Retona todas as colunas do tipo texto da tabela.
	 */
	function get_fields_text()
	{
		$fields_text	=	array(); 
		$fields		=	$this->_get_fields();
		foreach ( $fields as $field )
		{
			if  ( $field->type == 'varchar' ||
			      $field->type == 'text'
			    )
			{
				$fields_text[]->name	=  $field->name;
			}
		}
		
		return $fields_text;
	}

	/**
	 * Retona as colunas de PK da tabela.
	 */
	function get_fields_pk()
	{
		$fields_pk	=	array();
		$fields		=	$this->_get_fields();
		foreach ( $fields as $field )
		{
			if  ( $field->primary_key == '1' )
			{
				$fields_pk[]->name	=  $field->name;
			}
		}
		
		return $fields_pk;
	}

	/**
	 * Retona as tabelas de FK da tabela atual.
	 */
	function get_tables_fk()
	{
		$tables_fk	=	array();
		$fields		=	$this->_get_fields();
		foreach ( $fields as $field )
		{
			$column_name	= $field->name;
			if  ( strpos( $column_name, '_id_' ) != 0 )
			{
				$words						=	explode( "_id_", $column_name );
				$table_name					=	$words[0];
				$key						=	$words[0].'_'.$words[1];
				$tables_fk[ $key ]->column_name			=	$column_name;
				$tables_fk[ $key ]->table_name			=	$table_name;
			}
			elseif  ( strpos( $column_name, '_id' ) != 0 )
			{
				$table_name					=	str_replace( "_id", "", $column_name );
				$key						=	$table_name;
				$tables_fk[ $key ]->column_name			=	$column_name;
				$tables_fk[ $key ]->table_name			=	$table_name;
			}
		}
		return $tables_fk;
	}

	function get_fields_fk()
	{
		$fields_fk	=	array();
		$fields		=	$this->_get_fields();
		foreach ( $fields as $field )
		{
			if  ( strpos( $field->name, '_id'  ) != 0
			||    strpos( $field->name, '_id_' ) != 0
			    )
			{
				$fields_fk[]->name	=  $field->name;
			}
		}
		
		return $fields_fk;
	}
	
	protected function copy_from( $id, $cube_keys )
	{
		if ( $cube_keys )
		{
/*foreach( $cube_keys as $key )
{
	echo 'key='.$key.'<br/>';
}

echo '(copy_from) '.$id.'<br/>';
*/			$this->input->copy_from_id( $id = $id, $cube_keys = $cube_keys, $table_name = $this->table, $tables_array = $this->tables );
		}
/*else
{
	echo '(copy_from) SEM COPY_FROM '.$id.'<br>';
}
*/	}
	
	/**
	 * Insere uma linha na tabela.
	 */
	public function _next_val()
	{
		$res	=	 $this->db-query( "SELECT AUTO_INCREMENT next_val FROM information_schema.TABLES WHERE table_name='{$this->table}'" );
		return $res->next_val;
	}
	public function _pre_insert()
	{
		$ret					=	TRUE;
		/*
		 * $this->insert_data pode ser modificado diretamente aqui que o insert usará o que foi mudado.
		 */
		if ( !$this->_in_pre_insert )
		{
		 	$this->_in_pre_insert		=	TRUE;
		 	// COMANDOS
		 	// use $this->insert_data para ler os dados que estão sendo utilizados.
		 	$this->_in_pre_insert		=	FALSE;
		}
		
		return $ret;
	}
	public function insert( $data, $cube_keys = NULL, $record_valid = TRUE )
	{
		if ( $this->is_view )
		{
/*			log_message( 'debug', "JX_Model.insert(VIEW) Insert nas tabelas da visão {$this->table}." );
			foreach( $this->table_config as $table )
			{
				$model_name	=	$table->model_name;
				log_message( 'debug', "......$model_name" );
				$this->$model_name->insert( $data, $cube_keys, $record_valid );
			}*/
		}
		else
		{
			/* Registra qual chave do cubo está sendo gravada agora */
			if ( is_array( $cube_keys )
			&&   key_exists( 0, $cube_keys )
			   )
			{
				$this->input->set_current_cube_key( $cube_keys[0] );
			}

//echo 'insert ('.$this->table.').<br/>';
			log_message( 'debug', "JX_Model.insert($this->table).start." );
			$id			=	NULL;
			$this->curr_id		=	$id;
			$this->curr_seq		+=	1;
			
			$this->insert_data	=	$this->_prep_data_for_base( 'insert', $data );
			
			$this->_pre_insert();
	
			$record_valid/*_DB*/	=	$this->validate_internal( 'insert' );
			
			// Se estiver válido seguimos na operação.
			if ( $record_valid/*
			&&   $record_valid_DB*/
			   )
			{
		
				// Durante a validação vimos que a linha deve ser alterada e não inserida.
				if ( $this->goback_to_update
				&&   !is_null( $this->insert_data->id )
				   )
				{
					$id			=	$this->insert_data->id;
					$this->db->where( 'id', $id );
					if ( $this->db->update( $this->table, $this->insert_data ) )
					{
						$this->copy_from( $this->insert_data->id, $cube_keys );
						log_message( 'debug', "JX_Model.insert($this->table). (a partir do insert) Alterou linhas em {$this->table} id($id)." );
					}
					else
					{
						log_message( 'debug', "JX_Model.insert($this->table). (a partir do insert) Não alterou nenhuma linha na tabela {$this->table} id($id)." );
					}
				}
				else
				{
					$this->db->insert( $this->table, $this->insert_data );
					$id			=	$this->db->insert_id();
				}
		
				$this->insert_data->id		=	$id;
				$this->curr_id			=	$id;
				
				$this->copy_from( $id, $cube_keys );
		
				$this->_post_insert();
				log_message( 'debug', "JX_Model.insert($this->table). Linha ID(".$id.") inserida em ".$this->table."." );
			}
			else
			{
				if ( !$record_valid )
				{
					log_message( 'debug', "JX_Model.insert($this->table). Não inseriu, está com erro de página em ".$this->table."." );
				}/*
				if ( !$record_valid_DB )
				{
					log_message( 'debug', "JX_Model.insert($this->table). Não inseriu, está com erro de BASE em ".$this->table."." );
				}*/
			}
	
			log_message( 'debug', "JX_Model.insert($this->table).FIM(1)." );
			return $id; 
		}
		
		return NULL;
	}

	public function _post_insert()
	{
		$ret					=	TRUE;
		if ( !$this->_in_post_insert )
		{
		 	$this->_in_post_insert		=	TRUE;
		 	// COMANDOS
		 	// use $this->insert_data para ler os dados que estão sendo utilizados.
		 	$this->_in_post_insert		=	FALSE;
		}
		
		return $ret;
	}
	
	/**
	 * 
	 * Prepara / valida os dados antes da alteração na base de dados.
	 * 	Como na maioria das vezes usasmos o UPDATE para inserir e alterar dados, o pre_update será utilizado para os dois comandos.
	 * 
	 */
	public function _pre_update()
	{
		$ret					=	TRUE;
		/*
		 * $this->update_data pode ser modificado diretamente aqui que o update usará o que foi mudado.
		 */
		if ( !$this->_in_pre_update )
		{
		 	$this->_in_pre_update		=	TRUE;
		 	// COMANDOS
		 	// use $this->update_data para ler os dados que estão sendo utilizados.
		 	$this->_in_pre_update		=	FALSE;
		}
		
		return $ret;
	}
	public function update_where_LIXO( $where, $data )
	{
		$this->db->where( $where );
		return $this->db->update( $this->table, $data, $record_valid );
	}

	/**
	 * Altera uma linha ou N na tabela. Sempre por ID.
	 */
	function update( $data = NULL, $cube_keys = NULL, $from_batch = FALSE, $record_status = 'CHANGED', $record_valid = TRUE )
	{
		if ( !$this->is_view ) // Em visões não fazemos update direto, fizemos a cópia dos dados da página para cada tabela da visão, configuradas no model dela.
		{
			/* Registra qual chave do cubo está sendo gravada agora */
			if ( is_array( $cube_keys )
			&&   key_exists( 0, $cube_keys )
			   )
			{
				$this->input->set_current_cube_key( $cube_keys[0] );
			}
			
			/*
			 * Se a tabela não tem multiplos valores, então a trataremos diretamente abaixo, pegando os dadao do cubo.
			 * Já se ela tiver multiplos valores, então trataremos de forma batch.
			 */
			if ( !$data
			&&   $this->input->are_multi_id( $this->table )
			&&   !$from_batch
			   )
			{
				return $this->update_batch(); // O batch criará um loop para todas as linhas da tabela e voltará a chamar o update linha a linha.
			}
			else
			{
				log_message( 'debug', "JX_Model.update($this->table).start." );

				if ( !$from_batch
				&&   !$data
				   )
				{
					$data			=	$this->input->get_table_data( $this->table );
					if ( isset( $data->record_status ) )
					{
						$record_status	=	$data->record_status;
					}
					else
					{
						$record_status	=	'CHANGED';
					}
				}
	
				if ( is_array( $data ) )
				{
					$id			=	$data[ 'id' ];
				}
				else
				{
					if ( isset( $data->id ) )
					{
						$id		=	$data->id;
					}
					else
					{
						$id		=	NULL;
					}
				}
/*log_message( 'debug', 'update via linha tabela('.$this->table.') (>>>' );
if ( isset( $data->id ) ) { log_message( 'debug', '......id='.$data->id ); }
if ( isset( $data->versao ) ) { log_message( 'debug', '......versao='.$data->versao ); }
if ( isset( $data->descr ) ) { log_message( 'debug', '......descr='.$data->descr ); }
if ( isset( $data->size ) ) { log_message( 'debug', '......size='.$data->size ); }
//print_r( $data );
log_message( 'debug', '<<<)' );
*/
				log_message( 'debug', "JX_Model.update RECORD_STATUS($record_status)" );
				
				if ( !$id ) // Não nulo, altera. Nulo, insere.
				{
					if ( $record_status != 'CHANGED' )
					{
						log_message( 'debug', "JX_Model.update($record_status). Não seguiu para inserção para aa tabela ".$this->table."." );
						$this->show_memory( 'update.fim(2)' );
						log_message( 'debug', "JX_Model.update($this->table).FIM(1)." );
						return TRUE;
					}
					else
					{
						$this->show_memory( 'update.fim(3)' );
						log_message( 'debug', "JX_Model.update($this->table).FIM(2)." );
						return $this->insert( $data, $cube_keys, $record_valid );
					}
				}
				else
				{
					if ( $id < 0 ) // Quando o ID está menor que zero indica que estamos recebendo um delete.
					{
						log_message( 'debug', "JX_Model.update($this->table). Fazendo o DELETE em ".$this->table."." );
						log_message( 'debug', "JX_Model.update($this->table).FIM(3)." );
						return $this->delete( $id = ( $id * (-1) ) );
					}
					else
					{
						$this->curr_id		=	$id;
						$this->curr_seq		+=	1;
	
						$ret				=	NULL;
						if ( $record_status != 'CHANGED' )
						{
							// Se alinha não foi alterada na tela, apenas enviamos o ID para as tabelas filha se existirem.
							$this->copy_from( $id, $cube_keys );
							log_message( 'debug', "JX_Model.update($this->table). A tabela ".$this->table." não foi alterada." );
							$ret			=	$id; // Mantém o id recebido.
						}
						else
						{
							$this->update_data	=	$this->_prep_data_for_base( 'update', $data );
							$record_valid/*_DB*/	=	$this->validate_internal( 'update' );
	
							// Se estiver válido seguimos na operação.
							if ( $record_valid/*
							&&   $record_valid_DB*/
							   )
							{
								$this->_pre_update( $this->update_data );
	
								$this->db->where( 'id', $id );
								if ( $this->db->update( $this->table, $this->update_data ) )
								{
									$ret	=	$id;
									$this->copy_from( $id, $cube_keys );
									$this->_post_update( $this->update_data );
									log_message( 'debug', "JX_Model.update($this->table). Alterou linhas em ".$this->table." id($id)." );
								}
								else
								{
									log_message( 'debug', "JX_Model.update($this->table). Não alterou nenhuma linha na tabela ".$this->table."." );
									$ret	=	$id;
								}
							}
							else
							{
								log_message( 'debug', "JX_Model.update($this->table).FIM(4)." );
								$ret		=	FALSE;
							}
						}
					}
					$this->show_memory( 'update.fim(1)' );
					log_message( 'debug', "JX_Model.update($this->table).FIM(5)." );
/*log_message( 'debug', "JX_MODEL.APOS A DUPLICACAO DE DADOS" );
if ( $this->input->input_cube )
{
	foreach( $this->input->input_cube as $cube_key => $cube_value )
	{
		log_message( 'debug', "JX_MODEL.cubo_de_dados  key={$cube_key}" );
		$this->input->_open_object( $cube_value );
	}
}*/
					return $ret;
				}
			}
		}
		
		return NULL;
	}
	public function update_batch()
	{
		if ( $this->is_view )
		{
/*			log_message( 'debug', "JX_Model.update_batch(VIEW) Update nas tabelas da visão {$this->table}." );
			// Estando numa visão, alteremos o batch para tratar tabela a tabela.
			foreach( $this->table_config as $table )
			{
				$model_name	=	$table->model_name;
				log_message( 'debug', "......$model_name" );
				foreach( $this->input->extract_rows( $fields = $this->$model_name->get_fields_name(), $table_name = $table->name ) as $row )
				{
log_message( 'debug', 'update VIEW LINHAS. keys='.$row->cube_keys );
					$ret	=	$this->$model_name->update( $row->data, $row->cube_keys, $from_batch = TRUE, $row->record_status, $row->record_valid );
				}
			}
*/
			return TRUE;
		}
		else
		{
//TODO: No retorno, verificar se alguma linha deu erro e retornar este erro.
			foreach( $this->input->extract_rows( $fields = $this->get_fields_name(), $table_name = $this->table ) as $row )
			{
//echo 'update LINHAS. keys='.$row->cube_keys.'<br/>';
				$ret	=	$this->update( $row->data, $row->cube_keys, $from_batch = TRUE, $row->record_status, $row->record_valid );
			}

			return $ret;
		}
	}
 	public function _post_update()
	{
		$ret					=	TRUE;
	
		if ( !$this->_in_post_update )
		{
		 	$this->_in_post_update		=	TRUE;
		 	// COMANDOS
		 	// use $this->update_data para ler os dados que estão sendo utilizados.
		 	$this->_in_post_update		=	FALSE;
		}
		
		return $ret;
	}
	
	/**
	 * Elimina linhas.
	 */
	public function _pre_delete( $id )
	{
		$ret					=	TRUE;
		if ( !$this->_in_pre_delete )
		{
		 	$this->_in_pre_delete		=	TRUE;
		 	// COMANDOS
		 	$this->_in_pre_delete		=	FALSE;
		}
		
		return $ret;
	}
	function delete( $id = NULL, $where = NULL, $record_valid = TRUE )
	{
		if ( $this->is_view )
		{
/*			foreach( $this->table_config as $table )
			{
				$table_name	=	$table->model_name;
				$this->$table_name->delete(  $id, $where, $record_valid );
			}
*/		}
		else
		{
			//echo( "deletando a tabela=" . $this->table . "<br>" );
			if ( empty( $id ) // Não passamos um ID
			&&   empty( $where ) // e nem um where, então buscamos o ID nos inputs da página.
			   )
			{
				 $id	=	$this->input->post_multi( $this->table.'[id]' );
			}
	
			if ( $id
			||   $where
			   )
			{
				if ( $where )
				{
					log_message( 'debug', "JX_Model.delete($this->table). Deletando com o WHERE ($where)." );
					$this->db->where( $where );
				}
				elseif ( $id )
				{
					$this->db->where( $this->table.'.id', $id );
				}
				
				if ( $this->table_config
				&&   count( $this->table_config ) > 0
				   )
				{
//echo( "delete com relation" . "<br>" );
					// Retorna a linha a ser deletada.
					foreach( $this->select_all()->result_object() as $row ) // usa o where montado acima.
					{
						// Varre a configuração de models do MODEL atual.
						foreach( $this->table_config as $table )
						{
							$model_name	=	$table->model_name;
							$table_name	=	$table->name;
/* TODO: rever este conceito. O delete_rule foi para dentro do array de constraints
							if ( $table->delete_rule == 'cascade' )
							{
								// Deleta a linha do filho.
								if ( !$this->$model_name->delete( NULL, array( $this->table.'_id' => $row->id ) ) )
								{
									$ret	=	FALSE;
								}
							}
							elseif ( $table->delete_rule == 'restrict' )
							{
								// Havendo filhos ligados à linha deletada, paramos o processo e retornamos um erro.
								if ( $this->$model_name->count_all( array( $this->table.'_id' => $row->id ) ) > 0 )
								{
									$ret	=	FALSE;
								}
							}
							elseif ( $table->delete_rule == 'setnull' )
							{
								// Desliga os filhos da linha deletada através de update com NULL na FK.
								if ( ! $this->$model_name->update_where( array( $this->table.'_id' => $row->id ), $data = array( $this->table.'_id' => 'null' ) ) )
								{
									$ret	=	FALSE;
								}
							}
*/
						}
					}
					// Recolocamos o where para a tabela, o select_all acima usou a primeira definição e limpou a informação.
					if ( $id )
					{
						$this->db->where( $this->table.'.id', $id );
					}
					if ( $where && is_array( $where ) )
					{
						$this->db->where( $where );
					}
				}
	
				$this->_pre_delete( $id );
//echo( "COMANDO DELETE da tabela=" . $this->table . "<br>" );
				if ( $this->db->delete( $this->table ) )
				{
					$rows	=	$this->db->affected_rows();
					$ret	=	( $rows > 0 );
					$this->_post_delete( $id );
					log_message( 'debug', "JX_Model.delete($this->table). Eliminou ".$rows." linhas em ".$this->table."." );
				}
				else
				{
					$ret	=	FALSE;				
					log_message( 'debug', "JX_Model.delete($this->table). Não eliminou nenhuma linha na tabela ".$this->table."." );
				}
	 
				return $ret;
			}
			else
			{
				log_message( 'debug', "JX_Model.delete($this->table). ID null em exclusão para ".$this->table."." );
				return FALSE;
			}
		}
//echo( "deletando a tabela=" . $this->table . ".FIM<br>" );
	}
	public function _post_delete( $id )
	{
		$ret					=	TRUE;
		if ( !$this->_in_post_delete )
		{
		 	$this->_in_post_delete		=	TRUE;
		 	// COMANDOS
		 	$this->_in_post_delete		=	FALSE;
		}
		
		return $ret;
	}
	
	/**
	 * Monta FROM e SELECT.
	 */
	public function get_select_for_index()
	{
		return	$this->get_column_key().','.
			$this->get_column_title().' AS title,'.
			$this->get_column_detail().','.
			$this->get_column_when();
	}
	public function set_select_for_index()
	{
		$this->db->select( $this->get_select_for_index() );
	}
 
	public function get_select_for_one()
	{
		return $this->get_select_for_index();
	}
	public function set_select_for_one()
	{
		$this->db->select( $this->get_select_for_one() );
	}

	/**
	 * Valida as colunas antes de serem enviadas à base de dados.
	 *  $field	nome da coluna sem a tabela
	 *  $msg	string livre
	 *  $type	error, warning, ok
	 */
	public function set_field_msg( $field, $msg, $type )
	{
		$this->ar_field_msg[ $field ][ $type ][ $this->get_curr_seq_id() ][]			=	$msg;

		log_message( 'debug', "JX_Model.set_field_msg(). Criada a mensagem coluna($field) msg($msg) type($type)." );
	}
	
	public function show_field_msg()
	{
		print_r( $this->ar_field_msg );
	}
	
	public function get_curr_seq_id()
	{
		return ( ( $this->curr_id ) ? $this->curr_id : $this->curr_seq );
	}
	
	/*
	 * Retorno as mensagens das colunas enviadas.
	 * 
	 * 	(1) $_field NULL e $type NULL
	 * 		array[ field ][ type ][ id / seq ]
	 * 		(1.1) informado o $_seq_id 
	 * 			array[ field ][ type ]
	 * 
	 * 	(2) $_field NULL e $type NOT NULL
	 * 		array[ field ][ id / seq ]
	 * 		(2.1) informado o $_seq_id 
	 * 			array[ field ]
	 * 
	 * 	(3) $_field NOT NULL e $type NULL
	 * 		array[ type ][ id / seq ]
	 * 		(3.1) informado o $_seq_id 
	 * 			array[ type ]
	 * 		
	 * 	(4) $_field NOT NULL e $type NOT NULL
	 * 		array[ id / seq ]
	 * 		(4.1) informado o $_seq_id 
	 * 			array[]
	 * 
	 *	(5) e (6) Não encontrou os dados solicitados.
	 */
	public function get_field_msg( $_field = null, $_type = null, $_seq_id = null, $so_msg = FALSE )
	{
		$ret										=	array();
		if ( is_null( $_field ) ) // para todas as colunas
		{
			if ( is_null( $_type ) )
			{
				// Isola o seq_id se este foi passado.
				if ( is_null( $_seq_id ) )
				{
					// (1)
					$ret							=	$this->ar_field_msg; // Retorna tudo.
				}
				else
				{
					foreach( $this->ar_field_msg as $field => $types )
					{
						foreach( $types as $type => $msgs )
						{
							if ( key_exists( $_seq_id, $msgs ) )
							{
								// (1.1)
								if ( $so_msg )
								{
									$ret[]				=	$msgs[ $_seq_id ];
								}
								else
								{
									$ret[$field][$type][]		=	$msgs[ $_seq_id ];
								}
							}
						}
					}
				}
			}
			else
			{
				foreach( $this->ar_field_msg as $field => $types )
				{
					if ( key_exists( $_type, $types ) )
					{
						foreach( $types[ $_type ] as $seq_id => $msgs )
						{
							if ( is_null( $_seq_id ) )
							{
								// (2)
								if ( $so_msg )
								{
									$ret[ $field ][ $seq_id ][]	=	$msgs;
								}
								else
								{
									$ret[ $field ][ $seq_id ][]	=	$msgs;
								}
							}
							elseif ( key_exists( $_seq_id, $msgs ) )
							{
								// (2.1)
								if ( $so_msg )
								{
									$ret[]				=	$msgs[ $_seq_id ];
								}
								else
								{
									$ret[ $field ][]		=	$msgs[ $_seq_id ];
								}
							}
						}
					}
				}
			}
		}
		else // Para uma coluna apenas.
		{
			if ( key_exists( $_field,  $this->ar_field_msg ) ) // Isolamos a coluna solicitada.
			{
				if ( is_null( $_type ) ) // Todos os tipos da coluna.
				{
					// Isola o seq_id se este foi passado.
					if ( is_null( $_seq_id ) )
					{
						// (3)
						$ret						=	$this->ar_field_msg[ $field ];
					}
					else
					{
						foreach( $this->ar_field_msg[ $field ] as $type => $msgs )
						{
							if ( key_exists( $_seq_id, $msgs ) )
							{
								// (3.1)
								if ( $so_msg )
								{
									$ret[]				=	$msgs[ $type ][ $_seq_id ];
								}
								else
								{
									$ret[ $type ][]			=	$msgs[ $type ][ $_seq_id ];
								}
							}
						}
					}
				}
				elseif ( key_exists( $_type, $this->ar_field_msg[ $_field ] ) )
				{
					// Isola o seq_id se este foi passado.
					if ( is_null( $_seq_id ) )
					{
						// (4)
						if ( $so_msg )
						{
							$ret[]						=	$this->ar_field_msg[ $_field ][ $_type ];
						}
						else
						{
							$ret[ $seq_id ]					=	$this->ar_field_msg[ $_field ][ $_type ];
						}
					}
					else
					{
						foreach( $this->ar_field_msg[ $_field ][ $_type ] as $seq_id => $msgs )
						{
							if ( $_seq_id == $seq_id )
							{
								// (4.1)
								$ret[]				=	$msgs;
							}
						}
					}
				}
				else
				{
					// (5)
					$ret							=	NULL; // Coluna não contém mensagem.
				}
			}
			else
			{
				// (6)
				$ret								=	NULL; // Coluna não contém mensagem.
			}
		}
				
		return $ret;
	}
	/**
	 * Valida as colunas antes de serem enviadas à base de dados.
	 * 
	 */
	// Lê a configuração da tabela e prepara as variávias para tratar as constraints.
	// $what nulo prepara todas as variáveis.
	public function _pre_validate( $what = NULL )
	{
//if ( $this->table == 'kick' ) echo "_pre_validate ($what) table({$this->table})";
		/*
	 	 *  Monta as variáveis de validação para a tabela do MODEL atual.
	 	 */
	 	if ( key_exists( $this->table, $this->table_config ) )
	 	{
//if ( $this->table == 'kick' ) echo "_pre_validate (exists)";
	 		foreach( $this->table_config[ $this->table ]->ar_constraint as $cons_name => $ar_value )
			{
//if ( $this->table == 'kick' ) echo "_pre_validate (constraint=$cons_name)";
				/* UK */
				if ( key_exists( 'cons_type', $ar_value )
				&&   $ar_value[ 'cons_type' ] == 'UK'
				&&   ( $what == 'UK'
				||     is_null( $what )
				     )
				   )
				{
					$this->uk_cons_name	=	$cons_name;
					$this->uk_cons_columns	=	( key_exists( 'cons_columns', $ar_value ) ) ? $ar_value[ 'cons_columns' ] : array();
					$this->uk_force_old_id	=	( key_exists( 'force_old_id', $ar_value ) ) ? $ar_value[ 'force_old_id' ] : FALSE;
					$this->uk_error_msg	=	( key_exists( 'error_msg', $ar_value ) )    ? $ar_value[ 'error_msg' ]    : 'Já existe...';
					
					// Monta a query para validar.
					$this->uk_select	=	NULL;
				}
			 	/* UK */
				
				/* CK */
				if ( key_exists( 'cons_type', $ar_value )
				&&   $ar_value[ 'cons_type' ] == 'CK'
				&&   ( $what == 'CK'
				||     is_null( $what )
				     )
				   )
				{
					$this->ck_cons_name	=	$cons_name;
					$this->ck_cons_columns	=	( key_exists( 'cons_columns', $ar_value ) )  ? $ar_value[ 'cons_columns' ] : array();
					$this->ck_condition_php	=	( key_exists( 'condition_php', $ar_value ) ) ? $ar_value[ 'condition_php' ] : NULL;
					$this->ck_condition_sql	=	( key_exists( 'condition_sql', $ar_value ) ) ? $ar_value[ 'condition_sql' ] : NULL;
					$this->ck_error_msg	=	( key_exists( 'error_msg', $ar_value ) )     ? $ar_value[ 'error_msg' ]    : 'Valor inválido...';
				}
			 	/* UK */
			}
	 	}
	}
	
	// Disable constraints.
	public function set_cons_disable( $cons_name )
	{
		// TODO: melhor este comando quando as constraints virarem array().
		if ( is_array( $cons_name ) )
		{
			foreach( $cons_name as $name )
			{
				$this->cons_disable[ $name ]	=	TRUE;
			}
		}
		else
		{
			$this->cons_disable[ $cons_name ]	=	TRUE;
		}
	}
	// Função para personalizar em cada model.
	public function validate( $oper, $data )
	{
		// Use esta construção para invalidar os dados.
		//$this->record_valid			=	FALSE;
		
		return $data; // este return é obrigatório.
	}
	// Função interna chamada no insert() e update().
	protected function validate_internal( $oper )
	{
		$this->record_valid							=	TRUE;
		$this->goback_to_update							=	FALSE;
		log_message( 'debug', "JX_Model.validate_internal(inicio) record_valid(".  ( ( $this->record_valid )? 'TRUE': 'FALSE' ) . ")." );
		
		// Aponta para os dados da operação corrente;
		if ( $oper == 'insert' )
		{
			$data		=	$this->insert_data;
		}
		else
		{
			$data		=	$this->update_data;
		}

		//TODO: validar os dados antes de enviar ao banco de dados.
		//TODO: Extender esta validação no javascript da página.
		$fields							=	$this->get_fields_info();
		foreach( $fields as $field )
		{
			$field_name					=	$field->name;

			// Nularidade
			if ( !$field->nullable // Não pode ser NULO
			&&   is_null( $data->$field_name )
			&&   ( !( $oper == 'insert'
			&&        $field_name == 'id'
			        )
			||      ( $oper == 'update'
			        )
			     )
			   )
			{
				$this->record_valid			=	FALSE;
				$this->set_field_msg( $field_name, 'Deve ser preenchido.', 'error' );
			}
			// fim: Nularidade
		}

		// UK
	 	/*
	 	 *  Valida o array de constrains da tabela do MODEL.
	 	 */
		if ( $this->uk_cons_name == NULL )
		{
			$this->_pre_validate( 'UK' );
		}
	 	if ( $this->uk_cons_name !== NULL
	 	&&   $this->uk_cons_name != "none" // none indica que não há UK para validar.
	 	&&   !key_exists( $this->uk_cons_name, $this->cons_disable ) // lista de constraints desativadas.
	 	   )
	 	{
			$this->uk_select	=	NULL;
			$column_name_uk		=	NULL;
//if ( $this->table == 'kick' ) print_r( $fields );
	 		foreach( $this->uk_cons_columns as $column_name )
			{
				if ( key_exists( $column_name, $fields ) )
				{
					if ( $fields[ $column_name ]->type == 'int'
					||   $fields[ $column_name ]->type == 'decimal'
					||   $fields[ $column_name ]->type == 'number'
					   )
		         		{
				         	$field_value			=	$data->$column_name;
		         		}
		         		else
		         		{
		         			$field_value			=	"'". $data->$column_name . "'";
		         		}

					$column_name_uk				=	$column_name;
		         		
					if ( is_null( $this->uk_select ) )
					{
						$this->uk_select		=	"select	{$this->table}.*
											from	{$this->table}
											where	$column_name = {$field_value}";
					}
					else
					{
						$this->uk_select		.=	" and	$column_name = {$field_value}";
					}
				}
			}

//if ( $this->table == 'kick' ) echo "validate_inter (select UK) ($this->uk_select)";
			if ( !is_null( $this->uk_select ) )
			{
				$qry		=	$this->db->query( $this->uk_select );
				$rows		=	$qry->result_object();
				$qry->free_result();

				if ( count( $rows ) == 1 ) // Existe UMA linha;
				{
					foreach( $rows as $row )
					{
						if ( $oper == 'insert' )
						{
							if ( $this->uk_force_old_id )
							{
								$data->id				=	$row->id;
								$this->goback_to_update			=	TRUE;
							}
							else
							{
								$this->record_valid			=	FALSE;
								$this->set_field_msg( $column_name_uk, $this->uk_error_msg, 'error' );
							}
						}
						else // update
						{
							if ( $this->uk_force_old_id )
							{
								$data->id				=	$row->id;
							}
							else
							{
								if ( $row->id != $data->id ) // No update se os IDs da base e da página forem diferentes nesta verificação indica que há uma erro de duplicidade.
								{
									$this->record_valid		=	FALSE;
									$this->set_field_msg( $column_name_uk, $this->uk_error_msg, 'error' );
								}
							}
						}
					}
				}
				elseif ( count( $rows ) > 1 ) // Existe MAIS DE UMA linha;
				{
					$this->set_field_msg( $column_name_uk, "UK {$this->uk_cons_name} corronpida na base de dados.", 'error' );
					$this->record_valid		=	FALSE;
				}
			}
	 	}
		// fim: UK

		// CK
	 	/*
	 	 *  Valida o array de constrains da tabela do MODEL.
	 	 */
		if ( $this->ck_cons_name == NULL )
		{
			$this->_pre_validate( 'CK' );
		}
	 	if ( $this->ck_cons_name !== NULL
	 	&&   $this->ck_cons_name != "none" // none indica que não há UK para validar.
	 	&&   !key_exists( $this->ck_cons_name, $this->cons_disable ) // lista de constraints desativadas.
	 	   )
	 	{
	 		if ( $this->ck_condition_sql )
	 		{
		 		$sql_command						=	$this->ck_condition_sql;
				$column_name_ck						=	NULL;
			
		 		foreach( $this->ck_cons_columns as $column_name )
				{
					if ( key_exists( $column_name, $fields ) )
					{
						if ( $fields[ $column_name ]->type == 'int'
						||   $fields[ $column_name ]->type == 'decimal'
						||   $fields[ $column_name ]->type == 'number'
						   )
			         		{
					         	$field_value			=	$data->$column_name;
			         		}
			         		else
			         		{
			         			$field_value			=	"'". $data->$column_name . "'";
			         		}
						$column_name_ck				=	$column_name;
						$sql_command				=	preg_replace( "/{" . $column_name . "}/", $field_value, $sql_command );
					}
				}

		 		if ( !is_null( $sql_command ) )
				{
					$qry		=	$this->db->query( $sql_command );
					$rows		=	$qry->result_object();
					$qry->free_result();

					// TRUE = Se contiver linhas.
					// FALSE = Se não retornar linha.	
					if ( count( $rows ) >= 1 ) // Existe UMA linha;
					{
						$this->record_valid			=	FALSE;
						$this->set_field_msg( $column_name_ck, $this->ck_error_msg, 'error' );
					}
				}
	 		}
	 	}
		// fim: CK

		// Chama a rotina personalizada em cada model, se existir.
		$data			=	$this->validate( $oper, $data );

		// Aponta para os dados da operação corrente;
		if ( $oper == 'insert' )
		{
			$this->insert_data		=	$data;
		}
		else
		{
			$this->update_data		=	$data;
		}
		
		log_message( 'debug', "JX_Model.validate_internal(fim) record_valid(".  ( ( $this->record_valid )? 'TRUE': 'FALSE' ) . ")." );
		return $this->record_valid;
	}
	
	public function _return_input_data()
	{
		//TODO: Rever esta função.
		return $this->isolate_fields();
	}

	/**
	 * 
	 * Prepara os dados para serem enviados à base de dados.
	 * 
	 */
	public function _prep_data_for_base( $oper, $data_obj )
	{
		if ( is_array( $data_obj ) )
		{
			$prep_data					=	(object) $data_obj;
			
		}
		else
		{
			$prep_data					=	$data_obj;
		}
		$data_ret						=	new stdClass();

		$fields							=	$this->get_fields_info();
		
		foreach( $fields as $field )
		{
			$field_name					=	$field->name;
			
			if ( isset( $prep_data->$field_name )
			&&   $prep_data->$field_name != NULL
			   )
			{
				if ( ( $field->type == 'datetime'
				||     $field->type == 'date'
				||     $field->type == 'timestamp'
				     )
				   )
				{
					if ( strtolower( $prep_data->$field_name ) == 'now()'
					||   strtolower( $prep_data->$field_name ) == 'current_timestamp'
					   )
					{
						$data_ret->$field_name		=	date( 'Y-m-d H:i:s' ); // $prep_data->$field_name.'xxx';
					}
					else
					{
						$_date				=	$this->singlepack->input_to_date( $prep_data->$field_name, ( strpos( $field->name, 'hora' ) != 0 ) );
						$data_ret->$field_name		=	$_date->format( 'Y-m-d H:i:s' ); // formato fixo do MYSQL.
					}
				}
				else 
				{
					$data_ret->$field_name		=	$prep_data->$field_name;
				}
			}
			else
			{
				$data_ret->$field_name			=	$field->default;

				if ( $field->type == 'timestamp' && strtolower( $data_ret->$field_name ) == 'current_timestamp' )
				{
					$data_ret->$field_name		=	date( 'Y-m-d H:i:s' );
				}
			}
		}

		// Toda tabela precisar ter ID, então criamos um caso não tenha sido informado.
		if ( !isset( $data_ret->id ) )
		{
			$data_ret->id					=	NULL;
		}
		
		return $data_ret;
	}

	/**
	 * select_one retorna uma linha específica.
	 * 
	 * Se não for passado nada para $where, será feito full na tabela e retornará a primeira linha do array.
	 */
	public function _pre_select_one()
	{
		return TRUE;
	}
	public function select_one( $where = NULL, $id = NULL )
	{
		/*
		 * Sem where fará full na tabela e não saberemos que linha retornará.
		 */
		$this->_pre_select_one();
		if ( $where )
		{
			$this->db->where( $where );
		}
		
		/*
		 * Se for enviado o ID, colocamos ele no where prefixando a tabela do model.
		 */
		if ( $id )
		{
			$this->db->where( $this->table .'.id' .' = '. $id );
		}
		$this->set_select_for_one();
		$this->set_where();
		$this->set_from_join_one();

		$this->query = $this->db->get(); // fixa para retornar apenas uma linha.
		
		log_message( 'debug', "JX_Model.select_one retornou {$this->query->num_rows} para {$this->table}." );
		if ( $this->query->num_rows > 0 )
		{
			$this->_post_select_one();
		}
		return $this->query;
	}
	public function _post_select_one()
	{
		return TRUE;
	}

	public function show_memory( $who = 'ninguem' )
	{
//		echo "...($who) memória=" . round(((memory_get_usage(true) / 1024) / 1024), 2) . "\n";
	}
	
	public function get_all_by_where( $where, $orderby = NULL, $max_rows = FALSE )
	{
		$ret			=	FALSE;
		$qry			=	$this->select_all( $where, $orderby );
		$this->show_memory( 'get_all_by_where.inicio('.$this->table.')' );
		
		// Se solicitado para limita a qtde de linha, usamos o loop abaixo para isso.
		if ( $max_rows )
		{
			$count		=	0;
			foreach( $qry->result_object() as $row )
			{
				$count	=	$count + 1;
				
				$ret[]	=	$row;
				
				if ( $count >= $max_rows )
				{
					break;
				}
			}
		}
		else // Retorna todas as linhas.
		{
			$ret		=	$qry->result_object();
		}

		$qry->free_result();
		$this->show_memory( 'get_all_by_where.fim' );
		return $ret;
	}
	
	public function get_one_by_id( $id, $set_image = TRUE )
	{
		$ret			=	FALSE;
		$qry			=	$this->select_one( NULL, $id );
		$this->show_memory( 'get_one_by_id.inicio('.$this->table.')' );
		foreach( $qry->result_object() as $row )
		{
			$ret		=	$row;
			break;
		}
		$qry->free_result();
		$this->show_memory( 'get_one_by_id.fim' );
		
		// Seta o nome do arquivo imagem.
		if ( $ret
		&&   $set_image
		   )
		{
			$ret->nome_arquivo_imagem	=	$this->get_image( $ret );
		}

		return $ret;
	}
	public function get_one_by_where( $where, $set_image = TRUE )
	{
		$ret			=	FALSE;
		$qry			=	$this->select_one( $where, NULL );
		$this->show_memory( 'get_one_by_where.inicio('.$this->table.')' );
		foreach( $qry->result_object() as $row )
		{
			$ret		=	$row;
			break;
		}
		$qry->free_result();
		$this->show_memory( 'get_one_by_where.fim' );
		
		// Seta o nome do arquivo imagem.
		if ( $ret
		&&   $set_image
		   )
		{
			$ret->nome_arquivo_imagem	=	$this->get_image( $ret );
		}

		return $ret;
	}

	/**
	 * select_all retorna todas as linhas resultantes do where enviado obedecendo o início e limite de linhas.
	 * Onde:
	 * 	$orderby		=	string composto com nome da coluna e ordenação.
	 * 						exemplo: 	'nome ASC, descr DESC'
	 * 
	 * 	$where			=	array com nome da coluna e operador como index do array e o valor da condição no valor do array.
	 * 						exemplo: 	'descr =>' => 'junior'
	 * 								'id'=>'1'
	 * 
	 * 	$row_offset		=	linha inicial do retorno. Opção para facilitar paginação.
	 * 
	 * 	$row_count		-	(limit) quantidade de linhas que devem ser retornadas após o row_start
	 */
	public function _prep_query($where = null, $orderby = null, $row_offset = null, $row_count = null )
	{
		/*
		 * Sem where fará full na tabela.
		 */
		if ( $where )
		{
			$this->db->where( $where );
		}
		
		/*
		 * Sem order by não saberemos a ordem das linhas.
		 */
		if ( $orderby )
		{
			$this->db->order_by( $orderby );
		}
		if ( $this->get_order_by() )
		{
			$this->db->order_by( $this->get_order_by() );
		}
		
		if ( $this->get_group_by() )
		{
			$this->db->group_by( $this->get_group_by() );
		}
		
		/*
		 * Determina os limites e offset da consulta.
		 */
		if ( $row_offset && $row_count )
		{
			$this->db->limit ( $row_count );
			$this->db->offset( $row_offset -1 );
		}
	}
	
	function select_for_index( $where = null, $orderby = null, $row_offset = null, $row_count = null )
	{
		$this->set_select_for_index();
		$this->set_where();

		return( $this->select_all( $where, $orderby, $row_offset, $row_count ) );
	}

	function select_all( $where = null, $orderby = null, $row_offset = null, $row_count = null, $select = null )
	{
		log_message( 'debug', 'JX_Mode.select_all()' );
		if ( $select )
		{
			$this->db->select( $select );
		}
		else
		{
			$this->set_select_for_index();
		}
		
		$this->_prep_query( $where, $orderby, $row_offset, $row_count );
		$this->set_from_join();
		
		$this->query = $this->db->get();

		/*
		 * Retorna sempre uma única linha, mesmo que a query gera N linhas.
		 */
		log_message( 'debug', "JX_Mode.select_all retornou {$this->query->num_rows} para {$this->table}." );
		return $this->query;
	}

	/**
	 * Igual ao select_all, mas aqui apenas conta as linhas que serão retornadas.
	 */
	function count_all( $where = null )
	{
		$this->_prep_query( $where, null, null, null );
		$this->set_from_join();

		$count_rows	= $this->db->count_all_results();
		
		/*
		 * Retorna sempre uma única linha, mesmo que a query gera N linhas.
		 */
		return $count_rows;
	}

	/**
	 * Retorna as linhas da última consulta executada com a opção de acrescentar os parents (linhas das tabelas pais) a cada linha.
	 * 
	 */
	public function get_query_rows( $set_parents = FALSE )
	{
		log_message( 'debug', "JX_Model.get_query_rows()." );
		$rows		=	$this->query->result_object();
		
		if ( $set_parents )
		{
			$rows	=	$this->set_parents( $rows );
		}

		if ( $rows )
		{
			$rows	=	$this->set_image( $rows );
		}

		// Set row_id.
			// Montamos o row_id sempre com {TABLE_NAME:ID}.
			//	{'STATUS':'CHANGED','VALID':'FALSE'}
		if ( $rows )
		{
			if ( $this->is_view )
			{
				$new_rows			=	array();
				foreach( $rows as $row )
				{
					$row_id			=	'{';
					$first			=	TRUE;
					foreach( $this->table_config as $table )
					{
						$column_name	=	$table->name . '_id';
						$row_id		=	( ( !$first ) ? $row_id . ',' : $row_id ) . "'$table->name':{$row->$column_name}";
						$first		=	FALSE;
					}
					$row_id			.=	'}';
					$row->row_id		=	$row_id;
					$new_rows[]		=	$row;
				}
				$rows				=	$new_rows;
			}
			else
			{
				$new_rows			=	array();
				foreach( $rows as $row )
				{
					$row->row_id		=	"{'$this->table':$row->id}";
					$new_rows[]		=	$row;
				}
				$rows				=	$new_rows;
			}
		}
		
		log_message( 'debug', "JX_Model.get_query_rows(fim.all_rows)." );
		$this->query->free_result();
		$this->show_memory( 'get_query_rows.fim' );
		return $rows;
	}
	
	/**
	 * Carrega as imagens em "nome_arquivo_imagem..." quando a linha retorna tiver um imagem_id.
	 */
	public function get_image( $row )
	{
		if ( isset( $row->imagem_id )
		&&   $row->imagem_id
		&&   class_exists( 'Imagem_model' )
		   )
		{
			return $this->imagem->get_file_name( $row->imagem_id, TRUE );
		}
		else
		{
			return NULL;
		}
	}
	public function set_image( $rows )
	{
		log_message( 'debug', "JX_Model.set_image(inicio)." );
		$ret_rows					= array();
	
		foreach ( $rows as $row )
		{
			$new_row			=	$row;
			$new_row->nome_arquivo_imagem	=	$this->get_image( $row );
			$ret_rows[]			=	$new_row;
			unset( $new_row );
		}

		log_message( 'debug', "JX_Model.set_image(fim)." );
		return $ret_rows;
	}
	
	/**
	 * Retorna as linhas dos pais (FKs) da tabela atual;
	 * 	Colunas:
	 * 		- title
	 * 		- table
	 * 		- id
	 * 		- div_content
	 */
	protected function set_parents( $rows )
	{
		return $this->get_parents( $rows, TRUE );
			
	}
	function get_parents( $par_rows = null, $set_to_row = FALSE )
	{
		log_message( 'debug', "JX_Model.get_parents(inicio)." );
		$parents					= array();
		$fk_tables					= $this->get_tables_fk();
		$ret_rows					= array();

		if ( !$par_rows )
		{
			$rows					= $this->query->result_object();
		}
		else
		{
			$rows					= $par_rows;
		}
		
		if ( $fk_tables )
		{
			foreach( $fk_tables as $key => $values )
			{
				$this->singlepack->load_lang_model_files( $values->table_name, $values->table_name, $values->table_name );
			}
	
			foreach ( $rows as $row )
			{
				$new_row		=	$row;
				$new_row->parents	=	array();

				foreach( $fk_tables as $fk_key => $values )
				{
					$column_name				= $values->column_name;
					$table_name				= $values->table_name;
					if ( isset( $row->$column_name ) )
					{
						$fk_where			= array	(
											 $table_name.'.id'	=>	$row->$column_name
											);
	
						$model_name			= $table_name;
						$rows_fk			= $this->$model_name->select_one( $fk_where )->result_object();
						foreach( $rows_fk as $row_fk )
						{
							$parent				= new stdClass();
							$parent->title			= $row_fk->title;
							$parent->table			= $table_name;
							$parent->column			= $column_name;
							$parent->header			= $this->$model_name->get_header();
							$parent->id			= $row_fk->id;
							if ( isset( $row_fk->email ) )
							{
								$parent->email		= $row_fk->email;
							}
							if ( isset( $row_fk->image ) )
							{
								$parent->image		= $row_fk->image;
								$parent->mime_type	= $row_fk->mime_type;
							}
							if ( isset( $row_fk->id_facebook ) )
							{
								$parent->id_facebook	= $row_fk->id_facebook;
							}
							
							$parents[$row->id][]		= $parent; // prepara para retornar o array de parents indexados por id.
							$new_row->parents[]		= $parent; // prepara para que a linha contenha o array de parents.
							unset( $parent );
						}
					}
					else
					{
						$parent			= new stdClass();
						$parent->title		= '(nenhum)'; // TODO: Criar lang para esta informação.
						$parent->table		= $table_name;
						$parent->column		= $column_name;
						$parent->header		= $this->$model_name->get_header();
						$parent->id		= -1;
						
						$parents[-1][]		= $parent; // prepara para retornar o array de parents indexados por id.
						$new_row->parents[]	= $parent; // prepara para que a linha contenha o array de parents.
						unset( $parent );
					}
				}

				$ret_rows[]		=	$new_row;
				unset( $new_row );
			}
		}
		else
		{
			$ret_rows			=	$rows;
		}
		log_message( 'debug', "JX_Model.get_parents(fim)." );
		
		if ( $set_to_row )
		{
			return $ret_rows;
		}
		else
		{
			return $parents;
		}
	}
	
	public function get_rows_parents()
	{
		$parents_info				=	array();
		$fk_tables				=	$this->get_tables_fk();
		
		foreach( $fk_tables as $fk_key => $values )
		{
			$column_name				= $values->column_name;
			$table_name				= $values->table_name;

			$model_name				= $table_name;
			$rows_fk				= $this->$model_name->select_for_index( NULL, 'title' )->result_object();
			$parents_info[ $table_name ]->rows	= $rows_fk;
			$parents_info[ $table_name ]->column	= $column_name;
		}

		return $parents_info;
	}
	/**
	 * Retorna string com a combinacao de colunas para montar o DETALHES que sera exibido no index.html.
	 */
	public function get_column_detail()
	{
		$ret_fields	=	'';
		$fields		=	$this->get_fields_info();
		if ( $fields )
		{
			foreach( $fields as $field )
			{
				$ret_fields	=	$ret_fields.', '.$this->table.'.'.$field->name;
			}
		}

		return $ret_fields;
	}

	/**
	 * Retorna string com a combinacao de colunas para montar o ID (pk) que sera exibido no index.html.
	 */
	public function get_column_id()
	{
		return $this->table.'.id';
	}
	public function get_column_key()
	{
		$base_fields	= '';
		$pk_fields	= $this->get_fields_pk();
		if ( $pk_fields )
		{
			foreach( $pk_fields as $field )
			{
				$base_fields	=	$base_fields.', '.$this->table.'.'.$field->name;
			}
		}

		$fk_fields	= $this->get_fields_fk();
		if ( $fk_fields )
		{
			foreach( $fk_fields as $field )
			{
				$base_fields	=	$base_fields.', '.$this->table.'.'.$field->name;
			}
		}

		return $base_fields;
	}

	/**
	 * Retorna string com a combinacao de colunas para montar o TITLE que sera exibido no index.html.
	 */
	public function get_column_title( $for_orderby = FALSE )
	{
		$peso_ref	=	array	(
						 'nome'		=>	1
						,'descr'	=>	2
						,'title'	=>	3
						,'cod'		=>	4
						);
		$peso_ant	=	9999;
		$sel_field	=	'id';
	
		foreach( $this->get_fields_text() as $field )
		{
			if ( $field->name == 'nome'
			||   $field->name == 'descr'
			||   $field->name == 'title'
			||   $field->name == 'cod'
			   )
			{
				if ( $peso_ref[ $field->name ] < $peso_ant )
				{
					$peso_ant	=	$peso_ref[ $field->name ];
					$sel_field	=	$field->name;
				}
			}
		}
		return $this->table.'.'.$sel_field;
	}
	public function get_column_title_orderby()
	{
		return $this->get_column_title( $for_orderby = TRUE );
	}
	
	/**
	 * Retorna string com a combinacao de colunas para montar o WHEN que sera exibido no index.html.
	 */
	public function get_column_when( $for_orderby = FALSE )
	{
		log_message( 'debug', "JX_Model.get_column_when( inicio )." );

		//date_format( now(), "%e/%b %Y %h:%i" )
		if ( $for_orderby )
		{
			$column				=	"now()";
		}
		else
		{
			$column				=	"now() when_field";
		}
		foreach( $this->get_fields_info() as $field )
		{
			if ( $field->type == 'datetime'
			||   $field->type == 'timestamp'
			   )
			{
				if ( $for_orderby )
				{
					$column	=	$this->table.'.'.$field->name;
				}
				else
				{
					$column	=	'date_format( '.$this->table.'.'.$field->name.', "%e/%m/%Y %h:%i" ) when_field';
				}
				break;
			}
			if ( $field->type == 'date' )
			{
				if ( $for_orderby )
				{
					$column	=	$this->table.'.'.$field->name;
				}
				else
				{
					$column	=	'date_format( '.$this->table.'.'.$field->name.', "%e/%m/%Y" ) when_field';
				}
				break;
			}
		}
		log_message( 'debug', "JX_Model.get_column_when( fim coluna=$column )." );
		return $column;
	}
	public function get_column_when_orderby()
	{
		return $this->get_column_when( $for_orderby = TRUE );
	}

	/**
	 * Configura a exibição de cada coluna na tela de edição.
	 */
	function set_field_property()
	{
		return TRUE;
	}

	/**
	 * Retorna o WHERE para retornar todas as linhas que contenham a "string" enviada.
	 * Usa todas as colunas do tipo texto.
	 */
	function get_where_search_all( $what = null )
	{
		$where				=	"";
		if ( $what )
		{
			$text_fields		=	$this->get_fields_text();
			foreach( $text_fields as $field )
			{
				if ( $where != '' )
				{
					$where	=	$where." or ";
				}
				$where		=	$where."upper( ".$this->table.'.'.$field->name." ) like '%".strtoupper( $what )."%'";
			}
		}

		// Acrescenta o title.
		$title_column	= $this->get_column_title();
		if ( $title_column )
		{
			if ( $where != '' )
			{
				$where	=	$where." or ";
			}
			$where		=	$where."upper( ".$title_column." ) like '%".strtoupper( $what )."%'";
		}
		
		return $where;
	}
	
	/**
	 * 
	 */
	function get_where_filter_parent( $filter_parent = null )
	{
		$where			=	"";
		if ( $filter_parent )
		{
			$where		=	 $this->table.'.'.$filter_parent;
		}

		return $where;
	}

	/**
	 * Fornece métodos para montar o where e permitir a personalização das queries do Model.
	 */
	public function get_where()
	{
		return null;
	}
	public function set_where()
	{
		if ( $this->get_where() )
		{
			$this->db->where( $this->get_where() );
		}
	}
	/**
	 * Fornece métodos para que seja possível alterar o from e acrescentar mais tabelas nas consultas.
	 */
	public function set_from_join()
	{
		$this->db->from( $this->table );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	/**
	 * Todas as páginas de index e search retornam "title" e "when".
	 * Aqui montamos um order by baseado nas informacoes enviadas pela página.
	 */
	public function get_order_by()
	{
		return null;
	}

	public function _prep_order_by( $selection = null, $direction = null )
	{
		$order_by			=	null;
		if ( $selection == 'title' )
		{
			$order_by		=	$this->get_column_title_orderby();
		}
		else
		{
			$order_by		=	$this->get_column_when_orderby();
		}
		
		if ( $direction == "+" )
		{
			$order_by		=	$order_by." ASC";
		}
		else 
		{
			$order_by		=	$order_by." DESC";
		}
		
		$pers_orderby			=	$this->get_order_by();
		if ( $pers_orderby )
		{
			if ( $order_by )
			{
				$return		=	$order_by . ', ' . $pers_orderby;
			}
			else
			{
				$return		=	$pers_orderby;
			}
		}
		else
		{
			$return			=	$order_by;
		}
		return $return;		
	}
	
	/**
	 * 
	 * Retorna o group by da query.
	 */
	public function get_group_by()
	{
		return NULL;
	}
	
	/**
	 * Retorna informações da revision do módulo.
	 * O que retorna:
	 * 	- rev			- último número
	 * 	- author		- quem fez a última alteração
	 * 	- when			- quando foi feita a última alteração
	 * 	- name			- nome do fonte.
	 * 	- all			- retorna toda a string.
	 */
	function get_version( $what = 'all' )
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
}
/* End of file JX_Model.php */
/* Location: ./application/core/JX_Model.php */