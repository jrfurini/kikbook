<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Extendendo o CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Jarvix.com.br
 * @license		http://jarvixplus.com.br/licence
 * @link		http://jarvixplus.com.br
 * @since		Version 0.1
 * @filesource		/application/core/JX_Input.php
 *
 * $Id: JX_Input.php,v 1.19 2013-03-11 18:46:15 junior Exp $
 *
 */

class JX_Input extends CI_Input
{
	var $input_cube					=	array();
	var $current_cube_key				=	NULL;
	
	function __construct()
	{
		parent::__construct();

		$this->_mount_cube();
	}

	/**
	 * 
	 * Monta um cubo contendo todos os campos da página.
	 * 	Em páginas com multi tabelas, o nome da tabela representará no primeiro nó de todas as linhas que existirem dela.
	 * 	Em páginas com array de linhas, multilinhas, estas serão representadas por um ARRAY no último nó da pilha de dados.
	 * 
	 */
	protected function _mount_cube()
	{
//echo json_encode( $_GET );
		if ( $_POST )
		{
			$this->input_cube			=	json_decode( json_encode( $_POST ) );
		}
		elseif ( $_GET )
		{
			$this->input_cube			=	json_decode( json_encode( $_GET ) );
//print_r($this->input_cube);
		}
		else
		{
			$this->input_cube			=	NULL;
		}
		
/*
		EXEMPLOS DE ACESSO AOS DADOS DO CUBO
*/

		if ( $this->input_cube )
		{
			foreach( $this->input_cube as $cube_key => $cube_value )
			{
				log_message( 'debug', "JX_Input._mount_cube  key={$cube_key}" );
				$this->_open_object( $cube_value );
			}
		}

/*			
			foreach( $this->input_cube->treino_exercicio->id->tab_1 as $k => $v )
			{
				echo 'key='.$k .' v='.$v.'<br/>';
			}
			$this->input_cube->treino_exercicio->id->tab_1[0] = 'aaa';
*/
/*
			echo 'teste de valores<br>';
			echo 'Junior teste jx-order-selection='.$this->input_cube->jx_action.'<br>';
			echo 'Junior teste treino.id='.$this->input_cube->treino->id.'<br>';
			echo 'Junior teste treino_sub.id='.$this->input_cube->treino_sub->id->tab_1.'<br>';
			echo 'Junior teste treino_exercicio.id='.$this->input_cube->treino_exercicio->id->tab_1[0].'<br>';
			echo 'Junior teste treino_exercicio.id='.$this->input_cube->treino_exercicio->id->tab_1[1].'<br>';
*/
	}
	
	public function _open_object( $obj )
	{
		$this->level++;
		
		$str_pontos = str_pad( '', $this->level * 6, "..." );
			
		if ( is_object( $obj ) )
		{
			foreach( $obj as $value_key => $value_value )
			{
				log_message( 'debug', $str_pontos." key={$value_key}" );
				$this->_open_object( $value_value );
			}
		}
		else if ( is_array( $obj ) )
		{
			foreach( $obj as $value_value )
			{
				log_message( 'debug', $str_pontos." value={$value_value}" );
				$this->_open_object( $value_value );
			}
		}
		
		$this->level--;
	}

	protected function _get_value_from_array( $array )
	{
		foreach( $array as $key => $value )
		{
			return $array[ array_values( $this->key_array ) ];
		}
	}
	
	/**
	 * Em visões temos que duplicar os dados vindos pela visão em todas as tabela configuradas no model da Visão.
	 */
	public function duplicate_data_vw( $view_name, $table_name )
	{
		if ( is_object( $this->input_cube )
		&&   isset( $this->input_cube->$view_name )
		   )
		{
			// Copia os dados para a tabela.
			$read_array					=	get_object_vars( $this->input_cube->$view_name );
			$new_obj					=	new stdClass();

			foreach( $read_array as $key => $value )
			{
				if ( $key != 'id' ) // Não inserimos o ID, ele será criado pelo IF abaixo.
				{
					if ( $key == $table_name . '_id' ) // Criamos um novo ID a partir do nome_da_tabel_ID.
					{
						$key			=	str_replace( $table_name . '_', '', $key );
					}

					$new_obj->$key			=	$value;
				}

			}

			$this->input_cube->$table_name			=	$new_obj;
		}
	}
	
