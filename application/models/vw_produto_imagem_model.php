<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Campeonato Imagem Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/vw_produto_imagem_model.php
 * 
 * $Id: vw_produto_imagem_model.php,v 1.1 2013-03-10 21:01:47 junior Exp $
 * 
 */

class Vw_produto_imagem_model extends JX_Model
{
	protected $_revision	=	'$Id: vw_produto_imagem_model.php,v 1.1 2013-03-10 21:01:47 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'imagem'					=>	array	(
															 'model_name'	=>	'imagem'
															,'where'	=>	'imagem.id in ( select prdimg.imagem_id from produto_imagem prdimg where prdimg.produto_id = ##id## )'
															,'r_table_name'	=>	''
															)
							,'produto_imagem'				=>	array	(
															 'model_name'	=>	'produto_imagem'
															,'where'	=>	'produto_imagem.produto_id = ##id##'
															,'r_table_name'	=>	'produto,imagem'
													 		)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 vw_produto_imagem.*
			,vw_produto_imagem.id	AS	title
			,now()			AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'vw_produto_imagem' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file vw_produto_imagem_model.php */