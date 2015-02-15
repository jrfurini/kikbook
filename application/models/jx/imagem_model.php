<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Imagem Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/imagem_model.php
 * 
 * $Id: imagem_model.php,v 1.9 2013-03-08 09:50:54 junior Exp $
 * 
 */

class Imagem_model extends JX_Model
{
	protected $_revision	=	'$Id: imagem_model.php,v 1.9 2013-03-08 09:50:54 junior Exp $';
	var $mime_type;
	var $extension;
	var $imagem_id;
	var $size;
	var $file_name;
	var $nome_arquivo_imagem;
	var $versao;
	
var $teste_nivel = 0;
	
	var $image_index	=	0;

	var $only_data		=	FALSE;

	var $image_fisical_path;
	var $image_virtual_path;
	
	function __construct()
	{
		parent::__construct();
		$this->image_fisical_path	=	$this->config->item( 'image_fisical_path' );
		$this->image_virtual_path	=	$this->config->item( 'image_virtual_path' );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}
	
	/*
	 * Sobrescreve funções do JX_Model.
	 */
	public function get_select_for_index()
	{
		return	"
			 imagem.*
			,CONCAT( IFNULL( IFNULL( imagem.descr, imagem.file_name ), imagem.id ), ' (versão: ', imagem.versao, ')' )	AS	title
			,now()					AS	when_field
			,imagem.id				AS	imagem_id
			,NULL					AS	nome_arquivo_imagem
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'imagem' );
	}

	public function get_order_by()
	{
		return "IFNULL( IFNULL( imagem.descr, imagem.file_name ), imagem.id )";
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
	public function _pre_select_one()
	{
		$this->set_null();
	}
	public function _post_select_one()
	{
		$image_info					=	 $this->query->result_object();
		$this->set_id( $image_info[0]->id );
		$this->set_extension( $image_info[0]->file_extension );
		$this->set_mime_type( $image_info[0]->mime_type );
		$this->set_name( $image_info[0]->file_name );
		$this->set_size( $image_info[0]->size );
		$this->set_versao( $image_info[0]->versao );
	}

	public function _post_insert()
	{
		$ret						=	TRUE;
		log_message( 'debug', 'IMAGEM_MODEL._post_insert start()' );
		if ( !$this->_in_post_insert )
		{
//echo '_post_insert id='.$this->insert_data->id.'<br/>';
			$this->_in_post_insert			=	TRUE;

			if ( $this->upload_file( 'insert' ) )
			{
//echo 'retorno do upload size='.$this->insert_data->size.'<br/>';
				$this->only_data		=	TRUE;

				//Após inserir a linha e carregar o arquivo, atualizamos os dados da tabela.
				$ret		=	$this->update( $this->insert_data );

				$this->only_data		=	FALSE;
			}

		 	$this->_in_post_insert			=	FALSE;
		}
		log_message( 'debug', 'IMAGEM_MODEL._post_insert fim()' );
		
		return $ret;
	}

	public function _post_update()
	{
		$ret						=	TRUE;
		log_message( 'debug', "IMAGEM_MODEL._post_update start({$this->teste_nivel})" );
		if ( !$this->_in_post_update )
		{
$this->teste_nivel += 1;
//echo '_post_update<br/>';
			$this->_in_post_update			=	TRUE;
			log_message( 'debug', "IMAGEM_MODEL._post_update set TRUE" );
			
			if ( $this->upload_file( 'update' ) )
			{
//echo 'retorno do upload size='.$this->update_data->size.'<br/>';
				$this->only_data		=	TRUE;

				//Após inserir a linha e carregar o arquivo, atualizamos os dados da tabela.
				$ret		=	$this->update( $this->update_data );

				$this->only_data		=	FALSE;
			}

		 	$this->_in_post_update			=	FALSE;
			log_message( 'debug', "IMAGEM_MODEL._post_update set FALSE" );
$this->teste_nivel -= 1;
		}
		log_message( 'debug', 'IMAGEM_MODEL._post_update fim('.$this->teste_nivel.')' );
		
		return $ret;
	}
	
	/*
	 * Funções personalizadas
	 */
	protected function set_null()
	{
		$this->set_id( NULL );
		$this->set_extension( NULL);
		$this->set_mime_type( NULL );
		$this->set_name( NULL );
		$this->set_size( NULL );
		$this->set_versao( NULL );
	}

	protected function set_id( $id )
	{
		$this->imagem_id	=	$id;
	}

	protected function set_name( $file_name )
	{
		$this->file_name	=	$file_name;
	}

	protected function set_mime_type( $mime_type )
	{
		$this->mime_type	=	$mime_type;
	}

	protected function set_extension( $extension )
	{
		$this->extension	=	$extension;
	}

	protected function set_size( $size )
	{
		$this->size		=	$size;
	}

	protected function set_versao( $versao )
	{
		$this->versao		=	$versao;
	}

	public function get_file_name( $id = NULL, $full = FALSE, $extension = NULL, $wish_path = 'VIRTUAL' )
	{
		$img_id			=	$this->get_id( $id );
		if ( $img_id )
		{
			$img_ext		=	( $extension ) ? $extension : $this->get_extension();
			$image_file		=	sha1( str_pad( $img_id, 11, "0", STR_PAD_LEFT ) );
			
			if ( $wish_path == 'VIRTUAL' )
			{
				$file_path	=	$this->image_virtual_path;
			}
			else
			{
				$file_path	=	$this->image_fisical_path;
			}
//echo'/assets/img/escudos/'. $image_file.'.'.$img_ext . ' id=' . $img_id . '<br/>';
//return '/assets/img/escudos/'.( $img_ext ) ? $image_file.'.'.$img_ext : $image_file;
			if ( $full )
			{
				return ( ( $img_ext ) ? $file_path.$image_file.'.'.$img_ext : $file_path.$image_file ) . ( ( $wish_path == 'VIRTUAL' ) ? '?v=' . $this->get_versao( $id ) : NULL );
			}
			else
			{
				return ( $img_ext ) ? $image_file.'.'.$img_ext : $image_file;
			}
		}
		else
		{
			return NULL;
		}
	}
	
	public function get_name( $id = NULL )
	{
		if ( !is_null( $id ) )
		{
			$this->get_one_by_id( $id, FALSE );
		}
		else
		{
			$this->set_null();
		}

		return $this->file_name;
	}
	public function get_id( $id = NULL )
	{
		if ( !is_null( $id ) )
		{
			$this->get_one_by_id( $id, FALSE );
		}
		else
		{
			$this->set_null();
		}
		
		return $this->imagem_id;
	}

	public function get_extension( $id = NULL )
	{
		return $this->extension;
	}

	public function get_mime_type( $id = NULL )
	{
		return $this->mime_type;
	}

	public function get_size( $id = NULL )
	{
		return $this->size;
	}

	public function get_versao( $id = NULL )
	{
		return $this->versao;
	}

	/**
	 * Funções para controle do arquivo da imagem.
	 */
	protected function upload_file( $operation )
	{
//echo 'upload_file '.$this->table.'<br/>';
//if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {    
//$poidsMax = ini_get('post_max_size'); 
//$upload_tmp_dir = ini_get('upload_tmp_dir'); 
    
//    $poidsMax = ini_get('post_max_size'); 
//    $oElement->addError("fileoverload", "Your feet is too big, maximum allowed size here is $poidsMax."); 
//echo("max_size=$poidsMax.<br/>"); 
//echo("temp_dir=$upload_tmp_dir.<br/>"); 
//} 
//print_r( $_FILES );
		$table_name			=	'null';
		if ( $_FILES )
		{
			foreach( $_FILES as $key => $files )
			{
				$upload_file	=	$files;
				$table_name	=	$key;
				break;
			}
		}
		else
		{
			$upload_file		=	NULL;
		}

		if ( $upload_file
		&&   key_exists( 'name', $upload_file )
		&&   $upload_file[ "name" ]
		   )
		{
			$this->image_index				=	FALSE;
			// Localizar o index correto para imagem envida.
			$cube_key					=	$this->input->get_current_cube_key();
			
			if ( !empty( $cube_key ) ) // Existindo uma chave para o cubo de dados usamos esta para ler o $_FILES.
			{
				$this->image_index			=	$cube_key;
			}
			else
			{
				$forcado				=	FALSE;
				foreach( $upload_file[ "name" ][ "file_name_arq" ] as $key => $file_name )
				{
					if ( ( !isset( $data->file_name )  // Não temos um nome.
					||     !$data->file_name
					     )
					&&   $upload_file[ "error" ][ "file_name_arq" ][ $key ] == 0 // E temos um arquivo válido entrando.
					&&   !$forcado
					   )
					{
						$this->image_index	=	$key;
						$forcado		=	TRUE;
					}
					elseif ( isset( $data->file_name )
					&&       $data->file_name == $file_name
					&&       $upload_file[ "error" ][ "file_name_arq" ][ $key ] == 0
					       )
					{
						$this->image_index	=	$key;
						break;
					}
				}
				
				if ( $forcado ) // Se forçamos o uso de um arquivo por falta de nome, então marcamos este arquivo como usado.
				{
					$_FILES[ $table_name ][ "error" ][ "file_name_arq" ][ $this->image_index ]	=	-1;
					$upload_file[ "error" ][ "file_name_arq" ][ $this->image_index ]		=	-1;
				}
			}
//echo "ENCONTRADO O INDEX $this->image_index<BR>";
//print_r( $upload_file );

			if ( $this->image_index !== FALSE
			&&   ( $upload_file[ "error" ][ "file_name_arq" ][ $this->image_index ] == 0
			||     ( $upload_file[ "error" ][ "file_name_arq" ][ $this->image_index ] == -1 // Acabamos de forçar
			&&       $forcado
			       )
			     )
			   )
			{
//print_r( $upload_file );
				log_message( 'debug', 'Imagem_Model - upload_file' );
				if ( $operation == 'insert' ) // devolve os dados para que a operação de banco de dados continue o que precisa fazer.
				{
					$data			=	$this->insert_data;
				}
				else
				{	
					$data			=	$this->update_data;
				}

				if ( ! $this->only_data
				&&   isset( $data->file_name ) 
				&&   $data->file_name // Se estamos no insert este campo estará em branco e pulamos o delete do file.
				   )
				{
					$this->delete_file( $data ); // Se existe o arquivo, apagamos antes.
					log_message( 'debug', '...APAGOU o arquivo' );
				}
				/*
				log_message( 'debug', 'values:' );
				log_message( 'debug', '...name='.$upload_file[ "name" ][ "file_name_arq" ][ $this->image_index ] );
				log_message( 'debug', '...ext='.strtolower( substr( strrchr( $upload_file[ "name" ][ "file_name_arq" ][ $this->image_index ], '.' ), 1 ) ) );
				log_message( 'debug', '...type='.$upload_file[ "type" ][ "file_name_arq" ][ $this->image_index ] );
				log_message( 'debug', '...size='.$upload_file[ "size" ][ "file_name_arq" ][ $this->image_index ] );
				log_message( 'debug', 'values db:' );
				log_message( 'debug', '...name='.$data->file_name );
				log_message( 'debug', '...ID='.$data->id );
				log_message( 'debug', '...type='.$data->mime_type );
				log_message( 'debug', '...size='.$data->size );
				*/
				
				$data->file_name		=	$upload_file[ "name" ][ "file_name_arq" ][ $this->image_index ];
				$data->file_extension		=	strtolower( substr( strrchr( $data->file_name, '.' ), 1 ) );
				$data->mime_type		=	$upload_file[ "type" ][ "file_name_arq" ][ $this->image_index ];
				$data->size			=	$upload_file[ "size" ][ "file_name_arq" ][ $this->image_index ];
				// Atualizamos a versão da imagem.
				$data->versao			=	$data->versao + 1;
/*echo 'values db(2):<br/>';
echo '...name='.$data->file_name.'<br/>';
echo '...ID='.$data->id.'<br/>';
echo '...type='.$data->mime_type.'<br/>';
echo '...size='.$data->size.'<br/>';
echo '...tmp_name='.$upload_file[ "tmp_name" ][ "file_name_arq" ][ $this->image_index ].'<br/>';
*/
			 	
				if ( $operation == 'insert' ) // devolve os dados para que a operação de banco de dados continue o que precisa fazer.
				{
					$this->insert_data	=	$data;
				}
				else
				{	
					$this->update_data	=	$data;
				}
				
				$new_name			=	$this->get_file_name( $id = $data->id, $full = TRUE, $extension = $data->file_extension, $wish_path = 'FISICAL' );
//echo '...new_name='.$new_name.'<br/>';
				
				if ( $this->only_data )
				{
//echo 'upload_file sem mexer no arquivo<br/>';
					$this->only_data	=	FALSE;
					
					return true;
				}
				elseif ( move_uploaded_file( $upload_file[ "tmp_name" ][ "file_name_arq" ][ $this->image_index ], $new_name ) ) // Troca o nome do arquivo.
				{
//echo 'upload_file >'.$new_name.'<br/>';
					log_message( 'debug', '...MOVEU de tmp para IMAGES' );
					
					return true;
				}
			}
		}

		return false;
	}

	protected function delete_file( &$data )
	{
		// Monta diretorio e arquivo.
		$file					=	$this->get_file_name( $id = $data->id, $full = TRUE, $extension = $data->file_extension, $wish_path = 'FISICAL' );
		if ( $file )
		{
			// Se existe o arquivo, apagamos.
			if ( file_exists( $file ) )
			{
				return unlink( $file );
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	/*
	 * Retorna o mime_type do arquivo informado. Usa a extensão.
	 */
	public function getMimeType( $file )
	{
		$filetype = strtolower( substr( strrchr( $file, '.' ), 1 ) );

		$mimetypes = array	(
						"ez"		=> "application/andrew-inset",
						"atom"		=> "application/atom+xml",
						"hqx"		=> "application/mac-binhex40",
						"cpt"		=> "application/mac-compactpro",
						"doc"		=> "application/msword",
						"lha"		=> "application/octet-stream",
						"lzh"		=> "application/octet-stream",
						"exe"		=> "application/octet-stream",
						"so"		=> "application/octet-stream",
						"dms"		=> "application/octet-stream",
						"class"		=> "application/octet-stream",
						"bin"		=> "application/octet-stream",
						"dll"		=> "application/octet-stream",
						"oda"		=> "application/oda",
						"pdf"		=> "application/pdf",
						"ps"		=> "application/postscript",
						"eps"		=> "application/postscript",
						"ai"		=> "application/postscript",
						"smi"		=> "application/smil",
						"smil"		=> "application/smil",
						"mif"		=> "application/vnd.mif",
						"xls"		=> "application/vnd.ms-excel",
						"ppt"		=> "application/vnd.ms-powerpoint",
						"wbxml"		=> "application/vnd.wap.wbxml",
						"wmlc"		=> "application/vnd.wap.wmlc",
						"wmlsc"		=> "application/vnd.wap.wmlscriptc",
						"bcpio"		=> "application/x-bcpio",
						"vcd"		=> "application/x-cdlink",
						"pgn"		=> "application/x-chess-pgn",
						"cpio"		=> "application/x-cpio",
						"csh"		=> "application/x-csh",
						"dir"		=> "application/x-director",
						"dxr"		=> "application/x-director",
						"dcr"		=> "application/x-director",
						"dvi"		=> "application/x-dvi",
						"spl"		=> "application/x-futuresplash",
						"gtar"		=> "application/x-gtar",
						"gz"		=> "application/x-gzip",
						"hdf"		=> "application/x-hdf",
						"php"		=> "application/x-httpd-php",
						"phps"		=> "application/x-httpd-php-source",
						"js"		=> "application/x-javascript",
						"skm"		=> "application/x-koan",
						"skt"		=> "application/x-koan",
						"skp"		=> "application/x-koan",
						"skd"		=> "application/x-koan",
						"latex"		=> "application/x-latex",
						"cdf"		=> "application/x-netcdf",
						"nc"		=> "application/x-netcdf",
						"sh"		=> "application/x-sh",
						"shar"		=> "application/x-shar",
						"swf"		=> "application/x-shockwave-flash",
						"sit"		=> "application/x-stuffit",
						"sv4cpio"	=> "application/x-sv4cpio",
						"sv4crc"	=> "application/x-sv4crc",
						"tar"		=> "application/x-tar",
						"tcl"		=> "application/x-tcl",
						"tex"		=> "application/x-tex",
						"texi"		=> "application/x-texinfo",
						"texinfo"	=> "application/x-texinfo",
						"roff"		=> "application/x-troff",
						"t"		=> "application/x-troff",
						"tr"		=> "application/x-troff",
						"man"		=> "application/x-troff-man",
						"me"		=> "application/x-troff-me",
						"ms"		=> "application/x-troff-ms",
						"ustar"		=> "application/x-ustar",
						"src"		=> "application/x-wais-source",
						"xht"		=> "application/xhtml+xml",
						"xhtml"		=> "application/xhtml+xml",
						"zip"		=> "application/zip",
						"au"		=> "audio/basic",
						"snd"		=> "audio/basic",
						"midi"		=> "audio/midi",
						"kar"		=> "audio/midi",
						"mid"		=> "audio/midi",
						"mp3"		=> "audio/mpeg",
						"mp2"		=> "audio/mpeg",
						"mpga"		=> "audio/mpeg",
						"aifc"		=> "audio/x-aiff",
						"aif"		=> "audio/x-aiff",
						"aiff"		=> "audio/x-aiff",
						"m3u"		=> "audio/x-mpegurl",
						"rm"		=> "audio/x-pn-realaudio",
						"ram"		=> "audio/x-pn-realaudio",
						"rpm"		=> "audio/x-pn-realaudio-plugin",
						"ra"		=> "audio/x-realaudio",
						"wav"		=> "audio/x-wav",
						"pdb"		=> "chemical/x-pdb",
						"xyz"		=> "chemical/x-xyz",
						"bmp"		=> "image/bmp",
						"gif"		=> "image/gif",
						"ief"		=> "image/ief",
						"jpe"		=> "image/jpeg",
						"jpeg"		=> "image/jpeg",
						"jpg"		=> "image/jpeg",
						"png"		=> "image/png",
						"tif"		=> "image/tiff",
						"tiff"		=> "image/tiff",
						"djvu"		=> "image/vnd.djvu",
						"djv"		=> "image/vnd.djvu",
						"wbmp"		=> "image/vnd.wap.wbmp",
						"ras"		=> "image/x-cmu-raster",
						"pnm"		=> "image/x-portable-anymap",
						"pbm"		=> "image/x-portable-bitmap",
						"pgm"		=> "image/x-portable-graymap",
						"ppm"		=> "image/x-portable-pixmap",
						"rgb"		=> "image/x-rgb",
						"xbm"		=> "image/x-xbitmap",
						"xpm"		=> "image/x-xpixmap",
						"xwd"		=> "image/x-xwindowdump",
						"igs"		=> "model/iges",
						"iges"		=> "model/iges",
						"mesh"		=> "model/mesh",
						"silo"		=> "model/mesh",
						"msh"		=> "model/mesh",
						"vrml"		=> "model/vrml",
						"wrl"		=> "model/vrml",
						"css"		=> "text/css",
						"htm"		=> "text/html",
						"html"		=> "text/html",
						"asc"		=> "text/plain",
						"txt"		=> "text/plain",
						"rtx"		=> "text/richtext",
						"rtf"		=> "text/rtf",
						"sgml"		=> "text/sgml",
						"sgm"		=> "text/sgml",
						"tsv"		=> "text/tab-separated-values",
						"wml"		=> "text/vnd.wap.wml",
						"wmls"		=> "text/vnd.wap.wmlscript",
						"etx"		=> "text/x-setext",
						"xml"		=> "text/xml",
						"xsl"		=> "text/xml",
						"mpe"		=> "video/mpeg",
						"mpeg"		=> "video/mpeg",
						"mpg"		=> "video/mpeg",
						"mov"		=> "video/quicktime",
						"qt"		=> "video/quicktime",
						"mxu"		=> "video/vnd.mpegurl",
						"avi"		=> "video/x-msvideo",
						"movie"		=> "video/x-sgi-movie",
						"ice"		=> "x-conference/x-cooltalk",
						"plb"		=> "application/x-sh",
						"war"		=> "application/zip"
					);

		return implode( '', array_keys( array_flip( $mimetypes ), $filetype ) );
	}
}

/* End of file imagem_model.php */