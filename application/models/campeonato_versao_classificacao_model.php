<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Classificação por Rodada Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/campeonato_versao_classificacao_model.php
 * 
 * $Id: campeonato_versao_classificacao_model.php,v 1.22 2013-04-14 12:58:25 junior Exp $
 * 
 */
class Campeonato_versao_classificacao_model extends JX_Model
{
	protected $_revision	=	'$Id: campeonato_versao_classificacao_model.php,v 1.22 2013-04-14 12:58:25 junior Exp $';

	var $rodada_fase_base_fim	=	NULL;
	var $rodada_fase_base		=	NULL;
	
	function __construct()
	{
		$_config		=	array	(
							 'kick'				=>	array	(
													 'model_name'	=>	'kick'
													)
							,'campeonato_versao_classificacao'=>	array	(
													 'model_name'	=>	'campeonato_versao_classificacao'
													)
							,'campeonato_versao'		=>	array	(
													 'model_name'	=>	'campeonato_versao'
													)
							,'rodada_fase'			=>	array	(
													 'model_name'	=>	'rodada_fase'
													)
							,'jogo'				=>	array	(
													 'model_name'	=>	'jogo'
													)
							,'campeonato_versao_equipe'	=>	array	(
													 'model_name'	=>	'campeonato_versao_equipe'
													)
							);

		parent::__construct( $_config );
		
		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 campeonato_versao_classificacao.*
			,ver.campeonato_id
			,eqp.nome
			,eqp.sigla
			,eqp.id_facebook
			,rod.cod
			,rod.cod					AS	rodada_fase_cod
			,rod.data_inicio
			,date_format( rod.data_inicio, '%Y%m%d' )	AS	data_inicio_fmt
			,concat( 'Rodada ', rod.cod, ' ', eqp.nome )	AS	title
			,grp.nome					AS	nome_grupo
			,rod.tipo					AS	rodada_tipo
			,rod.tipo_fase					AS	rodada_tipo_fase
			";
	}
	
	public function set_from_join()
	{
		$this->db->from( 'campeonato_versao_classificacao' );
		$this->db->join( 'equipe		AS	eqp',    'eqp.id           = campeonato_versao_classificacao.equipe_id' );
		$this->db->join( 'rodada_fase		AS	rod',    'rod.id           = campeonato_versao_classificacao.rodada_fase_id' );
		$this->db->join( 'campeonato_versao	AS	ver',    'ver.id           = rod.campeonato_versao_id' );
		$this->db->join( 'grupo			AS	grp',    'grp.id           = campeonato_versao_classificacao.grupo_id', 'LEFT' );
		$this->db->join( 'equipe_imagem		AS	eqpimg', 'eqpimg.equipe_id = eqp.id', 'LEFT' );
	}

	function get_order_by( $selection = null, $direction = null )
	{
		return "grp.nome ASC, campeonato_versao_classificacao.posicao ASC, eqp.nome ASC";
	}
	
