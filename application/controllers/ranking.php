<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Ranking de jogadores.
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/ranking.php
 * 
 * $Id: ranking.php,v 1.19 2013-03-02 14:37:48 junior Exp $
 * 
 */

class Ranking extends JX_Page
{
	protected $_revision	=	'$Id: ranking.php,v 1.19 2013-03-02 14:37:48 junior Exp $';

	var $rodada_fase_proxima;
	
	function __construct()
	{
		$_config		=	array	(
							 'kick'					=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	TRUE
														)
							,'pessoa_rodada_fase'			=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	FALSE
														)
							,'pessoa_rodada_fase_power'		=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	FALSE
														)
							,'pessoa_rodada_fase_resumo_power'	=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	FALSE
														)
							,'pessoa_campeonato_versao_resumo_power'=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	FALSE
														)
							,'pessoa_ranking_grupo_amigos_resumo_power'	=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	FALSE
														)
							,'pessoa_campeonato_versao'		=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'pessoa_ranking_grupo_amigos'		=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'campeonato_versao'			=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'rodada_fase'				=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'equipe'				=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'imagem'				=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'grupo_amigos'				=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'grupo_amigos_fase'			=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'grupo_amigos_pessoa'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	FALSE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							);
		parent::__construct( $_config );
		if ( $this->first_time )
		{
			$this->jx_order_direction	=	'-';
			$data					=	array	(
										 'jx_order_direction'	=> $this->jx_order_direction
										);
			$this->load->vars( $data );
		}

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	/**
	 * Página princial do site.
	 */
	public function index()
	{
		$this->rodada();
	}
	
	/**
	 * Ranking.
	 */
	protected function _prep_show_ranking( $rodada_fase_id, $campeonato_versao_id, $tipo_visual, $tipo_calculo, $grupo_id, $grupo_fase_id )
	{
		/*
		 * Determinamos o tipo de visualização que será utilizado. Isso determina a tabela a ser lida.
		 * 
		 * 	- Amigos
		 * 	- Kikbook (Geral)
		 * 	- Grupo
		 * 
		 */
		if ( !$tipo_visual ) // não enviado pela página, buscamos na sessão.
		{
			$tipo_visual		=	$this->singlepack->get_sessao( 'tipo_visual' );
		}

		if ( !$tipo_visual
		||   $tipo_visual == 'amigos'
		   )
		{
			$tipo_visual		=	'amigos';
		}

		if ( $tipo_visual == 'grupos' )
		{
			if ( !$grupo_id )
			{
				$grupo_id	=	$this->singlepack->get_sessao( 'grupo_id' );
			}
	
			if ( !$grupo_fase_id )
			{
				$grupo_fase_id	=	$this->singlepack->get_sessao( 'grupo_fase_id' );
			}
			if ( $grupo_fase_id == -1 )
			{
				$grupo_fase_id	=	NULL;
			}
		}
		else
		{
			$grupo_id		=	NULL;
			$grupo_fase_id		=	NULL;
		}
		
		/*
		 * Determinamos o tipo de cálculo que será utilizado. Isso determina a tabela a ser lida.
		 * 
		 * 	- Melhor Rodada
		 * 		pessoa_rodada_fase com ID vindo de pessoa_campeonato_versao
		 * 	- Rodada
		 * 		pessoa_rodada_fase
		 * 	- Campeonato
		 * 		pessoa_campeonato_versao
		 * 
		 */
		if ( !$tipo_calculo ) // não enviado pela página, buscamos na sessão.
		{
			$tipo_calculo		=	$this->singlepack->get_sessao( 'tipo_calculo' );
		}

		if ( !$tipo_calculo ) // não tendo valor escolhido ainda, assumimos os valores abaixo.
		{
			$tipo_calculo		=	 'campeonato';
		}

		$model_ranking			=	'pessoa_rodada_fase';
		if ( $tipo_calculo == 'melhor-rodada' )
		{
			$tipo_calculo		=	'melhor-rodada';
			$model_ranking		=	'pessoa_rodada_fase';
		}
		elseif  ( $tipo_calculo == 'campeonato' )
		{
			if ( $tipo_visual == 'grupos' )
			{
				$model_ranking	=	'pessoa_ranking_grupo_amigos';
			}
			else
			{
				$model_ranking	=	'pessoa_campeonato_versao';
			}
		}
		elseif  ( $tipo_calculo == 'rodada' )
		{
			$model_ranking		=	'pessoa_rodada_fase';
		}
		
		// Pega os IDs dos amigos da pessoa atual.
		$ar_facebook_ids		=	$this->singlepack->get_friends_id();

		// Prepara o array para ser usado no IN do where da tabela user.
		$in				=	NULL;
		$where				=	NULL;
		$grupo_nome			=	NULL;
		$grupo_fase_nome		=	NULL;
		if ( ( $tipo_visual == 'amigos'
		||     $tipo_visual == 'grupos'
		     )
		// Não estando conectado, buscamos aleatoriamente um ranking.
		&&   $this->singlepack->user_connected()
		   )
		{
			if ( is_array( $ar_facebook_ids )
			&&   array_count_values( $ar_facebook_ids ) > 0
			   )
			{
				// Acrescenta o ID da pessoa atual.
				$ar_facebook_ids[]		=	$this->singlepack->get_facebook_id( $installed = FALSE );
				foreach( $ar_facebook_ids as $id )
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
				if ( $in )
				{
					$where			=	"usr.id_facebook in ( {$in} )";
				}
			}

			// Acrescenta o grupo selecionado ao where.
			if ( $tipo_visual == 'grupos' )
			{
				// Caso não foi selecionado o grupo, escolhemos o primeiro da lista.
				if ( !$grupo_id )
				{
					$grupo_query		=	$this->grupo_amigos->get_one_by_where( "
														grupo_amigos.id in	(
																	select	pesgrp.grupo_amigos_id
																	from	grupo_amigos_pessoa pesgrp
																	where	pesgrp.pessoa_id = {$this->singlepack->user_info->pessoa_id}
																	)
														" );
				}
				else
				{
					$grupo_query		=	$this->grupo_amigos->get_one_by_where( "grupo_amigos.id = {$grupo_id}" );
				}
				$grupo_id			=	$grupo_query->id;
				$grupo_nome			=	$grupo_query->nome;
				
				if ( $where )
				{
					$where			=	$where . " and usr.pessoa_id in	(
													select	pesgrp.pessoa_id
													from	grupo_amigos_pessoa pesgrp
													where	pesgrp.grupo_amigos_id = {$grupo_id}
													)
										";
				}
				else
				{
					$where			=	"usr.pessoa_id in	(
												select	pesgrp.pessoa_id
												from	grupo_amigos_pessoa pesgrp
												where	pesgrp.grupo_amigos_id = {$grupo_id}
												)
									";
				}
				// Caso não tenha sido selecionada a fase do grupo, escolhemos a primeira da lista.
				if ( !$grupo_fase_id )
				{
					$grupo_fase_query	=	$this->grupo_amigos_fase->get_one_by_where( "grupo_amigos_fase.grupo_amigos_id = {$grupo_id}" );
				}
				else
				{
					$grupo_fase_query	=	$this->grupo_amigos_fase->get_one_by_where( "grupo_amigos_fase.id = {$grupo_fase_id}" );
				}
				$grupo_fase_id			=	$grupo_fase_query->id;
				$grupo_fase_nome		=	$grupo_fase_query->nome;
			}
		}

		// Monta linhas para seleção de Grupos.
		if ( $this->singlepack->user_connected() )
		{
			$this->grupo_amigos_pessoa->insert_new(); // Coloca a pessoa automaticamente em grupos já cadastrados no Kikbook e que ela faça parte no Facebook.
			
			$this->grupo_amigos->select_all	(
							 "grupo_amigos.id in	(
										select	pesgrp.grupo_amigos_id
										from	grupo_amigos_pessoa pesgrp
										where	pesgrp.pessoa_id = {$this->singlepack->user_info->pessoa_id}
										)"
							);
			$grupo_rows		= $this->grupo_amigos->get_query_rows();
			
			if ( $tipo_visual == 'grupos'
			&&   $grupo_id
			   )
			{
				$grupo_fase_query	= $this->grupo_amigos_fase->select_all( "grupo_amigos_fase.grupo_amigos_id = {$grupo_id}" );
				$grupo_fase_rows	= $this->grupo_amigos_fase->get_query_rows();
			}
			else
			{
				$grupo_fase_rows	= FALSE;
			}
		}
		else
		{
			$grupo_rows			= FALSE;
			$grupo_fase_rows		= FALSE;
		}

		// Somente pessoas ativas.
		if ( $where )
		{
			$where			= $where." and ( usr.ativo = 'S' )";
		}
		else
		{
			$where			= "( usr.ativo = 'S' )";
		}
	
		if ( ( $tipo_calculo != 'melhor-rodada'
		&&     $rodada_fase_id
		&&     $model_ranking == 'pessoa_rodada_fase'
		     )
		||   ( $tipo_calculo == 'melhor-rodada'
		&&     $model_ranking == 'pessoa_rodada_fase'
		     )
		   )
		{
			if ( $where )
			{
				if ( $tipo_calculo == 'melhor-rodada' )
				{
					if ( $tipo_visual == 'grupos'
					&&   $grupo_fase_id
					   )
					{
						$where	= $where." and ( {$model_ranking}.id in	(
												select	pesgrp.pessoa_rodada_fase_id
												from	pessoa_ranking_grupo_amigos pesgrp
												where	pesgrp.grupo_amigos_fase_id = {$grupo_fase_id}
												)
									)";
					}
					else
					{
						$where	= $where." and ( {$model_ranking}.id in	(
												select	pescam.pessoa_rodada_fase_id
												from	pessoa_campeonato_versao pescam
												where	pescam.campeonato_versao_id = {$campeonato_versao_id}
												)
									)";
					}
				}
				else
				{
					$where		= $where." and ( {$model_ranking}.rodada_fase_id = {$rodada_fase_id} )";
				}
			}
			else
			{
				if ( $tipo_calculo == 'melhor-rodada' )
				{
					if ( $tipo_visual == 'grupos' 
					&&   $grupo_fase_id
					   )
					{
						$where	= 	"
								( {$model_ranking}.id in	(
												select	pesgrp.pessoa_rodada_fase_id
												from	pessoa_ranking_grupo_amigos pesgrp
												where	pesgrp.grupo_amigos_fase_id = {$grupo_fase_id}
												)
								)
								";
					}
					else
					{
						$where	=	"
								( {$model_ranking}.id in	(
												select	pescam.pessoa_rodada_fase_id
												from	pessoa_campeonato_versao pescam
												where	pescam.campeonato_versao_id = {$campeonato_versao_id}
												)
								)
								";
					}
				}
				else
				{
					$where		=	"( {$model_ranking}.rodada_fase_id = {$rodada_fase_id} )";
				}
			}
		}
		elseif ( $campeonato_versao_id
		&&       $model_ranking == 'pessoa_campeonato_versao'
		       )
		{
			if ( $where )
			{
				$where		= $where." and ( {$model_ranking}.campeonato_versao_id = {$campeonato_versao_id} )";
			}
			else
			{
				$where		= "( {$model_ranking}.campeonato_versao_id = {$campeonato_versao_id} )";
			}
		}
		elseif ( $grupo_fase_id
		&&       $model_ranking == 'pessoa_ranking_grupo_amigos'
		       )
		{
			if ( $where )
			{
				$where		= $where." and ( {$model_ranking}.grupo_amigos_fase_id = {$grupo_fase_id} )";
			}
			else
			{
				$where		= "( {$model_ranking}.grupo_amigos_fase_id = {$grupo_fase_id} )";
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

		/*
		 * Registra na sessão os valores usados.
		 */
		$this->singlepack->set_sessao( 'tipo_calculo', $tipo_calculo );
		$this->singlepack->set_sessao( 'tipo_visual', $tipo_visual );
		$this->singlepack->set_sessao( 'grupo_id', $grupo_id );
		$this->singlepack->set_sessao( 'grupo_fase_id', $grupo_fase_id ); 

		$this->_prep_index( $master_model_name = $model_ranking, $where_external = $where, $orderby_external = FALSE, $set_parent = FALSE );

		$rows						=	$this->load->get_var( 'rows' ); // pega as linhas carregadas no comando anterior.
		$ret_rows					=	array();
		$count_rows					=	0;
		$pessoa_na_lista				=	FALSE;
		$ranking_pessoa					=	NULL;
		if ( !$this->singlepack->user_connected() )
		{
			foreach ( $rows as $row ) // Apenas registra as linhas encontradas.
			{
				$ret_rows[]			=	$row;
			}
		}
		else
		{
			foreach ( $rows as $row ) // Procura a pessoa na lista.
			{
				// Define se a pessoa está ou não na lista que será exibida.
				if ( $row->pessoa_id == $this->singlepack->get_pessoa_id() )
				{
					$pessoa_na_lista	=	TRUE;
					$ranking_pessoa		=	$row;
				}
				$ret_rows[]			=	$row;
			}
		}

		// Insere no final a linha da pessoa no ranking.
		if ( !$pessoa_na_lista
		&&   !is_null( $this->singlepack->get_pessoa_id() )
		   )
		{
			$ranking_pessoa				=	$this->$model_ranking->get_one_by_where( "usr.pessoa_id = {$this->singlepack->get_pessoa_id()} and " . $where );
			if ( $ranking_pessoa )
			{
				$ret_rows[]			=	$ranking_pessoa;
			}
			else
			{
				$ranking_pessoa				=	new stdClass();
				$ranking_pessoa->pontos_kick		=	0;
				$ranking_pessoa->pontos_gols		=	0;
				$ranking_pessoa->pontos_power		=	0;
			}
		}
		
		if ( !$ranking_pessoa )
		{
			$ranking_pessoa				=	new stdClass();
			$ranking_pessoa->pontos_kick		=	0;
			$ranking_pessoa->pontos_gols		=	0;
			$ranking_pessoa->pontos_power		=	0;
		}
	
		// Carrega os poderes da rodada da pessoa, se existirem.
		$ret_rows_powers				=	array();
		foreach ( $ret_rows as $row )
		{
			// Monta os dados dos poderes na linha dos ranking das pessoas.
			$new_row				=	$row;
			$new_row->powers			=	array();

			if ( $model_ranking == 'pessoa_rodada_fase' )
			{
				$rows_power			=	$this->pessoa_rodada_fase_resumo_power->get_all_by_where( 'pessoa_rodada_fase_id = '.$row->id );
			}
			elseif ( $model_ranking == 'pessoa_ranking_grupo_amigos' )
			{
				$rows_power			=	$this->pessoa_ranking_grupo_amigos_resumo_power->get_all_by_where( 'pessoa_ranking_grupo_amigos_id = '.$row->id );
			}
			else // $model_ranking == 'pessoa_campeonato_versao'
			{
				$rows_power			=	$this->pessoa_campeonato_versao_resumo_power->get_all_by_where( 'pessoa_campeonato_versao_id = '.$row->id );
			}
			foreach( $rows_power as $row_power )
			{
				if ( $row_power->qtde > 0 )
				{
					$power			=	new stdClass();
					$power->nome		=	$row_power->nome_power;
					$power->descr		=	$row_power->descr_power;
					$power->cod		=	$row_power->cod_power;
					$power->css_class	=	$row_power->css_class;
					$power->power_id	=	$row_power->power_id;
					$power->pontos		=	$row_power->pontos;
					$power->qtde		=	$row_power->qtde;
					$new_row->powers[]	=	$power;
					unset( $power );
				}
			}

			$ret_rows_powers[]			=	$new_row;
			unset( $new_row );
			$count_rows				=	$count_rows + 1;

			if ( !$this->singlepack->user_connected()
			&&   $count_rows >= 6
			   )
			{
				break;
			}
		}
		unset( $ret_rows );

		// Variáveis para a página.
		$data						=	array	(
										 'rows'				=> $ret_rows_powers // Sobrepoem o enviou anterior das rows.
										,'rows_campeonato'		=> $this->campeonato_versao->get_campeonato_selecao()
										,'rodada_atual'			=> $this->rodada_fase->get_one_by_id( $rodada_fase_id )
										,'rows_rodada'			=> $this->rodada_fase->get_rodadas_selecao( $rodada_fase_id, 'RANK', $campeonato_versao_id, $grupo_fase_id )
										,'rodada_anterior'		=> $this->rodada_fase->get_rodada_selecao_anterior()
										,'rodada_posterior'		=> $this->rodada_fase->get_rodada_selecao_posterior()
										,'tipo_visual'			=> $tipo_visual
										,'tipo_calculo'			=> $tipo_calculo
										,'grupo_id'			=> $grupo_id
										,'grupos'			=> $grupo_rows
										,'grupo_selecionado'		=> $grupo_nome
										,'grupo_fases'			=> $grupo_fase_rows
										,'grupo_fase_selecionada'	=> $grupo_fase_nome
										,'start_position'		=> ( $this->jx_pagination->get_cur_page() - 1 ) * $this->jx_pagination->get_lines_per_page()
										,'campeonato_versao_atual'	=> $this->campeonato_versao->get_one_by_id( $campeonato_versao_id )
										,'ranking_pessoa'		=> $ranking_pessoa
										,'kiker_info'			=> $this->kick->kiker_info()
										);
		$this->load->vars( $data );
	}
	
	public function campeonato( $campeonato_versao_id = NUll, $tipo_calculo = NULL )
	{
		// Confirma se a entrada é numérica.
		if ( !is_numeric( $campeonato_versao_id ) )
		{
			$campeonato_versao_id			=	NULL;
		}
		$this->rodada_fase->set_id_sessao( NULL );
		
		if ( $tipo_calculo )
		{
			$this->rodada( NULL, $campeonato_versao_id, NULL, $tipo_calculo );
		}
		else
		{
			$this->rodada( NULL, $campeonato_versao_id );
		}
	}

	public function tipo_visual( $tipo_visual = NUll )
	{
		$this->rodada( NULL, NULL, $tipo_visual, NULL );
	}

	public function tipo_calculo( $tipo_calculo = NUll )
	{
		$this->rodada( NULL, NULL, NULL, $tipo_calculo );
	}
	
	public function grupo( $grupo_id = NULL, $grupo_fase_id = NULL )
	{
		if ( !is_numeric( $grupo_id ) )
		{
			$grupo_id			=	NULL;
		}
		if ( !is_numeric( $grupo_fase_id )
		||   !$grupo_fase_id
		   )
		{
			$grupo_fase_id			=	-1;
			$tipo_visual			=	NULL;
		}
		else
		{
			$tipo_visual			=	'campeonato';
		}
		
		$this->rodada( NULL, NULL, 'grupos', $tipo_visual, $grupo_id = $grupo_id, $grupo_fase_id = $grupo_fase_id );
	}
	
	public function rodada( $rodada_fase_id = NULL, $campeonato_versao_id = NULL, $tipo_visual = NULL, $tipo_calculo = NULL, $grupo_id = NULL, $grupo_fase_id = NULL )
	{
		// Confirma se a entrada é numérica.
		if ( !is_numeric( $grupo_id ) )
		{
			$grupo_id				=	NULL;
		}

		$campeonato_versao_id				=	$this->campeonato_versao->get_id_selecionado( $campeonato_versao_id, $rodada_fase_id );
		$rodada_fase_id					=	$this->rodada_fase->get_id_selecionado( $rodada_fase_id, $campeonato_versao_id );
		$campeonato_versao_id				=	$this->rodada_fase->get_id_campeonato( $campeonato_versao_id );
		
		$this->_prep_show_ranking( $rodada_fase_id, $campeonato_versao_id, $tipo_visual, $tipo_calculo, $grupo_id, $grupo_fase_id );

		$this->load->view( 'ranking.html' );
	}
}
/* End of file ranking.php */
/* Location: /application/controllers/ranking.php */