	/**
	 * Esta função responde para todos os métodos se a tabela enviada contém ou não multiplos valores no cubo.
	 */
	public function are_multi_id( $table_name )
	{
		if ( isset( $this->input_cube->$table_name ) )
		{
			return is_object( $this->input_cube->$table_name->id )
			||     is_array( $this->input_cube->$table_name->id );
		}
		else
		{
			return FALSE;
		}
	}
	
	/*
	 * Esta função pega de forma direta as informações da tabela no cubo.
	 */
	public function get_table_data( $table_name )
	{
		if ( $this->are_multi_id( $table_name ) )
		{
			return FALSE;
		}
		else
		{
			if ( isset( $this->input_cube->$table_name ) )
			{
				$ret_data	=	$this->input_cube->$table_name;
			}
			else
			{
				$ret_data	=	FALSE;
			}
			return $ret_data;
		}
	}

	/*
	 * Retorna o status da linha atual da tabela informada.
	 */
	public function get_table_status( $table_name )
	{
		if ( $this->are_multi_id( $table_name ) )
		{
			return FALSE;
		}
		else
		{
			return 'CHANGED';
		}
	}

	/**
	 * Retorna N conjuntos de campos para que o update ou insert funcionem.
	 */
	public function extract_rows( $fields, $table_name, $first = TRUE, &$ret_array = NUll, &$cube_keys = NULL, &$array_key = NULL )
	{
		if ( $first )
		{
			$cube_keys						=	array();
			$loop_array_key						=	$this->input_cube->$table_name->id;
		}
		else
		{
			$loop_array_key						=	$array_key;
		}
		
		// Usamos o ID como referencia para ler todas as colunas em seus index no cubo.
		foreach( $loop_array_key as $key => $value  )
		{
			if ( is_object( $value )
			||   is_array( $value )
			   )
			{
				$cube_keys[]					=	$key;
				$this->extract_rows( $fields, $table_name, FALSE, $ret_array, $cube_keys, $value );
				$dummy_key					=	array_pop( $cube_keys );
			}
			else
			{
				$cube_keys[]					=	$key;
				$new_row					=	new stdClass();
				$new_row->data					=	new stdClass();
				foreach( $fields as $field )
				{
					$field_name				=	$field;
					if ( isset( $this->input_cube->$table_name->$field_name ) )
					{
						$new_row->data->$field_name	=	$this->get_value_from_object( $this->input_cube->$table_name->$field_name, $cube_keys );
					}
				}
				
				
				// Registra as chaves de acesso ao cubo.
				$new_row->cube_keys				=	$cube_keys;
				// Registra o status da linha no cubo.
				$field_name					=	"jx_record_control";
				if ( isset( $this->input_cube->$table_name->$field_name ) )
				{
					$jx_record_control			=	str_replace( "[", "", $this->get_value_from_object( $this->input_cube->$table_name->$field_name, $cube_keys ) ); 
					$jx_record_control			=	str_replace( "]", "", $jx_record_control ); 
					$jx_record_control			=	str_replace( "'", '"', $jx_record_control ); 
					if ( $jx_record_control ) // {STATUS:"NEW",VALID:"TRUE"}
					{
						$jx_record_control		=	json_decode( $jx_record_control );

						$new_row->record_status		=	$jx_record_control->STATUS;
						$new_row->record_valid		=	( strtoupper( $jx_record_control->VALID ) == 'TRUE' ) ? TRUE : FALSE;
					}
					else
					{
						$new_row->record_status		=	'CHANGED';
						$new_row->record_valid		=	TRUE;
					}
					$new_row->db_message			=	NULL;
				}
				$ret_array[]					=	$new_row;
				
				unset( $new_row );
				// Como já usou esta chave, retiramos a última chave do array para ser colocada outro no retorno do loop.
				$dummy_key					=	array_pop( $cube_keys );
			}
		}
		return $ret_array;
	}
	protected function get_value_from_object( $obj, $keys )
	{
		$ret_value				=	$obj;
		if ( is_array( $keys )
		&&   ! empty( $keys )
		   )
		{
			$key_array			=	$keys;
			$key				=	array_shift( $key_array ); // retorna o primeiro valor do array
		
			while ( is_object( $ret_value )
			||      is_array( $ret_value )
			      )
			{
				if ( is_numeric( $key )
				&&   is_array( $ret_value )
				   )
				{
					if ( key_exists( $key, $ret_value ) )
					{
						$ret_value	=	$ret_value[ $key ];
					}
					else
					{
/*
echo( '$ret_value<br/>');
print_r( $ret_value );
echo( '<br/>$key<br/>');
print_r( $key );
echo( '<br/>$keys<br/>');
print_r( $keys );
echo( '<br/>$obj<br/>');
print_r( $obj );
show_error( 'JX_Input.get_value_from_object():<br/>Há uma disparidade entre $keys e o array $obj.<br/><br/>Isso pode estar ocorrendo por existir uma quantidade de INPUTs diferentes entre as colunas da tabela.<br/><br/>Veja o topo da página para ter o conteúdo das variáveis de controle.' );
die;
*/
						$ret_value	=	NULL;
					}
				}
				else
				{
					$ret_value	=	$ret_value->$key;
				}
				$key			=	array_shift( $key_array ); // retorna o próximo valor do array.
			}

		}

		return $ret_value;
	}
	