	public function _prep_order_by( $selection = null, $direction = null )
	{
//		return $this->get_order_by();
		$order_by			=	'';
		if ( $selection == 'clas' )
		{
			if ( $direction == "+" )
			{
				$order_by		=	$order_by." campeonato_versao_classificacao.posicao ASC, eqp.nome ASC";
			}
			else 
			{
				$order_by		=	$order_by." campeonato_versao_classificacao.posicao DESC, eqp.nome DESC";
			}
		}
		else
		{
			if ( $selection == 'avanco' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.diff_posicao_anterior"; }
			if ( $selection == 'pontos' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.total_ponto"; }
			if ( $selection == 'jogos' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.total_jogo"; }
			if ( $selection == 'vitorias' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.total_vitoria"; }
			if ( $selection == 'empates' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.total_empate"; }
			if ( $selection == 'derrotas' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.total_derrota"; }
			if ( $selection == 'golpos' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.gol_favor"; }
			if ( $selection == 'golneg' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.gol_contra"; }
			if ( $selection == 'golcasa' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.gol_casa"; }
			if ( $selection == 'golfora' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.gol_fora_casa"; }
			if ( $selection == 'saldogol' )	{ $order_by	=	$order_by." ( campeonato_versao_classificacao.gol_favor - campeonato_versao_classificacao.gol_contra )"; }
			if ( $selection == 'aprov' )	{ $order_by	=	$order_by." campeonato_versao_classificacao.aproveitamento"; }
	
			if ( $order_by )
			{
				if ( $direction == "+" )
				{
					$order_by		=	$order_by." ASC";
				}
				else 
				{
					$order_by		=	$order_by." DESC";
				}
			}
		}
		
		$pers_orderby			=	$this->get_order_by();
		
		if ( $pers_orderby )
		{
			if ( $order_by )
			{
				$return		=	$order_by . ', ' . $pers_orderby;
			}
			else
			{
				$return		=	$pers_orderby;
			}
		}
		else
		{
			$return			=	$order_by;
		}
		return $return;		
	}
	
	public function get_column_title()
	{
		return "concat( 'Rodada ', rod.cod, ' ', eqp.nome )";
	}

	function get_where_search_all( $what = null )
	{
		$where			=	"(";
		$where			=	$where."    upper( eqp.nome ) like '%".strtoupper( $what )."%'";
		$where			=	$where." or upper(	case
										when campeonato_versao_classificacao.situacao = 'L' then
											'LIBERTADORES'
										when campeonato_versao_classificacao.situacao = 'P' then
											'PRÉ-LIBERTADORES'
										when campeonato_versao_classificacao.situacao = 'S' then
											'SULAMERICANA'
										when campeonato_versao_classificacao.situacao = 'R' then
											'REBAIXADO'
									end
								 ) like '%".strtoupper( $what )."%'";
		$where			=	$where." or upper( 'JUNIOR' ) like '%".strtoupper( $what )."%'";

		// Acrescenta o title.
		$title_column		=	$this->get_column_title();
		if ( $title_column )
		{
			if ( $where != '' )
			{
				$where	=	$where." or ";
			}
			$where		=	$where."upper( ".$title_column." ) like '%".strtoupper( $what )."%'";
		}
		$where			=	$where.")";
		
		return $where;
	}
	
	/**
	 * Copia a classificacao de uma rodada para outra.
	 */
	public function copy_rodada_to( $rodada_fase_id )
	{
		$rodada_destino		=	$this->rodada_fase->get_one_by_id( $rodada_fase_id );
		
		$rodada_origem		=	$this->rodada_fase->get_rodada_anterior( $rodada_destino->data_inicio, $rodada_destino->campeonato_versao_id, $rodada_destino->tipo_fase );
		// Existe a rodada anterior e são do mesmo tipo, copiamos.
		if ( $rodada_origem
		&&   $rodada_origem->tipo      == $rodada_destino->tipo
		&&   $rodada_origem->tipo_fase == $rodada_destino->tipo_fase
		   )
		{
			$inset_all	=	"
						insert into campeonato_versao_classificacao
									(
									 id
									,rodada_fase_id
									,equipe_id
									,grupo_id
									,posicao
									,diff_posicao_anterior
									,total_ponto
									,total_jogo
									,total_vitoria
									,total_empate
									,total_derrota
									,gol_favor
									,gol_contra
									,gol_casa
									,gol_fora_casa
									,aproveitamento
									,situacao
									)
								select	 NULL
									,{$rodada_fase_id}
									,equipe_id
									,grupo_id
									,posicao
									,diff_posicao_anterior
									,total_ponto
									,total_jogo
									,total_vitoria
									,total_empate
									,total_derrota
									,gol_favor
									,gol_contra
									,gol_casa
									,gol_fora_casa
									,aproveitamento
									,situacao
								from	campeonato_versao_classificacao
								where	rodada_fase_id	=	{$rodada_origem->id}
						";
			
			$this->db->query( $inset_all );
		}
		else
		{
			$this->gerar_classificacao_equipe( $rodada_destino->id, $calcular_ranking = FALSE, $personalizar = FALSE, $rodada_fase_id_fim = NULL, FALSE );
		}
	}

	/**
	 * Prepara dados para ser exibido nas páginas.
	 */
	public function _prep_show( $rodada_fase_id, $rodada_fase_id_fim = NULL, $personalizado = FALSE )
	{
		log_message('debug', "Controller._prep_show_classificacao ($rodada_fase_id,$rodada_fase_id_fim,$personalizado) initialized.");
		// Usamos os comando para manter as seleções da página de ranking caso o usuário volte para lá.
		$tipo_calculo			=	$this->input->post_multi( 'tipo_calculo' );
		if ( !$tipo_calculo ) // não enviado pela página, buscamos na sessão.
		{
			$tipo_calculo		=	$this->singlepack->get_sessao( 'tipo_calculo' );
		}
		$tipo_visual			=	$this->input->post_multi( 'tipo_visual' );
		if ( !$tipo_visual ) // não enviado pela página, buscamos na sessão.
		{
			$tipo_visual		=	$this->singlepack->get_sessao( 'tipo_visual' );
		}
		$grupo_id			=	$this->input->post_multi( 'grupo_id' );
		$grupo_id			=	$this->input->post_multi( 'grupo_id' );
		if ( !$grupo_id ) // não enviado pela página, buscamos na sessão.
		{
			$grupo_id		=	$this->singlepack->get_sessao( 'grupo_id' );
		}
		/*
		 * Registra na sessão os valores usados.
		 */
		$this->singlepack->set_sessao( 'tipo_calculo', $tipo_calculo );
		$this->singlepack->set_sessao( 'tipo_visual', $tipo_visual );
		$this->singlepack->set_sessao( 'grupo_id', $grupo_id );

		if ( $rodada_fase_id_fim )
		{
			log_message('debug', "Indo ao gerar_classificacao_equipe.");
			$rows				= $this->gerar_classificacao_equipe( $rodada_fase_id, $calcular_ranking = FALSE, $personalizado, $rodada_fase_id_fim, FALSE );
			log_message('debug', "Após ao gerar_classificacao_equipe.");
			$total_rows			= count( $rows );
		}
		else
		{
			// Se o campo search foi preenchido na página, restringimos a consulta usando esta informacao.
			if ( $this->jx_search_what )
			{
				$where			= "(".$this->get_where_search_all( $this->jx_search_what ).")";
			}
			else
			{
				$where			= NULL;
			}
			// Se ha algum filtro de FK, montamos a query para isso.
			if ( $this->jx_filter_parent )
			{
				if ( $where )
				{
					$where		= $where." and (".$this->get_where_filter_parent( $this->jx_filter_parent ).")";
				}
				else
				{
					$where		= "(".$this->get_where_filter_parent( $this->jx_filter_parent ).")";
				}
			}
			if ( $rodada_fase_id )
			{
				if ( $where )
				{
					$where		= $where." and ( campeonato_versao_classificacao.rodada_fase_id = {$rodada_fase_id} )";
				}
				else
				{
					$where		= "( campeonato_versao_classificacao.rodada_fase_id = {$rodada_fase_id} )";
				}
			}
			else // Sem rodada não podemos exibir nada.
			{
				if ( $where )
				{
					$where		= $where." and ( 1 = 2 )";
				}
				else
				{
					$where		= "( 1 = 2 )";
				}
			}
			// Usa informacoes da página para montar o order by.
			$order_by			= $this->_prep_order_by( $this->jx_order_selection, $this->jx_order_direction );
	
			// Obtém o número total de linhas.
			$total_rows			= $this->count_all( $where );
	
			// Pega o número da página
			$this->jx_pagination->initialize(
								array	(
									 'total_rows_classif'		=> $total_rows
									,'cur_page'		=> $this->jx_pagina_atual
									)
							);
	
			// Executa a consulta.
			$this->select_for_index( $where, $order_by, 1, 1000 ); // não usaremos a paginação para a classificação.
			$rows				= $this->get_query_rows( $set_parents = TRUE );
		}
		
		$images				= array();
		foreach( $rows as $equipe )
		{
			if ( $equipe->equipe_id )
			{
				foreach( $this->equipe->select_one( 'equipe.id = '.$equipe->equipe_id )->result_object() as $eqp_imagem )
				{
					$images[ $equipe->equipe_id ]	=	$this->imagem->get_file_name( $eqp_imagem->imagem_id, TRUE );
				}
			}
		}

		if ( !$rodada_fase_id_fim
		&&   !$rodada_fase_id
		   )
		{
			$rodada_atual		= $this->rodada_fase->get_rodada_aberta();
		}
		else
		{
			$rodada_atual		= $this->rodada_fase->get_one_by_id( ( ( !$rodada_fase_id_fim ) ? $rodada_fase_id : $rodada_fase_id_fim ) );
		}
		
		$rodada_inicial			= $this->rodada_fase->get_rodada_anterior( $rodada_atual->data_inicio, $rodada_atual->campeonato_versao_id, $rodada_atual->tipo_fase, $forcar_menor = TRUE );
		if ( !$rodada_inicial )
		{
			$rodada_inicial		= $rodada_atual;
		}

		$data				= array	(
							// Obtém as linhas que serão exibidas.
							 'rows_classif'			=> $rows
							,'fields'			=> $this->get_fields_info( 'INDEX', $rows )
							,'rows_campeonato'		=> $this->campeonato_versao->get_campeonato_selecao()
							,'rodada_classif'		=> $rodada_atual
							,'rodada_atual'			=> $rodada_atual
							,'rodada_inicial'		=> $rodada_inicial
							,'rows_rodada'			=> $this->rodada_fase->get_rodadas_selecao( ( ( !$rodada_fase_id_fim ) ? $rodada_fase_id : $rodada_fase_id_fim ), 'CLAS', $rodada_atual->campeonato_versao_id )
							,'rows_rodada_all'		=> $this->rodada_fase->get_all_by_where( "campeonato_versao_id = {$rodada_atual->campeonato_versao_id}" )
							,'rodada_anterior'		=> $this->rodada_fase->get_rodada_selecao_anterior()
							,'rodada_posterior'		=> $this->rodada_fase->get_rodada_selecao_posterior()
							,'master_table'			=> 'campeonato_versao_classificacao'
							,'total_rows_classif'		=> $total_rows
							,'jx_pagina_atual'		=> $this->jx_pagination->get_cur_page()
							,'start_line'			=> $this->jx_pagination->get_start_line()
							,'last_line'			=> $this->jx_pagination->get_last_line()
							,'total_lines'			=> $this->jx_pagination->get_total_lines()
							,'images_classif'		=> $images
							,'tipo_visual'			=> 'amigos'
							,'tipo_calculo'			=> 'rodada'
							,'grupo_id'			=> $grupo_id
							,'campeonato_versao_atual'	=> $this->campeonato_versao->get_one_by_id( $rodada_atual->campeonato_versao_id )
							,'rodada_fase_inicial'		=> $this->rodada_fase_base
							,'rodada_fase_final'		=> $this->rodada_fase_base_fim
							,'personalizada_meus_chutes'	=> ( $personalizado ) ? "true" : "false"
							,'kiker_info'			=> $this->kick->kiker_info()
							);
		$this->load->vars( $data );
	}

	public function get_xml_chart( $tipo_retorno, $equipe_id, $rodada_fase_id )
	{
		$ret				=	NULL;

		$min_pos			=	1;
		$max_pos			=	0;
		// Pega a rodada atual.
		$rodada_fase			=	$this->rodada_fase->get_one_by_id( $rodada_fase_id );
		
		// Pega todas as rodadas anteriores e a atual para obter os valores.
		$rows				=	$this->campeonato_versao_classificacao->get_all_by_where	(
															 "	rod.data_inicio <= '{$rodada_fase->data_inicio}'
															 and    rod.tipo_fase = '{$rodada_fase->tipo_fase}'
															 and	rod.campeonato_versao_id = {$rodada_fase->campeonato_versao_id}
															 "
															,"rod.data_inicio" // order by
															);

		// Determina a menor e maior posição de classificação.
		// Valores para a equipe na rodada selecionada
		$min_pos			=	999;
		$max_pos			=	-1;
		$min_value			=	1;
		$max_value			=	-1;

		$posicao_final			=	20;
		$saldo_gols_final		=	0;
		$aproveitamento_final		=	100;
		foreach( $rows as $key => $row )
		{
			// Posicao
			if ( $row->posicao < $min_pos )
			{
				$min_pos	=	$row->posicao;
			}
			if ( $row->posicao > $max_pos )
			{
				$max_pos	=	$row->posicao;
			}
			// Apenas o maior Valor
			if ( round( $row->aproveitamento ) > $max_value )
			{
				$max_value	=	round( $row->aproveitamento );
			}
			if ( ( $row->gol_favor - $row->gol_contra ) > $max_value )
			{
				$max_value	=	( $row->gol_favor - $row->gol_contra );
			}
			
			// Registra os valores da rodada selecionada para a equipe selecionada.
			if ( $row->rodada_fase_id == $rodada_fase_id
			&&   $row->equipe_id == $equipe_id
			   )
			{
				$posicao_final		=	$row->posicao;
				$saldo_gols_final	=	( $row->gol_favor - $row->gol_contra );
				$aproveitamento_final	=	round( $row->aproveitamento );
			}
		}
		
		$ind_max			=	$max_pos;
		$ar_pos				=	array();
		for ( $i = 1; $i <= $max_pos; $i++ )
		{
			if ( $tipo_retorno == 'OBJ' )
			{
				$ar_pos[$i]		=	$i;
			}
			else
			{
				$ar_pos[$i]		=	$ind_max;
			}
			$ind_max--;
		}

		$rows				=	$this->campeonato_versao_classificacao->get_all_by_where	(
															 "	rod.data_inicio <= '{$rodada_fase->data_inicio}'
															 and    rod.tipo_fase = '{$rodada_fase->tipo_fase}'
															 and	eqp.id = {$equipe_id}
															 and	rod.campeonato_versao_id = {$rodada_fase->campeonato_versao_id}
															 "
															,"rod.data_inicio" // order by
															);
		if ( $tipo_retorno == 'CSV' )
		{
			if ( count( $rows ) > 1 )
			{
				//$ret				=	"rodada,Pos,SG,% Aprov,GL,GF";
				$ret				=	"rodada,Posição ($posicao_final),SG ($saldo_gols_final),% Aprov ($aproveitamento_final)";
				foreach( $rows as $key => $row )
				{
					//				     rodada		  Pos			SG					    % Aprov					GL			GF
					//$ret			.=	"\n" . ( $key + 1 ). "," . $row->posicao . "," . ( $row->gol_favor - $row->gol_contra ) . "," . round( $row->aproveitamento ) . "," . $row->gol_casa . "," .  $row->gol_fora_casa ;
					//				     rodada		  Pos			SG					    % Aprov
					$ret			.=	"\n" . ( $key + 1 ). "," . ( $ar_pos[ $row->posicao ] * ( $max_value / $max_pos ) ) . "," . ( $row->gol_favor - $row->gol_contra ) . "," . round( $row->aproveitamento ) ;
				}
			}
			
		}
		elseif ( $tipo_retorno == 'OBJ' ) // Retorno o objeto.
		{
			$ret				=	new stdClass();
			$ret->rows			=	$rows;
			$ret->maior_classificacao	=	$max_pos;

			if ( $rows
			&&   is_array( $rows )
			&&   count( $rows ) > 0
			   )
			{
				$ret->equipe_sigla	=	$rows[0]->sigla;
				$ret->equipe_nome	=	$rows[0]->nome;
				$ret->equipe_id		=	$rows[0]->equipe_id;
				
			}
			else
			{
				$ret->equipe_sigla	=	'KIK';
				$ret->equipe_nome	=	'Kikbook FC';
				$ret->equipe_id		=	263;
			}
			$ret->ar_pos			=	$ar_pos;
		}
		
		return $ret;
	}

	/**
	 * 
	 * Rotina para cálculo da classificação dos times dentro da Versão do Campeonato.
	 * 
	 * @param INT $rodada_fase_id	= Recebe o ID da rodada que será a ÚLTIMA a ser processada. Ele pegará todos os jogos da rodada informada e das anteriores à ela.
	 * @param INT $grupo_id		= Recebe o ID do grupo para o qual deve calcular. Hoje ainda não há lógica para o grupo, mas ...
	 * @param STRING $order_by	= Permite selecionar qual a ordenação da saída da query. Isso pode ajudar no cálculo.
	 */
	public function classificar( $rodada_fase_id, $campeonato_versao_id = NULL, $calcular_ranking = TRUE, $personalizar = FALSE, $rodada_fase_id_fim = NULL )
	{
		log_message( 'debug', 'classificar rodada_fase='.$rodada_fase_id.' camp='.$campeonato_versao_id );

		if ( $campeonato_versao_id )
		{
			$rodadas			=	$this->rodada_fase->get_all_by_where( "rodada_fase.campeonato_versao_id = {$campeonato_versao_id} and rodada_fase.data_inicio <= CURRENT_TIMESTAMP" );
			foreach( $rodadas as $key => $rodada )
			{
				$this->rodada_fase->set_inicio_fim( $rodada->id );
				$this->gerar_classificacao_equipe( $rodada->id, $calcular_ranking, $personalizar, $rodada_fase_id_fim, TRUE );
			}
		}
		elseif ( $rodada_fase_id )
		{
			$this->rodada_fase->set_inicio_fim( $rodada_fase_id );
			$this->gerar_classificacao_equipe( $rodada_fase_id, $calcular_ranking, $personalizar, $rodada_fase_id_fim, TRUE );
		}
	}

	protected function get_estatistica( $rodada_fase_id, $order_by = NULL, $personalizado = TRUE, $rodada_fase_id_fim = NULL )
	{
		log_message('debug', "Campeonato_versao_classificacao_model.get_estatistica ($rodada_fase_id,$rodada_fase_id_fim,$personalizado)");
		$this->rodada_fase_base	=	$this->rodada_fase->get_one_by_id( $rodada_fase_id );

		if ( $rodada_fase_id_fim )
		{
			$this->rodada_fase_base_fim	=	$this->rodada_fase->get_one_by_id( $rodada_fase_id_fim );
		}
		else
		{
			$this->rodada_fase_base_fim	=	NULL;
		}

		if ( $personalizado === TRUE || $personalizado === FALSE )
		{
			$personalizado		=	$personalizado;
		}
		else 
		{
			$personalizado		=	( strtoupper( $personalizado ) == "TRUE" ) ? TRUE : FALSE;
		}

		if ( $personalizado )
		{
			$tabela_resultado	=	"kick.kick";
		}
		else
		{
			$tabela_resultado	=	"jog.resultado";
		}

		log_message('debug', "Campeonato_versao_classificacao_model.get_estatistica $tabela_resultado.");
		
		$select		=	"
					select	 eqp.id					AS	equipe_id/*1*/
						,eqp.nome				AS	nome/*2*/
						,null					AS	imagem_id/*3*/
					";
		
		if ( $this->rodada_fase_base->tipo_fase == 'M' ) // Por grupo mista
		{
			$select		.=	"
						,IFNULL( eqpgrp.grupo_id, 0 )		AS	grupo_id/*4*/
						";
		}
		else
		{
			$select		.=	"
						,IFNULL( jog.grupo_id, 0 )		AS	grupo_id/*4*/
						";
		}
		
		$select			.=	"
						,rod.tipo				AS	tipo_rodada_fase/*5*/
						,rod.tipo_fase				AS	tipo_fase_rodada_fase/*6*/
						,sum(	case
								when ( ( eqp.id = jog.equipe_id_casa
								and      ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) ) > ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) )
								       )
								or     ( eqp.id = jog.equipe_id_visitante
								and      ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) ) > ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) )
								       )
								     ) then
									1
								else
									0
							end
							)				AS					total_vitoria/*7*/
						,sum(
							case
								when ( ( eqp.id = jog.equipe_id_casa
								and      {$tabela_resultado}_casa is not null
								       )
								or     ( eqp.id = jog.equipe_id_visitante
								and      {$tabela_resultado}_visitante is not null
								       )
								     ) then
									case
										when ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) ) = ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) ) then
											1
										else
											0
									end
								else
									0
							end
							)				AS					total_empate/*8*/
						,sum(
							case
								when ( ( eqp.id = jog.equipe_id_casa
								and      ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) ) < ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) )
								       )
								or     ( eqp.id = jog.equipe_id_visitante
								and      ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) ) < ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) )
								       )
								     ) then
									1
								else
									0
							end
							)									total_derrota/*9*/
						,sum(
							case
								when jog.equipe_id_casa = eqp.id then
									( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) )
								else
									case
										when jog.equipe_id_visitante = eqp.id then
											( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) )
										else
											0
									end
							end
							)									gol_favor/*10*/
						,sum(
							case
								when jog.equipe_id_casa = eqp.id then
									( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) )
								else
									case
										when jog.equipe_id_visitante = eqp.id then
											( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) )
										else
											0
									end
							end
							)									gol_contra/*11*/
						,sum(
							case
								when jog.equipe_id_casa = eqp.id then
									  ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) )
									- ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) )
								else
									case
										when jog.equipe_id_visitante = eqp.id then
											  ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) )
											- ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) )
										else
											0
									end
								end
							)									saldo_gol/*12*/
						,sum(
							case
								when {$tabela_resultado}_casa is not null
								or       {$tabela_resultado}_visitante is not null then
									case
										when ( ( eqp.id = jog.equipe_id_casa
										and      ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) ) > ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) )
										       )
										or     ( eqp.id = jog.equipe_id_visitante
										and      ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) ) > ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) )
										       )
										     ) then
											3 /*vitoria*/
										else
											case
												when ( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) ) = ( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) ) then
													1 /*empate*/
												else
													0 /*derrota*/
											end
									end
								else
									0 /* jogo não realizado */
							end
							)									total_ponto/*13*/
						,sum(
							case
								when ( ( eqp.id = jog.equipe_id_casa
								and      {$tabela_resultado}_casa is not null
								       )
								or     ( eqp.id = jog.equipe_id_visitante
								and      {$tabela_resultado}_visitante is not null
								       )
								     ) then
									1
								else
									0
							end
							)									total_jogo/*14*/
						,sum(
								case
									when jog.equipe_id_casa = eqp.id then
										( IFNULL( {$tabela_resultado}_casa, 0 ) + IFNULL( jog.resultado_casa_prorrogacao, 0 ) + IFNULL( jog.penaltis_casa, 0 ) )
									else
										0
								end
							)									gol_casa/*15*/
						,sum(
								case
									when jog.equipe_id_visitante = eqp.id then
										( IFNULL( {$tabela_resultado}_visitante, 0 ) + IFNULL( jog.resultado_visitante_prorrogacao, 0 ) + IFNULL( jog.penaltis_visitante, 0 ) )
									else
										0
								end
							)									gol_fora_casa/*16*/
						,rod.campeonato_versao_id
					";
		if ( $this->rodada_fase_base->tipo_fase == 'M' // Por grupo mista
		||   $rodada_fase_id_fim
		||   $personalizado
		   )
		{
			$select		.=
					"
						,grp.nome			AS	nome_grupo
						,eqp.id_facebook
						,eqp.sigla
					";
		}

		$select			.=
					"
					from		rodada_fase			rod
					";
					
		if ( $rodada_fase_id_fim
		||   $personalizado
		   )
		{
			$select		.=
					"
					join		rodada_fase			rod_fim	ON		rod_fim.id			=	{$this->rodada_fase_base_fim->id}
					join		rodada_fase			rod_2	ON		rod_2.data_inicio		between	rod.data_inicio
																		and	rod_fim.data_inicio
					";
		}
		else
		{
			$select		.=
					"
					join		rodada_fase			rod_2	ON		rod_2.data_inicio		<=	rod.data_inicio
					";
		}
		$select			.=
					"
													and	rod_2.campeonato_versao_id	=	rod.campeonato_versao_id
													and	rod_2.tipo_fase			=	rod.tipo_fase
													and	rod_2.data_inicio		>=	(
																			/* Evitamos que sejam lidos jogos de rodadas anteriores ao tipo atual. Isso ocorre no campeonato carioca que os tipos de rodada se repetem mas não na sequencia. */
																			select	IFNULL( max( rod_3.data_inicio ), rod_2.data_inicio )
																			from	rodada_fase	AS	rod_3
																			where	rod_3.data_inicio		<=	rod.data_inicio
																			and	rod_3.campeonato_versao_id	=	rod.campeonato_versao_id
																			and	rod_3.tipo_fase			!=	rod.tipo_fase
																			)
					join	 	campeonato_versao_equipe	vereqp	ON		vereqp.campeonato_versao_id	=	rod.campeonato_versao_id
					join		equipe				eqp	ON		eqp.id				=	vereqp.equipe_id
					join		jogo				jog	ON		( jog.equipe_id_casa		=	eqp.id
													or	  jog.equipe_id_visitante	=	eqp.id
														)
													and	jog.rodada_fase_id		=	rod_2.id
													/*and	( {$tabela_resultado}_casa		is not null
													or	  {$tabela_resultado}_visitante	is not null
														)*/
					";
		if ( $personalizado )
		{
			if ( is_object( $this->singlepack->get_user_info() ) && $this->singlepack->get_user_info()->pessoa_id )
			{
				$pessoa_id	=	$this->singlepack->get_user_info()->pessoa_id;
			}
			else
			{
				$pessoa_id	=	-1;
			}

			$select		.=	"
					left join	kick				kick	ON		kick.jogo_id			=	jog.id
													and	kick.pessoa_id			=	{$pessoa_id}
						";
		}
		
		if ( $this->rodada_fase_base->tipo_fase == 'M' ) // Por grupo mista
		{
			$select		.=	"
					join		grupo				grp	ON		jog.rodada_fase_id		between	grp.rodada_fase_id_inicio
																		and	grp.rodada_fase_id_fim
					join		grupo_equipe			eqpgrp	ON		eqpgrp.grupo_id			=	grp.id
													and	eqpgrp.equipe_id		=	eqp.id
						";
		}
		elseif ( $rodada_fase_id_fim
		||       $personalizado
		       )
		{
			$select		.=
					"
					left join	grupo				grp	ON		grp.id           		= 
					";
					if ( $this->rodada_fase_base->tipo_fase == 'M' ) // Por grupo mista
					{
						$select		.=	"
									IFNULL( eqpgrp.grupo_id, 0 )
									";
					}
					else
					{
						$select		.=	"
									IFNULL( jog.grupo_id, 0 )
									";
					}
		}
		
		$select			.=	"
					where		rod.id				=	{$this->rodada_fase_base->id}
					";
		
		if ( $this->rodada_fase_base->tipo_fase == 'M' ) // Por grupo mista
		{
			$select		.=	"
					group by	 IFNULL( eqpgrp.grupo_id, 0 )
						";
		}
		else
		{
			$select		.=	"
					group by	 IFNULL( jog.grupo_id, 0 )
						";
		}
		
		$select			.=	"
							,eqp.id
							,eqp.nome
							,rod.tipo
							,rod.tipo_fase
							";
		
		if ( $order_by )
		{
			$select		=	$select.' order by '.$order_by; 	// order by grupo, total_ponto desc, total_vitoria desc, saldo_gol desc, total_gol_favor desc, total_gol_contra desc
											// 		4,          13                   7              12                   10                      11
		}
		else
		{
			$select		=	$select.' order by 4, 13 DESC, 7 DESC, 12 DESC, 10 DESC, 16 DESC, 15 DESC, 2';
 		}
		
		$query			=	$this->db->query( $select );
		return $query->result_object();
	}
	
	public function gerar_classificacao_equipe( $rodada_fase_id, $calcular_ranking = TRUE, $personalizado = FALSE, $rodada_fase_id_fim = NULL, $show_log = FALSE )
	{
if ( $show_log ) echo "iniciando gerar_classificacao_equipe rodada={$rodada_fase_id}<br/>\n";
//		$this->db->trans_begin();
		$ret_class				=	NULL;

		// Seleciona os dados da rodada do jogo.
		$rodada					=	$this->rodada_fase->get_one_by_id( $rodada_fase_id );
		if ( is_object( $rodada )
		&&   isset( $rodada->campeonato_versao_id )
		   )
		{
			$campeonato_versao_id		=	$rodada->campeonato_versao_id;
			$campeonato_versao		=	$this->campeonato_versao->get_one_by_id( $campeonato_versao_id );
			$campeonato_id			=	$campeonato_versao->campeonato_id;
		}
		else
		{
			return FALSE;
		}

		/*
		 * CÁLCULO PARA PONTOS CORRIDOS (brasileirão) OU FASE DE GRUPOS (Libertadores, liga dos campeões e copa do mundo).
		 */
//		if ( $rodada->tipo == 'G' /*Grupos*/
//		||   $rodada->tipo == 'T' /*Pontos Corridos*/
//		   )
		{
			// Cria array com a classificação da rodada anterior.
			$rodada_ant				=	$this->rodada_fase->get_one_by_where	(
														"	rodada_fase.data_inicio in	(
																			select 	max( rod2.data_inicio )
																			from	rodada_fase rod2
																			where	rod2.campeonato_versao_id = {$rodada->campeonato_versao_id}
																			and	rod2.data_inicio < '{$rodada->data_inicio}'
																			)
														and	rodada_fase.campeonato_versao_id = {$rodada->campeonato_versao_id}
														"
														);
	
			$clas_ant				=	array();
			if ( is_object( $rodada_ant )
			&&   isset( $rodada_ant->id )
			&&   $rodada_ant
			   )
			{
if ( $show_log ) echo 'inicio atual='.$rodada->data_inicio."<br/>\n";
if ( $show_log ) echo 'RODADA ant='.$rodada_ant->id."<br/>\n";
				/*
				 * Quando os tipos das rodadas anterior e atual mudam são diferentes não usamos a rodada anterior para calcular a diferença, mas sim zeramos.
				 */
				if ( $rodada->tipo == $rodada_ant->tipo )
				{
					foreach( $this->select_all( 'rodada_fase_id = '.$rodada_ant->id )->result_object() as $row )
					{
						$clas_ant[ $row->equipe_id ]	=	$row;
					}
				}
				else
				{
if ( $show_log ) echo "SEM RODADA ant tipos diferentes<br/>\n";
				}
			}
			else
			{
if ( $show_log ) echo "SEM RODADA ant inicio atual=$rodada->data_inicio<br/>\n";
//			return FALSE;
			}
	
			// Cria array com a classificação da rodada atual.
			$clas_atual				=	array();
			$clas_atual_repro			=	array();
			foreach( $this->select_all( 'rodada_fase_id = '.$rodada_fase_id )->result_object() as $row )
			{
				$clas_atual_repro[ $row->equipe_id ]	=	$row;
			}
	
			$posicao				=	0;
			$qtde_libertadores			=	4;
			
			if ( $campeonato_id == 4 ) // Eliminatória 2014 América do Sul.
			{
				$order_by			=	'4, 13 DESC, 12 DESC, 15 DESC, 2';
			}
			else if ( $campeonato_id == 1 ) // Brasileirão.
			{
				$order_by			=	'4, 13 DESC, 7 DESC, 12 DESC, 10 DESC, 2';
			}
			else if ( $campeonato_id == 6 ) // Carioca.
			{
				$order_by			=	'4, 13 DESC, 7 DESC, 12 DESC, 10 DESC, 2';
			}
			else if ( $campeonato_id == 7 ) // Paulista.
			{
				$order_by			=	'4, 13 DESC, 7 DESC, 12 DESC, 10 DESC, 2';
			}
			else if ( $campeonato_id == 8 ) // Brasileiro Série B.
			{
				$order_by			=	'4, 13 DESC, 7 DESC, 12 DESC, 10 DESC, 2';
			}
			else
			{
				$order_by			=	'4, 13 DESC, 7 DESC, 12 DESC, 10 DESC, 16 DESC, 15 DESC, 2';
			}
if ( $show_log ) echo "camp=$campeonato_id ORDER BY $order_by<br/>\n";

			foreach( $this->get_estatistica( $rodada_fase_id, $order_by, $personalizado, $rodada_fase_id_fim ) as $clas_equipe )
			{
		 		$posicao			=	$posicao + 1;
	
				/*
				 * Verifica se a equipe já está classificada para a libertadores.
				 */
				$equipe_camp			=	$this->campeonato_versao_equipe->get_one_by_where(	"
																	campeonato_versao_id	= {$campeonato_versao_id}
																and	equipe_id		= ".$clas_equipe->equipe_id
															);
	
				$situacao			=	'N'; // Normal
				if ( isset( $equipe_camp->ja_libertadores )		// Libertadores. Regra Brasileirão 2012
				&&   $equipe_camp->ja_libertadores == 'S'
				   )
				{
					$situacao		=	'L'; 		// Libertadores
					$qtde_libertadores	=	$qtde_libertadores - 1;
				}
				
				// Procura se é reprocessamento ou novo registro.
				if ( is_array( $clas_atual_repro )
				&&   key_exists( $clas_equipe->equipe_id, $clas_atual_repro )
				   )
				{
					$id			=	$clas_atual_repro[ $clas_equipe->equipe_id ]->id; // Vai para update.
				}
				else
				{
					$id			=	NULL; // Vai para insert.
				}
				
				$data				=	new stdClass();
				$data->id			=	$id;
				$data->rodada_fase_id		=	$rodada_fase_id;
				$data->campeonato_versao_id	=	$clas_equipe->campeonato_versao_id;
				$data->tipo_rodada_fase		=	$clas_equipe->tipo_rodada_fase;
				$data->equipe_id		=	$clas_equipe->equipe_id;
				$data->grupo_id			=	$clas_equipe->grupo_id;
				$data->posicao			=	$posicao; // Recalculado abaixo.
				$data->diff_posicao_anterior	=	0;
				$data->total_ponto		=	$clas_equipe->total_ponto;
				$data->total_jogo		=	$clas_equipe->total_jogo;
				$data->total_vitoria		=	$clas_equipe->total_vitoria;
				$data->total_empate		=	$clas_equipe->total_empate;
				$data->total_derrota		=	$clas_equipe->total_derrota;
				$data->gol_favor		=	$clas_equipe->gol_favor;
				$data->gol_contra		=	$clas_equipe->gol_contra;
				$data->gol_casa			=	$clas_equipe->gol_casa;
				$data->gol_fora_casa		=	$clas_equipe->gol_fora_casa;
				$data->aproveitamento		=	( $clas_equipe->total_jogo == 0 ) ? 0 : round( $clas_equipe->total_ponto / ( $clas_equipe->total_jogo * 3 ) * 100, 1 );
				$data->situacao			=	$situacao;

				$data->nome_grupo		=	( isset( $clas_equipe->nome_grupo ) ) ? $clas_equipe->nome_grupo :  NULL;
				$data->id_facebook		=	( isset( $clas_equipe->id_facebook ) ) ? $clas_equipe->id_facebook :  NULL;
				$data->nome			=	( isset( $clas_equipe->nome ) ) ? $clas_equipe->nome :  NULL;
				$data->campeonato_id		=	$campeonato_versao_id;

				$clas_atual[ $posicao ]		=	$data; // Registramos com posição como KEY para que a leitura na segunda etapa seja na sequencia criada aqui.
				unset( $data );
			}
	
			$ar_libertadores			=	array();
			$ar_prelibertadores			=	array();
			// BRASILEIRÃO
			if ( $campeonato_id == 1 )
			{
				// Prepara arrays para libertadores (2012)
				if ( $qtde_libertadores == 3 )
				{
					$ar_libertadores	=	array( 1, 2 );
					$ar_prelibertadores	=	array( 3 );
					$qtde_prelibertadores	=	1;
				}
				elseif ( $qtde_libertadores == 4 )
				{
					$ar_libertadores	=	array( 1, 2, 3 );
					$ar_prelibertadores	=	array( 4 );
					$qtde_libertadores	=	3;
					$qtde_prelibertadores	=	1;
				}
				else
				{
					$ar_libertadores	=	array( 1, 2, 3 );
					$ar_prelibertadores	=	array( 4 );
					$qtde_prelibertadores	=	1;
					$qtde_libertadores	=	3;
				}
				$qtde_sulamericana		=	8;
			}
			
			/*
			 * Recalcula a posição e situação.
			 * Atualiza a base de dados
			 */
			$grupo_id_ant				=	-1;
			$ar_pos_grupo				=	array();
			$clas_atual_redo			=	array();
			$posicao				=	0;
			foreach( $clas_atual as $key_pos_for => $clas_equipe2 )
			{
				$key_pos							=	$key_pos_for;
				/**
				 * Calcula a posição da equipe na classificação.
				 */
				if ( ( $clas_equipe2->tipo_rodada_fase == 'G' /* Fase de Grupos */
				||     $clas_equipe2->tipo_rodada_fase == 'M' /* Mata Mata */
				||     $clas_equipe2->tipo_rodada_fase == 'I' /* Jogo de Ida e de Volta */
				     )
				&&   $clas_equipe2->grupo_id
				   ) /* Por Grupos */
				{
					if ( $grupo_id_ant == -1
					||   $clas_equipe2->grupo_id != $grupo_id_ant
					   )
					{
						$grupo_id_ant					=	$clas_equipe2->grupo_id;
						if ( !key_exists( $clas_equipe2->grupo_id, $ar_pos_grupo ) )
						{
							$pos_grupo				=	new stdClass();
							$pos_grupo->key_pos			=	0; // Abrindo o Grupo, só pode ser a posição 1.
							$ar_pos_grupo[ $clas_equipe2->grupo_id ]=	$pos_grupo;
							unset( $pos_grupo );
						}
					}
					$ar_pos_grupo[ $clas_equipe2->grupo_id ]->key_pos	=	$ar_pos_grupo[ $clas_equipe2->grupo_id ]->key_pos +1;
					$key_pos						=	$ar_pos_grupo[ $clas_equipe2->grupo_id ]->key_pos;
					$grupo_id						=	$clas_equipe2->grupo_id;
				}
				elseif ( $clas_equipe2->tipo_rodada_fase == 'M' ) /* Mata Mata */
				{
					$key_pos						=	$key_pos_for;
					$grupo_id						=	0; // Apenas para o array de classificacao anterior.
				}
				elseif ( $clas_equipe2->tipo_rodada_fase == 'I' ) /* Jogo de Ida e de Volta */
				{
					$key_pos						=	$key_pos_for;
					$grupo_id						=	0; // Apenas para o array de classificacao anterior.
				}
				elseif ( $clas_equipe2->tipo_rodada_fase == 'T' ) /* Todos os times */
				{
					$key_pos						=	$key_pos_for;
					$grupo_id						=	0; // Apenas para o array de classificacao anterior.
				}
				else
				{
					$key_pos						=	$key_pos_for;
					$grupo_id						=	0; // Apenas para o array de classificacao anterior.
				}
				
				// Aqui mantemos as equipes na mesma posição
				if ( $key_pos == 1 ) // Primeira linha.
				{
					$posicao				=	$key_pos; // Fica 1.
				}
				else
				{
					if ( key_exists( $grupo_id, $clas_atual_redo )
					&&   key_exists( $key_pos -1, $clas_atual_redo[ $grupo_id ] )
					   )
					{
						$equipe_ant			=	$clas_atual_redo[ $grupo_id ][ $key_pos -1 ];
					}
					else
					{
						$equipe_ant			=	new stdClass();
						$equipe_ant->total_ponto	=	0;
						$equipe_ant->total_vitoria	=	0;
						$equipe_ant->gol_favor		=	0;
						$equipe_ant->gol_contra		=	0;
						$equipe_ant->gol_fora_casa	=	0;
						$equipe_ant->gol_casa		=	0;
					}
					// Regra Brasileirão 2012.
						// Tudo igual, mesma posição.
					if ( $clas_equipe2->total_ponto					== $equipe_ant->total_ponto
					&&   $clas_equipe2->total_vitoria				== $equipe_ant->total_vitoria
					&&   ( $clas_equipe2->gol_favor - $clas_equipe2->gol_contra )	== ( $equipe_ant->gol_favor - $equipe_ant->gol_contra )
					&&   $clas_equipe2->gol_favor					== $equipe_ant->gol_favor
					&&   $clas_equipe2->gol_contra					== $equipe_ant->gol_contra
					&&   ( $campeonato_id						!= 3 /*Libertadores*/
					||     ( $campeonato_id						== 3 /*Libertadores*/
						 // Gols em casa e gols fora de casa para desempatar também. Libertadores.
					&&       $clas_equipe2->gol_fora_casa				== $equipe_ant->gol_fora_casa
					&&       $clas_equipe2->gol_casa				== $equipe_ant->gol_casa
					       )
					     )
					   )
					{
						$posicao			=	$posicao; // Não muda, fica igual a equipe anterior.
					}
					else
					{
						$posicao			=	$key_pos; // Se tivermos N equipes na mesma posição, quando a igualdade acabar temos que iniciar na posição seguinte mais as N equipes iguals anteriores.
					}
					unset( $equipe_ant );
				}
				$clas_equipe2->posicao				=	$posicao;
	
				// Calcula a diferença entre uma rodada atual e a anterior.
				if ( key_exists( $clas_equipe2->equipe_id, $clas_ant ) )
				{
					$pos_ant				=	$clas_ant[ $clas_equipe2->equipe_id ]->posicao;
					$clas_equipe2->diff_posicao_anterior	=	$clas_ant[ $clas_equipe2->equipe_id ]->posicao - $posicao;
				}
				if ( !$clas_equipe2->diff_posicao_anterior )
				{
					$pos_ant				=	0;
					$clas_equipe2->diff_posicao_anterior	=	0;
				}
	
				/*
				 *	Conclui cálculo da situação da equipe.
				 */
				// BRASILEIRÃO
				if ( $campeonato_id == 1 )
				{
					if ( $clas_equipe2->situacao == 'N' )
					{
						if ( $qtde_libertadores > 0 )
						{
							$qtde_libertadores		=	$qtde_libertadores - 1;
							$clas_equipe2->situacao		=	'L'; 		// Libertadores
						}
						elseif ( $qtde_prelibertadores > 0 )
						{
							$qtde_prelibertadores		=	$qtde_prelibertadores - 1;
							$clas_equipe2->situacao		=	'P'; 		// Pré-libertadores
						}
						elseif ( $qtde_sulamericana > 0 )
						{
							$qtde_sulamericana		=	$qtde_sulamericana - 1;
							$clas_equipe2->situacao		=	'S'; 		// Sulamericana
						}
						elseif ( $key_pos >= 17 )
						{
							$clas_equipe2->situacao		=	'R'; 		// Rebaixado
						}
					}
				}
				elseif ( $campeonato_id == 8 ) // Brasileiro Série B
				{
					if ( $clas_equipe2->situacao == 'N' )
					{
						if ( in_array( $clas_equipe2->posicao, array( 1, 2, 3, 4 ) ) )
						{
							$clas_equipe2->situacao		=	'C'; 		// Classificado
						}
						if ( in_array( $clas_equipe2->posicao, array( 17, 18, 19, 20 ) ) )
						{
							$clas_equipe2->situacao		=	'E'; 		// Desclassificado
						}
					}
				}
				elseif ( $campeonato_id == 6 ) // Carioca.
				{
					if ( $clas_equipe2->situacao == 'N' )
					{
						if ( in_array( $clas_equipe2->posicao, array( 1, 2, 3, 4 ) ) )
						{
							$clas_equipe2->situacao		=	'C'; 		// Classificado
						}
					}
				}
				elseif ( $campeonato_id == 7 ) // Paulista.
				{
					if ( $clas_equipe2->situacao == 'N' )
					{
						if ( in_array( $clas_equipe2->posicao, array( 1, 2, 3, 4, 5, 6, 7, 8 ) ) )
						{
							$clas_equipe2->situacao		=	'C'; 		// Classificado
						}
						if ( in_array( $clas_equipe2->posicao, array( 17, 18, 19, 20 ) ) )
						{
							$clas_equipe2->situacao		=	'R'; 		// Desclassificado
						}
					}
				}
				elseif ( $clas_equipe2->campeonato_versao_id == 9 ) // Eliminatórias 2014 - Sulamericana
				{
					if ( $clas_equipe2->situacao == 'N' )
					{
						if ( in_array( $clas_equipe2->posicao, array( 1, 2, 3, 4 ) ) )
						{
							$clas_equipe2->situacao		=	'C'; 		// Classificado
						}
						elseif ( $clas_equipe2->posicao == 5 )
						{
							$clas_equipe2->situacao		=	'M'; 		// Respescagem
						}
						else
						{
							$clas_equipe2->situacao		=	'E'; 		// Desclassificado
						}
					}
				}
				elseif ( $clas_equipe2->campeonato_versao_id == 10 // Eliminatórias 2014 - Europa
				&&       $clas_equipe2->tipo_rodada_fase == 'G'
				&&       $clas_equipe2->grupo_id
				       )
				{
					if ( $clas_equipe2->situacao == 'N' )
					{
						if ( $clas_equipe2->posicao == 1 )
						{
							$clas_equipe2->situacao		=	'C'; 		// Classificado
						}
						else
						{
							$clas_equipe2->situacao		=	'E'; 		// Desclassificado
						}
					}
				}
				// FASE de GRUPOS
				elseif ( $clas_equipe2->tipo_rodada_fase == 'G'
				&&       $clas_equipe2->grupo_id
				       )
				{
					if ( $clas_equipe2->situacao == 'N' )
					{
						if ( in_array( $clas_equipe2->posicao, array( 1, 2 ) ) )
						{
							$clas_equipe2->situacao		=	'C'; 		// Classificado
						}
						else
						{
							$clas_equipe2->situacao		=	'E'; 		// Desclassificado
						}
					}
				}
				// Mata Mata ou Ida e Volta
				elseif ( ( $clas_equipe2->tipo_rodada_fase == 'M'
				||         $clas_equipe2->tipo_rodada_fase == 'I'
				         )
				&&       $clas_equipe2->grupo_id
				       )
				{
					if ( $clas_equipe2->situacao == 'N' )
					{
						if ( $clas_equipe2->posicao == 1 )
						{
							$clas_equipe2->situacao		=	'C'; 		// Classificado
						}
						else
						{
							$clas_equipe2->situacao		=	'E'; 		// Desclassificado
						}
					}
				}

if ( $show_log ) echo 'Gerado '.
' cmpVer='.$clas_equipe2->campeonato_versao_id.
' key='.$key_pos.
' grp='.$clas_equipe2->grupo_id.
' tRod='.$clas_equipe2->tipo_rodada_fase.
' pos='.$clas_equipe2->posicao.
' posAnt='.$pos_ant.
' diff='.$clas_equipe2->diff_posicao_anterior.
' id='.$clas_equipe2->id.
' rod='.$clas_equipe2->rodada_fase_id.
' eqp='.$clas_equipe2->equipe_id.
' pt='.$clas_equipe2->total_ponto.
' jg='.$clas_equipe2->total_jogo.
' v='.$clas_equipe2->total_vitoria.
' e='.$clas_equipe2->total_empate.
' d='.$clas_equipe2->total_derrota.
' gf='.$clas_equipe2->gol_favor.
' gc='.$clas_equipe2->gol_contra.
' gL='.$clas_equipe2->gol_casa.
' gF='.$clas_equipe2->gol_fora_casa.
' sg='.( $clas_equipe2->gol_favor - $clas_equipe2->gol_contra ).
' apr='.$clas_equipe2->aproveitamento.
' sit='.$clas_equipe2->situacao.
"<br/>\n";
				// Recria o array de classificação acrescentando o grupo a chave.
				$clas_atual_redo[ $grupo_id ][ $key_pos ]		=	$clas_equipe2;
				
				if ( $clas_equipe2->grupo_id == 0 ) // Colocamos zero no grupo_id para controle de quebra, mas aqui retiramos o zero para evitar erro de base de dados.
				{
					$tmp_grupo_id			=	$clas_equipe2->grupo_id;
					$clas_equipe2->grupo_id		=	NULL;

					if ( $personalizado || $rodada_fase_id_fim )
					{
						$ret_class[]		=	$clas_equipe2;
					}
					else
					{
						$this->update( $clas_equipe2 );
					}

					$clas_equipe2->grupo_id		=	$tmp_grupo_id;
				}
				else
				{
					if ( $personalizado || $rodada_fase_id_fim )
					{
						$ret_class[]		=	$clas_equipe2;
					}
					else
					{
						$this->update( $clas_equipe2 );
					}
				}
			}
		}

		if ( $calcular_ranking
		&&   ( !$personalizado
		||     !$rodada_fase_id_fim
		     )
		   )
		{
if ( $show_log ) echo "<br/>\n";
if ( $show_log ) echo "vai calcular ranking<br/>\n";
if ( $show_log ) echo "<br/>\n";
			$this->kick->calcular_kicks( $rodada_fase_id );
		}

		$this->db->trans_commit();
		
		if ( $ret_class )
		{
			return ( $personalizado || $rodada_fase_id_fim ) ? $ret_class : TRUE;
		}
		else
		{
			return TRUE;
		}
	}
}
/* End of file campeonato_versao_classificacao_model.php */