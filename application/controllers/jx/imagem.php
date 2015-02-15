<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Controller de Imagem.
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/imagem.php
 * 
 * $Id: imagem.php,v 1.2 2012-09-06 10:13:14 junior Exp $
 * 
 */

class Imagem extends JX_Page
{
	var $image_fisical_path;

	protected $_revision	=	'$Id: imagem.php,v 1.2 2012-09-06 10:13:14 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							);
		parent::__construct( $_config );
		$this->image_fisical_path	=	$this->config->item( 'image_fisical_path' );
		
		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	/**
	 * Exibe a imagem
	 */
	public function show( $id )
	{
		$this->imagem->select_one( NULL, $id );
		$image_info		=	$this->imagem->get_query_rows( FALSE );
		
		$this->show_file( $this->imagem->get_file_name( $id, $full = FALSE, $extension = $image_info[0]->file_extension, $mime_type = $image_info[0]->mime_type ) );
//		echo( 'junior'.$this->get_file_name( $id, $full = TRUE, $extension = $image_info[0]->file_extension, $mime_type = $image_info[0]->mime_type ) );

		return TRUE;
	}

	public function show_file( $file_name )
	{
		$expires = 60*60*24*14;
		header( "Pragma: public" );
		header( "Expires: ". gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT' );
		header( "Cache-Control: maxage=".$expires );
//		header( "Pragma: no-cache" );
//		header( "Expires: 0" );
//		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header( "Robots: kikbook, futebol" );
		header( "Content-Type: {$this->imagem->getMimeType( $file_name )}" );
		header( "Content-Length: ".filesize( $this->image_fisical_path.$file_name ) );
		readfile( $this->image_fisical_path.$file_name );
//echo $this->image_fisical_path.$file_name.' mime='.$this->model_master->getMimeType( $file_name ).'<br/>';
/*
echo( "Pragma: no-cache<br/>" );
echo( "Expires: 0<br/>" );
echo( "Cache-Control: must-revalidate, post-check=0, pre-check=0<br/>" );
echo( "Robots: none<br/>" );
echo( "Content-Type: {$this->imagem->getMimeType( $file_name )}<br/>" );
echo( "Content-Length: ".filesize( $this->image_fisical_path.$file_name )."<br/>" );
echo( $this->image_fisical_path.$file_name.'<br/>' );
*/		return TRUE;
	}
}
/* End of file imagem.php */
/* Location: /application/controllers/imagem.php */