	public function set_current_cube_key( $value )
	{
		$this->current_cube_key				=	$value;
	}
	public function get_current_cube_key()
	{
		return $this->current_cube_key;
	}
	
	/**
	 * Copy_from IDs que acabaram de ser inseridos/alterados para os registros filhos, caso existam, e estejam nulos.
	 */
	public function copy_from_id( $id, $cube_keys, $table_name, $tables_array )
	{
		log_message( 'debug', "JX_Input.copy_from_id(). ID(".$id.") Table(".$table_name.")." );

		$field_name				=	$table_name . '_id';
		$set_command_last_key			=	NULL;
		$last_is_obj				=	FALSE;
		$set_command_prev			=	NULL;
		$set_command				=	NULL;
		$command				=	NULL;

		foreach( $cube_keys as $key )
		{
			log_message( 'debug', "...key(cube) ".$key );
		}

		if ( is_array( $tables_array )
		&&   ! empty( $tables_array )
		   )
		{
			if ( is_array( $tables_array ) )
			{
				$from_table			=	$tables_array[ $table_name ];
			}
			else
			{
				$from_table			=	$tables_array->$table_name;
			}

			foreach( $tables_array as $table )
			{
				log_message( 'debug', '...tableName='.$table->name );
				if ( is_array($table->r_table_name) )
				{
					foreach( $table->r_table_name as $pais )
					{
						log_message( 'debug', '......r_table_name='.$pais );
					}
				}
				else
				{
						log_message( 'debug', '......r_table_name='.$table->r_table_name );
				}

				// Copia o ID para as tabelas FILHO.
				if ( $table_name != NULL
				&&   $table->read_write	!= 'readonly'
				&&   class_exists( $table->name . "_model" ) // Confirma se o model foi instanciado.
				&&   $table_name != $table->name // Não copia para a própria tabela.
				&&   ( ( is_array( $table->r_table_name )
				&&       in_array( $table_name, $table->r_table_name )
				       )
				||     ( !is_array( $table->r_table_name )
				&&       $table_name == $table->r_table_name
				       )
				     )
				   )
				{
					$set_command						=	'$this->input_cube->'.$table->name.'->'.$table_name.'_id';// colocamos a tabela e a coluna de FK.
					$set_command_recctrl					=	'$this->input_cube->'.$table->name.'->jx_record_control';// preparamos para marcar a linha dos filhos como alteradas.
					$set_command_prev					=	$set_command;
					$set_command_recctrl_prev				=	$set_command_recctrl;
					if ( is_array( $cube_keys )
					&&   ! empty( $cube_keys )
					   )
					{
						foreach ( $cube_keys as $key )
						{
							$set_command_prev			=	$set_command;
							$set_command_recctrl_prev		=	$set_command_recctrl;
							if ( is_numeric( $key ) )
							{
								if ( $from_table->part_of_view !== FALSE
								&&   $table->part_of_view == $from_table->part_of_view
								   )
								{
									$set_command		=	$set_command.'['.$key.']';
									$set_command_recctrl	=	$set_command_recctrl.'['.$key.']';
								}
								else
								{
									$set_command		=	$set_command;
									$set_command_recctrl	=	$set_command_recctrl;
								}
								$last_is_obj			=	FALSE;
								$set_command_last_key		=	$key;
							}
							else
							{
								if ( $from_table->part_of_view !== FALSE
								&&   $table->part_of_view == $from_table->part_of_view
								   )
								{
									$set_command		=	$set_command.'->'.$key;
									$set_command_recctrl	=	$set_command_recctrl.'->'.$key;
								}
								else
								{
									$set_command		=	$set_command;
									$set_command_recctrl	=	$set_command_recctrl;
								}
								$last_is_obj			=	TRUE;
								$set_command_last_key		=	$key;
							}
						}
					}
					
					if ( $last_is_obj )
					{
						$command		=	"
										if ( isset( {$set_command} ) )
										";
					}
					else
					{
						$command		=	"
										if ( key_exists( '$set_command_last_key', {$set_command_prev} ) )
										";
					}

					if ( $from_table->part_of_view !== FALSE
					&&   $table->part_of_view == $from_table->part_of_view
					   )
					{
						$command		=	$command.
										"
										{
											if ( is_array( {$set_command} )  )
											{
												log_message( 'debug', 'command.1( \"é um array()\" )' );
												if ( key_exists( \$key, {$set_command_recctrl} ) )
												{
													\$jx_record_control		=	str_replace( \"[\", \"\", {$set_command_recctrl}[\$key] );
													\$jx_record_control		=	str_replace( \"]\", \"\", \$jx_record_control );
													\$jx_record_control		=	str_replace( \"'\", '\"', \$jx_record_control );
													if ( \$jx_record_control )
													{
														\$jx_record_control	=	json_decode( \$jx_record_control );
														\$record_status		=	\$jx_record_control->STATUS;
													}
													else
													{
														\$record_status		=	'CHANGED';
													}
												}
												else
												{
													\$record_status			=	'CHANGED';
												}
												
												if ( ( \$table->force_copy_from
												||     \$record_status == 'CHANGED'
												     )
												&&   empty( {$set_command}[\$key] )
												   )
												{
													{$set_command}[\$key] = {$id};
													{$set_command_recctrl}[\$key] = \"{'STATUS':'CHANGED','VALID':'FALSE'}\";
												}
											}
											else
											{
												log_message( 'debug', 'command.1( \"NÃO é um array()\" )' );
												if ( isset( {$set_command_recctrl} ) )
												{
													log_message( 'debug', '....jx_record_control=' . $set_command_recctrl );
													\$jx_record_control		=	str_replace( \"[\", \"\", {$set_command_recctrl} );
													\$jx_record_control		=	str_replace( \"]\", \"\", \$jx_record_control );
													\$jx_record_control		=	str_replace( \"'\", '\"', \$jx_record_control );
													
													log_message( 'debug', '....ficou assim jx_record_control=' . \$jx_record_control );
													if ( \$jx_record_control )
													{
														\$jx_record_control	=	json_decode( \$jx_record_control );
														\$record_status		=	\$jx_record_control->STATUS;
														log_message( 'debug', '....record_status(1)=' . \$record_status );
													}
													else
													{
														\$record_status		=	'CHANGED';
														log_message( 'debug', '....record_status(2)=' . \$record_status );
													}
												}
												else
												{
													log_message( 'debug', 'command.1( \"....NÃO tem jx_record_control=\" )' );
													\$record_status			=	'CHANGED';
													log_message( 'debug', '....record_status(3)=' . \$record_status );
												}
												
												if ( ( \$table->force_copy_from
												||     \$record_status == 'CHANGED'
												     )
												&&   empty( {$set_command}[\$key] )
												   )
												{
													log_message( 'debug', '....ALTEROU' );
													{$set_command} = {$id};
													{$set_command_recctrl} = \"{'STATUS':'CHANGED','VALID':'FALSE'}\";
												}
											}
										}
										";
						log_message( 'debug', "command.1.2(  ".$command."  )" );
					}
					else
					{						$command		=	$command.
										"
										{
											if ( is_array( {$set_command} )  )
											{
												log_message( 'debug', 'command.1( \"é um array()\" )' );
												foreach( {$set_command} as \$key => \$value )
												{
													if ( key_exists( \$key, {$set_command_recctrl} ) )
													{
														\$jx_record_control		=	str_replace( \"[\", \"\", {$set_command_recctrl}[\$key] );
														\$jx_record_control		=	str_replace( \"]\", \"\", \$jx_record_control );
														\$jx_record_control		=	str_replace( \"'\", '\"', \$jx_record_control );
														if ( \$jx_record_control )
														{
															\$jx_record_control	=	json_decode( \$jx_record_control );
															\$record_status		=	\$jx_record_control->STATUS;
														}
														else
														{
															\$record_status		=	'CHANGED';
														}
													}
													else
													{
														\$record_status			=	'CHANGED';
													}
													
													if ( ( \$table->force_copy_from
													||     \$record_status == 'CHANGED'
													     )
													&&   empty( {$set_command}[\$key] )
													   )
													{
														{$set_command}[\$key] = {$id};
														{$set_command_recctrl}[\$key] = \"{'STATUS':'CHANGED','VALID':'FALSE'}\";
													}
												}
											}
											else
											{
												log_message( 'debug', 'command.1( \"NÃO é um array()\" )' );
												if ( isset( {$set_command_recctrl} ) )
												{
													log_message( 'debug', '....jx_record_control=' . $set_command_recctrl );
													\$jx_record_control		=	str_replace( \"[\", \"\", {$set_command_recctrl} );
													\$jx_record_control		=	str_replace( \"]\", \"\", \$jx_record_control );
													\$jx_record_control		=	str_replace( \"'\", '\"', \$jx_record_control );
													
													log_message( 'debug', '....ficou assim jx_record_control=' . \$jx_record_control );
													if ( \$jx_record_control )
													{
														\$jx_record_control	=	json_decode( \$jx_record_control );
														\$record_status		=	\$jx_record_control->STATUS;
														log_message( 'debug', '....record_status(1)=' . \$record_status );
													}
													else
													{
														\$record_status		=	'CHANGED';
														log_message( 'debug', '....record_status(2)=' . \$record_status );
													}
												}
												else
												{
													log_message( 'debug', 'command.1( \"....NÃO tem jx_record_control=\" )' );
													\$record_status			=	'CHANGED';
														log_message( 'debug', '....record_status(3)=' . \$record_status );
												}
												
												if ( ( \$table->force_copy_from
												||     \$record_status == 'CHANGED'
												     )
												&&   empty( {$set_command}[\$key] )
												   )
												{
													log_message( 'debug', '....ALTEROU' );
													{$set_command} = {$id};
													{$set_command_recctrl} = \"{'STATUS':'CHANGED','VALID':'FALSE'}\";
												}
											}
										}
										";
						log_message( 'debug', "command.1.1(  ".$command."  )" );
					}
//echo 'comando='.$set_command.'<br>';
//>>>
/*
if ( is_array( $this->input_cube->equipe->equipe_id[0] ) )
{
	foreach( $this->input_cube->equipe->equipe_id[0] as $key => $value )
	{
		eval( '$this->input_cube->equipe->equipe_id[0][$key] = 5;' );
	}
}
else
{
	$this->input_cube->equipe->equipe_id[0] = 5;
}
*/
//<<<
					eval( $command );
					log_message( 'debug', "command.1.OK" );
				}
				// Copia o ID para a própria tabela dentro do Cubo. Isso é muito útil após o insert.
				elseif ( $table_name != NULL
				&&   ( ( is_array( $table->name )
				&&       in_array( $table_name, $table->name )
				       )
				||     ( $table_name == $table->name
				       )
				     )
				   )
				{
					$set_command					=	'$this->input_cube->'.$table->name.'->id';// colocamos a tabela e a coluna ID.
					$set_command_prev				=	$set_command;
					if ( is_array( $cube_keys )
					&&   ! empty( $cube_keys )
					   )
					{
						foreach ( $cube_keys as $key )
						{
							$set_command_prev		=	$set_command;
							if ( is_numeric( $key ) )
							{
								$set_command		=	$set_command.'['.$key.']';
								$last_is_obj		=	FALSE;
								$set_command_last_key	=	$key;
							}
							else
							{
								$set_command		=	$set_command.'->'.$key;
								$last_is_obj		=	TRUE;
								$set_command_last_key	=	$key;
							}
						}
					}
					
					
					if ( $last_is_obj )
					{
						$command		=	"
										if ( isset( {$set_command} ) )
										";
					}
					else
					{
						$command		=	"
										if ( key_exists( '$set_command_last_key', {$set_command_prev} ) )
										";
					}
					$command			=	$command.
										"
										{
											if ( is_array( {$set_command} )  )
											{
												foreach( {$set_command} as \$key => \$value )
												{
													eval( '{$set_command}[\$key] = {$id};' );
												}
											}
											else
											{
												{$set_command} = {$id};
											}
										}
										";
					log_message( 'debug', "command.2(  ".$command."  )" );
					eval( $command );
					log_message( 'debug', "command.2.OK" );
				}
			}
		}
		log_message( 'debug', "JX_Input.copy_from_id(). FIM" );
	}
	
	/**
	 * Limpa valores inválidos do input de dados.
	 * 
	 */
	function _clean_input_keys($str)
	{
		if ( ! preg_match("/^[a-z0-9:_\/-]+$/i", $str))
		{
			$str = '0';//exit('Disallowed Key Characters.');
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			$str = $this->uni->clean_string($str);
		}

		return $str;
	}

	/**
	 * Transforma nome do campo em um array.
	 */
	protected function _explode_index( $index = '' )
	{
		$new_index	=	str_replace( '][', ',', $index );
		$new_index	=	str_replace( ']', '', $new_index );
		$new_index	=	str_replace( '[', ',', $new_index );

		return explode( ',', $new_index );
	}
	// --------------------------------------------------------------------

	/**
	 * Fetch from multidimension array
	 */
	protected function _fetch_from_array_multi( &$array, $index = '', $xss_clean = TRUE)
	{
		$index_array 				=	$this->_explode_index( $index );
		$str_value				=	NULL;
		$str_index				=	NULL;
		$new_array				=	$array;
		
		if ( is_array( $index_array ) )
		{
			// Varre o array multidimensional descendo até achar um valor.
			foreach( $index_array as $ind )
			{
//echo '_fetch index='.$ind.'<br/>';
				if ( is_array( $new_array ) )
				{
					$new_array	=	$new_array[ $ind ];
				}
				$str_index		=	$ind;
			}

		}
		else
		{
			$str_value			=	$index;
		}
		
		$str_value				=	$new_array;
		
		if ( $str_value == NULL )
		{
			return FALSE;
		}

		if ($xss_clean === TRUE)
		{
			if ( ! is_array( $str_value ) )
			{
				return $this->security->xss_clean( $str_value );
			}
		}

		return $str_value;
	}

	// --------------------------------------------------------------------

	/**
	* Fetch an item from the GET multidimension array
	*/
	function get_multi($index = NULL, $xss_clean = FALSE)
	{
//echo 'get_multi='. $index . '<br>';
		if ( count( $this->_explode_index( $index ) ) > 1 )
		{
			// Check if a field has been provided
			if ($index === NULL AND ! empty($_GET))
			{
				$post = array();
	
				// Loop through the full _POST array and return it
				foreach (array_keys($_GET) as $key)
				{
					$post[$key] = $this->_fetch_from_array_multi($_GET, $key, $xss_clean);
				}
				return $post;
			}
	
			return $this->_fetch_from_array_multi($_GET, $index, $xss_clean);
		}
		else
		{
//echo '...get_multi return (1)<br>';
			return $this->get($index, $xss_clean);
		}
	}

	// --------------------------------------------------------------------

	/**
	* Fetch an item from the POST multidimension array
	*/
	function post_multi( $index = NULL, $xss_clean = FALSE )
	{
//echo 'post_mul='. $index . '<br>';
		if ( count( $this->_explode_index( $index ) ) > 1 )
		{
			// Check if a field has been provided
			if ($index === NULL AND ! empty($_POST))
			{
				$post = array();
	
				// Loop through the full _POST array and return it
				foreach (array_keys($_POST) as $key)
				{
					$post[$key] = $this->_fetch_from_array_multi($_POST, $key, $xss_clean);
				}
				return $post;
			}
	
			return $this->_fetch_from_array_multi($_POST, $index, $xss_clean);
		}
		else
		{
			return $this->post($index, $xss_clean);
		}
	}


	// --------------------------------------------------------------------

	/**
	* Fetch an item from either the GET multidimension array or the POST
	*/
	function get_post_multi( $index = '', $xss_clean = FALSE)
	{
//echo 'get_post_multi='. $index . '<br>';
		
		if ( ! isset( $_POST[ $index ] ) )
		{
			return $this->get_multi( $index, $xss_clean );
		}
		else
		{
			return $this->post_multi( $index, $xss_clean );
		}
	}
}

/* End of file JX_Input.php */
/* Location: ./application/core/JX_Input.php */