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
 * @filesource		/application/controllers/painel.php
 * 
 * $Id: painel.php,v 1.4 2013-04-07 14:02:33 junior Exp $
 * 
 */

class Painel extends JX_Page
{
	protected $_revision	=	'$Id: painel.php,v 1.4 2013-04-07 14:02:33 junior Exp $';
	
	var $area_selecionada	=	'calendar';
	var $equipe_nome	=	NULL;
	var $arena_nome		=	NULL;

	function __construct()
	{
		$_config		=	array	(
							 'kick'					=>	array	(
														 'read_write'		=>	'readonly'
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
							,'campeonato_versao_classificacao'	=>	array	(
														 'read_write'		=>	'readonly'
														,'r_table_name'		=>	''
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	TRUE
														)
							,'campeonato_versao'			=>	array	(
														 'read_write'		=>	'readonly'
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
														 'read_write'		=>	'readonly'
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
							,'equipe'				=>	array	(
														 'read_write'		=>	'readonly'
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
														 'read_write'		=>	'readonly'
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
														 'read_write'		=>	'readonly'
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
							);
		parent::__construct( $_config );
		
		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	public function campeonato( $campeonato_versao_id = NULL )
	{
		// Confirma se a entrada é numérica.
		if ( !is_numeric( $campeonato_versao_id ) )
		{
			$campeonato_versao_id			=	NULL;
		}
		$this->rodada_fase->set_id_sessao( NULL );
		
		$this->index( NULL, $campeonato_versao_id );
	}
	
	public function calendario( $data_selecionada = NULL, $campeonatos = 'M', $equipe_nome = NULL, $arena_nome = NULL )
	{
		$this->area_selecionada				=	'calendar';
		$this->singlepack->set_sessao( 'painel_area', $this->area_selecionada );
		$this->singlepack->set_sessao( 'calendario_campeonatos', $campeonatos );
		
		if ( !$data_selecionada
		||   strtoupper( $data_selecionada ) == 'NULL'
		   )
		{
			$data_selecionada			=	date( 'Y-m-d' );
		}

		$this->equipe_nome				=	( $equipe_nome == 'none' ) ? NULL : $equipe_nome;
		$this->arena_nome				=	( $arena_nome == 'none' ) ? NULL : $arena_nome;

		$equipe_id					=	NULL;
		if ( !empty( $equipe_nome )
		&&   $equipe_nome !== 'none'
		   )
		{
			$equipe_base				=	$this->equipe->get_one_by_where( "upper( equipe.nome ) like '%$equipe_nome%'" );
			if ( $equipe_base )
			{
				$equipe_id			=	$equipe_base->id;
			}
			else
			{
				$equipe_id			=	NULL;
			}
		}
		
		$arena_id					=	NULL;
		if ( !empty( $arena_nome )
		&&   $arena_nome !== 'none'
		   )
		{
			$arena_base				=	$this->arena->get_one_by_where( "upper( arena.nome ) like '%$arena_nome%'" );
			if ( $arena_base )
			{
				$arena_id			=	$arena_base->id;
			}
			else
			{
				$arena_id			=	NULL;
			}
		}
		
		$this->index( NULL, NULL, $data_selecionada, $arena_id, $equipe_id, strtoupper( $campeonatos ) );
	}

	/**
	 * Página princial do site.
	 */
	public function index( $rodada_fase_id = NULL, $campeonato_versao_id = NULL, $data_selecionada = NULL, $arena_id = NULL, $equipe_id = NULL, $campeonatos = 'M' )
	{
		// Obtém dados para a classificação.
		$campeonato_versao_id					=	$this->campeonato_versao->get_id_selecionado( $campeonato_versao_id, $rodada_fase_id );
		$rodada_fase_id						=	$this->rodada_fase->get_id_selecionado( $rodada_fase_id, $campeonato_versao_id );
		$campeonato_versao_id					=	$this->rodada_fase->get_id_campeonato( $campeonato_versao_id );

		$this->campeonato_versao_classificacao->_prep_show( $rodada_fase_id );

		// Recuperamos a última seleção de área do usuário.
		$area_sess						=	$this->singlepack->get_sessao( 'painel_area' );
		if ( $area_sess )
		{
			$this->area_selecionada				=	$area_sess;
		}
		$this->load->vars( array( 'painel_area' => $this->area_selecionada ) );
		
		// Obtém dados para o calendário.
		if ( !$data_selecionada )
		{
			$data_selecionada				=	$this->singlepack->get_sessao( 'calendario_data_inicio' );
			if ( !$data_selecionada ) // Se ainda ficar NULL, forçamos HOJE.
			{
				$data_selecionada			=	new DateTime( 'now' );
			}
			else
			{
				$data_selecionada			=	new DateTime( $data_selecionada );
			}
		}
		else
		{
			$data_selecionada				=	new DateTime( $data_selecionada );
			
		}
		
		$this->singlepack->set_sessao( 'calendario_data_inicio', $data_selecionada->format( 'Y-m-d' ) );

		if ( !$campeonatos )
		{
			$campeonatos					=	'M';
		}
		$this->singlepack->set_sessao( 'calendario_campeonatos', $campeonatos );

		if ( !is_numeric( $arena_id ) )
		{
			$arena_id				=	NULL;
		}

		if ( !is_numeric( $equipe_id ) )
		{
			$equipe_id				=	NULL;
		}
	
		$data_fim								=	new DateTime( $data_selecionada->format( 'Y-m-d' ) );
		// Determina o período
		if ( $equipe_id
		||   $arena_id
		   )
		{
			$data_fim->add( new DateInterval( 'P9M' ) );
		}
		else
		{
			$data_fim->add( new DateInterval( 'P1M' ) );
		}

		$data_inicio_calend							=	new DateTime( $data_selecionada->format( 'Y-m-d' ) );
		$data_inicio_calend->modify( 'first day of this month' );
		$data_fim_calend							=	new DateTime( $data_selecionada->format( 'Y-m-t' ) );
		
		$this->kick->_prep_show( NULL, NULL, $pessoa_id = NULL, $data_inicio_calend->format( 'Y-m-d' ), $data_fim->format( 'Y-m-d' ), $arena_id, $equipe_id, $controller = 'Painel', $campeonatos );
		$rows									=	$this->load->get_var( 'rows' );

		$ar_datas_jogos								=	array();
		foreach( $rows as $jogo )
		{
			$data_hora_jogo							=	new DateTime( $jogo->data_hora_jogo );
			$ar_datas_jogos[ $data_hora_jogo->format( 'Y-m-d') ]		=	'S';
		}

		// Monta o array com o calendário.
		$intervalo_loop								=	DateInterval::createFromDateString( '1 day' );
		// Adicionado 1 à data final, pois não estava listando o último dia do mês.
		$fim_periodo								=	$data_fim_calend;
		$fim_periodo->add( new DateInterval( 'P1D' ) );
		
		$periodo								=	new DatePeriod( $data_inicio_calend, $intervalo_loop, $fim_periodo );

		$ar_calend								=	array();
		$ar_semana								=	array();
		
		$semana_ant								=	-1;
		$dia_ant								=	-1;
		$last_dia_jogo								=	$data_selecionada;
		
		foreach ( $periodo as $data_loop )
		{
			$nro_semana							=	$data_loop->format( "W" );
			$nro_dia_semana							=	$data_loop->format( "N" );
//echo 'data=' . $data_loop->format( 'd-m-Y' ) . " sem=" . $nro_semana;
			if ( $semana_ant != $nro_semana )
			{
				if ( $semana_ant != -1 )
				{
					$ar_calend[ $semana_ant ]			=	$ar_semana;
//echo ' set(1)' .  "<br/>";
				}
				$semana_ant						=	$nro_semana;

				$ar_semana						=	array();
				
				if ( $nro_dia_semana !== 0 )
				{
					for ( $i = 0;  $i < ( $nro_dia_semana -1 );  $i++)
					{
						$ar_semana[ $i ]			=	NULL;
					}
				}
			}

			$temp_dia							=	new stdClass();
			$temp_dia->data							=	$data_loop;

			$temp_dia->dia							=	$data_loop->format( 'd' );
			$temp_dia->mes							=	$data_loop->format( 'm' );
			$temp_dia->css_td						=	'';
			$temp_dia->css							=	'';
			
			// Mês selecionado
			if ( $data_selecionada->format( 'm' ) == $temp_dia->data->format( 'm' ) )
			{
				$temp_dia->css						=	' m';
			}
			else
			{
				$temp_dia->css						=	' out';
			}

			// Domingo
			if ( $temp_dia->data->format( 'D' )  == 'Sun' )
			{
				$temp_dia->css						.=	' sun';
			}

			// Hone
			if ( $temp_dia->data->format( 'Y-m-d' ) == date( 'Y-m-d' )
			||   $temp_dia->data                    == $data_selecionada
			   )
			{
				$temp_dia->css						.=	' tod';
			}
			
			// Jogo
			if ( key_exists( $temp_dia->data->format( 'Y-m-d' ), $ar_datas_jogos ) )
			{
				$temp_dia->css						.=	' jg';
				$temp_dia->css_td					.=	' jg';
				$last_dia_jogo						=	$temp_dia->data;
			}
		
			// Jogo
			if ( $temp_dia->data >= $data_inicio_calend
			&&   $temp_dia->data <= $data_fim_calend
			&&   key_exists( $temp_dia->data->format( 'Y-m-d' ), $ar_datas_jogos )
			   )
			{
				$temp_dia->css_td					.=	' calend-nav';
				$temp_dia->href						=	'#'. $last_dia_jogo->format( 'Y-m-d' );
			}
			else
			{
				$temp_dia->href						=	"/painel/calendario/" . $temp_dia->data->format( 'Y-m-d' );
			}

			$ar_semana[ $nro_dia_semana ]					=	$temp_dia;
		}
		$ar_calend[ $data_fim_calend->format( "W" ) ]				=	$ar_semana;
//echo ' set(2)' .  "<br/>";

		// Monta objeto com a descrição da data selecionada.
		$obj_data_selecionada							=	new stdClass();
		$obj_data_selecionada->data						=	DateTime::createFromFormat( 'Y-m-d', $data_selecionada->format( 'Y-m-d' ) );
			
		// Dia da semana
		switch ( $data_selecionada->format( 'w' ) )
			{
				case 0:
				$obj_data_selecionada->dia_da_semana		=	'Domingo';
				$obj_data_selecionada->dia_da_semana_curto	=	'Dom';
				break;

				case 1:
				$obj_data_selecionada->dia_da_semana		=	'Segunda-feira';
				$obj_data_selecionada->dia_da_semana_curto	=	'Seg';
				break;

				case 2:
				$obj_data_selecionada->dia_da_semana		=	'Terça-feira';
				$obj_data_selecionada->dia_da_semana_curto	=	'Ter';
				break;

				case 3:
				$obj_data_selecionada->dia_da_semana		=	'Quarta-feira';
				$obj_data_selecionada->dia_da_semana_curto	=	'Qua';
				break;

				case 4:
				$obj_data_selecionada->dia_da_semana		=	'Quinta-feira';
				$obj_data_selecionada->dia_da_semana_curto	=	'Qui';
				break;

				case 5:
				$obj_data_selecionada->dia_da_semana		=	'Sexta-feira';
				$obj_data_selecionada->dia_da_semana_curto	=	'Sex';
				break;

				case 6:
				$obj_data_selecionada->dia_da_semana		=	'Sábado';
				$obj_data_selecionada->dia_da_semana_curto	=	'Sab';
				break;

				default:
				$obj_data_selecionada->dia_da_semana		=	'None';
				$obj_data_selecionada->dia_da_semana_curto	=	'Non';
				break;
			}

		// Data por extenso
		$obj_data_selecionada->dia					=	$data_selecionada->format( 'j' );
		$obj_data_selecionada->data_extenso_curto			=	' de ';
		switch ( $data_selecionada->format( 'm' ) )
			{
				case 1:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'janeiro';
				break;

				case 2:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'fevereiro';
				break;

				case 3:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'março';
				break;

				case 4:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'abril';
				break;

				case 5:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'maio';
				break;

				case 6:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'junho';
				break;

				case 7:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'julho';
				break;

				case 8:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'agosto';
				break;

				case 9:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'setembro';
				break;

				case 10:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'outubro';
				break;

				case 11:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'novembro';
				break;

				case 12:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'dezembro';
				break;
				
				default:
				$obj_data_selecionada->data_extenso_curto	=	$obj_data_selecionada->data_extenso_curto . 'None';
				break;
			}
		$obj_data_selecionada->data_extenso_curto			=	$obj_data_selecionada->data_extenso_curto . ' de ' . $data_selecionada->format( 'Y' );
		$obj_data_selecionada->data_extenso				=	$data_selecionada->format( 'j' ) . $obj_data_selecionada->data_extenso_curto;
		$obj_data_selecionada->data_jogo_id				=	$data_selecionada->format( 'Y-m-d' );

		$this->load->view( 'painel.html',	array	(
								 'calend'		=>	$ar_calend
								,'data_selecionada'	=>	$obj_data_selecionada
								,'equipe_selecionada'	=>	$this->equipe_nome
								,'arena_selecionada'	=>	$this->arena_nome
								)
				);
	}


	/**
	 * Retorna dados para "auto"completar campos em páginas.
	 */
	public function autocomplete_arena( $campeonatos = 'M' )
	{

		// Prepara dados para seleções do calendário.
		if ( $campeonatos == 'M'
		&&   $this->singlepack->get_pessoa_id()
		   )
		{
			$where						=	"arena.id	in	(
													select	jog.arena_id
													from	 jogo				jog
														,rodada_fase			rod
														,pessoa_campeonato_versao	verpes
														,campeonato_versao		ver
													where	verpes.pessoa_id		= {$this->singlepack->get_pessoa_id()}
													and	verpes.cadastrado_para_jogar	= 'S'
													and	rod.campeonato_versao_id	= verpes.campeonato_versao_id
													and	jog.rodada_fase_id		= rod.id
													and	ver.id				= verpes.campeonato_versao_id
													and	ver.ativa			= 'S'
													)";
		}
		else
		{
			$where						=	NULL;
		}

		$term							=	strtoupper( $this->input->get_post_multi( 'term' ) );
		if ( $term )
		{
			$title_column	= $this->arena->get_column_title();
			if ( $term != '$@#$' ) // esta string é enviada quando queremos retornar todas as linhas da tabela. Será limitado a 100 pelo select_all abaixo
			{
				if ( $where )
				{
					$where				=	$where."AND ( upper( $title_column ) like '%". $term ."%' )";
				}
				else
				{
					$where				=	"( upper( $title_column ) like '%". $term ."%' )";
				}
			}

			$query						=	$this->arena->select_all( $where, $title_column, 0, 20, $this->arena->get_column_id() . ' as id, '. $title_column .' as title ' );
			$json_array					=	array();
			foreach( $query->result_array() as $row )
			{
				$row[ 'value' ]				=	$row['title'];
				$row[ 'label' ]				=	$row['title'];
				$json_array[]				=	$row;
			}

			if ( count( $json_array ) > 0 )
			{
//TODO: Verificar se o campo é obrigatório para inserir ou não a linha abaixo.
				$row					=	$json_array[0];
				
				// deixa a linha em branco.
				$row[ 'id' ] 				=	null;
				$row[ 'value' ] 			=	null;
				$row[ 'label' ]				=	'(nenhum)';
				
				$json_array[]				=	$row;
			}

			echo json_encode( $json_array );
		}
		else
		{
			echo null;
		}
	}
	public function autocomplete_equipe( $campeonatos = 'M' )
	{
		if ( $campeonatos == 'M'
		&&   $this->singlepack->get_pessoa_id()
		   )
		{
			$where						=	"equipe.id	in	(
													select	vereqp.equipe_id
													from	 campeonato_versao_equipe	vereqp
														,campeonato_versao		ver
														,pessoa_campeonato_versao	verpes
													where	verpes.pessoa_id		= {$this->singlepack->get_pessoa_id()}
													and	verpes.cadastrado_para_jogar	= 'S'
													and	ver.id				= verpes.campeonato_versao_id
													and	ver.ativa			= 'S'
													and	vereqp.campeonato_versao_id	= verpes.campeonato_versao_id
													)";
		}
		else
		{
			$where						=	NULL;
		}

		$term							=	strtoupper( $this->input->get_post_multi( 'term' ) );
		if ( $term )
		{
			$title_column	= $this->equipe->get_column_title();
			if ( $term != '$@#$' ) // esta string é enviada quando queremos retornar todas as linhas da tabela. Será limitado a 100 pelo select_all abaixo
			{
				if ( $where )
				{
					$where				=	$where."AND ( upper( $title_column ) like '%". $term ."%' )";
				}
				else
				{
					$where				=	"( upper( $title_column ) like '%". $term ."%' )";
				}
			}

			$query						=	$this->equipe->select_all( $where, $title_column, 0, 20, $this->equipe->get_column_id() . ' as id, '. $title_column .' as title ' );
			$json_array					=	array();
			foreach( $query->result_array() as $row )
			{
				$row[ 'value' ]				=	$row['title'];
				$row[ 'label' ]				=	$row['title'];
				$json_array[]				=	$row;
			}

			if ( count( $json_array ) > 0 )
			{
//TODO: Verificar se o campo é obrigatório para inserir ou não a linha abaixo.
				$row					=	$json_array[0];
				
				// deixa a linha em branco.
				$row[ 'id' ] 				=	null;
				$row[ 'value' ] 			=	null;
				$row[ 'label' ]				=	'(nenhum)';
				
				$json_array[]				=	$row;
			}

			echo json_encode( $json_array );
		}
		else
		{
			echo null;
		}
	}

	/*
	 * Registra a seleção feita na página no controle de sessão.
	 */
	public function set_sel( $area )
	{
		$this->singlepack->set_sessao( 'painel_area', $area );

		return	json_encode( array( 'painel_area' => $area ) );
	}
}

/* End of file painel.php */
/* Location: /application/controllers/painel.php */
