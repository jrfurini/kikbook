<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Single Pack
 *
 * @package		Single Pack
 * @author		Junior Furini
 * @copyright	Copyright (c) 2012 - 2012, Jarvix, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 0.1
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Montagem de campos em páginas
 *
 * @package		Single Pack
 * @subpackage	Core
 * @category	Fields
 * @author		Junior Furini
 */
class JX_Field
{
	protected $CI;
	protected $field_info;
	protected $table_name;
	
	protected $count_fields			=	0;
	protected $count_ck			=	0;
	
	protected $data_type_field		=	'text';
	protected $input_type			=	'text';
	protected $value_field			=	NULL;
	protected $field_array_count_id		=	array();
	protected $html_field_id		=	NULL;
	
	/**
	 * Constructor
	 */
	public function __construct( $field = NULL, $table_name = NULL )
	{
		$this->CI =& get_instance();

		if ( !$field )
		{
			log_message('debug', "JX_Field Class AUTOLOAD. Initialized.");
		}
		else
		{
			$this->field_info		=	$field;
			$this->table_name		=	$table_name;
			
			if ( isset( $this->field_info->name ) )
			{
				$field_name		=	$this->field_info->name;
			}
			else
			{
				$field_name		=	'não enviado';
			}
			$this->field_info->fk_field	=	( strrpos( strtolower( $this->field_info->name ), "_id" ) != 0 );
			
			$this->count_ck			=	0;
			
			log_message('debug', "JX_Field Class '".$this->table_name." / ".$field_name."' Initialized.");
		}
	}

	/**
	 * Funções para auxiliar a montagem de formulários de edição.
	 */
	public function label( $th = FALSE )
	{
		if ( $th )
		{
			$dois_ponto	=	"";
		}
		else
		{
			$dois_ponto	=	":";
		}
		if ( $this->field_info->nullable ) // se der erro aqui, pode ser pela falta desta variável em mysql_result.php em /system/database/drivers/mysql.
		{
			return "<label>".$this->CI->lang->get_line( $this->field_info->name, $this->table_name ) . $dois_ponto.  "</label>";	
		}
		else
		{
			return "<label class='required'>". "* ". $this->CI->lang->get_line( $this->field_info->name, $this->table_name )  . $dois_ponto . "</label>";
		}
	}
	
	public function get_max_id( $field_index )
	{
		return $this->field_array_count_id[ $field_index ];
	}

	protected function get_field_id( $add = FALSE, $string_for_id = NULL, $field_name = NULL )
	{
		if ( !$field_name )
		{
			$field_name		=	$this->field_info->name;
		}

		if ( !array_key_exists( $this->table_name."_".$field_name, $this->field_array_count_id ) )
		{
			$this->field_array_count_id[ $this->table_name."_".$field_name ]	=	0;
		}

		if ( $add
		||   $this->field_array_count_id[ $this->table_name."_".$field_name ]	== 0
		   )
		{
			$this->field_array_count_id[ $this->table_name."_".$field_name ]++;
		}


		if ( $string_for_id == NULL )
		{
			return $this->field_array_count_id[ $this->table_name."_".$field_name ];
		}
		else
		{
			return $string_for_id;
		}
	}

	protected function get_field_name( $print_as_array = TRUE, $add_id = TRUE, $string_for_tabs = NULL, $string_for_id = NULL, $prefix = NULL, $field_name = NULL )
	{
		if ( !$field_name )
		{
			$field_name		=	$this->field_info->name;
			$set_id			=	TRUE;
		}
		else
		{
			$set_id			=	FALSE;
		}

		$id				=	$this->get_field_id( $add_id, $string_for_id, $field_name );
		if ( $set_id )
		{
			$this->html_field_id	=	$this->table_name.'_'.$field_name."_".$id;
		}

		$html_field_title		=	$this->table_name.'_'.$field_name."_".$id;
		
		if ( $string_for_tabs )
		{
			$str_tabs		=	'['.$string_for_tabs.']';
		}
		else
		{
			$str_tabs		=	NULL;
		}

		if ( $print_as_array )
		{
			return 'name="'.$this->table_name.'['.$prefix.$field_name.']'.$str_tabs.'[]" id="'.$html_field_title.'"';
		}
		else
		{
			return 'name="'.$this->table_name.'['.$prefix.$field_name.']'.$str_tabs.'" id="'.$html_field_title.'"';
		}
	}

