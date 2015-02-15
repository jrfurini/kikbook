<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Grupo de Amigos PESSOA Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/grupo_amigos_pessoa_model.php
 * 
 * $Id: grupo_amigos_pessoa_model.php,v 1.3 2013-01-28 22:36:11 junior Exp $
 * 
 */

class Grupo_amigos_pessoa_model extends JX_Model
{
	protected $_revision	=	'$Id: grupo_amigos_pessoa_model.php,v 1.3 2013-01-28 22:36:11 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'grupo_amigos'			=>	array	(
													 'model_name'	=>	'grupo_amigos'
													)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}
	
	public function insert_new()
	{
		$in				=	NULL;
		$ar_facebook_groups		=	$this->singlepack->get_groups_id( $what = 'id', $where = NULL );

		if ( is_array( $ar_facebook_groups ) )
		{
			foreach( $ar_facebook_groups as $id )
			{
				if ( $in )
				{
					$in		=	$in.','.$id;
				}
				else
				{
					$in		=	$id;
				}
			}
			
			// Só teremos IN se a pessoa tiver algum grupo cadastrado. Caso contrário não temos o que fazer.
			if ( $in )
			{
				$pessoa_id		=	$this->singlepack->user_info->pessoa_id;
				$where			=		"
									grupo_amigos.id_facebook in ( {$in} )
								and	not exists	(
											select	grupo_amigos_pessoa.id
											from	grupo_amigos_pessoa
											where	grupo_amigos_pessoa.grupo_amigos_id	=	grupo_amigos.id
											and	grupo_amigos_pessoa.pessoa_id		=	{$pessoa_id}
											)
									";
				
				foreach( $this->grupo_amigos->get_all_by_where( $where ) as $grupo_novo )
				{
					$new_grupo_amigos_pessoa			=	new stdClass();
					$new_grupo_amigos_pessoa->id			=	NULL;
					$new_grupo_amigos_pessoa->grupo_amigos_id	=	$grupo_novo->id;
					$new_grupo_amigos_pessoa->pessoa_id		=	$pessoa_id;
					$new_grupo_amigos_pessoa->admin			=	'N';
					
					$this->update( $new_grupo_amigos_pessoa );
					unset( $new_grupo_amigos_pessoa );
				}
			}
		}
		else
		{
			log_message( 'debug', "(jxModel)Grupo_amigos_pessoa_model erro ao acessar os grupos=$ar_facebook_groups");
		}
	}
}

/* End of file grupo_amigos_pessoa_model.php */