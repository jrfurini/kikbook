<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Resumo dos poderes de uma rodada da pessoa Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/pessoa_ranking_grupo_amigos_resumo_power.php
 * 
 * $Id: pessoa_ranking_grupo_amigos_resumo_power_model.php,v 1.3 2012-10-26 23:35:18 junior Exp $
 * 
 */

class Pessoa_ranking_grupo_amigos_resumo_power_model extends JX_Model
{
	protected $_revision	=	'$Id: pessoa_ranking_grupo_amigos_resumo_power_model.php,v 1.3 2012-10-26 23:35:18 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 pessoa_ranking_grupo_amigos_resumo_power.*
			,power.cod						AS	cod_power
			,power.nome						AS	nome_power
			,power.descr						AS	descr_power
			,power.css_class					AS	css_class
			,power.nome						AS	title
			,now()							AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'pessoa_ranking_grupo_amigos_resumo_power' );
		$this->db->join( 'power		AS	power',         'power.id = pessoa_ranking_grupo_amigos_resumo_power.power_id', '' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	function get_order_by( $selection = null, $direction = null )
	{
		return "power.id";
	}
}

/* End of file pessoa_ranking_grupo_amigos_resumo_power.php */