	/**
	 * Conjunto de funções para controle de validação de campos em server side e client side.
	 */
	/*
	 * 
	 * Retorna a validação do campo em Javascript.
	 * 
	 * @param string $prefix
	 */
	public function validation_javascript( $prefix = NULL )
	{
		return $prefix . $this->html_field_id .":{ required: true, email: true }" . "\n";
	}
	
	/*
	 * 
	 * Retorna a mensagem que será exibida pelo Javascript quando ocorrer algum erro no campo.
	 * 
	 * @param string $prefix
	 */
	public function validation_message( $prefix = NULL )
	{
		return $prefix . $this->html_field_id .':{ required: "Este campo deve ser preenchido", email: "Digite um e-mail válido." }' . "\n";
	}
	
	public function validation_client( $table_fields )
	{
return NULL;
		if ( isset( $table_fields )
		&&   $table_fields
		&&   is_object( $table_fields )
		   )
		{
			$html			=	NULL;
	
			$html			=	$html .
						'
<script>
$(document).ready( function() {
	$("#formularioContatoform").validate({
		rules:		{
						';

			$first_field				=	TRUE;
			foreach( $table_fields as $table_name => $table_info )
			{
				foreach( $table_info->fields as $field )
				{
//$html		=	$html . $field->name . ' count=' . $this->field_array_count_id[ $table_name."_".$field->name ];
					for ( $i=1; $i <= $field->edit->get_max_id( $table_name."_".$field->name ); $i++ )
					{
						if ( $first_field )
						{
							$first_field	=	FALSE;
							$html		=	$html .
										$field->edit->validation_javascript( " " ); 
						}
						else
						{
							$html		=	$html .
										$field->edit->validation_javascript( $prefix = "," ); 
						}
					}
				}
			}
	
			$html			=	$html .
						'
				}
		,messages:	{
						';

			$first_field				=	TRUE;
			foreach( $table_fields as $table_name => $table_info )
			{
				foreach( $table_info->fields as $field )
				{
					if ( $first_field )
					{
						$first_field	=	FALSE;
						$html		=	$html .
									$field->edit->validation_message(); 
					}
					else
					{
						$html		=	$html .
									$field->edit->validation_message( $prefix = "," ); 
					}
				}
			}
	
			$html			=	$html . 
							'
				}
	});
});
</script>
							';
			return $html;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 
	 * Cria o input para as colunas da tabela.
	 * @param um array de objetos com os dados $row
	 * @param boolean $print_as_array, usamos TRUE quando a página estiver sendo renderizada como GRID;
	 * @param varchar $string_for_tabs, se a página tiver tab (abas), colocamos a string que o JavaScript conhece para fazer o tratamento das abas.
	 * @param varchar $string_for_id, um conjunto de caracteres, ex.: ##id##, que permite ao JavaScript da página fazer alteração quando duplicar linhas.
	 * @param tamano  $size, tamanho em caracteres do campo na página.
	 * @param boolane $force_hidden, usamos TRUE quando queremos que o campo fique escondido do usuário na página.
	 * 
	 */
	public function html( $row, $print_as_array = FALSE, $string_for_tabs = NULL, $string_for_id = NULL, $size = NULL, $force_hidden = FALSE )
	{
		$html					=	NULL;
		/*
		 * Inicializa/cria variáveis de trabalho da função.
		 */
		$field_type				=	NULL;
		$field_name_ref				=	$this->field_info->name;
		$html_field_name			=	$this->get_field_name( $print_as_array, $add_id = TRUE, $string_for_tabs, $string_for_id );
		$fk_field				=	$this->field_info->fk_field;
		if ( $fk_field )
		{
			$html_field_fk_name		=	$this->get_field_name( $print_as_array, $add_id = FALSE, $string_for_tabs, $string_for_id, $prefix = 'titleFK_', $field_name = NULL );
			$fk_table			=	substr( strtolower( strtolower( $field_name_ref ) ), 0, strrpos( strtolower( strtolower( $field_name_ref ) ), "_id" , -1 ) );
		}
		else
		{
			$html_field_fk_name		=	NULL;
			$fk_table			=	NULL;
		}
//		log_message('debug', "JX_Field.html ($html_field_name)");
		
		/*
		 * Determina o tipo de campo, a exibição do dado.
		 */
		if ( $field_name_ref == 'id' )
		{
			$field_type			=	'id';
		}
		elseif ( $this->field_info->type == 'blob' && $this->field_info->name == 'image' )
		{
			$field_type			=	'img';
		}
		elseif ( $this->field_info->name == 'file_name' )
		{
			$field_type			=	'file';
		}
		elseif ( $this->field_info->name == 'password' )
		{
			$field_type			=	'password';
		}
		elseif ( $this->field_info->type == 'text'
		||       $this->field_info->type == 'mediumtext'
		||       $this->field_info->type == 'longtext'
		)
		{
			$field_type			=	'textarea';
		}
		elseif ( $this->field_info->type == 'enum' )
		{
			$field_type			=	'select';
		}
		else
		{
			$field_type			=	'input';
		}
		
		/*
		 * Determina se há ou não valor para o campo.
		 */
		if ( isset( $row->$field_name_ref ) )
		{
			$this->value_field		=	$row->$field_name_ref;
		}
		else
		{
			$this->value_field		=	$this->field_info->default;
		}
//		log_message('debug', "...value(".$this->value_field.") type($field_type)");
		
		/*
		 * Limita o tamanho do campo.
		 */
		$this->data_type_field			=	'text';
		$this->input_type			=	'text';
		
		if ( $this->field_info->max_length > 60 )
		{
			$this->field_info->max_length		=	60;
		}
		
		/*
		 * Define o tipo de input que será exibido e o data_type.
		 */
		if ( $this->field_info->type == 'datetime'
		||   $this->field_info->type == 'date'
		||   $this->field_info->type == 'timestamp'
		)
		{
			$this->field_info->max_length		=	14;
			$this->input_type			=	'text';
			
			if ( strpos( $this->field_info->name, 'hora' ) == 0 )
			{
				$this->data_type_field	=	'date';
			}
			else
			{
				$this->data_type_field	=	'datetime';
			}
		}
		elseif ( ( $this->field_info->type == 'int'
		||         $this->field_info->type == 'decimal'
		||         $this->field_info->type == 'number'
		         )
		&&       !$fk_field
		       )
		{
			$this->input_type		=	'number';
		}
		
		$this->value_field			=	$this->CI->singlepack->print_datetime_value( $this->value_field, $this->field_info->type, $this->data_type_field );
		/*
		 * Imprime o campo na página.
		 */
		if ( $field_name_ref == 'imagem_facebook' )
		{
			$html = $html .  '<img src="'.$this->value_field.'"/><br/>';
		}
		elseif ( $field_type == 'id'
		||       $force_hidden
		       )
		{
//			log_message('debug', "...ID" );
			$html = $html .  '<input type="hidden" value="'. $this->value_field .'" '. $html_field_name .'/>';
			if ( $field_type == 'id' )
			{
				$html = $html . '<input type="hidden" value="'. ( ( $this->value_field ) ? "{'STATUS':'QUERY','VALID':'TRUE'}" : "{'STATUS':'NEW','VALID':'TRUE'}" ) .'" ';
				$html = $html . $this->get_field_name( $print_as_array, TRUE, $string_for_tabs, $string_for_id, NULL, $field_name = 'jx_record_control' );
				$html = $html . '/>';
			}
		}
		elseif ( $field_type == 'img' )
		{
//			log_message('debug', "...IMG" );
			
			if ( $this->value_field
			&&   isset( $row->mime_type )
			&&   isset( $row->image )
			   )
			{
				$html = $html .  '<img src="data:'. $row->mime_type .';base64,'. base64_encode( $row->image ) .'"/>';
			}

			if ( !in_array( $this->field_info->name, $this->CI->tables[ $this->table_name ]->readonly_columns ) )
			{
				$html = $html .  '<input size="25" type="file"'. $html_field_name;

				if ( $this->CI->singlepack->get_count_field() == 1 )
				{
					$html = $html .  ' autofocus="autofocus"';
				}

				$html = $html . ' />';
			}
		}
		elseif ( $field_type == 'file' )
		{
//			log_message('debug', "...FILE" );

			if ( class_exists( 'Imagem_model' )
			&&   isset( $row->imagem_id )
			   )
			{
				$image_file	=	$this->CI->imagem->get_name( $row->imagem_id );
				if ( $image_file )
				{
					$html = $html .  '<img class="image-grid" src="'.$this->CI->imagem->get_file_name( $row->imagem_id, TRUE ).'"/><br/>';
					$html = $html .  '<span class="image-descr">'.$this->CI->imagem->get_name( $row->imagem_id ).'</span><br/>';
				}
				else
				{
//					$html = $html . 'junior';
				}
			}
			
			if ( !in_array( $this->field_info->name, $this->CI->tables[ $this->table_name ]->readonly_columns ) )
			{
				$html = $html .  '<input size="25" type="file" value="" name="'.$this->table_name.'[file_name_arq][]" id="'.$this->table_name.'_file_name_arq_'.$this->field_array_count_id[ $this->table_name."_file_name" ].'"';

				if ( $this->CI->singlepack->get_count_field() == 1 )
				{
					$html = $html .  ' autofocus="autofocus"';
				}

				$html = $html .  ' />';
				$html = $html .  '<input size="25" type="hidden" value="'. $this->value_field .'" '. $html_field_name .' />';
			}
		}
		elseif ( $field_type == 'textarea' )
		{
//			log_message('debug', "...TEXTAREA" );

			$html = $html .  '<textarea class="textarea" '. $html_field_name .' cols="62" rows="3"';
			if ( in_array( $this->field_info->name, $this->CI->tables[ $this->table_name ]->readonly_columns ) )
			{
				$html = $html .  'readonly="readonly"';
			}
			else
			{
				if ( $this->CI->singlepack->get_count_field() == 1 )
				{
					$html = $html .  ' autofocus="autofocus"';
				}
			}
			$html = $html .  '>'. $this->value_field . '</textarea>';
		}
		elseif ( $field_type == 'password' )
		{
//			log_message('debug', "...PASSWORD" );

			// Imprime a senha
			$html = $html .  '<input '; // Abre o campo.
			$html = $html .  ' type="password"';
			$html = $html .  ' datatype="'. $this->data_type_field .'"';
			$html = $html .  $html_field_name;
			$html = $html .  ' class="input"';
			if ( $size )
			{
				$html = $html .  ' size="'.$size.'"';
			}
			else
			{
				$html = $html .  ' size="'. $this->field_info->max_length .'"';
			}
			$html = $html .  ' value=""';
			if ( in_array( $this->field_info->name, $this->CI->tables[ $this->table_name ]->readonly_columns ) )
			{
				$html = $html .  'readonly="readonly"';
			}
			else
			{
				if ( $this->CI->singlepack->get_count_field() == 1 )
				{
					$html = $html .  ' autofocus="autofocus"';
				}
			}
			$html = $html .  '/><br/>'; // fecha o campo.

			// Imprime a confirmação
			$html = $html .  '<input '; // Abre o campo.
			$html = $html .  ' type="password"';
			$html = $html .  ' datatype="'. $this->data_type_field .'"';
			$html = $html .  'name="user[password_conf][]" id="'.$this->table_name.'_'.$field_name_ref.'_'.$this->field_array_count_id[ $this->table_name."_".$field_name_ref ].'"';
			$html = $html .  ' class="input"';
			if ( $size )
			{
				$html = $html .  ' size="'.$size.'"';
			}
			else
			{
				$html = $html .  ' size="'. $this->field_info->max_length .'"';
			}
			$html = $html .  ' value=""';
			if ( in_array( $this->field_info->name, $this->CI->tables[ $this->table_name ]->readonly_columns ) )
			{
				$html = $html .  'readonly="readonly"';
			}
			else
			{
				if ( $this->CI->singlepack->get_count_field() == 1 )
				{
					$html = $html .  ' autofocus="autofocus"';
				}
			}
			$html = $html .  '/>'; // fecha o campo.
		}
		elseif ( $field_type == 'input' )
		{
//			log_message('debug', "...input.1");

			if ( $fk_field )
			{
				$html = $html .  '<div class="campo-borda normal-border">';
			}

			$html = $html .  '<input '; // Abre o campo.

			if ( $field_name_ref == 'email' )
			{
				$html = $html .  ' type="email"';
			}
			else
			{
				$html = $html .  ' type="'. $this->input_type .'"';
			}

			 // É uma FK, então preparamos o campo para o AUTOCOMPLETE.
			if ( $fk_field )
			{
//				log_message('debug', "...input.FK");

				$html = $html .  ' style="float:left;" '. $html_field_fk_name;
				$html = $html .  ' class="input jx-autocomplete" jx_autocomplete_source="'. '/'. $fk_table .'/autocomplete'  .'"';

				if ( $size )
				{
					$html = $html .  ' size="'.$size.'"';
				}
				else
				{
					$html = $html .  ' size="60"';
				}
				
				// Busca o valor para a FK.
				if ( $row
				&&   isset( $row->parents )
				&&   count( $row->parents ) > 0
				   )
				{
					foreach( $row->parents as $parent )
					{
						if ( $parent->title != ''
						&&   $fk_table == $parent->table
						&&   $field_name_ref == $parent->column
						   )
						{
							$html = $html .  ' value="'. $parent->title .'"';
						}
					}
				}
				else
				{
					$html = $html .  ' value="'. $this->value_field .'"';
				}
			}
			// É um campo normal.
			else
			{
//				log_message('debug', "...input.não.FK");

				$html = $html .  $html_field_name;
				$html = $html .  ' class="input"';
				if ( $size )
				{
					$html = $html .  ' size="'.$size.'"';
				}
				else
				{
					$html = $html .  ' size="'. $this->field_info->max_length .'"';
				}
				$html = $html .  ' value="'. $this->value_field .'"';
			}
			if ( in_array( $this->field_info->name, $this->CI->tables[ $this->table_name ]->readonly_columns ) )
			{
				$html = $html .  'readonly="readonly"';
			}
			else // só exibimos o datatype para compos alteráveis.
			{
				$html = $html . ' datatype="'. $this->data_type_field .'"';

				if ( $this->CI->singlepack->get_count_field() == 1 )
				{
					$html = $html .  ' autofocus="autofocus"';
				}
			}
			
			$html = $html .  '/>'; // fecha o campo.
			if ( $fk_field
			&&   !in_array( $this->field_info->name, $this->CI->tables[ $this->table_name ]->readonly_columns )
			   )
			{
				$html = $html .  '	<a class="show_all" title="'. $this->CI->lang->get_line( 'button_show_all' ) .'" href="#">'. $this->CI->lang->get_line( 'button_show_all' ) .'</a>';
				if ( $this->CI->singlepack->has_access_prg( $fk_table .'.edit' ) ) // Se não tem permissão de editar não será exibida a opção editar a tabela pai.
				{
					$html = $html .  '	<a class="create_edit" title="'. $this->CI->lang->get_line( 'button_create_edit' ) .'" create_url="/'. $fk_table .'/edit" field_key="'.$this->html_field_id.'" href="#">'. $this->CI->lang->get_line( 'button_create_edit' ) .'</a>';
				}
				$html = $html .  '</div>';
			}

			// Imprime o ID da FK.
			if ( $fk_field )
			{
				$html = $html .  '<input type="hidden" value="'. $this->value_field .'" '. $html_field_name .'/>';
			}
		}
		elseif ( $field_type == 'select' )
		{
//			log_message('debug', "...SELECT" );

			if ( in_array( $this->field_info->name, $this->CI->tables[ $this->table_name ]->readonly_columns ) )
			{
				$html = $html .  '<input type="text" readonly="readonly" value="'. $this->value_field .'" '. $html_field_name .'/>';
			}
			else
			{
				$ck_values	=	$this->CI->lang->get_ck_line( $this->table_name.'.'.$this->field_info->name . "_ck" );
				if ( $ck_values )
				{
					$bootstrap	=	TRUE;
					if ( $bootstrap )
					{
						$html = $html . 	'<div class="radiogroup_ck" id="'.$this->html_field_id.'">';
						$html = $html . 	'<input type="hidden" id="'.$this->html_field_id.'" value="'.$this->value_field.'" '.$html_field_name.'>';
						$html = $html . 	'<div class="radioset_ck btn-group" autocomplete="off" data-toggle="buttons-radio">';
					}
					else
					{
						$html = $html . 	'<div id="div_radio_'.$field_name_ref.'_'.$this->count_ck.'" class="ui-buttonset">';
					}
					//$html = $html . 	'<div id="'.$field_name.'" class="radioset_ck ui-buttonset">';
					$count		=	0;
					foreach ( $ck_values as $key => $ck )
					{
						$this->count_ck++;
						$count++;
	
						if ( $bootstrap )
						{
							$html = $html . 	'<button class="radioset_ck btn';
							if ( $this->value_field == $ck[ 'value' ] ) // ativa o item se ele tem o valor atual.
							{
								$html = $html . 		' btn-info active';
							}
							else
							{
								$html = $html . 		' btn-info';
							}
							$html = $html . 	'" value-ck="'.$ck[ 'value' ].'" field-ck="'.$this->html_field_id.'">'.$ck[ 'label' ].'</button>';
						}
						else 
						{
							//$html = $html . 	'<input type="radio" id="radio'.$this->count_ck.'_'.$field->name.'" value="'.$ck[ 'value' ].'" name="'.$field->name.$this->print_as_array( $print_as_array ).'"';
							$html = $html . 	'<input type="radio" id="radio_'.$field_name_ref.'_'.$this->count_ck.'" value="'.$ck[ 'value' ].'" '.$html_field_name;
							//$html = $html . 	'<input type="radio" id="radio'.$count.'" value="'.$ck[ 'value' ].'" name="'.$field->name.$this->print_as_array( $print_as_array ).'"';
							if ( $this->value_field == $ck[ 'value' ] ) // ativa o item se ele tem o valor atual.
							{
								$html = $html . 		' checked="checked"';
							}
							$html = $html . 	' class="ui-helper-hidden-accessible">';
							//$html = $html . 	'<label for="radio'.$this->count_ck.'_'.$field->name.'"';
							$html = $html . 	'<label for="radio_'.$field_name_ref.'_'.$this->count_ck.'"';
							//$html = $html . 	'<label for="radio'.$count.'"';
							
							if ( $this->value_field == $ck[ 'value' ] ) // Monta a classe para item ativo.
							{
								$html = $html . 	' aria-pressed="true" class="ui-state-active ';
							}
							else
							{
								$html = $html . 	' aria-pressed="false" class="';
							}
			
							$html = $html . 	' ui-button ui-widget ui-state-default ui-button-text-only';
							$html = $html . 	' ui-button ui-widget ui-state-default ui-button-text-only';
							if ( $count == 1 ) // Ajusta as bordas do item.
							{
								$html = $html . 	' ui-corner-left';
							}
							elseif ( $count == count( $ck_values ) )
							{
								$html = $html . 	' ui-corner-right';
							}
			
							$html = $html . 	'" role="button" aria-disabled="false">';
							$html = $html . 	'	<span class="ui-button-text">'.$ck[ 'label' ].'</span>';
							$html = $html . 	'</label>';
	//      <label for="radio1" aria-pressed="false" class="                ui-button ui-widget ui-state-default ui-button-text-only ui-corner-left " role="button" aria-disabled="false">
	//ativo <label for="radio2" aria-pressed="true"  class="ui-state-active ui-button ui-widget ui-state-default ui-button-text-only                " role="button" aria-disabled="false">
	//      <label for="radio3" aria-pressed="false" class="                ui-button ui-widget ui-state-default ui-button-text-only ui-corner-right" role="button" aria-disabled="false">
							
						}
					}
					if ( $bootstrap )
					{
						$html = $html . 	'</div>';
					}
					$html = $html . 	'</div>';
				}
				else
				{
					$html = $html . 	'Configure o arquivo _LANG para esta classe.';
				}
			}
		}
		else
		{
			$html = $html .  "sem tipo definido.";
		}

		return $html;
	}

	protected function is_hidden()
	{
		$id_table_ant			=	FALSE;
		$r_table_name			=	array();
		if ( is_array( $this->CI->tables[ $this->table_name ]->r_table_name ) )
		{
			$r_table_name		=	$this->CI->tables[ $this->table_name ]->r_table_name;
		}
		else
		{
			$r_table_name[]		=	$this->CI->tables[ $this->table_name ]->r_table_name;
		}

		if ( $r_table_name
		&&   array_count_values( $r_table_name ) != 0
		   )
		{
			foreach( $r_table_name as $table )
			{
				if ( $table != '' 
				&&   $table != $this->table_name
				   )
				{
					if ( strrpos( $this->field_info->name, "_id" )  !== FALSE
					&&   str_replace( "_id", "", $this->field_info->name ) == $table
					   )
					{
						$id_table_ant	=	TRUE;
						break;
					}
				}
			}
		}
		/**
		 * Deixamos HIDDEN os campos:
		 * 	- ID de todas as tabelas;
		 * 	- Colunas de FK de tabelas que já foram exibidas na página. Exemplo: <table>_ID.
		 */
		return	(  $this->field_info->name == 'id'
			|| ( $id_table_ant
			&&   $this->field_info->fk_field
			   )
			|| in_array( $this->field_info->name, $this->CI->tables[ $this->table_name ]->hide_columns )
			);
	}
	public function li( $class_css, $row, $print_as_array = TRUE, $string_for_tabs = NULL, $string_for_id = NULL, $size = NULL, $force_hidden = FALSE )
	{
		if ( $this->is_hidden() )
		{
			return	$this->html( $row, TRUE /*$print_as_array*/, $string_for_tabs, $string_for_id, $size, $force_hidden = TRUE );
		}
		else
		{
			return	"<li class='{$class_css}'>
					<dl>
						<dt>
							{$this->label()}
						</dt>
						<dd>
							{$this->html( $row, $print_as_array, $string_for_tabs, $string_for_id, $size, $force_hidden )}
						</dd>
					</dl>
				</li>";
		}
	}

	public function td_index_title( $title )
	{
		$html			=	NULL;
		preg_match_all( '/{[^}]*}/', $title, $imagem_values, PREG_SET_ORDER );
		if ( is_array( $imagem_values ) )
		{
			foreach( $imagem_values as $array )
			{
				foreach( $array as $key => $values )
				{
					$id			=	str_replace( '}', '', substr( $values, strpos( $values, '=' ) +1 ) );
					if ( class_exists( 'Imagem_model' ) )
					{
						$image_file	=	$this->CI->imagem->get_name( $id );
						if ( $image_file )
						{
							$html	=	'<img class="image-index" src="'.$this->CI->imagem->get_file_name( $id, TRUE ).'"/>';
						}
					}
//					$title			.=	$values.$html.$id;
					$title			=	str_replace( $values, $html, $title );
//					$title			=	str_replace( 'imagem_id', 'JUNIOR', $title );
				}
			}
		}

		return	$title;
	}

	public function td( $class_css, $row, $print_as_array = TRUE, $string_for_tabs = NULL, $string_for_id = NULL, $size = NULL, $force_hidden = FALSE )
	{
		if ( $this->is_hidden() )
		{
			return	$this->html( $row, TRUE /*$print_as_array*/, $string_for_tabs, $string_for_id, $size, $force_hidden = TRUE );
		}
		else
		{
			return	"<td>
					{$this->html( $row, $print_as_array, $string_for_tabs, $string_for_id, $size, $force_hidden )}
				</td>";
		}
	}
	public function th_label()
	{
		if ( ! $this->is_hidden() )
		{
			return	"<th>
					{$this->label( TRUE )}
				</th>";
		}
	}
	public function td_image( $class_css, $row, $print_as_array = TRUE, $string_for_tabs = NULL, $string_for_id = NULL, $size = NULL, $force_hidden = FALSE )
	{
		if ( class_exists( 'Imagem_model' ) )
		{
			$image_file	=	$this->CI->imagem->get_name( $id = $row->imagem_id );
			if ( $image_file )
			{
				return	"
					<img  class='image-index' src='".$this->CI->imagem->get_file_name( $row->imagem_id, TRUE )."' alt='{$row->imagem_id}'/><br/>
					";
			}
			return NULL;
		}
		return NULL;
	}
}

/* End of file Form_validation.php */
/* Location: ./system/libraries/Form_validation.php */
