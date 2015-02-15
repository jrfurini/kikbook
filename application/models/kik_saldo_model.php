<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Saldos de Kiks Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/kik_saldo_model.php
 * 
 * $Id: kik_saldo_model.php,v 1.3 2013-02-28 18:08:31 junior Exp $
 * 
 */

class Kik_saldo_model extends JX_Model
{
	protected $_revision		=	'$Id: kik_saldo_model.php,v 1.3 2013-02-28 18:08:31 junior Exp $';
	
	function __construct()
	{
		$_config		=	array	(
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 kik_saldo.*
			,concat( pes.nome, ' ', pes.sobrenome, ' (', case when kik_saldo.saldo_kik < 0 then '-' else '' end, kik_saldo.saldo_kik, ')' )					AS	title
			,kik_saldo.data_hora_atualizacao					AS	when_field
			,date_format( kik_saldo.data_hora_atualizacao, '%e/%m/%Y %H:%i' )	AS	data_hora_atualizacao_fmt
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'kik_saldo' );
		$this->db->join( 'pessoa		AS	pes',		'pes.id		=	kik_saldo.pessoa_id',				'' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	public function get_order_by()
	{
		return "kik_saldo.saldo_kik DESC, kik_saldo.id";
	}
}
/* End of file kik_saldo_model.php */
