<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kik Book
 *
 *     Controller principal do sistema de Cadastro.
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Kik Book.
 * @license		http://kikbook.com/license.html
 * @link		http://kikbook.com
 * @since		Version 0.0.1
 * @filesource		/application/controllers/integracao.php
 * 
 * $Id: integracao.php,v 1.9 2013-03-27 01:22:48 junior Exp $
 * 
 */

class Integracao extends JX_Process
{
	protected $_revision		=	'$Id: integracao.php,v 1.9 2013-03-27 01:22:48 junior Exp $';

	var $level			=	0;
	var $ativado_pelo_campeonato	=	FALSE;
	
	var $fase			=	NULL;
	var $fase_nome			=	NULL;
	var $fase_id_externo		=	NULL;
	var $grupo_data			=	NULL;
	var $campeonato_versao_id	=	NULL;
	var $rodada_fase_id		=	NULL;
	var $rodada_fase_selecionada	=	NULL;
	
	function __construct()
	{
		$_config		=	array	(
							 'equipe'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'equipe_imagem'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'imagem'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'campeonato_versao'			=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'campeonato_versao_equipe'		=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'arena'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'rodada_fase'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'grupo'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'grupo_equipe'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'grupo'
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'jogo'					=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'arena,grupo,rodada_fase'
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														)
							,'campeonato_versao_classificacao'	=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'arena,grupo,rodada_fase'
														,'show'			=>	TRUE
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
		
		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	public function define_id_externo_rodada( $rodada, $tipo_rodada )
	{
		if ( !is_null( $rodada ) )  // Existindo a rodada na origem dos dados, mantemos o número dela como ID externo.
		{
			// Verificamos se existe uma rodada com ID Externo no formato anterior (fase e rodada bem juntos).
			$rodada_fase_id_externo				=	$this->fase_id_externo . $rodada;
			$rodada_fase_base				=	$this->rodada_fase->get_one_by_where( 	"   rodada_fase.campeonato_versao_id	= {$this->campeonato_versao_id}
															and rodada_fase.id_externo 		= '{$rodada_fase_id_externo}'
															" );
			// Mudamos o padrão do ID externo.
			$rodada_fase_id_externo				=	'fase_' . $this->fase_id_externo . '_rod_'. $rodada;

			// Existindo com o formato anterior, alteramos para ficar com a nova regra.
			if ( is_object( $rodada_fase_base ) )
			{
				$rodada_fase_base->id_externo		=	$rodada_fase_id_externo;
				$this->rodada_fase->update( $rodada_fase_base );
			}
			else
			{
				// Primeiro formato utilizado era só a RODADA. Testamos ainda se existe alguma com este forma e corrigimos.
				$rodada_fase_base			=	$this->rodada_fase->get_one_by_where( 	"   rodada_fase.campeonato_versao_id	= {$this->campeonato_versao_id}
															and rodada_fase.id_externo 		= '{$rodada}'
															" );
				if ( is_object( $rodada_fase_base ) )
				{
					$rodada_fase_base->id_externo		=	$rodada_fase_id_externo;
					$this->rodada_fase->update( $rodada_fase_base );
				}
			}
		}
		else
		{
			if ( is_array( $this->fase ) )
			{
				// Se foi informado o TIPO DE JOGO e este for diferente de "U"nico, então usamos ele para montar o ID externo da rodada.
				// Fazendo isso dividimos em 2 rodadas as fases de mata-mata de ida e volta de alguns campeonatos.
				if ( $tipo_rodada != 'U' )
				{
					$rodada_fase_id_externo	=	strtolower( $this->fase_nome ). "_". $tipo_rodada . "_". $this->fase_id_externo;
				}
				else
				{
					$rodada_fase_id_externo	=	strtolower( $this->fase_nome ). "_". $this->fase_id_externo;
				}
			}
			else
			{
				$rodada_fase_id_externo		=	"sem id_externo";
			}
		}
		
		return $rodada_fase_id_externo;
	}
	
	/**
	 * Mapa do json usado
	 * 
	 * 	Equipes
	 * 		[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'equipes' ]
	 * 
	 * 	Arenas
	 * 		[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'sedes' ]
	 * 
	 * 	Rodadas
	 * 		[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'chaves' ]
	 * 
	 * 	Grupos
	 * 		[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'grupos' ]
	 * 			[ seq_num ]	id
	 * 					nome
	 * 					rodada_atual
	 * 					[ 'jogos' ]
	 * 
	 * 	Jogos
	 * 		<<<< LIGA DOS CAMPEÕES >>>>
	 * 		FASE_GRUPOS 
	 * 			Como identificar --- é uma fase de grupos se o campo "grupos" for um array();
	 * 					---- "jogos" também contém todos os jogos, mas não compensa, pois perdemos a referencia com o grupo.
	 * 			[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'grupos' ][ seq_num ][ 'jogos' ][ seq_num ]
	 * 						rodada_fase_id	=	...[ seq_num ][ 'rodada' ]
	 * 						grupo_id	=	veja explicação acima.
	 * 
	 * 		OITAVAS / QUARTAS / SEMIFINAIS / FINAL
	 * 			Como identificar --- se "grupos" e "jogos" estiverem vazios, então é uma fase única (brasileirão, por exemplo).
	 * 			[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'chaves' ][ seq_num ][ 'jogos' ]
	 * 
	 * 						rodada_fase_id	=	Cada fase desta será uma rodada a parte.
	 * 									Pegar o "id" da fase como id_externo
	 * 
	 * 		Ida e Vola
	 * 			Como identificar --- [ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'ida_volta' ] == TRUE
	 * 			[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'chaves' ][ seq_num ]
	 * 							Cada sequencia é um jogo
	 * 
	 * 		PLAYOFFS
	 * 
	 * 
	 * 		<<<< BRASILEIRÃO >>>>
	 * 		FASE_UNICA
	 * 			Como identificar --- se "grupos" e "chaves" estiverem vazios, então é uma fase única (brasileirão, por exemplo).
	 * 			[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ 0 ][ 'jogos' ] <<< sempre com zero
	 * 
	 */
	protected function atualizar_jogo( $jogo, $tipo_jogo = 'U', $rodada_tipo_fase = NULL, $rodada_tipo = NULL )
	{
		if ( is_object( $this->rodada_fase_selecionada ) // Informamos uma rodada específica.
		&&   key_exists( 'rodada', $jogo )
		   )
		{
			$rodada_id_externo				=	$this->define_id_externo_rodada( $jogo[ 'rodada' ], $rodada_tipo );
		}
		else
		{
			$rodada_id_externo				=	NULL;
		}

		if ( ( is_object( $this->rodada_fase_selecionada ) // Informamos uma rodada específica.
		&&     $rodada_id_externo == $this->rodada_fase_selecionada->id_externo
		     )
		||   !is_object( $this->rodada_fase_selecionada ) // Sem rodada específica.
		   )
		{
			//$jogo_value[ 'data_original' ] "18-9-2012"
//$jogo_value[ 'hora' ] "15h45"
			$jogo_data					=	new stdClass();
			
			if ( key_exists( 'jogo_id', $jogo ) )
			{
				$jogo_data->id_externo			=	$jogo[ 'jogo_id' ];
			}
			else
			{
				$jogo_data->id_externo			=	"sem id_externo";
			}
			
			//	GRUPO			$this->grupo_data->id
			if ( is_object( $this->grupo_data )
			&&   $rodada_tipo_fase != 'M' // O Jogo não pertence a nenhum grupo quando a rodada é do tipo "Fase de Grupo Mista". Os times jogam entre grupos, apenas a classificação é por grupo.
			   )
			{
				$jogo_data->grupo_id			=	$this->grupo_data->id;
			}
			else
			{
				$jogo_data->grupo_id			=	NULL;
			}
	
			//	COD
			$jogo_data->cod					=	$jogo_data->id_externo;
	
			//	DATA_HORA
			if ( key_exists( 'data_original', $jogo ) )
			{
				$jogo_data->data_hora			=	$jogo[ 'data_original' ];
			}
			else
			{
				$jogo_data->data_hora			=	$jogo[ 'data' ];
				$jogo_data->data_hora			=	substr( strrchr( $jogo_data->data_hora, ' ' ), 1 );
			}
	
			$jogo_data->data_hora				=	str_replace( '-', '/',$jogo_data->data_hora );
			$ar_data					=	explode( '/', $jogo_data->data_hora );
			$ar_hora					=	explode( ':', str_replace( 'h', ':', $jogo[ 'hora' ] ) );
			if ( strlen( $ar_data[0] ) < 2 )
			{
				 $ar_data[0]				=	"0". $ar_data[0];
			}
			if ( strlen( $ar_data[1] ) < 2 )
			{
				 $ar_data[1]				=	"0". $ar_data[1];
			}
			if ( strlen( $ar_data[2] ) < 4 )
			{
				 $ar_data[2]				=	"20". $ar_data[2];
			}
			
			if ( is_array( $ar_hora )
			&&   key_exists( 0, $ar_hora )
			&&   key_exists( 1, $ar_hora )
			   )
			{
				if ( strlen( $ar_hora[0] ) < 2 )
				{
					 $ar_hora[0]			=	"0". $ar_hora[0];
				}
				if ( strlen( $ar_hora[1] ) < 2 )
				{
					 $ar_hora[1]			=	"0". $ar_hora[1];
				}
			}
			else
			{
				$ar_hora[0]				=	"00";
				$ar_hora[1]				=	"00";
			}
			$jogo_data->data_hora				=	$ar_data[0] ."/". $ar_data[1] ."/". $ar_data[2] ." ". $ar_hora[0] .":". $ar_hora[1];
	
			//	MANDANTE
			if ( key_exists( 'equipe_mandante', $jogo ) )
			{
				$equipe_casa_id_externo			=	$jogo[ 'equipe_mandante' ];
			}
			else
			{
				$equipe_casa_id_externo			=	$jogo[ 'equipe_mandante_id' ];
			}
			if ( $equipe_casa_id_externo )
			{
				$equipe_casa_base			=	$this->equipe->get_one_by_where( "equipe.id_externo = '{$equipe_casa_id_externo}'" );
			}
			else
			{
				$equipe_casa_base			=	NULL;
			}
	
			$jogo_data->titulo_casa		=	NULL;
			if ( is_object( $equipe_casa_base ) )
			{
				$jogo_data->equipe_id_casa		=	$equipe_casa_base->id;
			}
			else
			{
				$jogo_data->equipe_id_casa		=	NULL;
				
				if ( key_exists( 'equipe_mandante_titulo', $jogo ) )
				{
					$jogo_data->titulo_casa		=	$jogo[ 'equipe_mandante_titulo' ];
				}
			}
			
			//	VISITANTE
			if ( key_exists( 'equipe_visitante', $jogo ) )
			{
				$equipe_visitante_id_externo		=	$jogo[ 'equipe_visitante' ];
			}
			else
			{
				$equipe_visitante_id_externo		=	$jogo[ 'equipe_visitante_id' ];
			}
			if ( $equipe_visitante_id_externo )
			{
				$equipe_visitante_base			=	$this->equipe->get_one_by_where( "equipe.id_externo = '{$equipe_visitante_id_externo}'" );
			}
			else
			{
				$equipe_visitante_base			=	NULL;
			}
			
			$jogo_data->titulo_visitante			=	NULL;
			if ( is_object( $equipe_visitante_base ) )
			{
				$jogo_data->equipe_id_visitante		=	$equipe_visitante_base->id;
			}
			else
			{
				$jogo_data->equipe_id_visitante		=	NULL;
				
				if ( key_exists( 'equipe_visitante_titulo', $jogo ) )
				{
					$jogo_data->titulo_visitante	=	$jogo[ 'equipe_visitante_titulo' ];
				}
			}
			
			//	AINDA NÃO LOCALIZADOS
			$jogo_data->resultado_casa_prorrogacao		=	(int) NULL;
			$jogo_data->resultado_visitante_prorrogacao	=	(int) NULL;
	
			// TODO: Rever o porque o 0 (zero) fica nulo na base de dados.
			//	RESULTADOS CASA
			$jogo_data->resultado_casa			=	( is_null( $jogo[ 'placar_mandante' ] ) ) ? NULL : ( $jogo[ 'placar_mandante' ] == 0 ) ? 99999 : $jogo[ 'placar_mandante' ];
			$jogo_data->penaltis_casa			=	( is_null( $jogo[ 'placar_penalti_mandante' ] ) ) ? NULL : ( $jogo[ 'placar_penalti_mandante' ] == 0 ) ? 99999 : $jogo[ 'placar_penalti_mandante' ];
	
			//	RESULTADOS VISITANTE
			$jogo_data->resultado_visitante			=	( is_null( $jogo[ 'placar_visitante' ] ) ) ? NULL : ( $jogo[ 'placar_visitante' ] == 0 ) ? 99999 : $jogo[ 'placar_visitante' ];
			$jogo_data->penaltis_visitante			=	( is_null( $jogo[ 'placar_penalti_visitante' ] ) ) ? NULL : ( $jogo[ 'placar_penalti_visitante' ] == 0 ) ? 99999 : $jogo[ 'placar_penalti_visitante' ];
	
			//	ARENA
			$arena_id_externo				=	$jogo[ 'sede' ];
			$arena_base					=	$this->arena->get_one_by_where( "arena.id_externo = '{$arena_id_externo}'" );
			if ( is_object( $arena_base ) )
			{
				$jogo_data->arena_id			=	$arena_base->id;
			}
			else
			{
				$jogo_data->arena_id			=	NULL;
			}
		
			$jogo_data->publico_total			=	$jogo[ 'publico_total' ];
			$jogo_data->publico_pagante			=	$jogo[ 'publico_pagante' ];
			$jogo_data->renda_moeda				=	$jogo[ 'renda' ][ 'moeda' ];
			$jogo_data->renda_total				=	$jogo[ 'renda' ][ 'total' ];
	
			//	TIPO DE JOGO
			$jogo_data->tipo				=	$tipo_jogo;
			
			//	RODADA
			if ( key_exists( 'rodada', $jogo )  // Existindo a rodada na origem dos dados, mantemos o número dela como ID externo.
			&&   !is_null( $jogo[ 'rodada' ] )
			   )
			{
				$rodada_fase_id_externo				=	$this->define_id_externo_rodada( $jogo[ 'rodada' ], $jogo_data->tipo );
			}
			else
			{
				$rodada_fase_id_externo				=	$this->define_id_externo_rodada( NULL, $jogo_data->tipo );
			}
			
			/*
			 * Se é para calcular apenas a rodada atual, então o rodada_atual abaixo vem com um ID. Do contrária vem com FALSE.
			 */
	//if ( $this->grupo_data->rodada_atual )
	//{
	//	echo "RODADA_ATUAL=TRUE<BR/>";
	//}
	//else
	//{
	//	echo "RODADA_ATUAL=FALSE<BR/>";
	//};
			if ( !is_object( $this->grupo_data )
			||   !isset( $this->grupo_data->rodada_atual )
			||   !$this->grupo_data->rodada_atual
			||   ( $this->grupo_data->rodada_atual
			&&     $rodada_fase_id_externo == $this->grupo_data->rodada_atual
			     )
			   )
			{
				$rodada_fase_base				=	$this->rodada_fase->get_one_by_where( 	"   rodada_fase.campeonato_versao_id	= {$this->campeonato_versao_id}
																and rodada_fase.id_externo 		= '{$rodada_fase_id_externo}'
																" );
				if ( is_object( $rodada_fase_base ) )
				{
					$jogo_data->rodada_fase_id		=	$rodada_fase_base->id;
				}
				else
				{
					$rodada_fase_base				=	$this->rodada_fase->get_one_by_where( 	"   rodada_fase.campeonato_versao_id	= {$this->campeonato_versao_id}
																	and rodada_fase.id_externo 		= '{$rodada_fase_id_externo}'
																	" );
					if ( is_object( $rodada_fase_base ) )
					{
						$jogo_data->rodada_fase_id		=	$rodada_fase_base->id;
					}
					else
					{
						$rodada_fase_data			=	new stdClass();
						$rodada_fase_data->id			=	NULL;
						$rodada_fase_data->campeonato_versao_id	=	$this->campeonato_versao_id;
	
						$rodada_fase_data->tipo			=	( ( !is_null( $rodada_tipo ) ) ? $rodada_tipo : 'T' );
						$rodada_fase_data->tipo_fase		=	( ( !is_null( $rodada_tipo_fase ) ) ? $rodada_tipo_fase : 'U' );
		
						$rodada_fase_data->cod			=	( $rodada_fase_data->tipo_fase == 'G' || $rodada_fase_data->tipo_fase == 'U' ) ? $jogo[ 'rodada' ] : ( $this->fase_nome . ( ( $jogo_data->tipo == "I") ? " - Ida" : " - Volta" ) );
						$rodada_fase_data->data_inicio		=	$jogo_data->data_hora;
						$rodada_fase_data->data_fim		=	$jogo_data->data_hora;
						$rodada_fase_data->obs			=	'Criado pela integração com o Globoesporte.';
						$rodada_fase_data->id_externo		=	$rodada_fase_id_externo;
	
						$jogo_data->rodada_fase_id		=	$this->rodada_fase->update( $rodada_fase_data );
		
						$rodada_fase_base			=	$this->rodada_fase->get_one_by_id( $jogo_data->rodada_fase_id );
	echo "<<>> CRIOU UMA NOVA RODADA ID=". $jogo_data->rodada_fase_id . "<br>";
	echo ".........RODADA_TIPO=$rodada_tipo<br>";
	echo ".........RODADA_TIPO_FASE=$rodada_tipo_fase<br>";
	echo ".........RODADA_TIPO=$rodada_fase_data->tipo<br>";
	echo ".........COD=$rodada_fase_data->cod<br>";
	echo ".........RODADA_TIPO_FASE=$rodada_fase_data->tipo_fase<br>";
	echo ".........RODADA_ID_EXTERNO=$rodada_fase_data->id_externo<br>";

						unset( $rodada_fase_data );
					}
				}
				$this->rodada_fase_id				=	$jogo_data->rodada_fase_id;
	
				// Coloca a rodada incial do grupo
	//print_r( $this->grupo_data );
				if ( is_object( $this->grupo_data )
				&&   !$this->grupo_data->rodada_fase_id_inicio
				   )
				{
					$this->grupo_data->rodada_fase_id_inicio	=	$this->rodada_fase_id;
					$this->grupo_data->rodada_fase_id_fim		=	$this->rodada_fase_id;
	//echo "......<<>> ATUALIZOU O GRUPO COM ROD_INICIO={$this->grupo_data->rodada_fase_id_inicio} e ROD_FIM={$this->grupo_data->rodada_fase_id_fim}<br>";
					$this->grupo_data->id				=	$this->grupo->update( $this->grupo_data );
				}
				else
				{
					// Alteramos sempre o grupo na rodada final, enquanto ele for enviado.
					if ( is_object( $this->grupo_data )
					&&   $this->grupo_data->rodada_fase_id_inicio
					   )
					{
	//echo "......<<>> ATUALIZOU(2) O GRUPO COM ROD_INICIO={$this->grupo_data->rodada_fase_id_inicio} e ROD_FIM={$this->grupo_data->rodada_fase_id_fim}<br>";
						$this->grupo_data->rodada_fase_id_fim		=	$this->rodada_fase_id;
						$this->grupo_data->id				=	$this->grupo->update( $this->grupo_data );
					}
					else
					{
	//echo "......<<>> Não atualizou O GRUPO<br>";
					}
				}
				
				//	PROCURA O JOGO
				echo "......<<>> JOGO";
				echo " JOG_ID_EXTERNO=".$jogo_data->id_externo;
				$jogo_base					=	$this->jogo->get_one_by_where( "jogo.id_externo = '$jogo_data->id_externo'" );
				if ( is_object( $jogo_base ) )
				{
					$jogo_data->id				=	$jogo_base->id;
					echo " JEE";
				}
				else
				{
					if ( $jogo_data->equipe_id_casa
					&&   $jogo_data->equipe_id_visitante
					   )
					{
						$jogo_base			=	$this->jogo->get_one_by_where( 	"   jogo.equipe_id_casa      = {$jogo_data->equipe_id_casa}
															and jogo.equipe_id_visitante = {$jogo_data->equipe_id_visitante}
															and jogo.rodada_fase_id      = {$jogo_data->rodada_fase_id}
															" );
					}
					elseif ( $jogo_data->titulo_casa
					&&       $jogo_data->titulo_visitante
					       )
					{
						$jogo_base			=	$this->jogo->get_one_by_where( 	"   jogo.titulo_casa         = '{$jogo_data->titulo_casa}'
															and jogo.titulo_visitante    = '{$jogo_data->titulo_visitante}'
															and jogo.rodada_fase_id      = {$jogo_data->rodada_fase_id}
															" );
					}
					if ( is_object( $jogo_base ) )
					{
						$jogo_data->id			=	$jogo_base->id;
						echo " JE1";
					}
					else
					{
						$jogo_data->id			=	NULL;
						echo " N";
					}
				}
				
				//	COMPLETA OS DADOS COM A BASE DE DADOS CASO ALGO ESTEJA VAZIO NO JSON.
				if ( !$jogo_data->arena_id )
				{
					if ( is_object( $jogo_base )
					&&   $jogo[ 'sede' ]
					   )
					{
						$jogo_data->arena_id		=	$jogo_base->arena_id;
					}
					else
					{
						$jogo_data->arena_id		=	229; // Sem arena
					}
				}
				
				echo " id=".$jogo_data->id;
				echo " TIPO=".$jogo_data->tipo;
		
				echo " GRUPO_ID=".$jogo_data->grupo_id;
				echo " RODADA=".$jogo_data->rodada_fase_id;
				echo " ARENA=".$jogo_data->arena_id . "({$jogo[ 'sede' ]})";
		
				echo " DATA_HORA=".$jogo_data->data_hora;
				
				echo " MANDANTE=".$jogo_data->equipe_id_casa . $jogo_data->titulo_casa;
				echo " RES_CASA=".$jogo_data->resultado_casa;
	//			echo " PEN_CASA=".$jogo_data->penaltis_casa;
	
				echo " VISITANTE=".$jogo_data->equipe_id_visitante . $jogo_data->titulo_visitante;
				echo " RES_VIS=".$jogo_data->resultado_visitante;
	//			echo " PEN_VIS=".$jogo_data->penaltis_visitante;
	/*
				echo " PUBL_PG=".$jogo_data->publico_pagante;
				echo " PUBL_TT=".$jogo_data->publico_total;
		
				echo " MOEDA=".$jogo_data->renda_moeda;
				echo " RENDA=".$jogo_data->renda_total;
	*/
				echo "<br>";
			
				//	ATUALIZA O JOGO
				$jogo_data->id					=	$this->jogo->update( $jogo_data );
			
	//			$jogo_data->titulo_casa				=	NULL;
	//			$jogo_data->resultado_casa_prorrogacao		=	NULL;
	
	//			$jogo_data->titulo_visitante			=	NULL;
	//			$jogo_data->resultado_visitante_prorrogacao	=	NULL;
			}
		}
	}

	protected function localizar_jogos( $jogos, $rodada_tipo_fase = NULL, $rodada_tipo = NULL )
	{
		// Se existirem corretamente as informações da chave, a trataremos como um grupo.
		if ( key_exists( 'nome', $jogos )
		&&   !is_null( $jogos[ 'nome' ] )
		&&   $jogos[ 'nome' ]
		&&   key_exists( 'chave_id', $jogos )
		&&   !is_null( $jogos[ 'chave_id' ] )
		&&   $jogos[ 'chave_id' ]
		   )
		{
			$this->grupo_data				=	new stdClass();
			$this->grupo_data->campeonato_versao_id		=	$this->campeonato_versao_id;
			$this->grupo_data->id_externo			=	'chave_'.$jogos[ 'chave_id' ];
			$this->grupo_data->nome				=	ucfirst( $jogos[ 'nome' ] );
			$this->grupo_data->descr			=	$this->grupo_data->nome;
			$this->grupo_data->cod				=	$this->grupo_data->nome;
			$this->grupo_data->rodada_atual			=	FALSE;
			$this->grupo_data->rodada_fase_id_inicio	=	NULL;
			$this->grupo_data->rodada_fase_id_fim		=	NULL;
			
			$grupo_base					=	$this->grupo->get_one_by_where( "grupo.id_externo = '". $this->grupo_data->id_externo . "' and grupo.campeonato_versao_id = " . $this->grupo_data->campeonato_versao_id );
			if ( is_object( $grupo_base ) )
			{
				$this->grupo_data->id			=	$grupo_base->id;
			}
			else
			{
				$this->grupo_data->id			=	NULL;
			}
			$this->grupo_data->id				=	$this->grupo->update( $this->grupo_data );
		}

		if ( $rodada_tipo == 'I' ) // Jogos de Ida e Volta.
		{
			echo "......jogo"
				." ".$jogos[ 'equipe_a' ]
				." ".$jogos[ 'equipe_b' ]
				." ".$jogos[ 'equipe_a_label' ]
				." ".$jogos[ 'equipe_b_label' ]
				." ".$jogos[ 'ida_jogo' ]
				." ".$jogos[ 'ida_sede' ]
				." ".$jogos[ 'volta_jogo' ]
				." ".$jogos[ 'volta_sede' ]
				." ".$jogos[ 'terceiro_jogo' ]
				." TIPO_FASE=".$rodada_tipo_fase
				." TIPO_JOGO=".$rodada_tipo
				." Nome da Chave=".ucfirst( $jogos[ 'nome' ] )
				."<br>";

			if ( ( key_exists( 'ida_jogo', $jogos )
 			&&     is_array( $jogos[ 'ida_jogo' ] )
 			&&     count( $jogos[ 'ida_jogo' ] ) > 0
 			     )
			   )
			{
				// Elimina, se existir, os jogos fakes.
				$rodada_id_externo		=	$this->define_id_externo_rodada( NULL, $rodada_tipo );

				$rodada_fase_base		=	$this->rodada_fase->get_one_by_where( 	"   rodada_fase.campeonato_versao_id	= {$this->campeonato_versao_id}
														and rodada_fase.id_externo 		= '$rodada_id_externo'
														" );
				if ( $rodada_fase_base )
				{
					$this->jogo->delete	( NULL,	"	id_externo like 'temp_inter_globo_I_%'
									and    rodada_fase_id = {$rodada_fase_base->id}
									"
								);
				}

				// Associa as equipes ao grupo / chave.
				$this->associa_equipe_grupo( $jogos[ 'ida_jogo' ][ 'equipe_mandante_id' ], 1 );
				$this->associa_equipe_grupo( $jogos[ 'ida_jogo' ][ 'equipe_visitante_id' ], 2 );

				// Atualiza o jogo
				$this->atualizar_jogo( $jogos[ 'ida_jogo' ],  $tipo_jogo = 'I', $rodada_tipo_fase, $rodada_tipo );
			}

			if ( ( key_exists( 'volta_jogo', $jogos )
 			&&     is_array( $jogos[ 'volta_jogo' ] )
 			&&     count( $jogos[ 'volta_jogo' ] ) > 0
 			     )
			   )
			{
				// Elimina, se existir, os jogos fakes.
				$rodada_id_externo		=	$this->define_id_externo_rodada( NULL, $rodada_tipo );

				$rodada_fase_base		=	$this->rodada_fase->get_one_by_where( 	"   rodada_fase.campeonato_versao_id	= {$this->campeonato_versao_id}
														and rodada_fase.id_externo 		= '$rodada_id_externo'
														" );
				if ( $rodada_fase_base )
				{
					$this->jogo->delete	( NULL,	"	id_externo like 'temp_inter_globo_V_%'
									and    rodada_fase_id = {$rodada_fase_base->id}
									"
								);
				}
				
				// Atualiza o jogo
				$this->atualizar_jogo( $jogos[ 'volta_jogo' ], $tipo_jogo = 'V', $rodada_tipo_fase, $rodada_tipo );
			}

			if ( ( key_exists( 'terceiro_jogo', $jogos )
 			&&     is_array( $jogos[ 'terceiro_jogo' ] )
 			&&     count( $jogos[ 'terceiro_jogo' ] ) > 0
 			     )
			   )
			{
			   	$this->atualizar_jogo( $jogos[ 'terceiro_jogo' ], $tipo_jogo = 'T', $rodada_tipo_fase, $rodada_tipo );
			}

			// Jogos sem data e time definido.
			if ( $rodada_tipo == 'I' // Ida e volta
			&&   ( key_exists( 'ida_jogo', $jogos )
			&&     empty( $jogos[ 'ida_jogo' ] )
			     )
			&&   ( key_exists( 'volta_jogo', $jogos )
			&&     empty( $jogos[ 'volta_jogo' ] )
			     )
			   )
			{
				echo '.....ida e volta SEM TIMES.<br/>';

				// Tentamos localizar um rodada já cadastrada para este que está sem jogos.
				$rodada_id_externo		=	$this->define_id_externo_rodada( NULL, $rodada_tipo );

				$rodada_fase_base		=	$this->rodada_fase->get_one_by_where( 	"   rodada_fase.campeonato_versao_id	= {$this->campeonato_versao_id}
														and rodada_fase.id_externo 		= '$rodada_id_externo'
														" );
				if ( is_object( $rodada_fase_base ) ) // Encontrando, mantemos as datas que foram usadas antes.
				{
					$rod_data_fim			=	DateTime::createFromFormat( 'Y-m-d H:i:s', $rodada_fase_base->data_inicio );
					$prox_data_1_semana		=	$rod_data_fim;
				}
				else
				{
					$ultima_rodada_fase		=	$this->rodada_fase->get_rodada_ultima( $this->campeonato_versao_id );
					if ( $ultima_rodada_fase->data_fim )
					{
						$rod_data_fim		=	DateTime::createFromFormat( 'Y-m-d H:i:s', $ultima_rodada_fase->data_fim );
					}
					else
					{
						$rod_data_fim		=	DateTime::createFromFormat( 'Y-m-d H:i:s', $ultima_rodada_fase->data_inicio );
					}
					$prox_data_1_semana		=	$rod_data_fim;
					$prox_data_1_semana->add( new DateInterval( 'P5D' ) );
				}

				// Ida
				$id_externo_ida			=	'temp_inter_globo_I_'.$jogos[ 'nome' ].$jogos[ 'chave_id' ];
				$jogo_base			=	$this->jogo->get_one_by_where( "jogo.id_externo = '$id_externo_ida'" );
				
				if ( !is_object( $jogo_base ) )
				{
					$ar_jogo		=	array	(
											 "cancelado"			=>	0
											,"data"				=>	'Dia ' . $prox_data_1_semana->format( 'd/m/Y' )
											,"decisivo"			=>	0 
											,"equipe_mandante_id"		=>	NULL
											,"equipe_mandante_titulo"	=>	$jogos[ 'equipe_a_label' ] . ' ' . $this->grupo_data->cod
											,"equipe_visitante_id"		=>	NULL 
											,"equipe_visitante_titulo"	=>	$jogos[ 'equipe_b_label' ] . ' ' . $this->grupo_data->cod
											,"hora"				=>	'00h00'
											,"jogo_id"			=>	'temp_inter_globo_I_'.$jogos[ 'nome' ].$jogos[ 'chave_id' ]
											,"placar_mandante"		=>	NULL
											,"placar_penalti_mandante"	=>	NULL
											,"placar_penalti_visitante"	=>	NULL
											,"placar_visitante"		=>	NULL
											,"publico_pagante"		=>	NULL
											,"publico_total"		=>	NULL
											,"renda"			=>	Array ( 'moeda' => NULL, 'total' => NULL ) 
											,"rodada"			=>	NULL
											,"sede"				=>	NULL 
											,"suspenso"			=>	0 
											,"url_confronto"		=>	NULL
										);
					$this->atualizar_jogo( $ar_jogo, $tipo_jogo = 'I', $rodada_tipo_fase, $rodada_tipo );
				}

				// Volta
				$id_externo_volta		=	'temp_inter_globo_V_'.$jogos[ 'nome' ].$jogos[ 'chave_id' ];
				$jogo_base			=	$this->jogo->get_one_by_where( "jogo.id_externo = '$id_externo_volta'" );
				
				if ( !is_object( $jogo_base ) )
				{
					$prox_data_1_semana->add( new DateInterval( 'P2D' ) );
					$ar_jogo		=	array	(
											 "cancelado"			=>	0 
											,"data"				=>	'Dia ' . $prox_data_1_semana->format( 'd/m/Y' )
											,"decisivo"			=>	0 
											,"equipe_mandante_id"		=>	NULL
											,"equipe_mandante_titulo"	=>	$jogos[ 'equipe_a_label' ] . ' ' . $this->grupo_data->cod
											,"equipe_visitante_id"		=>	NULL 
											,"equipe_visitante_titulo"	=>	$jogos[ 'equipe_b_label' ] . ' ' . $this->grupo_data->cod
											,"hora"				=>	'00h00'
											,"jogo_id"			=>	'temp_inter_globo_V_'.$jogos[ 'nome' ].$jogos[ 'chave_id' ]
											,"placar_mandante"		=>	NULL
											,"placar_penalti_mandante"	=>	NULL
											,"placar_penalti_visitante"	=>	NULL
											,"placar_visitante"		=>	NULL
											,"publico_pagante"		=>	NULL
											,"publico_total"		=>	NULL
											,"renda"			=>	Array ( 'moeda' => NULL, 'total' => NULL ) 
											,"rodada"			=>	NULL
											,"sede"				=>	NULL
											,"suspenso"			=>	0 
											,"url_confronto"		=>	NULL
										);
					$this->atualizar_jogo( $ar_jogo, $tipo_jogo = 'V', $rodada_tipo_fase, $rodada_tipo );
				}
			}
		}
		else
		{
			if ( $jogos
			&&   is_array( $jogos )
			   )
			{
				$atualizou								=	FALSE;
				foreach( $jogos as $jogo_key => $jogo_value )
				{
					if ( is_array( $jogo_value ) )
					{
						$this->atualizar_jogo( $jogo_value, $tipo_jogo = 'U', $rodada_tipo_fase, $rodada_tipo );
						$atualizou						=	TRUE;
					}
				}
				
				if ( $atualizou )
				{
					// Elimina, se existir, os jogos fakes.
					$rodada_id_externo		=	$this->define_id_externo_rodada( NULL, $rodada_tipo );
	
					$rodada_fase_base		=	$this->rodada_fase->get_one_by_where( 	"   rodada_fase.campeonato_versao_id	= {$this->campeonato_versao_id}
															and rodada_fase.id_externo 		= '$rodada_id_externo'
															" );
					if ( $rodada_fase_base )
					{
						$this->jogo->delete	( NULL,	"	id_externo like 'temp_inter_globo_I_%'
										and    rodada_fase_id = {$rodada_fase_base->id}
										"
									);
					}
				}
				else
				{
					echo '.....jogo único SEM TIMES.<br/>';

					// Tentamos localizar um rodada já cadastrada para este que está sem jogos.
					$rodada_id_externo		=	$this->define_id_externo_rodada( NULL, $rodada_tipo );

					$rodada_fase_base		=	$this->rodada_fase->get_one_by_where( 	"   rodada_fase.campeonato_versao_id	= {$this->campeonato_versao_id}
																and rodada_fase.id_externo 		= '$rodada_id_externo'
																" );
					if ( is_object( $rodada_fase_base ) ) // Encontrando, mantemos as datas que foram usadas antes.
					{
						$rod_data_fim			=	DateTime::createFromFormat( 'Y-m-d H:i:s', $rodada_fase_base->data_inicio );
						$prox_data_1_semana		=	$rod_data_fim;
					}
					else
					{
						$ultima_rodada_fase		=	$this->rodada_fase->get_rodada_ultima( $this->campeonato_versao_id );
						if ( $ultima_rodada_fase->data_fim )
						{
							$rod_data_fim		=	DateTime::createFromFormat( 'Y-m-d H:i:s', $ultima_rodada_fase->data_fim );
						}
						else
						{
							$rod_data_fim		=	DateTime::createFromFormat( 'Y-m-d H:i:s', $ultima_rodada_fase->data_inicio );
						}
						$prox_data_1_semana		=	$rod_data_fim;
						$prox_data_1_semana		=	$prox_data_1_semana->add( new DateInterval( 'P5D' ) );
					}
	
					// Ida
					$id_externo_jogo		=	'temp_inter_globo_I_'.$jogos[ 'nome' ].$jogos[ 'chave_id' ];
					$jogo_base			=	$this->jogo->get_one_by_where( "jogo.id_externo = '$id_externo_jogo'" );
					
					if ( !is_object( $jogo_base ) )
					{
						$ar_jogo		=	array	(
												 "cancelado"			=>	0
												,"data"				=>	'Dia ' . $prox_data_1_semana->format( 'd/m/Y' )
												,"decisivo"			=>	0 
												,"equipe_mandante_id"		=>	NULL
												,"equipe_mandante_titulo"	=>	$jogos[ 'equipe_a_label' ]
												,"equipe_visitante_id"		=>	NULL 
												,"equipe_visitante_titulo"	=>	$jogos[ 'equipe_b_label' ]
												,"hora"				=>	'00h00'
												,"jogo_id"			=>	'temp_inter_globo_I_'.$jogos[ 'nome' ].$jogos[ 'chave_id' ]
												,"placar_mandante"		=>	NULL
												,"placar_penalti_mandante"	=>	NULL
												,"placar_penalti_visitante"	=>	NULL
												,"placar_visitante"		=>	NULL
												,"publico_pagante"		=>	NULL
												,"publico_total"		=>	NULL
												,"renda"			=>	Array ( 'moeda' => NULL, 'total' => NULL ) 
												,"rodada"			=>	NULL
												,"sede"				=>	NULL 
												,"suspenso"			=>	0 
												,"url_confronto"		=>	NULL
											);
						$this->atualizar_jogo( $ar_jogo, $tipo_jogo = 'U', $rodada_tipo_fase, $rodada_tipo );
					}
				}
			}
		}
	}
	
	protected function associa_equipe_grupo( $equipe_id_externo, $ordem )
	{
		$equipe_base					=	$this->equipe->get_one_by_where( "equipe.id_externo = '{$equipe_id_externo}'" );
		if ( is_object( $equipe_base ) )
		{
			$equipe_grupo_data		=	new stdClass();
			$equipe_grupo_data->grupo_id	=	$this->grupo_data->id; // O grupo deve estar criado antes.
			
			$equipe_grupo_data->equipe_id	=	$equipe_base->id;
			$equipe_grupo_data->ordem	=	$ordem;
			
			$equipe_grupo_gase		=	$this->grupo_equipe->get_one_by_where( "grupo_equipe.grupo_id = $equipe_grupo_data->grupo_id and grupo_equipe.equipe_id = $equipe_grupo_data->equipe_id" );
			if ( is_object( $equipe_grupo_gase ) )
			{
				$equipe_grupo_data->id	=	$equipe_grupo_gase->id;
			}
			else
			{
				$equipe_grupo_data->id	=	NULL;
			}
			$equipe_grupo_data->id		=	$this->grupo_equipe->update( $equipe_grupo_data );

			unset( $equipe_grupo_data );
		}
	}

	public function jogos( $campeonato_versao_id, $atualizar_todo_campeonato = FALSE, $so_atual = TRUE, $p_json = NULL )
	{
		$this->campeonato_versao_id		=	$campeonato_versao_id;
		
		if ( is_string( $atualizar_todo_campeonato ) )
		{
			$atualizar_todo_campeonato	=	( strtoupper( $atualizar_todo_campeonato ) == 'FALSE' ) ? FALSE : TRUE;
		}
		if ( is_string( $so_atual ) )
		{
			$so_atual			=	( strtoupper( $so_atual ) == 'FALSE' ) ? FALSE : TRUE;
		}

		if ( $this->campeonato_versao_id )
		{
			$obj					=	NULL;
			if ( !$p_json )
			{
				$versao_base			=	$this->campeonato_versao->get_one_by_id( $this->campeonato_versao_id );
	
				if ( is_object( $versao_base )
				&&   $versao_base->url_dado_externo
				   )
				{
					$json			=	file_get_contents( $versao_base->url_dado_externo );
					$versao_base->dados_externos	=	$json;
					$this->campeonato_versao->update( $versao_base );
					
					$obj			=	json_decode( $json, TRUE );
				}
			}
			else
			{
				$obj				=	json_decode( $p_json, TRUE );
			}

			if ( $obj )
			{
				$liberado_atualizar		=	( $atualizar_todo_campeonato ) ? TRUE : FALSE;
				$this->grupo_data		=	NULL;
				$this->rodada_fase_id		=	NULL;

				foreach( $obj[ 'lista_de_jogos' ][ 'campeonato' ][ 'edicao_campeonato' ][ 'fases' ] as $key => $this->fase )
				{
					// Fases
					echo $this->fase[ 'nome' ] . " FaseKey={$key} FaseAtual?=".( ( $this->fase[ 'atual' ] ) ? 'TRUE' : 'FALSE' )."<br>";
					$this->fase_nome	=	$this->fase[ 'nome' ];
					$this->fase_id_externo	=	$this->fase[ 'id' ];
					if ( isset( $this->fase[ 'subtipo' ] ) )
					{
						$this->fase_subtipo	=	$this->fase[ 'subtipo' ];
					}
					else
					{
						$this->fase_subtipo	=	'simples';
					}
					
//foreach( $this->fase as $fase_key => $fase_valor )
//{
//	echo "campo={$fase_key} valor={$fase_valor}<br>";
//}

					// FASE_GRUPOS 
			 		//	Como identificar --- é uma fase de grupos se o campo "grupos" for um array();
			 		//			---- "jogos" também contém todos os jogos, mas não compensa, pois perdemos a referencia com o grupo.
			 		//	[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'grupos' ][ seq_num ][ 'jogos' ][ seq_num ]
			 		//				rodada_fase_id	=	...[ seq_num ][ 'rodada' ]
			 		//				grupo_id	=	veja explicação acima.
			 		if ( ( key_exists( 'grupos', $this->fase )
			 		&&     is_array( $this->fase[ 'grupos' ] )
			 		&&     count( $this->fase[ 'grupos' ] ) > 0
			 		     )
			 		   )
			 		{
						echo "...Calculo por GRUPOS<br>";
						
						if ( $this->fase[ 'atual' ]
						||   $liberado_atualizar
						||   is_object( $this->rodada_fase_selecionada ) // Informamos uma rodada específica.
						   )
						{
							foreach( $this->fase[ 'grupos' ] as $grupo_key => $grupo_value )
							{
								$this->grupo_data				=	new stdClass();
								$this->grupo_data->campeonato_versao_id		=	$this->campeonato_versao_id;
								$this->grupo_data->id_externo			=	$grupo_value[ 'id' ];
								$this->grupo_data->nome				=	$grupo_value[ 'nome' ];
								$this->grupo_data->descr			=	$grupo_value[ 'nome' ];
								$this->grupo_data->cod				=	substr( strrchr( $grupo_value[ 'nome' ], ' ' ), 1 );
								$this->grupo_data->rodada_fase_id_inicio	=	NULL;
								$this->grupo_data->rodada_fase_id_fim		=	NULL;

								if ( $so_atual )
								{
									$this->grupo_data->rodada_atual		=	$grupo_value[ 'rodada_atual' ];
								}
								else
								{
									$this->grupo_data->rodada_atual		=	FALSE;
								}
								
								$grupo_base					=	$this->grupo->get_one_by_where( "grupo.id_externo = '". $this->grupo_data->id_externo . "' and grupo.campeonato_versao_id = " . $this->grupo_data->campeonato_versao_id );
								if ( is_object( $grupo_base ) )
								{
									$this->grupo_data->id			=	$grupo_base->id;
								}
								else
								{
									$this->grupo_data->id			=	NULL;
								}
								$this->grupo_data->id				=	$this->grupo->update( $this->grupo_data );
								
								foreach( $grupo_value[ 'equipes' ] as $eqp_key => $eqp_value )
								{
									echo "...eqp_id={$eqp_value['id']} grupo=" . $this->grupo_data->nome . " ordem={$eqp_value['ordem']}<br>";
									$this->associa_equipe_grupo( $eqp_value['id'], $eqp_value['ordem'] );
								}
	
								$this->localizar_jogos( $grupo_value[ 'jogos' ], $rodada_tipo_fase = ( ( $this->fase_subtipo == 'simples' ) ? 'G' : 'M' ), $rodada_tipo = 'G' );
								
								$this->grupo_data				=	NULL; // Se o próximo jogo não for do tipo grupo, este NULL evitará que este se ligue ao último grupo usado.
							}

							if ( $this->fase[ 'atual' ] // libera todas as fase da atual para frente.
							&&   !$so_atual
							   )
							{
								$liberado_atualizar				=	TRUE;
							}
						}
			 		}

			 		// OITAVAS / QUARTAS / SEMIFINAIS / FINAL
			 		//	Como identificar --- se "grupos" e "jogos" estiverem vazios, então é uma fase única (brasileirão, por exemplo).
			 		//	[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'chaves' ][ seq_num ][ 'jogos' ]
					//
			 		//				rodada_fase_id	=	Cada fase desta será uma rodada a parte.
			 		//							Pegar o "id" da fase como id_externo
			 		else if ( ( key_exists( 'grupos', $this->fase )
			 		     &&     is_array( $this->fase[ 'grupos' ] )
			 		     &&     count( $this->fase[ 'grupos' ] ) == 0
			 		          )
			 		     &&   ( key_exists( 'jogos', $this->fase )
			 		     &&     is_array( $this->fase[ 'jogos' ] )
			 		     &&     count( $this->fase[ 'jogos' ] ) == 0
			 		          )
			 		     &&   ( key_exists( 'chaves', $this->fase )
			 		     &&     is_array( $this->fase[ 'chaves' ] )
			 		     &&     count( $this->fase[ 'chaves' ] ) > 0
			 		          )
			 			)
			 		{
						echo "...Calculo de Mata/Mata, 'ida e volta' e etc. ";
						$rodada_tipo				=	'T';
						$rodada_tipo_fase			=	'U';

						if ( strrpos( strtoupper( $this->fase_nome ), 'PLAY' )    !== FALSE
						||   strrpos( strtoupper( $this->fase_nome ), 'FASE PR' ) !== FALSE
						||   strrpos( strtoupper( $this->fase_nome ), 'PRIMEIRA FASE' ) !== FALSE
						||   strrpos( strtoupper( $this->fase_nome ), 'SEGUNDA FASE' ) !== FALSE
						||   strrpos( strtoupper( $this->fase_nome ), 'TERCEIRA FASE' ) !== FALSE
						   )
						{
							$rodada_tipo			=	( $this->fase[ 'ida_volta' ] === TRUE ) ? 'I' : 'M';
							$rodada_tipo_fase		=	'P';
							echo "PLAYOFFS<br>";
						}
						else if ( strrpos( strtoupper( $this->fase_nome ), 'OITAVA' ) !== FALSE )
						{
							$rodada_tipo			=	( $this->fase[ 'ida_volta' ] === TRUE ) ? 'I' : 'M';
							$rodada_tipo_fase		=	'8';
							echo "OITAVAS<br>";
						}
						else if ( strrpos( strtoupper( $this->fase_nome ), 'QUARTA' ) !== FALSE )
						{
							$rodada_tipo			=	( $this->fase[ 'ida_volta' ] === TRUE ) ? 'I' : 'M';
							$rodada_tipo_fase		=	'4';
							echo "QUARTAS<br>";
						}
						else if ( strrpos( strtoupper( $this->fase_nome ), 'SEMI' ) !== FALSE )
						{
							$rodada_tipo			=	( $this->fase[ 'ida_volta' ] === TRUE ) ? 'I' : 'M';
							$rodada_tipo_fase		=	'S';
							echo "SEMIFINAL<br>";
						}
						else if ( strrpos( strtoupper( $this->fase_nome ), 'FINAL'  ) !== FALSE
						||        strrpos( strtoupper( $this->fase_nome ), 'FINAIS' ) !== FALSE
						        )
						{
							$rodada_tipo			=	( $this->fase[ 'ida_volta' ] === TRUE ) ? 'I' : 'M';
							$rodada_tipo_fase		=	'F';
							echo "FINAL<br>";
						}
						else
						{
							$rodada_tipo			=	( $this->fase[ 'ida_volta' ] === TRUE ) ? 'I' : 'M';
							echo "COM TIPO NÃO DEFINIDO<br>";
						}
						
						if ( $this->fase[ 'atual' ]
						||   $liberado_atualizar
						||   is_object( $this->rodada_fase_selecionada ) // Informamos uma rodada específica.
						   )
						{
							foreach( $this->fase[ 'chaves' ] as $chave_key => $chave_value )
							{
								$this->localizar_jogos( $chave_value, $rodada_tipo_fase, $rodada_tipo );
							}
							
							if ( $this->fase[ 'atual' ] // libera todas as fase da atual para frente.
							&&   !$so_atual
							   )
							{
								$liberado_atualizar	=	TRUE;
							}
						}
					}
			 		// Ida e Vola
			 		//	Como identificar --- [ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'ida_volta' ] == TRUE
			 		//	[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ SEQ_NUM ][ 'chaves' ][ seq_num ]
			 		//					Cada sequencia é um jogo

			 		// 
			 		// <<<< BRASILEIRÃO >>>>
			 		// FASE_UNICA
			 		//	Como identificar --- se "grupos" e "chaves" estiverem vazios, então é uma fase única (brasileirão, por exemplo).
			 		//	[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'fases' ][ 0 ][ 'jogos' ] <<< sempre com zero
			 		else if ( ( key_exists( 'jogos', $this->fase )
			 		     &&     is_array( $this->fase[ 'jogos' ] )
			 		     &&     count( $this->fase[ 'jogos' ] ) > 0
			 		          )
			 			)
			 		{
						echo "...Calculo de fase única<br>";
						
						$rodada_atual				=	$this->fase[ 'rodada_atual' ];
						
						foreach( $this->fase[ 'jogos' ] as $jogo_key => $jogo_value )
						{
//							echo "key={$jogo_key} value=$jogo_value <br>";
//							foreach( $jogo_value as $kkk => $vvv )
//							{
//								echo ".... $kkk == $vvv <br>";
//							}

							if ( $jogo_value[ 'rodada' ] == $rodada_atual 
							||   $liberado_atualizar
							||   is_object( $this->rodada_fase_selecionada ) // Informamos uma rodada específica.
							   )
							{
								$this->atualizar_jogo( $jogo_value );

								if ( $jogo_value[ 'rodada' ] == $rodada_atual // libera todas as fase da atual para frente.
								&&   !$so_atual
								   )
								{
									$liberado_atualizar	=	TRUE;
								}
							}
						}
			 		}
//					$this->show_array( $value, $key );
				}
			}
		}

		// Resolve o problema de NULL quando o resultado do jogo é 0 (zero).
		// TODO: Rever o porque o 0 (zero) fica nulo na base de dados.
		$this->db->query(	"
					update	jogo
					set	resultado_casa = 0
						,data_hora = data_hora
					where   resultado_casa = 99999
					"
				);
		$this->db->query(	"
					update	jogo
					set	resultado_visitante = 0
						,data_hora = data_hora
					where   resultado_visitante = 99999
					"
				);
		$this->db->query(	"
					update	jogo
					set	penaltis_casa = 0
						,data_hora = data_hora
					where   penaltis_casa = 99999
					"
				);
		$this->db->query(	"
					update	jogo
					set	penaltis_visitante = 0
						,data_hora = data_hora
					where   penaltis_visitante = 99999
					"
				);

		// Atualiza a data e hora de todas as rodadas do campeonato.
		$this->rodada_fase->set_inicio_fim( NULL, $this->campeonato_versao_id );
	}

	protected function imagem_equipe( $equipe_id, $url_escudo, $nome_equipe, $equipe_imagem )
	{
		$tmp_file	=	'/tmp/'.strtolower( substr( strrchr( $url_escudo, '/' ), 1 ) );
		echo "......Escudo={$url_escudo} tmp={$tmp_file}<br>";

		if ( !file_exists( $tmp_file ) )
		{
			file_put_contents( $tmp_file, file_get_contents( $url_escudo ) );
			echo ".........Baixou o arquivo<br>";
		}
		else
		{
			echo ".........NÃO Baixou, já existia<br>";
		}

		if ( file_exists( $tmp_file ) )
		{
			$data				=	new stdClass();
			if ( is_object( $equipe_imagem ) 
			&&   $equipe_imagem->id
			   )
			{
				$data->id		=	$equipe_imagem->imagem_id;
				echo ".........tem tabela imagem {$data->id}<br>";
			}
			else
			{
				$data->id		=	NULL;
			}
			
			$data->file_name		=	strtolower( substr( strrchr( $url_escudo, '/' ), 1 ) );
			$data->file_extension		=	strtolower( substr( strrchr( $url_escudo, '.' ), 1 ) );
			$data->mime_type		=	$this->imagem->getMimeType( $url_escudo );
			$data->descr			=	$nome_equipe;
			$data->size			=	filesize( $tmp_file );
			echo "......ID_Img={$data->id} Size={$data->size} mime={$data->mime_type} ext={$data->file_extension}<br>";

			$data->id			=	$this->imagem->update( $data );
			echo "......ID_Img={$data->id} apos update.<br>";
			
			$new_name			=	$this->imagem->get_file_name( $id = $data->id, $full = TRUE, $extension = $data->file_extension, $wish_path = 'FISICAL' );
			echo "......NewName={$new_name}<br>";
			
			if ( file_exists( $new_name ) )
			{
				unlink( $new_name );
				echo ".........ARQUIVO JÁ EXISTE, apaguei.<BR>";
			}
			copy( $tmp_file, $new_name );
			
			if ( !is_object( $equipe_imagem ) 
			||   !$equipe_imagem->id
			   )
			{
				$data_imgeqp		=	new stdClass();
				$data_imgeqp->id	=	NULL;
				$data_imgeqp->imagem_id	=	$data->id;
				$data_imgeqp->equipe_id	=	$equipe_id;
				$data_imgeqp->tamanho	=	'M';

				$this->equipe_imagem->update( $data_imgeqp );
				unset( $data_imgeqp );
			}
			
			unset( $data );
		}
	}

	function equipe( $campeonato_versao_id, $p_json = NULL )
	{

		if ( $campeonato_versao_id )
		{
			$obj			=	NULL;

			if ( !$p_json )
			{
				$versao_base	=	$this->campeonato_versao->get_one_by_id( $campeonato_versao_id );
	
				if ( is_object( $versao_base )
				&&   $versao_base->url_dado_externo
				   )
				{
					$json	=	file_get_contents( $versao_base->url_dado_externo );
					$obj	=	json_decode( $json, TRUE );
				}
			}
			else
			{
				$obj		=	json_decode( $p_json, TRUE );
			}
	
			if ( $obj )
			{
				// ATUALIZA EQUIPE
				foreach( $obj[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'equipes' ] as $key => $value )
				{
					echo $value[ 'nome_popular' ] . '<br/>';
		
					$equipe			=	new stdClass();
					$equipe->nome		=	str_replace( "'", "`", $value[ 'nome_popular' ] );
					
					if ( strrpos( ( $value[ 'escudo_grande' ] ), 'escudo_default' ) === FALSE )
					{
						$equipe->escudo		=	$value[ 'escudo_grande' ];
					}
					else if ( strrpos( ( $value[ 'escudo_medio' ] ), 'escudo_default' ) === FALSE )
					{
						$equipe->escudo		=	$value[ 'escudo_medio' ];
					}
					else if ( strrpos( ( $value[ 'escudo' ] ), 'escudo_default' ) === FALSE )
					{
						$equipe->escudo		=	$value[ 'escudo' ];
					}
					else if ( strrpos( ( $value[ 'escudo_pequeno' ] ), 'escudo_default' ) === FALSE )
					{
						$equipe->escudo		=	$value[ 'escudo_pequeno' ];
					}
					else
					{
						$equipe->escudo		=	$value[ 'escudo' ];
					}
//					echo "...... ESCUDO grande {$value[ 'escudo_grande' ]}<br>";
//					echo "...... ESCUDO medio {$value[ 'escudo_medio' ]}<br>";
//					echo "...... ESCUDO pequeno {$value[ 'escudo_pequeno' ]}<br>";
//					echo "...... ESCUDO normal {$value[ 'escudo' ]}<br>";
					
					$equipe->nome_completo	=	$value[ 'nome' ];
					$equipe->sigla		=	$value[ 'sigla' ];
					$equipe->id_externo	=	$key;
					$equipe_base		=	$this->equipe->get_one_by_where( "id_externo = '{$equipe->id_externo}'" );
					if ( is_object( $equipe_base ) )
					{
						$equipe->id		=	$equipe_base->id;
						$equipe->id_facebook	=	$equipe_base->id_facebook;
					}
					else
					{
						$equipe_base		=	$this->equipe->get_one_by_where( "nome = '{$equipe->nome}'" );
						if ( is_object( $equipe_base ) )
						{
							$equipe->id		=	$equipe_base->id;
							$equipe->id_facebook	=	$equipe_base->id_facebook;
						}
						else
						{
							$equipe->id		=	NULL;
							$equipe->id_facebook	=	NULL;
						}
					}
		
					if ( $equipe->id )
					{
						echo "... vai atualizar";
						$imagem_equipe		=	$this->equipe_imagem->get_one_by_where( "equipe_id = {$equipe->id}" );
						if ( is_object( $imagem_equipe ) 
						&&   $imagem_equipe->id
						   )
						{
							$precisa_imagem	=	FALSE;
						}
						else
						{
							$precisa_imagem	=	TRUE;
						}
						$nova			=	FALSE;
					}
					else
					{
						echo "... NOVA EQUIPE";
						$nova			=	TRUE;
						$imagem_equipe		=	NULL;
					}
		
					$equipe->id			=	$this->equipe->update( $equipe );
					echo " ID={$equipe->id}<br/>";
		
					if ( $nova || $precisa_imagem )
					{
//						echo "...... ESCUDO {$equipe->escudo}";
						$this->imagem_equipe( $equipe->id, $equipe->escudo, $equipe->nome, $imagem_equipe );
					}
					
					$equipe_versao					=	$this->campeonato_versao_equipe->get_one_by_where( "campeonato_versao_id = {$campeonato_versao_id} and equipe_id = {$equipe->id}" );
					if ( is_object( $equipe_versao ) 
					&&   $equipe_versao->id
					   )
					{
						echo "... Já está ligada ao campeonato.<br>";
					}
					else
					{
						$equipe_versao				=	new stdClass();
						$equipe_versao->id			=	NULL;
						$equipe_versao->equipe_id		=	$equipe->id;
						$equipe_versao->campeonato_versao_id	=	$campeonato_versao_id;
						$equipe_versao->ja_libertadores		=	'N';
						$equipe_versao->id			=	$this->campeonato_versao_equipe->update( $equipe_versao );
						echo "... Ligou ao campeonato<br>";
						unset( $equipe_versao );
					}
					
					unset( $equipe );
				}
			}
			else
			{
				echo "versão do campeonato Não existe.";
			}
		}
		else
		{
			echo "informe o id da versão do campeonato";
		}
	}

	function arena( $campeonato_versao_id, $p_json = NULL )
	{

		if ( $campeonato_versao_id )
		{
			$obj			=	NULL;

			if ( !$p_json )
			{
				$versao_base	=	$this->campeonato_versao->get_one_by_id( $campeonato_versao_id );
	
				if ( is_object( $versao_base )
				&&   $versao_base->url_dado_externo
				   )
				{
					$json	=	file_get_contents( $versao_base->url_dado_externo );
					$obj	=	json_decode( $json, TRUE );
				}
			}
			else
			{
				$obj		=	json_decode( $p_json, TRUE );
			}
	
			if ( $obj )
			{
				// ATUALIZA ARENA
				foreach( $obj[ 'lista_de_jogos' ]['campeonato'][ 'edicao_campeonato' ][ 'sedes' ] as $key => $value )
				{
					echo $value[ 'nome_popular' ] . ' ' . $value[ 'nome' ] . '<br/>';
		
					$arena			=	new stdClass();
					$arena->nome		=	str_replace( "'", ' ', $value[ 'nome_popular' ] );
					$arena->nome_oficial	=	str_replace( "'", ' ', $value[ 'nome' ] );
					$arena->descr		=	NULL;

					$arena->localizacao_gps	=	$value[ 'localizacao_geografica' ];
					$localizacao		=	explode( ',', $value[ 'localizacao' ] );
					if ( is_array( $localizacao ) )
					{
						$arena->cidade	=	trim( ( key_exists( 0, $localizacao ) ) ? $localizacao[0] : NULL );
						$arena->estado	=	trim( ( key_exists( 1, $localizacao ) ) ? $localizacao[1] : NULL );
					}
					else
					{
						$arena->cidade	=	$value[ 'localizacao' ];
						$arena->estado	=	NULL;
					}

					$arena->capacidade	=	$value[ 'capacidade_maxima' ];
					
					$array_inauguracao	=	explode( '-', str_replace( ' ', '', $value[ 'inauguracao' ] ) );
					if ( $value[ 'inauguracao' ]
					&&   is_array( $localizacao )
					   )
					{
//						$_date			=	new DateTime(  .' 00:00 GMT' );
//						$arena->inauguracao	=	$_date;
						$arena->inauguracao	=	$array_inauguracao[ 0 ] .'/'. $array_inauguracao[ 1 ] .'/'. $array_inauguracao[ 2 ];
					}
					else
					{
						$arena->inauguracao	=	NULL;
					}
					$arena->id_externo	=	$key;
					$arena_base		=	$this->arena->get_one_by_where( "id_externo = '{$arena->id_externo}'" );
					if ( is_object( $arena_base ) )
					{
						$arena->id		=	$arena_base->id;
					}
					else
					{
						// Tentamos pelo nome popular antes de inserir uma nova arena.
						$arena_base		=	$this->arena->get_one_by_where( "nome = '{$arena->nome}'" );
						if ( is_object( $arena_base ) )
						{
							$arena->id		=	$arena_base->id;
						}
						else 
						{
							$arena->id		=	NULL;
						}
					}
		
					if ( $arena->id )
					{
						echo "... vai atualizar";
						$nova			=	FALSE;
					}
					else
					{
						echo "... NOVA ARENA";
						$nova			=	TRUE;
					}
		
					$arena->id			=	$this->arena->update( $arena );
					echo " ID={$arena->id}<br/>";
					
					unset( $arena );
				}
			}
			else
			{
				echo "versão do campeonato Não existe.";
			}
		}
		else
		{
			echo "informe o id da versão do campeonato";
		}
	}
	
	public function rodada_fase( $rodada_fase_id )
	{
		if ( is_numeric( $rodada_fase_id ) )
		{
			$rodada_base				=	$this->rodada_fase->get_one_by_id( $rodada_fase_id );
			
			if ( $rodada_base )
			{
				$this->rodada_fase_selecionada		=	$rodada_base;
				$this->campeonato_versao_id		=	$rodada_base->campeonato_versao_id;
				$this->ativado_pelo_campeonato		=	FALSE;
		
				if ( $this->campeonato_versao_id )
				{
					$versao_base	=	$this->campeonato_versao->get_one_by_id( $this->campeonato_versao_id );
					if ( is_object( $versao_base )
					&&   $versao_base->url_dado_externo
					   )
					{
						$json				=	file_get_contents( $versao_base->url_dado_externo );
						$versao_base->dados_externos	=	$json;
						$this->campeonato_versao->update( $versao_base );
					}
					else
					{
						$json				=	NULL;
					}
					
//					$this->arena( $this->campeonato_versao_id, $json );
//					$this->equipe( $this->campeonato_versao_id, $json );
					$this->jogos( $this->campeonato_versao_id, FALSE, FALSE, $json );
					
					$this->rodada_fase->set_inicio_fim( $rodada_fase_id, NULL );

					$this->campeonato_versao_classificacao->classificar( $rodada_fase_id, FALSE, TRUE );
				}
				else
				{
					echo "informe o id da versão do campeonato";
				}
			}
			else
			{
				echo "informe o id da rodada que exista na base de dados.";
			}
		}
		else
		{
			echo "informe o id da rodada corretamente.";
		}
	}

	public function campeonato_versao( $campeonato_versao_id, $atualizar_todo_campeonato = FALSE, $classificar_todo_campeonato = FALSE )
	{
		$this->campeonato_versao_id		=	$campeonato_versao_id;
		$this->ativado_pelo_campeonato		=	TRUE;
		
		if ( is_string( $atualizar_todo_campeonato ) )
		{
			$atualizar_todo_campeonato	=	( strtoupper( $atualizar_todo_campeonato ) == 'FALSE' ) ? FALSE : TRUE;
		}

		if ( $this->campeonato_versao_id )
		{
			$versao_base	=	$this->campeonato_versao->get_one_by_id( $this->campeonato_versao_id );
			if ( is_object( $versao_base )
			&&   $versao_base->url_dado_externo
			   )
			{
				$json				=	file_get_contents( $versao_base->url_dado_externo );
				$versao_base->dados_externos	=	$json;
				$this->campeonato_versao->update( $versao_base );
			}
			else
			{
				$json				=	NULL;
			}
			
			$this->arena( $this->campeonato_versao_id, $json );
			$this->equipe( $this->campeonato_versao_id, $json );
			$this->jogos( $this->campeonato_versao_id, $atualizar_todo_campeonato, FALSE, $json );
			
			$this->rodada_fase->set_inicio_fim( NULL, $this->campeonato_versao_id );

			if ( $classificar_todo_campeonato )
			{
				$this->campeonato_versao_classificacao->classificar( NULL, $this->campeonato_versao_id, FALSE );
			}
		}
		else
		{
			echo "informe o id da versão do campeonato";
		}
	}
	
	protected function show_array( $valor, $chave )
	{
		$this->level++;
		
		$str_pontos = str_pad('', $this->level * 6, "...");

		if ( is_array( $valor ) )
		{
			echo $str_pontos." {$chave} array()<br/>";
			
			foreach( $valor as $key => $value )
			{
				$this->show_array( $value, $key );
			}
		}
		else
		{
			echo $str_pontos." {$chave}={$valor}<br/>";
		}
		
		$this->level--;
	}
}
/* End of file integracao.php */
/* Location: /application/controllers/integracao.php */
