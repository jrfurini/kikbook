<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Grupo Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/process_model.php
 * 
 * $Id: process_model.php,v 1.1 2012-05-11 11:07:36 junior Exp $
 * 
 */

class Process_model extends JX_Model
{
	protected $_revision	=	'$Id: process_model.php,v 1.1 2012-05-11 11:07:36 junior Exp $';

	function __construct()
	{
		parent::__construct();

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 process.*
			,his.data_hora_inicio_exec	AS	data_hora_ultimo_exec
			,his.status			AS	status_ultimo_exec
			,process.descr			AS	title
			,his.data_hora_inicio_exec	AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'process' );
		$this->db->join( 'process_history	AS	his'	, 	'	his.process_id = process.id
										and	his.data_hora_inicio_exec in	(
															select max( his2.data_hora_inicio_exec )
															from   process_history AS his2
															where  his2.process_id = process.id
															)
										'
									, 'LEFT' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
	}
}

/* End of file process_model.php */