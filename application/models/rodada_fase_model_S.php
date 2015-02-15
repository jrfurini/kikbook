<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Rodada / Fase Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/rodada_fase_model.php
 * 
 * $Id: rodada_fase_model.php,v 1.14 2012-12-08 00:24:41 junior Exp $
 * 
 */

class Rodada_fase_model extends JX_Model
{
	protected $_revision		=	'$Id: rodada_fase_model.php,v 1.14 2012-12-08 00:24:41 junior Exp $';
	
	var $retorno_selecao_rodada	=	array();

	var $rodada_fase_id_anterior	=	1;
	var $rodada_fase_id_posterior	=	38;
	
	function __construct()
	{
		$_config		=	array	(
							 'jogo'				=>	array	(
													 'read_write'	=>	'write'
													,'r_table_name'	=>	'rodada_fase'
													,'show'		=>	TRUE
													,'where'	=>	''
													,'max_rows'	=>	99999
													,'orderby'	=>	''
													)
							,'grupo_amigos_fase'		=>	array	(
													 'read_write'	=>	'read'
													,'r_table_name'	=>	''
													,'show'		=>	FALSE
													,'where'	=>	''
													,'max_rows'	=>	99999
													,'orderby'	=>	''
													)
							,'campeonato_versao'		=>	array	(
													 'read_write'	=>	'read'
													,'r_table_name'	=>	''
													,'show'		=>	FALSE
													,'where'	=>	''
													,'max_rows'	=>	99999
													,'orderby'	=>	''
													)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 rodada_fase.*
			,date_format( rodada_fase.data_inicio, '%e/%m/%y' )						AS	dd_mm_inicio
			,date_format( rodada_fase.data_fim, '%e/%m/%y' )						AS	dd_mm_fim
			,rodada_fase.cod										AS	nome
			,concat( ver.descr, ' ', rodada_fase.cod, ' (', date_format( rodada_fase.data_inicio, '%e/%m' ), ' até ', date_format( rodada_fase.data_fim, '%e/%m/%Y' ), ')' )		AS	title
			,date_format( rodada_fase.data_inicio, '%e/%m/%Y' )						AS	when_field
			";
	}
//			,rodada_fase.data_inicio						AS	when_field
	
	public function set_from_join()
	{
		$this->db->from( 'rodada_fase' );
		$this->db->join( 'campeonato_versao	AS	ver',  'ver.id = rodada_fase.campeonato_versao_id' );
		$this->db->join( 'campeonato		AS	camp', 'camp.id = ver.campeonato_id' );
	}

	public function get_column_title()
	{
		return "concat( rodada_fase.cod, ' (', date_format( rodada_fase.data_inicio, '%e/%m' ), ' até ', date_format( rodada_fase.data_fim, '%e/%m/%Y' ), ')' )";
	}
	public function get_order_by()
	{
		return "rodada_fase.data_inicio, rodada_fase.data_fim";
	}
	
	public function set_inicio_fim( $id = NULL, $campeonato_versao_id = NULL )
	{
		if ( !$id && !$campeonato_versao_id )
		{
			foreach( $this->select_all( )->result_object() as $rodadas )
			{
				$this->set_inicio_fim( $rodadas->id, NULL );
			}
		}
		else
		{
			if ( $campeonato_versao_id )
			{
				$where				=	"rodada_fase.campeonato_versao_id = {$campeonato_versao_id}";	
			}
			else
			{
				$where				=	"rodada_fase.id = {$id}";	
			}
			foreach( $this->select_all( $where )->result_object() as $rodada )
			{
				$query_jogos		=	$this->db->query(	"
											select	 max( data_hora )	as	data_fim
												,min( data_hora )	as	data_inicio
											from	 jogo
											where	 jogo.rodada_fase_id	=	{$rodada->id}
											"
										);
				foreach( $query_jogos->result_object() as $data_jogos )
				{
					$rodada->data_inicio	=	$data_jogos->data_inicio;
					$rodada->data_fim	=	$data_jogos->data_fim;
				}
				$this->update( $rodada );
			}
		}
	}

	public function get_rodada_atual( $campeonato_versao_id = NULL )
	{
		if ( !$campeonato_versao_id )
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_atual*/rodada_fase.data_inicio in	(
																select max( rod2.data_inicio )
																from rodada_fase rod2 
																where rod2.data_inicio <= now() 
																)"
									);
		}
		else
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_atual*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
											and	rodada_fase.data_inicio in	(
																select max( rod2.data_inicio ) 
																from	rodada_fase rod2
																where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																and	rod2.data_inicio <= now()
																)"
									);
		}
		return $rodada;
	}

	public function get_rodada_anterior( $data_hora = NULL, $campeonato_versao_id )
	{
		if ( $data_hora )
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_proxima*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
												and	rodada_fase.data_inicio in	(
																	select	max( rod2.data_inicio )
																	from	rodada_fase rod2
																	where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																	and	( rod2.data_inicio 	< '{$data_hora}'
																		)
																	)" );
		}
		else
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_proxima*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
												and	rodada_fase.data_inicio in 	(
																	select	mas( rod2.data_inicio )
																	from	rodada_fase rod2
																	where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																	and 	( rod2.data_inicio < curdate()
																		)
																	)" );
		}
		return $rodada;
	}

	public function get_rodada_proxima( $data_hora = NULL, $campeonato_versao_id )
	{
		if ( $data_hora )
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_proxima*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
												and	rodada_fase.data_inicio in	(
																	select	min( rod2.data_inicio )
																	from	rodada_fase rod2
																	where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																	and	rod2.data_inicio 	> '{$data_hora}'
																	)" );
		}
		else
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_proxima*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
												and	rodada_fase.data_inicio in 	(
																	select	min( rod2.data_inicio )
																	from	rodada_fase rod2
																	where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																	and 	rod2.data_inicio > curdate()
																	)" );
		}
		return $rodada;
	}

	public function get_rodada_aberta( $campeonato_versao_id = NULL )
	{
		if ( !$campeonato_versao_id )
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_aberta*/	rodada_fase.id in	(
															select	jog.rodada_fase_id
															from	jogo jog
															where	jog.data_hora in	(
																			select	min( jog2.data_hora )
																			from	jogo jog2
																			where	jog2.data_hora > now()
																			)
															)" );
		}
		else
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_aberta*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
											and	rodada_fase.id in	(
															select	jog.rodada_fase_id
															from	jogo jog
															where	jog.data_hora in 	(
																			select	( min( jog2.data_hora ) )
																			from	 jogo jog2
																				,rodada_fase rod2
																			where	jog2.data_hora > now()
																			and	jog2.rodada_fase_id		=	rod2.id
																			and	rod2.campeonato_versao_id	=	{$campeonato_versao_id}
																			)
															)" );
		}
		return $rodada;
	}
	
	/**
	 * 
	 * Devolve um array contendo as rodadas
	 * @param INT $rodada_fase_id		ID da rodada atual
	 * @param STR $quem			quem usará o array
	 * @param INT $count_max		informa a qtde de linhas a ser retornada.
	 */
	public function get_rodadas_selecao( $rodada_fase_id = NULL, $quem = 'CLAS', $campeonato_versao_id = NULL, $grupo_fase_id = NULL )
	{
		// Quando o ranking chama o chute ele não estava, em alguns casos, trazendo o id ca versao do campeonato. Este comando abaixo tenta resolver isso.
		if ( !$campeonato_versao_id )
		{
			if ( !$rodada_fase_id ) // Caso não seja enviada a rodada, indica que o campeonato não tem nenhuma rodada cadastrada.
			{
				$campeonato_versao_id			=	1;
				$rodada_fase_id				=	1;
			}
			else
			{
				$rodada_fase_base			=	$this->get_one_by_id( $rodada_fase_id );
				$campeonato_versao_id			=	$rodada_fase_base->campeonato_versao_id;
			}
		}
		
		$rodada_atual						=	$this->get_rodada_atual( $campeonato_versao_id );
		$rodada_prox						=	$this->get_rodada_aberta( $campeonato_versao_id );
		$this->retorno_selecao_rodada				=	array();
		$this->rodada_fase_id_anterior				=	0;
		$this->rodada_fase_id_posterior				=	0;

		if ( $rodada_fase_id ) // Caso não seja enviada a rodada, indica que o campeonato não tem nenhuma rodada cadastrada.
		{
			$count_before						=	3;
			$count_after						=	2;
			
			if ( $quem == 'CLAS' || $quem == 'RANK' )
			{
				$count_before					=	4;
				$count_after					=	1;
			}
			elseif ( $quem == 'KICK' )
			{
				$count_before					=	3;
				$count_after					=	2;
			}
	
			// Prepara rodadas antes
			$count_use						=	0;
			$count_before_future					=	0;
			$ret_tmp						=	array();
			
			// Se enviamos uma fase de grupos de amigos, então as rodadas exibidas serão apenas as cadastradas no grupo.
			if ( $grupo_fase_id )
			{
				$fase_query					=	$this->grupo_amigos_fase->get_one_by_id( $grupo_fase_id );
				$where						=	" and ( rodada_fase.id between {$fase_query->rodada_fase_id_inicio} and {$fase_query->rodada_fase_id_fim} )";
			}
			else
			{
				$where						=	NULL;
			}

			$query							=	$this->select_all( "rodada_fase.campeonato_versao_id = {$campeonato_versao_id} and rodada_fase.data_inicio <= ( select rod2.data_inicio from rodada_fase rod2 where rod2.id = {$rodada_fase_id} )".$where, "rodada_fase.data_inicio DESC, rodada_fase.data_fim DESC", 0, 6 );
			foreach( $query->result_object() as $rodada )
			{
				if ( $count_use < ( $count_before + 1 ) ) // Mais 1 equivale a própria rodada atual.
				{
					$count_use				=	$count_use + 1;
					$ret_tmp[]				=	$rodada;
				}
				else // Calcula o ID da rodada para ser colocada na seta de anterior.
				{
					$count_before_future			=	$count_before_future + 1;
					$this->rodada_fase_id_anterior		=	$rodada->id;
					if ( $count_before_future == ( $count_before + 1 ) ) // Mais 1 equivale a própria rodada atual.
					{
						break;
					}
				}
			}
			$query->free_result();
	
			// Ajusta a qtde de rodadas AFTER se as before não conseguirem preencher a qtde. total.
			if ( $count_use < ( $count_before + 1 ) )
			{
				$count_after_new				=	$count_after + ( ( $count_before + 1 ) - $count_use );
			}
			else
			{
				$count_after_new				=	$count_after;
			}
	
			foreach( array_reverse( $ret_tmp ) as $rodada )
			{
				if ( ( $quem == 'KICK'
				&&     is_object( $rodada_prox )
				&&     ( $rodada->id == $rodada_prox->id
				||       $rodada->id == ( $rodada_prox->id + 1 )
				       )
				     )
				||   $quem == 'CLAS'
				||   $quem == 'RANK'
				   )
				{
					$rodada->open				=	TRUE;
				}
				else
				{
					$rodada->open				=	FALSE;
				}
				$rodada->quem					=	$quem;
				$this->retorno_selecao_rodada[]			=	$rodada;
			}
			
			// Prepara rodadas depois
//			if ( $count_after_new != 0 )
			if ( isset( $rodada_atual->data_inicio ) )
			{
				$count_use					=	0;
				$count_after_future				=	0;
				if ( $quem == 'CLAS' || $quem == 'RANK' )
				{
					$query					=	$this->select_all( "rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
													and rodada_fase.data_inicio <= '{$rodada_atual->data_inicio}' 
													and rodada_fase.data_inicio > ( select rod2.data_inicio 
																	from rodada_fase rod2 
																	where rod2.id = {$rodada_fase_id} )"
													, "rodada_fase.data_inicio ASC, rodada_fase.data_fim ASC"
													, 0
													, 6 );
				}
				else // KICK
				{
					$query					=	$this->select_all( "rodada_fase.campeonato_versao_id = {$campeonato_versao_id} and rodada_fase.data_inicio > ( select rod2.data_inicio from rodada_fase rod2 where rod2.id = {$rodada_fase_id} )", "rodada_fase.data_inicio ASC, rodada_fase.data_fim ASC", 0, 6 );
				}
				foreach( $query->result_object() as $rodada )
				{
					if ( $count_use != $count_after_new )
					{
						$count_use			=	$count_use + 1;
						if ( ( $quem == 'KICK'
						&&     is_object( $rodada_prox )
						&&     ( $rodada->id == $rodada_prox->id
						||       $rodada->id == ( $rodada_prox->id + 1 )
						       )
						)
						||   $quem == 'CLAS'
						||   $quem == 'RANK'
						   )
						{
							$rodada->open		=	TRUE;
						}
						else
						{
							$rodada->open		=	FALSE;
						}
						$rodada->quem			=	$quem;
						$this->retorno_selecao_rodada[]	=	$rodada;
					}
					else
					{
						$count_after_future		=	$count_after_future + 1;
						$this->rodada_fase_id_posterior	=	$rodada->id;
						if ( $count_after_future >= $count_after_new )
						{
							break;
						}
					}
				}
				$query->free_result();
			}
		}
		return $this->retorno_selecao_rodada;
	}

	public function get_rodada_selecao_anterior()
	{
		return $this->rodada_fase_id_anterior;
	}

	public function get_rodada_selecao_posterior()
	{
		return $this->rodada_fase_id_posterior;
	}

	/**
	 * 
	 * Permiter regisrar na sessão o ID do campeonato selecionado pelo usuário. Isso será usado para que na troca de página o campeonato permaneça.
	 * @param unknown_type $campeonato_versao_id
	 */
	public function set_id_sessao( $rodada_fase_id )
	{
		$this->singlepack->set_sessao( 'rodada_fase_id', $rodada_fase_id );
	}
	public function get_id_sessao()
	{
		return $this->singlepack->get_sessao( 'rodada_fase_id' );
	}
	public function set_id_fim_sessao( $rodada_fase_id_fim )
	{
		$this->singlepack->set_sessao( 'rodada_fase_id_fim', $rodada_fase_id_fim );
	}
	public function get_id_fim_sessao()
	{
		return $this->singlepack->get_sessao( 'rodada_fase_id_fim' );
	}
}

/* End of file rodada_fase_model.php */