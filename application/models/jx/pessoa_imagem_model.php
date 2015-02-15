<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Pessoa Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/pessoa_imagem_model.php
 * 
 * $Id: pessoa_imagem_model.php,v 1.1 2012-08-29 14:31:52 junior Exp $
 * 
 */

class Pessoa_imagem_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_imagem_model.php,v 1.1 2012-08-29 14:31:52 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 pessoa.*
			,concat( pessoa.nome, ' ', pessoa.sobrenome )		AS	title
			,now()							AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'pessoa_imagem' );
		$this->db->join( 'pessoa	AS	pessoa', 'pessoa.id = pessoa_imagem.pessoa_id', '' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file pessoa_imagem_model.php */
