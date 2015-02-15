<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Poderes de chutes Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/kick_power_model.php
 * 
 * $Id: kick_power_model.php,v 1.2 2012-10-24 11:36:31 junior Exp $
 * 
 */

class Kick_power_model extends JX_Model
{
	protected $_revision	=	'$Id: kick_power_model.php,v 1.2 2012-10-24 11:36:31 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 kick_power.*
			,power.nome						AS	title
			,now()							AS	when_field
			,power.css_class					AS	css_class
			,power.cod						AS	power_cod
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'kick_power' );
		$this->db->join( 'power		AS	power', 'power.id = kick_power.power_id', '' );
	}
	
	public function get_qtde_usada( $pessoa_id, $power_id, $rodada_fase_id )
	{
		$qry			=	$this->db->query(
								"
								select	count( k.id )	AS	qtde_usada
								from	 kick_power	AS	kp
									,kick		AS	k
									,jogo		AS	j
								where	j.rodada_fase_id		=	{$rodada_fase_id}
								and	k.jogo_id			=	j.id
								and	k.pessoa_id			=	{$pessoa_id}
								and	kp.kick_id			=	k.id
								and	kp.power_id			=	{$power_id}
								and	kp.anulado			<>	'S'
								"
								);

		foreach( $qry->result_object() as $qtde )
		{
			$qtde_usada	=	$qtde->qtde_usada;
		}
			
		$qry->free_result();

		return $qtde_usada;
	}
}

/* End of file kick_power_model.php */