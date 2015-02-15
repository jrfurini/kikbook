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
 * $Id: rodada_fase_model.php,v 1.17 2013-02-25 21:01:19 junior Exp $
 * 
 */

class Rodada_fase_model extends JX_Model
{
	protected $_revision		=	'$Id: rodada_fase_model.php,v 1.17 2013-02-25 21:01:19 junior Exp $';
	
	var $retorno_selecao_rodada	=	array();
	
	var $rodada_fase_selecionada	=	NULL;

	var $rodada_fase_id_anterior	=	1;
	var $rodada_fase_id_posterior	=	38;
	var $qtde_rodadas_exibidas	=	6;
	
	var $str_camp_user		=	NULL;
	
	function __construct()
	{
		$_config		=	array	(
							 'jogo'				=>	array	(
													 'model_name'	=>	'jogo'
													)
							,'grupo_amigos_fase'		=>	array	(
													 'model_name'	=>	'grupo_amigos_fase'
													)
							,'campeonato_versao'		=>	array	(
													 'model_name'	=>	'campeonato_versao'
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
			,replace( replace( cod, '<small>', '' ), '</small>', '' )					AS	nome_clear
			,concat( ver.descr, ' - ', rodada_fase.cod, ' (', date_format( rodada_fase.data_inicio, '%e/%m' ), ' até ', date_format( rodada_fase.data_fim, '%e/%m/%Y' ), ')' )		AS	title
			,date_format( rodada_fase.data_inicio, '%e/%m/%Y' )						AS	when_field
			,replace( format( rodada_fase.peso_kik, 3 ), '.', ',' )						AS	peso_kik_fmt
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
												,count( id )		as	qtde_jogos
											from	 jogo
											where	 jogo.rodada_fase_id	=	{$rodada->id}
											"
										);
				foreach( $query_jogos->result_object() as $data_jogos )
				{
					$rodada->data_inicio	=	$data_jogos->data_inicio;
					$rodada->data_fim	=	$data_jogos->data_fim;

					// Este cálculo também está no "kick_model.php" em calcular_posicao_kiks.
					if ( $data_jogos->qtde_jogos > 0 )
					{
						$rodada->peso_kik	=	10 / $data_jogos->qtde_jogos; // 10 é o max ou min por jogo.
					}
					else
					{
						$rodada->peso_kik	=	0; // Sem jogos, sem pontos.
					}
					
				}
				$this->update( $rodada );
			}
		}
	}

	private function set_campeonatos_pessoa( $prefix = 'rodada_fase' )
	{
		if ( !is_null( $this->singlepack->get_pessoa_id() ) )
		{
			if ( is_null( $this->str_camp_user ) )
			{ // Monta uma string que será usada em um IN na seleção de campeonatos.
				$qry_camp_user			=	$this->campeonato_versao->get_all_by_where	( "exists 	(
																	select	verpes.campeonato_versao_id
																	from	pessoa_campeonato_versao	verpes
																	where	verpes.pessoa_id = {$this->singlepack->get_pessoa_id()}
																	and	verpes.cadastrado_para_jogar = 'S'
																	and	verpes.campeonato_versao_id  = campeonato_versao.id
																	)
															and	campeonato_versao.ativa = 'S'"
															);
				foreach( $qry_camp_user as $line )
				{
					if  ( !is_null( $this->str_camp_user ) )
					{
						$this->str_camp_user		.=	',';
					}
					$this->str_camp_user			.=	$line->id;
				}
			}

			if ( $this->str_camp_user )
			{
				return "and $prefix.campeonato_versao_id in ( {$this->str_camp_user} )";
			}
			else
			{
				return NULL;
			}
		}
		else
		{
			return NULL;
		}
	}
	
	public function get_rodada_atual( $campeonato_versao_id = NULL )
	{
		if ( !$campeonato_versao_id )
		{
			// Busca em todos os campeonato cadastrado para PESSOA e ABERTO.
			//    DATA e HORA antes de HOJE.
			$rodada		=	$this->get_one_by_where( "/*get_rodada_atual (1) */rodada_fase.data_inicio in	(
																select max( rod2.data_inicio )
																from rodada_fase rod2 
																where rod2.data_inicio <= now() 
																{$this->set_campeonatos_pessoa('rod2')}
																)
									" . $this->set_campeonatos_pessoa()
									);
			if ( !$rodada )
			{
				//    DATA e HORA depois de HOJE.
				$rodada		=	$this->get_one_by_where( "/*get_rodada_atual (2) */rodada_fase.data_inicio in	(
																select min( rod2.data_inicio )
																from rodada_fase rod2 
																where rod2.data_inicio >= now()
																{$this->set_campeonatos_pessoa('rod2')}
																)
									" . $this->set_campeonatos_pessoa()
									);
				if ( !$rodada )
				{
					$rodada		=	$this->get_one_by_where( "/*get_rodada_atual (4) */rodada_fase.data_inicio in	(
																		select max( rod2.data_inicio )
																		from rodada_fase rod2 
																		where rod2.data_inicio <= now() 
																		)
											"
											);
				}
			}
		}
		else
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_atual (5) */	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
											and	rodada_fase.data_inicio in	(
																select max( rod2.data_inicio ) 
																from	rodada_fase rod2
																where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																and	rod2.data_inicio <= now()
																)
									"
									);
			if ( !$rodada )
			{
				$rodada	=	$this->get_one_by_where( "/*get_rodada_atual (6) */	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
											and	rodada_fase.data_inicio in	(
																select min( rod2.data_inicio ) 
																from	rodada_fase rod2
																where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																and	rod2.data_inicio >= now()
																)
									"
									);
			}
		}
		return $rodada;
	}

	public function get_rodada_anterior( $data_hora = NULL, $campeonato_versao_id, $tipo_fase, $forcar_menor = FALSE )
	{
		if ( $data_hora )
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_anterior $tipo_fase*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
												and	rodada_fase.tipo_fase = '{$tipo_fase}'
												and	rodada_fase.data_inicio in	(
																	select	". ( ( $forcar_menor ) ? 'min' : 'max' ) ."( rod2.data_inicio )
																	from	rodada_fase rod2
																	where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																	and	rod2.tipo_fase            = '{$tipo_fase}'
																	and	rod2.data_inicio 	  < '{$data_hora}'
																	)
									"
									);
		}
		else
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_anterior*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
												and	rodada_fase.tipo_fase = '{$tipo_fase}'
												and	rodada_fase.data_inicio in 	(
																	select	". ( ( $forcar_menor ) ? 'min' : 'max' ) ."( rod2.data_inicio )
																	from	rodada_fase rod2
																	where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																	and	rod2.tipo_fase           = '{$tipo_fase}'
																	and 	rod2.data_inicio < now()
																	)
									"
									);
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
																	)
									"
									);
		}
		else
		{
			$rodada		=	$this->get_one_by_where( "/*get_rodada_proxima*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
												and	rodada_fase.data_inicio in 	(
																	select	min( rod2.data_inicio )
																	from	rodada_fase rod2
																	where	rod2.campeonato_versao_id = {$campeonato_versao_id}
																	and 	rod2.data_inicio > now()
																	)
									"
									);
		}
		return $rodada;
	}

	public function get_rodada_aberta( $campeonato_versao_id = NULL )
	{
		if ( !$campeonato_versao_id )
		{
			// Buscamos a próxima data e hora de jogo não iniciado.
			$query			=	$this->db->query( 	"
										select	min( jog2.data_hora )	AS	menor_data
										from	 jogo jog2
											,rodada_fase rod2
										where	jog2.data_hora > now()
										and	jog2.resultado_casa IS NULL
										and	jog2.rodada_fase_id = rod2.id
										{$this->set_campeonatos_pessoa('rod2')}
										"
									);
			if ( $this->query->num_rows = 0 ) // Não achou. Tentamos sem os campeonatos da pessoa.
			{
				$query		=	$this->db->query( 	"
										select	min( jog2.data_hora )	AS	menor_data
										from	 jogo jog2
											,rodada_fase rod2
										where	jog2.data_hora > now()
										and	jog2.resultado_casa IS NULL
										and	jog2.rodada_fase_id = rod2.id
										"
									);
			}
			if ( $this->query->num_rows = 0 ) // Não tem jogo próximo.
			{
				$rodada			=	FALSE;
			}
			else
			{
				// Pegamos as datas, é sempre uma só, e rodamos a query para encontrar a rodada de menor ID para e data e hora do próximo jogo.
				foreach( $query->result_object() as $prox_jogo )
				{
					$rodada		=	$this->get_one_by_where( "/*get_rodada_aberta (1)*/	rodada_fase.id in	(
																		select	min( rod1.id )
																		from	 jogo		jog1
																			,rodada_fase	rod1
																		where	jog1.data_hora		=	'{$prox_jogo->menor_data}'
																		and	jog1.rodada_fase_id	=	rod1.id
																		{$this->set_campeonatos_pessoa('rod1')}
																		)
											" . $this->set_campeonatos_pessoa()
											);
				}
			}

			if ( !$rodada )
			{
				$rodada		=	$this->get_one_by_where( "/*get_rodada_aberta (2)*/	rodada_fase.data_inicio in	(
																		select	( min( rod2.data_inicio ) )
																		from	 jogo jog2
																			,rodada_fase rod2
																		where	jog2.data_hora > now()
																		and	jog2.resultado_casa IS NULL
																		and	jog2.rodada_fase_id		=	rod2.id
																		)
										"
										);
			}
		}
		else
		{
			$rodada			=	$this->get_one_by_where( "/*get_rodada_aberta (3)*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
												and	rodada_fase.id in	(
																select	( min( jog2.rodada_fase_id ) )
																from	 jogo jog2
																	,rodada_fase rod2
																where	jog2.data_hora > now()
																and	jog2.resultado_casa IS NULL
																and	jog2.rodada_fase_id		=	rod2.id
																and	rod2.campeonato_versao_id	=	{$campeonato_versao_id}
																and	rod2.adiada			=	'N'
																{$this->set_campeonatos_pessoa('rod2')}
																)
										" . $this->set_campeonatos_pessoa()
										);
			if ( !$rodada )
			{
				$rodada		=	$this->get_one_by_where( "/*get_rodada_aberta (4)*/	rodada_fase.campeonato_versao_id = {$campeonato_versao_id}
												and	rodada_fase.id in	(
																select	( min( jog2.rodada_fase_id ) )
																	from	 jogo jog2
																		,rodada_fase rod2
																	where	jog2.data_hora > now()
																	and	jog2.resultado_casa IS NULL
																	and	jog2.rodada_fase_id		=	rod2.id
																	and	rod2.campeonato_versao_id	=	{$campeonato_versao_id}
																	and	rod2.adiada			=	'N'
																)
										"
										);
			}
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
		// Caso não seja enviada a rodada, escolhemos uma dentro...
		if ( !$rodada_fase_id )
		{
			if ( $campeonato_versao_id )
			{
				 //... do campeonato selecionado pela pessoa.
				if ( $quem == 'KICK' ) // para chute usamos a rodada aberta e os outros a última rodada ou a atual.
				{
					$rodada_fase_base		=	$this->get_rodada_aberta( $campeonato_versao_id );
				}

				if ( $quem != 'KICK' // Campeonatos fechados não retonam nada acima, então precisamos pegar a próxima aberta.
				||   !$rodada_fase_base
				   )
				{
					$rodada_fase_base		=	$this->get_rodada_atual( $campeonato_versao_id );
				}
			}
			else
			{
				 //.. dos campeonatos cadastrados para a pessoa.
				if ( $quem == 'KICK' ) // para chute usamos a rodada aberta e os outros a última rodada ou a atual.
				{
					$rodada_fase_base		=	$this->get_rodada_aberta();
				}

				if ( $quem != 'KICK' // Campeonatos fechados não retonam nada acima, então precisamos pegar a próxima aberta.
				||   !$rodada_fase_base
				   )
				{
					$rodada_fase_base		=	$this->get_rodada_atual();
				}
			}
			
			if ( !$rodada_fase_base )
			{
				$rodada_fase_base			=	new stdClass();
				$rodada_fase_base->id			=	NULL;
				$rodada_fase_base->campeonato_versao_id	=	NULL;
			}
			
			$rodada_fase_id					=	$rodada_fase_base->id;
			$campeonato_versao_id				=	$rodada_fase_base->campeonato_versao_id;
			$rodada_atual					=	$rodada_fase_base;
		}
		else
		{
			$rodada_fase_base				=	$this->get_one_by_id( $rodada_fase_id );
			$campeonato_versao_id				=	$rodada_fase_base->campeonato_versao_id;
			$rodada_atual					=	$this->get_rodada_atual( $campeonato_versao_id );
		}
		// Próxima rodada aberta.
		$rodada_prox_aberta					=	$this->get_rodada_aberta( $campeonato_versao_id );

		$this->retorno_selecao_rodada				=	array();
		$this->rodada_fase_id_anterior				=	-1;
		$this->rodada_fase_id_posterior				=	-1;

		if ( $rodada_fase_id ) // Caso não seja encontrada/enviada a rodada, indica que o campeonato não tem nenhuma rodada cadastrada.
		{
			// Se enviamos uma fase de grupos de amigos, então as rodadas exibidas serão apenas as cadastradas no grupo.
			if ( $grupo_fase_id )
			{
				$fase_query				=	$this->grupo_amigos_fase->get_one_by_id( $grupo_fase_id );
				
				$fase_grupo_inicio			=	$this->rodada_fase->get_one_by_id( $fase_query->rodada_fase_id_inicio );
				$fase_grupo_fim				=	$this->rodada_fase->get_one_by_id( $fase_query->rodada_fase_id_fim );
				
				$where					=	" and ( ( rodada_fase.data_inicio <= '{$fase_grupo_fim->data_fim}'
										  and     rodada_fase.data_inicio >= '{$fase_grupo_inicio->data_inicio}'
										        )
										  or    ( rodada_fase.data_fim    >= '{$fase_grupo_inicio->data_inicio}'
										  and     rodada_fase.data_fim    <= '{$fase_grupo_fim->data_fim}'
										        )
										      )
										";
			}
			else
			{
				$where					=	NULL;
			}

			$this->select_all( 	"   rodada_fase.campeonato_versao_id = {$campeonato_versao_id}"
						  . $where
						, "rodada_fase.data_inicio ASC, rodada_fase.data_fim ASC"
						, 0
						, 999999
					);
			$rows_rodada					=	$this->get_query_rows();
			$rodada_fase_id_index				=	0;
			$rodada_prox_id_index				=	-1;
			$ret_tmp					=	array();
			foreach( $rows_rodada as $rodada )
			{
				$rodada->quem				=	$quem;
				$rodada->open				=	FALSE; // tudo FECHADO por padrão.
				if ( $rodada->id == $rodada_fase_id ) // Marcamos a posição no array da rodada selecionada.
				{
					$rodada_fase_id_index		=	count( $ret_tmp );
				}

				// Define as rodadas abertas. Atual e próxima à ela.
				if ( $rodada_prox_aberta
				&&   $rodada_prox_aberta->id == $rodada->id // Encontramos a rodada aberta
				   )
				{
					$rodada->open			=	TRUE; // Abrimos a rodada atual.
					$rodada_prox_id_index		=	count( $ret_tmp );
				}
				// Rodada seguinte a primeira rodada aberta.
				if ( $rodada_prox_id_index != -1 // Já selecionou a primeira rodada aberta
				&&   $rodada_prox_id_index == count( $ret_tmp ) -1 // Posição anterior do array
				   )
				{
					$rodada->open			=	TRUE; // Abrimos a rodada seguinte a rodada atual.
				}
				// 3a. rodada aberta.
				/* ainda não
				if ( $rodada_prox_id_index != -1 // Já selecionou a primeira rodada aberta
				&&   $rodada_prox_id_index == count( $ret_tmp ) -2 // Posição anterior do array
				   )
				{
					$rodada->open			=	TRUE; // Abrimos a rodada seguinte a rodada atual.
				}
				*/
				// Registramos as rodadas em um novo array.
				$ret_tmp[]				=	$rodada;
			}
			
			if ( $quem == 'CLAS' || $quem == 'RANK' )
			{
				$start_index				=	4; // Qtde de rodadas antes da rodada selecionada.
				$after_index				=	1; // Qtde de rodadas após a rodada selecionada.
			}
			elseif ( $quem == 'KICK' )
			{
				$start_index				=	3;
				$after_index				=	2;
			}

			// Monta a qtde de rodadas anteriores.
			if ( $rodada_fase_id_index == 0 // A rodada selecionada é a primeira do array;
			||   ( $rodada_fase_id_index - $start_index ) < 0 // A qtde de rodadas anterior não pode ser menor que 0.
			   )
			{
				$start_index				=	0;
			}
			else
			{
				$start_index				=	$rodada_fase_id_index - $start_index;
			}
			
			// Define a quantidade de rodadas que serão retornadas.
			$qtde_rodadas					=	$start_index + $after_index;
			
			if ( ( $start_index + $this->qtde_rodadas_exibidas ) > count( $ret_tmp ) // Não conseguimos montar as $this->qtde_rodadas_exibidas rodadas, provavelmente por estarmos na última rodada.
						// Então vamos tentar colocar rodadas antes da rodada selecionada.
			&&   $start_index != 0 // Se estamos na rodada inicial 0, então não temos como colocar mais rodadas antes a rodada atual.
			   )
			{
				$start_index				=	( ( $start_index - ( ( $start_index + $this->qtde_rodadas_exibidas ) - count( $ret_tmp ) ) ) < 0 ) ? 0 : ( $start_index - ( ( $start_index + $this->qtde_rodadas_exibidas ) - count( $ret_tmp ) ) );
			}

			// Define as rodadas de navegação além do visual. Setas.
				// primeiro achamos o indice no array. depois pegamos o ID da rodada.
			$this->rodada_fase_id_anterior			=	( ( $rodada_fase_id_index - $this->qtde_rodadas_exibidas ) < 0 ) ? 0 : $rodada_fase_id_index - $this->qtde_rodadas_exibidas;
			if ( $this->rodada_fase_id_anterior != -1
			&&   $rodada_fase_id_index != 0 // A estamos na primeira rodada
			   )
			{
				$this->rodada_fase_id_anterior		=	$ret_tmp[ $this->rodada_fase_id_anterior ]->id;
			}

			$this->rodada_fase_id_posterior			=	( ( $rodada_fase_id_index + $this->qtde_rodadas_exibidas ) > count( $ret_tmp ) ) ? count( $ret_tmp ) -1 : ( $rodada_fase_id_index + $this->qtde_rodadas_exibidas ) -1;
			if ( $this->rodada_fase_id_posterior != -1 )
			{
				if ( $rodada_fase_id_index != count( $ret_tmp ) -1 )
				{
					$this->rodada_fase_id_posterior	=	$ret_tmp[ $this->rodada_fase_id_posterior ]->id;
				}
				else
				{
					$this->rodada_fase_id_posterior	=	0;
				}
			}

			$this->retorno_selecao_rodada			=	array_slice( $ret_tmp, $start_index, $this->qtde_rodadas_exibidas );
		}

		// Não enviamos -1 quando solicitada a informação. usamos apenas internamente.
		$this->rodada_fase_id_anterior				=	( $this->rodada_fase_id_anterior == -1 ) ? 0 : $this->rodada_fase_id_anterior;
		$this->rodada_fase_id_posterior				=	( $this->rodada_fase_id_posterior == -1 ) ? 0 : $this->rodada_fase_id_posterior;
		
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

	// Retorna o id da rodada selecionada comparando sessão, id enviado e campeonato.
	public function get_id_selecionado( $rodada_fase_id = NULL, $campeonato_versao_id = NULL, $rodada_aberta = FALSE )
	{
//echo "rod=$rodada_fase_id  camp=$campeonato_versao_id";
		// Recebe o que foi enviado apenas se for numérico.
		if ( !is_numeric( $rodada_fase_id ) )
		{
			$rodada_fase_id				=	NULL;
		}

		// Recebe o id da rodada que está na sessão.
		$rodada_fase_id_sess				=	$this->get_id_sessao();

		$rodada_fase_sel				=	NULL;
		
		if ( $campeonato_versao_id // Foi enviado apenas o id do campeonato e não da rodada. Devemos opter a próxima rodada do campeonato.
		&&   !$rodada_fase_id
		   )
		{
			// Iniciamos pelo id da sessão.
			$rodada_fase_id				=	( is_numeric( str_replace( '"', '', $rodada_fase_id_sess ) ) ) ? str_replace( '"', '', $rodada_fase_id_sess ) : NULL;
			if ( $rodada_fase_id )
			{
//echo "  (1)";
				$rodada_fase_atual		=	$this->get_one_by_id( $rodada_fase_id );
				if ( !is_object( $rodada_fase_atual )
				||   $rodada_fase_atual->campeonato_versao_id != $campeonato_versao_id
				   ) // Tem que trocar a rodada, pois trocou o campeonato.
				{
					$rodada_fase_atual	=	NULL;
					$rodada_fase_id		=	NULL;
				}
			}

			if ( !$rodada_fase_id ) // A sessão não existe ou é de outro campeonato.
			{
//echo "  (2)";
				if ( $rodada_aberta ) // Chutes
				{
					$rodada_fase_atual		=	$this->get_rodada_aberta( $campeonato_versao_id );
					if ( !$rodada_fase_atual ) // Não tem rodada aberta para chutes para o campeonato.
					{
						$rodada_fase_atual	=	$this->get_rodada_atual( $campeonato_versao_id );
					}
				}
				else
				{
					$rodada_fase_atual	=	$this->get_rodada_atual( $campeonato_versao_id );
				}
			}

			if ( !$rodada_fase_atual )
			{
//echo "  (3)";
				$rodada_fase_id			=	NULL;
			}
			else
			{
				$rodada_fase_id			=	$rodada_fase_atual->id;
			}
		}
		else // Foi passado o id da rodada ou não foi passado nenhum id.
		{
//echo "  (4)";
			if ( $rodada_fase_id )
			{
//echo "  (5)";
				$rodada_fase_atual		=	$this->get_one_by_id( $rodada_fase_id );
			}
			else // Não foi passado nenhum ID.
			{
//echo "  (6)";
				if ( $rodada_aberta ) // Chutes
				{
					$rodada_fase_atual		=	$this->get_rodada_aberta();
					if ( !$rodada_fase_atual ) // Não tem rodada aberta para chutes para o campeonato.
					{
						$rodada_fase_atual	=	$this->get_rodada_atual();
					}
				}
				else
				{
					$rodada_fase_atual		=	$this->get_rodada_atual();
				}
				if ( !$rodada_fase_atual )
				{
					$rodada_fase_atual				=	new stdClass();
					$rodada_fase_atual->id				=	NULL;
					$rodada_fase_atual->campeonato_versao_id	=	NULL;
				}

				$rodada_fase_id				=	$rodada_fase_atual->id;
				$campeonato_versao_id			=	$rodada_fase_atual->campeonato_versao_id;
			}
		}
		
		// Registramos os 2 ids.
		$this->campeonato_versao->set_id_sessao( $campeonato_versao_id );
		$this->set_id_sessao( $rodada_fase_id );

		$this->rodada_fase_selecionada	=	$rodada_fase_atual;
		return $rodada_fase_id;
	}
	
	public function get_rodada_selecionada()
	{
		return $this->rodada_fase_selecionada;
	}

	// Retorna o ID do campeonato selecionado;
	public function get_id_campeonato( $campeonato_versao_id )
	{
		if ( !$campeonato_versao_id )
		{
			if ( is_object( $this->rodada_fase_selecionada ) )
			{
				$this->campeonato_versao->set_id_sessao( $this->rodada_fase_selecionada->campeonato_versao_id );
				return $this->rodada_fase_selecionada->campeonato_versao_id;
			}
			else
			{
				$this->rodada_fase_selecionada		=	$this->get_rodada_atual();

				if ( !is_object( $this->rodada_fase_selecionada ) )
				{
					$campeonato_versao_id		=	NULL;
				}
				else
				{
					$campeonato_versao_id		=	$this->rodada_fase_selecionada->campeonato_versao_id;
				}

				return $campeonato_versao_id;
			}
		}
		else
		{
			return $campeonato_versao_id;
		}
	}

	// Retorna o id da rodada selecionada comparando sessão, id enviado e campeonato.
	public function get_id_fim_selecionado( $rodada_fase_id_fim = NULL, $rodada_fase_id = NULL, $campeonato_versao_id = NULL )
	{
		if ( !is_numeric( $rodada_fase_id_fim ) )
		{
			$rodada_fase_id_fim			=	NULL;
		}
		
		$rodada_fase_id_fim_sess			=	$this->get_id_fim_sessao();

		if ( !$rodada_fase_id_fim )
		{
			$rodada_fase_id_fim			=	( is_numeric( str_replace( '"', '', $rodada_fase_id_fim_sess ) ) ) ? str_replace( '"', '', $rodada_fase_id_fim_sess ) : NULL;
			if ( !$rodada_fase_id_fim )
			{
				$rodada_fase_id_fim		=	$rodada_fase_id;
			}
		}

		$this->set_id_fim_sessao( $rodada_fase_id_fim );
		
		return $rodada_fase_id_fim;
	}
}

/* End of file rodada_fase_model.php */