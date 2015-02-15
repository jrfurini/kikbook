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
 * @filesource		/application/models/pessoa_model.php
 * 
 * $Id: pessoa_model.php,v 1.1 2012-08-29 14:31:52 junior Exp $
 * 
 */

class Pessoa_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_model.php,v 1.1 2012-08-29 14:31:52 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 pessoa.*
			,usr.id_facebook					AS	id_facebook
			,usr.username						AS	username
			,usr.ativo						AS	ativa
			,cfg.lembrar_via_facebook				AS	lembrar_via_facebook
			,cfg.lembrar_via_email					AS	lembrar_via_email
			,concat( pessoa.nome, ' ', pessoa.sobrenome )		AS	title
			,now()							AS	when_field
			,img.imagem_id						AS	imagem_id
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'pessoa' );
		$this->db->join( 'pessoa_imagem	AS	img', 'img.pessoa_id = pessoa.id', 'LEFT' );
		$this->db->join( 'user		AS	usr', 'usr.pessoa_id = pessoa.id', 'LEFT' );
		$this->db->join( 'user_cfg	AS	cfg', 'cfg.user_id = usr.id', 'LEFT' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file pessoa_model.php */