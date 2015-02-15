<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Kicks Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/kick_model.php
 * 
 * $Id: kick_model.php,v 1.32 2013-04-07 13:59:54 junior Exp $
 * 
 */

class Kick_model extends JX_Model
{
	protected $_revision		=	'$Id: kick_model.php,v 1.32 2013-04-07 13:59:54 junior Exp $';
	var $memoria_usada			=	0;
	var $show_log				=	FALSE;
	var $count_pessoa			=	0;
	var $count_pessoa_ant			=	0;

	/*
	 * Parâmetros para os cálculos de pontos.
	 */
	protected $fator_kiks			=	1;
	protected $peso_vitoria			=	5;
	protected $peso_empate			=	5;
	protected $acrescimo_por_cheio		=	5;

	protected $pessoa_id			=	NULL;

	protected $pessoa_ranking_grupo;
	protected $pessoa_rodada_update;
	protected $pessoa_campeonato_update;
	protected $last_pessoa_rodada_fase_id;
	
	protected $ar_kick_power		=	array();
	protected $ar_powers_rodada_seguinte	=	array();
	protected $ar_powers_rodada_atual	=	array();
	protected $rodada_anterior		=	NULL;
	protected $ar_equipes_zebra		=	array();
	protected $proxima_rodada		=	NULL;

	protected $rodada_base			=	NULL;
	protected $kick_update;
	protected $pessoa_best_rodada;
	protected $pessoa_best_rodada_grupo;
	protected $ar_pessoa_best_rodada_grupo;
	protected $qry_powers;
	protected $pessoa_rodada_power_new;
	protected $pessoa_rodada_seguinte;
	protected $kick_guru_power_update	=	NULL;
	protected $kick_barbada_power_update	=	NULL;
	protected $kick_zebra_power_update	=	NULL;
	protected $kick_tjunto_power_update	=	NULL;
	protected $kick_duelo_power_update	=	NULL;
	protected $kick_espiao_power_update	=	NULL;
	protected $limite_acumulo_poder		=	2;
	protected $calcular_power		=	FALSE;
	protected $calcular_zebra		=	FALSE;
	
	protected $str_camp_user		=	NULL;
	
	/*
	 * Notificações.
	 */
	protected $notificacao_template_id_melhor_na_rodada	=	6;
	protected $notificacao_template_id_melhor_rodada	=	7;
	
	function __construct()
	{
		$_config		=	array	(
							 'kick'						=>	array	(
															 'model_name'	=>	'kick'
															,'ar_constraint'=>	array	(
																			 'kick_uk'	=>	array	(
																 							 'cons_type'		=>	'UK'
																 							,'cons_columns'		=>	array( 'pessoa_id', 'jogo_id' )
																 							,'force_old_id'		=>	TRUE
																 							,'error_msg'		=>	'Já existe um chute para este jogo.'
																 							)
																 			,'kick_ck_1'	=>	array	(
																 							 'cons_type'		=>	'CK'
																 							,'cons_columns'		=>	array( 'jogo_id' )
																							,'condition_sql'	=>	'select id from jogo where id = {jogo_id} and data_hora < now()'
																							,'condition_php'	=>	NULL
																							,'error_msg'		=>	'Este jogo já iniciou e você não pode mais incluir ou alterar o seu chute para ele.'
																 							)
													 						)
													 		)
							,'pessoa_campeonato_versao'			=>	array	(
															 'model_name'	=>	'pessoa_campeonato_versao'
															)
							,'pessoa_rodada_fase'				=>	array	(
															 'model_name'	=>	'pessoa_rodada_fase'
															)
							,'pessoa_ranking_grupo_amigos'			=>	array	(
															 'model_name'	=>	'pessoa_ranking_grupo_amigos'
															)
							,'pessoa_rodada_fase_power'			=>	array	(
															 'model_name'	=>	'pessoa_rodada_fase_power'
															)
							,'pessoa_rodada_fase_resumo_power'		=>	array	(
															 'model_name'	=>	'pessoa_rodada_fase_resumo_power'
															)
							,'pessoa_campeonato_versao_resumo_power'	=>	array	(
															 'model_name'	=>	'pessoa_campeonato_versao_resumo_power'
															)
							,'pessoa_ranking_grupo_amigos_resumo_power'	=>	array	(
															 'model_name'	=>	'pessoa_ranking_grupo_amigos_resumo_power'
															)
							,'kick_power'					=>	array	(
															 'model_name'	=>	'kick_power'
															)
							,'kik_saldo'					=>	array	(
															 'model_name'	=>	'kik_saldo'
															)
							,'kik_movimento'				=>	array	(
															 'model_name'	=>	'kik_movimento'
															)
							,'jogo'						=>	array	(
															 'model_name'	=>	'jogo'
															)
							,'notificacao'					=>	array	(
															 'model_name'	=>	'notificacao'
															)
							);

		parent::__construct( $_config );

		// Cria constantes para os poderes.
		define( "QQI",		"1" );
		define( "GURU",		"2" );
		define( "DUELO",	"3" );
		define( "TJUNTO",	"4" );
		define( "ESPIAO",	"5" );
		define( "BARBADA",	"6" );
		define( "ZEBRA",	"7" );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		if ( $this->pessoa_id )
		{
			$pessoa_id	=	$this->pessoa_id;
		}
		elseif ( is_object( $this->singlepack->get_user_info() ) && $this->singlepack->get_pessoa_id() )
		{
			$pessoa_id	=	$this->singlepack->get_pessoa_id();
		}
		else
		{
			$pessoa_id	=	-1;
		}
				
		return	"
			 kick.*
			,IFNULL( kick.pessoa_id, {$pessoa_id} )						AS	pessoa_id
			,jogo.id									AS	jogo_id
			,grp.id										AS	grupo_id
			,grp.nome									AS	nome_grupo
			,IFNULL( jogo.equipe_id_casa, 223 )						AS	equipe_id_casa
			,IFNULL( jogo.equipe_id_visitante, 223 )					AS	equipe_id_visitante
			,jogo.rodada_fase_id
			,jogo.data_hora									AS	data_hora_jogo
			,jogo.resultado_casa
			,jogo.resultado_visitante
			,pes.imagem_facebook
			,usr.id_facebook
			,concat( rod.cod, ' rodada de ', date_format( rod.data_inicio, '%e/%m' ) )	AS	rodada_fase_title
			,pes.nome									AS	nome_pessoa
			,IFNULL( eqp_casa.nome, jogo.titulo_casa )					AS	nome_equipe_casa
			,IFNULL( eqp_vis.nome, jogo.titulo_casa )					AS	nome_equipe_visitante
			,IFNULL( eqp_casa.nome_completo, jogo.titulo_casa )				AS	nome_completo_equipe_casa
			,IFNULL( eqp_vis.nome_completo, jogo.titulo_casa )				AS	nome_completo_equipe_visitante
			,IFNULL( eqp_casa.sigla, 'KIK' )						AS	sigla_equipe_casa
			,IFNULL( eqp_vis.sigla, 'KIK' )							AS	sigla_equipe_visitante
			,arena.nome									AS	nome_arena
			,concat( eqp_casa.nome, ' {imagem_id=', eqpimg_casa.imagem_id, '} ', cast( IFNULL( jogo.resultado_casa, '' ) AS CHAR ), ' X ', cast( IFNULL( jogo.resultado_visitante, '' ) AS CHAR ), '{imagem_id=', eqpimg_vis.imagem_id, '}', ' ', eqp_vis.nome, ' (Rodada ', rod.cod, ') ', ' ', date_format( jogo.data_hora, '%a %e/%m/%Y %H:%i' ), ' ', case when IFNULL( jogo.resultado_visitante, '-1' ) = -1 then 'Em aberto' else 'Realizado' end )		AS	title
			,date_format( jogo.data_hora, '%e/%m/%y %H:%i' )				AS	dd_mm_jogo
			,date_format( jogo.data_hora, '%e/%m/%Y %H:%i' )				AS	when_field
			,rod.cod									AS	cod_rodada_fase
			,rod.tipo									AS	rodada_tipo
			,rod.tipo_fase									AS	rodada_tipo_fase
			,rod.data_inicio								AS	rodada_data_inicio
			,rod.data_fim									AS	rodada_data_fim
			,rod.campeonato_versao_id							AS	campeonato_versao_id
			,verimg.imagem_id								AS	campeonato_versao_imagem_id
			,ver.descr									AS	campeonato_descr
			,concat( 'De ', date_format( ver.data_inicio, '%e/%m' ), ' até ', date_format( ver.data_fim, '%e/%m/%Y' ), ', organizado pela ', ver.entidade_organizadora )	AS	campeonato_content
			";
	}

	public function set_pessoa_id( $pessoa_id = NULL )
	{
		log_message( 'debug', "KICK_MODEL.set_pessoa_id=". $pessoa_id );
		
		$this->pessoa_id	=	$pessoa_id;
	}

	public function set_from_join()
	{
		if ( $this->pessoa_id )
		{
			$pessoa_id	=	$this->pessoa_id;
		}
		elseif ( is_object( $this->singlepack->get_user_info() ) && $this->singlepack->get_pessoa_id() )
		{
			$pessoa_id	=	$this->singlepack->get_pessoa_id();
		}
		else
		{
			$pessoa_id	=	-1;
		}
		log_message( 'debug', "KICK_MODEL.set_from_join this->pessoa_id=". $this->pessoa_id . " PessoaSel=" . $pessoa_id );
		
		$this->db->from( 'jogo' );
		$this->db->join( 'rodada_fase		AS	rod',         'rod.id                = jogo.rodada_fase_id' );
		$this->db->join( 'campeonato_versao     AS	ver',         'ver.id                = rod.campeonato_versao_id' );
		$this->db->join( 'campeonato_versao_imagem AS	verimg',      'verimg.campeonato_versao_id	=	rod.campeonato_versao_id' );
		
		if ( $pessoa_id == -3 ) // -3 indica que queremos todos os chutes para o jogo
		{
			$this->db->join( 'kick		AS	kick',        'kick.jogo_id          = jogo.id', '' );
		}
		else
		{
			$this->db->join( 'kick		AS	kick',        'kick.jogo_id          = jogo.id and kick.pessoa_id = '.$pessoa_id, 'LEFT' );			
		}
		
		$this->db->join( 'grupo			AS	grp',         'grp.id                = jogo.grupo_id', 'LEFT' );
		$this->db->join( 'pessoa		AS	pes',         'pes.id                = kick.pessoa_id', 'LEFT' );
		$this->db->join( 'user			AS	usr',         'usr.pessoa_id         = pes.id', 'LEFT' );
		$this->db->join( 'equipe		AS	eqp_casa',    'eqp_casa.id           = jogo.equipe_id_casa', 'LEFT' );
		$this->db->join( 'equipe		AS	eqp_vis',     'eqp_vis.id            = jogo.equipe_id_visitante', 'LEFT' );
		$this->db->join( 'equipe_imagem		AS	eqpimg_casa', 'eqpimg_casa.equipe_id = eqp_casa.id', 'LEFT' );
		$this->db->join( 'equipe_imagem		AS	eqpimg_vis',  'eqpimg_vis.equipe_id  = eqp_vis.id', 'LEFT' );
		$this->db->join( 'arena			AS	arena',       'arena.id              = jogo.arena_id', 'LEFT' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	function get_order_by()
	{
		return "grp.nome, jogo.data_hora, concat( eqp_casa.nome, ' X ', eqp_vis.nome, ' (', jogo.cod, ')' )";
	}
	function get_order_by_chrono()
	{
		return "jogo.data_hora, concat( eqp_casa.nome, ' X ', eqp_vis.nome, ' (', jogo.cod, ')' )";
	}
	public function get_column_title()
	{
		return "concat( eqp_casa.nome, ' ', cast( IFNULL( jogo.resultado_casa, ' ' ) AS CHAR ), ' X ', cast( IFNULL( jogo.resultado_visitante, ' ' ) AS CHAR ), ' ', eqp_vis.nome, ' (Rodada ', rod.cod, ') ', ' ', date_format( jogo.data_hora, '%a %e/%m/%Y %H:%i' ), ' ', case when IFNULL( jogo.resultado_visitante, '-1' ) = -1 then 'Em aberto' else 'Realizado' end )";
	}

	private function set_campeonatos_pessoa( $prefix = 'rodada_fase' )
	{
		if ( !is_null( $this->singlepack->get_pessoa_id() ) )
		{
				return "exists 	(
						select	verpes.campeonato_versao_id
						from	pessoa_campeonato_versao	verpes
						where	verpes.pessoa_id = {$this->singlepack->get_pessoa_id()}
						and	verpes.cadastrado_para_jogar = 'S'
						and	verpes.campeonato_versao_id  = ver.id
						)
						and	ver.ativa = 'S'";
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Prepara dados para as páginas que exibem os chutes.
	 */
	public function _prep_show( $rodada_fase_id, $campeonato_versao_id, $pessoa_id = NULL, $data_inicio = NULL, $data_fim = NULL, $arena_id = NULL, $equipe_id = NULL, $controller = 'Chute', $campeonatos = 'T' )
	{
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

		//
		// Registra na sessão os valores usados.
		//
		$this->singlepack->set_sessao( 'tipo_calculo', $tipo_calculo );
		$this->singlepack->set_sessao( 'tipo_visual', $tipo_visual );
		$this->singlepack->set_sessao( 'grupo_id', $grupo_id );

		$where				=	"( ver.ativa in( 'S', 'A' ) )";
		
		// Se recebemos a data início indica que queremos todos os jogos de todos os campeonato e rodadas a partir desta data.
		if ( $data_inicio )
		{
			if ( $where )
			{
				$where		=	$where." and ( jogo.data_hora >= '$data_inicio' and jogo.data_hora <= '$data_fim' )";
			}
			else
			{
				$where		=	"( jogo.data_hora >= '$data_inicio' and jogo.data_hora <= '$data_fim' )";
			}
		}

		// Se foi informada a arena retornamos apenas jogos desta arena.
		if ( $arena_id )
		{
			if ( $where )
			{
				$where		=	$where." and ( jogo.arena_id = $arena_id )";
			}
			else
			{
				$where		=	"( jogo.arena_id = $arena_id )";
			}
		}

		// Se foi informada a equipe, buscamos apenas jogos dela.
		if ( $equipe_id )
		{
			if ( $where )
			{
				$where		=	$where." and ( jogo.equipe_id_casa = $equipe_id or jogo.equipe_id_visitante = $equipe_id )";
			}
			else
			{
				$where		=	"( jogo.equipe_id_casa = $equipe_id or jogo.equipe_id_visitante = $equipe_id )";
			}
		}
		
		// Determinamos os campeonatos que serão exibidos.
		if ( $campeonatos
		&&   $campeonatos == 'M'
		&&   $this->set_campeonatos_pessoa()
		   )
		{
			if ( $where )
			{
				$where		=	$where." and ( " . $this->set_campeonatos_pessoa() . ")";
			}
			else
			{
				$where		=	"(" . $this->set_campeonatos_pessoa() . ")";
			}
		}

		if ( $rodada_fase_id )
		{
			if ( $where )
			{
				$where		=	$where." and ( jogo.rodada_fase_id = ".$rodada_fase_id." )";
			}
			else
			{
				$where		=	"( jogo.rodada_fase_id = ".$rodada_fase_id." )";
			}
		}

		// Sem nenhum dos parametros principais.
		if ( !$rodada_fase_id // Sem rodada não podemos exibir nada.
		&&   !$data_inicio
		   )
		{
			if ( $where )
			{
				$where		=	$where." and ( 1 = 2 )";
			}
			else
			{
				$where		=	"( 1 = 2 )";
			}
		}
		
		// Executa a consulta.
		log_message( 'debug', "KICK_MODEL._prep_show pessoa=". $pessoa_id );
		$this->set_pessoa_id( $pessoa_id );
		if ( $controller == 'Chute' )
		{
			Chute::_prep_index ( 'kick', $where_external = $where, $orderby_external = $this->get_order_by(),        $set_parent = FALSE, $use_pagination = FALSE );
		}
		if ( $controller == 'Chute Crono' )
		{
			Chute::_prep_index ( 'kick', $where_external = $where, $orderby_external = $this->get_order_by_chrono(), $set_parent = FALSE, $use_pagination = FALSE );
		}
		if ( $controller == 'Painel' )
		{
			Painel::_prep_index( 'kick', $where_external = $where, $orderby_external = $this->get_order_by_chrono(), $set_parent = FALSE, $use_pagination = FALSE );
		}
		$rows							=	$this->load->get_var( 'rows' ); // pega as linhas carregadas no comando anterior.
		$total_rows						=	$this->load->get_var( 'total_rows' ); // pega as linhas carregadas no comando anterior.

		$rodadas_selecao					=	$this->rodada_fase->get_rodadas_selecao( $rodada_fase_id, 'KICK', $j = $campeonato_versao_id );
		$rodadas_open						=	array();
		$rodadas_jogo_adiado					=	array();
		
		// Define os IDs das rodadas que estão abertas.
		foreach( $rodadas_selecao as $rodada )
		{
			if ( $rodada->open )
			{
				$rodadas_open[ $rodada->id ]		=	TRUE;
			}
			if ( $rodada->adiada == 'S' )
			{
				$rodadas_jogo_adiado[ $rodada->id ]	=	TRUE;
			}
		}

		$rodada_atual						=	array();
		
		$data_agora										=	new DateTime( 'now' );
		$data_agora_mais_5									=	new DateTime( 'now' );
		$data_agora_mais_5->add( new DateInterval( 'P5D' ) );
		$data_agora_mais_1h									=	new DateTime( 'now' );
		$data_agora_mais_1h->add( new DateInterval( 'PT1H' ) );

		// Carrega as imagens das equipes.
		$images											=	array();
		$images_campeonato									=	array();
		$new_rows										=	array();
		$ar_rodadas										=	array();
		$rodada_atual_open									=	FALSE;
		foreach( $rows as $kick )
		{
			/*
			 * Cria um array de rodadas
			 */
			if ( !key_exists( $kick->rodada_fase_id, $ar_rodadas ) )
			{
				$rodada_atual				=	$this->rodada_fase->get_one_by_id( $kick->rodada_fase_id );

				$obj_rodada				=	new stdClass();
				$obj_rodada->rodada_fase_id		=	$kick->rodada_fase_id;
				$obj_rodada->cod_rodada_fase		=	$kick->cod_rodada_fase;
				$obj_rodada->qtde_chutes		=	0;
				$obj_rodada->qtde_chutes_feitos		=	0;
				$obj_rodada->zebras			=	array();
				$obj_rodada->rodada_anterior		=	$this->rodada_fase->get_rodada_anterior( $rodada_atual->data_inicio , $rodada_atual->campeonato_versao_id, $rodada_atual->tipo_fase );

				// Monta array com as equipes zebras.
				if ( is_object( $obj_rodada->rodada_anterior ) )
				{
					$equipes_zebra			=	$this->campeonato_versao_classificacao->get_all_by_where( "campeonato_versao_classificacao.rodada_fase_id = {$obj_rodada->rodada_anterior->id}", "campeonato_versao_classificacao.posicao DESC, campeonato_versao_classificacao.total_ponto DESC, campeonato_versao_classificacao.total_vitoria DESC, ( campeonato_versao_classificacao.gol_favor - campeonato_versao_classificacao.gol_contra ) DESC, campeonato_versao_classificacao.gol_favor DESC", 2 ); // Apenas os 2 últimos colocados.
				}
				else
				{
					$equipes_zebra			=	NULL;
				}
				if ( is_array( $equipes_zebra ) )
				{
					foreach( $equipes_zebra as $zebra )
					{
						$obj_rodada->zebras[ $zebra->equipe_id ]	=	$zebra;
					}
				}
				
				// Carrega os poderes liberados para a rodada da pessoa, se existirem.
				if ( $kick->rodada_fase_id
				&&   $this->singlepack->get_pessoa_id()
				   )
				{
					$powers_rod_base		=	$this->pessoa_rodada_fase_power->get_all_by_where( "pessoa_rodada_fase_id	in	(
																					select	pesrod.id
																					from	pessoa_rodada_fase pesrod
																					where	pesrod.rodada_fase_id = {$kick->rodada_fase_id}
																					and	pesrod.pessoa_id = {$this->singlepack->get_pessoa_id()}
																					)
																" );
				}
				else
				{
					$powers_rod_base		=	array();
				}
		
				if ( !$powers_rod_base
				&&   key_exists( $kick->rodada_fase_id, $rodadas_open ) // Libera apenas para rodada aberta.
				&&   $kick->rodada_fase_id
				&&   $this->singlepack->get_pessoa_id()
				   )
				{
					$this->pessoa_rodada_fase_power->libera_poderes( $kick->rodada_fase_id, $this->singlepack->get_pessoa_id() );
					$powers_rod_base		=	$this->pessoa_rodada_fase_power->get_all_by_where( "pessoa_rodada_fase_id	in	(
																					select	pesrod.id
																					from	pessoa_rodada_fase pesrod
																					where	pesrod.rodada_fase_id = {$kick->rodada_fase_id}
																					and	pesrod.pessoa_id = {$this->singlepack->get_pessoa_id()}
																					)
																" );
				}

				$ar_powers				=	array();
				foreach( $powers_rod_base as $row_power )
				{
					if ( $row_power->power_id == 1 // QQI
					||   $row_power->power_id == 2 // GURU
//					||   $row_power->power_id == 3 // DUELO
//					||   $row_power->power_id == 5 // ESPIAO
					||   $row_power->power_id == 6 // BARBADA
					||   $row_power->power_id == 7 // ZEBRA
					   )
					{
						$power				= new stdClass();
						$power->nome			= $row_power->nome_power;
						$power->id			= $row_power->id;
						$power->pessoa_rodada_fase_id	= $row_power->pessoa_rodada_fase_id;
						$power->descr			= $row_power->descr_power;
						$power->cod			= $row_power->cod_power;
						$power->css_class		= $row_power->css_class;
						$power->power_id		= $row_power->power_id;
						$power->qtde_liberado		= $row_power->qtde_liberado;
						$power->qtde_usada		= $row_power->qtde_usada;
						$ar_powers[]			= $power;
						unset( $power );
					}
				}

				$obj_rodada->powers			=	$ar_powers;

				$ar_rodadas[ $kick->rodada_fase_id ]	=	$obj_rodada;
			}
			// Registra os chutes feitos.
			$ar_rodadas[ $kick->rodada_fase_id ]->qtde_chutes			+=	2;
			if ( !is_null( $kick->kick_casa ) )
			{
				$ar_rodadas[ $kick->rodada_fase_id ]->qtde_chutes_feitos	+=	1;
			}
			if ( !is_null( $kick->kick_visitante ) )
			{
				$ar_rodadas[ $kick->rodada_fase_id ]->qtde_chutes_feitos	+=	1;
			}

			if ( $kick->equipe_id_casa )
			{
				foreach( $this->equipe->select_one( 'equipe.id = '.$kick->equipe_id_casa )->result_object() as $eqp_imagem )
				{
					$images[ $kick->equipe_id_casa ]				=	$this->imagem->get_file_name( $eqp_imagem->imagem_id, TRUE );
				}
			}
			if ( $kick->equipe_id_visitante )
			{
				foreach( $this->equipe->select_one( 'equipe.id = '.$kick->equipe_id_visitante )->result_object() as $eqp_imagem )
				{
					$images[ $kick->equipe_id_visitante ]				=	$this->imagem->get_file_name( $eqp_imagem->imagem_id, TRUE );
				}
			}
			if ( $kick->campeonato_versao_imagem_id )
			{
				if ( !key_exists( $kick->campeonato_versao_imagem_id, $images_campeonato ) )
				{
					$images_campeonato[ $kick->campeonato_versao_id ]	=	$this->imagem->get_file_name( $kick->campeonato_versao_imagem_id, TRUE );
				}
			}
			
			$row					=	$kick;
			$data_jogo				=	DateTime::createFromFormat( 'Y-m-d H:i:s', $row->data_hora_jogo );
			
			// Abre os jogos que ainda não iniciaram mesmo que a rodada esteja indicada como fechada.
			$row->open				=	(  ( key_exists( $row->rodada_fase_id, $rodadas_open )
									||   key_exists( $row->rodada_fase_id, $rodadas_jogo_adiado )
									   )
									&& mysql_to_unix( $row->data_hora_jogo ) >= now()
									// Jogo sem time ainda.
									&& ( $row->equipe_id_casa != 223
									&&   $row->equipe_id_visitante != 223
									   )
									) ? TRUE : FALSE;
			// Regra para jogo aberto ou não para chute cronológico.
			$row->open_crono			=	(  $data_jogo >= $data_agora
									&& $data_jogo <= $data_agora_mais_5
									// Jogo sem time ainda.
									&& ( $row->equipe_id_casa != 223
									&&   $row->equipe_id_visitante != 223
									   )
									) ? TRUE : FALSE;
									
			// Acrescenta os poderes da linhas
			if ( $kick->id )
			{
				$row->powers			=	$this->kick_power->get_all_by_where( "kick_power.kick_id = {$kick->id}" );
			}
			else
			{
				$row->powers			=	array();
			}
			
			// Zera os pontos, caso eles venham nulo.
			if ( !$row->pontos_kick )
			{
				$row->pontos_kick		=	0;
			}
			if ( !$row->pontos_gols )
			{
				$row->pontos_gols		=	0;
			}
			if ( !$row->pontos_power )
			{
				$row->pontos_power		=	0;
			}
			
			// Formata a data para ser exibida no painel.
			// Dia da semana
			switch ( $data_jogo->format( 'w' ) )
				{
					case 0:
					$row->dia_da_semana_jogo	=	'Domingo';
					$row->dia_da_semana_jogo_curto	=	'Dom';
					break;

					case 1:
					$row->dia_da_semana_jogo	=	'Segunda-feira';
					$row->dia_da_semana_jogo_curto	=	'Seg';
					break;

					case 2:
					$row->dia_da_semana_jogo	=	'Terça-feira';
					$row->dia_da_semana_jogo_curto	=	'Ter';
					break;

					case 3:
					$row->dia_da_semana_jogo	=	'Quarta-feira';
					$row->dia_da_semana_jogo_curto	=	'Qua';
					break;

					case 4:
					$row->dia_da_semana_jogo	=	'Quinta-feira';
					$row->dia_da_semana_jogo_curto	=	'Qui';
					break;

					case 5:
					$row->dia_da_semana_jogo	=	'Sexta-feira';
					$row->dia_da_semana_jogo_curto	=	'Sex';
					break;

					case 6:
					$row->dia_da_semana_jogo	=	'Sábado';
					$row->dia_da_semana_jogo_curto	=	'Sab';
					break;

					default:
					$row->dia_da_semana_jogo	=	'None';
					$row->dia_da_semana_jogo_curto	=	'Non';
					break;
				}

			// Data por extenso
			$row->dia_do_jogo				=	$data_jogo->format( 'j' );
			$row->data_jogo_extenso_curto			=	' de ';
			switch ( $data_jogo->format( 'm' ) )
				{
					case 1:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'janeiro';
					break;

					case 2:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'fevereiro';
					break;

					case 3:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'março';
					break;

					case 4:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'abril';
					break;

					case 5:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'maio';
					break;

					case 6:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'junho';
					break;

					case 7:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'julho';
					break;

					case 8:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'agosto';
					break;

					case 9:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'setembro';
					break;

					case 10:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'outubro';
					break;

					case 11:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'novembro';
					break;

					case 12:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'dezembro';
					break;
					
					default:
					$row->data_jogo_extenso_curto	=	$row->data_jogo_extenso_curto . 'None';
					break;
				}
			$row->data_jogo_extenso_curto			=	$row->data_jogo_extenso_curto . ' de ' . $data_jogo->format( 'Y' );
			$row->data_jogo_extenso				=	$data_jogo->format( 'j' ) . $row->data_jogo_extenso_curto;

			// Data do jogo
			$row->data_jogo					=	$data_jogo;
			$row->data_jogo_id				=	$data_jogo->format( 'Y-m-d' );
			
			// Hora do jogo
			$row->hora_jogo					=	$data_jogo->format( 'H:i' );
			
			// Nível de aviso
			$row->css_hora_jogo				=	'cl'; // Fechado
			if ( !is_null( $row->kick_casa )
			&&   !is_null( $row->kick_visitante )
			   )
			{
				if ( $data_jogo < $data_agora )
				{
					$row->css_hora_jogo			=	'sta ok'; // Chutes feitos anterior
				}
				else
				{
					$row->css_hora_jogo			=	'ok'; // Chutes feitos
				}
			}
			elseif ( !$row->open_crono
			&&       $data_jogo < $data_agora
			       )
			{
				if ( ( !is_null( $row->kick_casa )
				&&     is_null( $row->kick_visitante )
				     )
				||   ( is_null( $row->kick_casa )
				&&     !is_null( $row->kick_visitante )
				     )
				   )
				{
					$row->css_hora_jogo		=	'sta wn'; // Fechado e 1/2 chute
				}
				else
				{
					$row->css_hora_jogo		=	'sta dg'; // Fechado e sem chute algum
				}
			}
			elseif ( $row->open_crono )
			{
				if ( $data_jogo >= $data_agora
				&&   $data_jogo <= $data_agora_mais_1h
				   )
				{
					$row->css_hora_jogo		=	'dg'; // Sem Chutes, menos de uma hora.
				}
				elseif ( $data_jogo > $data_agora )
				{
					$row->css_hora_jogo		=	'wn'; // Sem Chutes, qualquer tempo.
				}
			}
			
			// Se quem está executando a consulta é uma pessoa diferente da "dona" dos chutes, só mostramos o que já se iniciou.
			if ( ( !$pessoa_id
			||     ( is_object( $this->singlepack->get_user_info() )
			&&       $pessoa_id == $this->singlepack->get_pessoa_id()
			       )
			     )
			||   mysql_to_unix( $row->data_hora_jogo ) <= now()
			)
			{
				$new_rows[]			=	$row;
			}
			
			// Verificamos se devemos ou não determinar que há uma rodada aberta na lista de jogos.
			if ( !$rodada_atual_open )
			{
				if ( ( $controller == 'Chute Crono'
				&&     $row->open_crono
				     )
				||   key_exists( $kick->rodada_fase_id, $rodadas_open )
				   )
				{
					$rodada_atual_open	=	TRUE;
				}
			}
			unset( $row );
		}

		$data				= array	(
							// Obtém as linhas que serão exibidas.
							 'rows_chutes'			=> $new_rows
							,'rows_campeonato'		=> $this->campeonato_versao->get_campeonato_selecao()
							,'rodada_atual'			=> $rodada_atual
							,'rows_rodada'			=> $rodadas_selecao
							,'rows_rodada_jogo'		=> $ar_rodadas
							,'rodada_anterior'		=> $this->rodada_fase->get_rodada_selecao_anterior()
							,'rodada_posterior'		=> $this->rodada_fase->get_rodada_selecao_posterior()
							,'master_table'			=> 'kick'
							,'images'			=> $images
							,'images_campeonato'		=> $images_campeonato
							,'tipo_visual'			=> 'amigos'
							,'tipo_calculo'			=> 'rodada'
							,'grupo_id'			=> $grupo_id
							,'total_rows_chutes'		=> $total_rows
							,'campeonato_versao_atual'	=> $this->campeonato_versao->get_one_by_id( $campeonato_versao_id )
							,'kiker_info'			=> $this->kiker_info()
							,'calendario_campeonatos'	=> $campeonatos
							,'rodada_atual_open'		=> $rodada_atual_open
							);
		$this->load->vars( $data );
	}

	/**
	 * Recupera os dados especiais do Kiker.
	 */
	public function kiker_info()
	{
		$ret							=	new stdClass();
		$pessoa_id						=	$this->singlepack->get_pessoa_id();

		// Retorna o saldo atual.
		if ( $pessoa_id )
		{
			$sld_base					=	$this->kik_saldo->get_one_by_where( "pessoa_id = " . $pessoa_id );
			
			if ( $sld_base )
			{
				$ret->saldo				=	$sld_base;
			}
			else
			{
				$ret->saldo				=	NULL;
			}
		}
		
		return $ret;
	}

	/**
	 * Cálculo dos pontos dos chutes.
	 */
	protected function libera_memoria()
	{
		unset( $this->pessoa_ranking_grupo );
		unset( $this->pessoa_rodada_update );
		unset( $this->pessoa_campeonato_update );
		unset( $this->last_pessoa_rodada_fase_id );
	
		unset( $this->ar_kick_power );
		unset( $this->ar_powers_rodada_seguinte );
		unset( $this->ar_powers_rodada_atual );
		$this->ar_kick_power			=	array();
		$this->ar_powers_rodada_seguinte	=	array();
		$this->ar_powers_rodada_atual		=	array();

		unset( $this->kick_update );
		unset( $this->pessoa_best_rodada );
		unset( $this->pessoa_best_rodada_grupo );
		unset( $this->ar_pessoa_best_rodada_grupo );
		unset( $this->qry_powers );
		unset( $this->pessoa_rodada_power_new );
		unset( $this->pessoa_rodada_seguinte );
		unset( $this->kick_guru_power_update );
		unset( $this->kick_barbada_power_update );
		unset( $this->kick_zebra_power_update );
		unset( $this->kick_tjunto_power_update );
		unset( $this->kick_duelo_power_update );
		unset( $this->kick_espiao_power_update );
	}

	/*
	 * Verifica se a pessoa errou tudo.
	 */
	protected function calcula_qqi()
	{
		/**
		 * Verifica o "Que qué isso".
		 */
//echo '(QQI) PESSOA_ID='.$this->pessoa_campeonato_update->pessoa_id . " QQI chutes={$this->pessoa_rodada_update->qtde_jogos_com_chute} == ( {$this->pessoa_rodada_update->qtde_errou_tudo} + {$this->pessoa_rodada_update->qtde_acertou_apenas_gol_1_equipe} )\n";
		if ( $this->show_log ) echo "QQI Calculo <br/>\n";
		if ( $this->pessoa_rodada_update->qtde_jogos_com_chute > 0
		&&   $this->pessoa_rodada_update->qtde_jogos_com_chute == ( $this->pessoa_rodada_update->qtde_errou_tudo + $this->pessoa_rodada_update->qtde_acertou_apenas_gol_1_equipe )
		&&   key_exists( QQI, $this->ar_powers_rodada_atual )
		   )
		{
			$this->ar_powers_rodada_atual[ QQI ]->pontos			=	( $this->pessoa_rodada_update->qtde_jogos_com_chute * $this->peso_vitoria ) / 2;
			$this->add_value( 'pontos_power', $this->ar_powers_rodada_atual[ QQI ]->pontos, QQI, 1 );
			if ( $this->show_log ) echo "QQI Calculo pontos({$this->ar_powers_rodada_atual[ QQI ]->pontos}) <br/>\n";
		}

		return TRUE;
	}
	
	/*
	 * Seleciona a melhor rodada fase da pessoa no campeonato.
	 */
	protected function define_melhor_rodada_campeonato()
	{
		$rodada_fase_id_best			=	( !$this->pessoa_best_rodada->id ) ? $this->last_pessoa_rodada_fase_id : $this->pessoa_best_rodada->id;

		if ( ( $this->pessoa_rodada_update->pontos_gols + $this->pessoa_rodada_update->pontos_kick + $this->pessoa_rodada_update->pontos_power ) > $this->pessoa_best_rodada->total_pontos )
		{
			$rodada_fase_id_best		=	$this->last_pessoa_rodada_fase_id;
		}

		$this->set_value_campeonato_versao( 'pessoa_rodada_fase_id', $rodada_fase_id_best );

		return TRUE;
	}

	/*
	 * Salva os dados da pessoa.
	 */
	protected function salvar_pessoa()
	{
		if ( isset( $this->pessoa_rodada_update )
		&&   is_object( $this->pessoa_rodada_update )
		   )
		{
			log_message( 'debug', "... salvando Pessoa=" .$this->pessoa_rodada_update->pessoa_id );
	
			$this->calcula_qqi();
	
			$this->salvar_pessoa_rodada();
			$this->salvar_pessoa_campeonato();
			$this->salvar_ranking_grupo();
	
			if ( $this->show_log ) { echo '(salvou) PESSOA_ID='.$this->pessoa_campeonato_update->pessoa_id.
						' best_ant='. $this->pessoa_best_rodada->total_pontos.
						' best atu='. ( $this->pessoa_best_rodada->pontos_gols + $this->pessoa_best_rodada->pontos_kick + $this->pessoa_rodada_update->pontos_power ).
						'<br/>\n'; }
		}		
	
		// Finaliza o controle de transação de Base de Dados.
		$this->db->trans_complete();
		$this->db->flush_cache();
		
//		$this->libera_memoria();
//echo "pessoa salva $this->count_pessoa\n";
		
		//if ( $this->show_log )
		{
			$memoria_diff		=	memory_get_usage(true) - $this->memoria_usada;
			if ( $memoria_diff )
			{
				echo "memória ($this->count_pessoa \/ ".($this->count_pessoa - $this->count_pessoa_ant)." )=" . round(((memory_get_usage(true) / 1024) / 1024), 2) . 'Mb diff='. round((($memoria_diff / 1024) / 1024), 2) . "<br/>\n";
				$this->count_pessoa_ant	=	$this->count_pessoa;
			}
			$this->memoria_usada	=	memory_get_usage(true);
		}

		// Libera a pessoa para evitar erro no recalculo.
		unset( $this->pessoa_rodada_update );
		
		return TRUE;
	}

	protected function salvar_pessoa_rodada()
	{
		// Acumula quantidade de rodadas para o campeonato.
		if ( $this->pessoa_rodada_update->jogou_rodada == 'N'
		&&   $this->pessoa_rodada_update->qtde_jogos_com_chute > 0
		   )
		{
			$this->pessoa_rodada_update->jogou_rodada	=	'S';
			$this->set_value_campeonato_versao( 'qtde_rodada_jogada', 1, FALSE );
			$this->set_value_ranking_grupo( 'qtde_rodada_jogada', 1 );
		}

		$this->pessoa_rodada_update->id		=	$this->pessoa_rodada_fase->update( $this->pessoa_rodada_update );
		$this->last_pessoa_rodada_fase_id	=	$this->pessoa_rodada_update->id;

		// Salva os resumos dos poderes.
		if ( isset( $this->pessoa_rodada_update->ar_resumo_poder ) )
		{
			foreach( $this->pessoa_rodada_update->ar_resumo_poder as $resumo )
			{
				$this->pessoa_rodada_fase_resumo_power->update( $resumo );
			}
		}

		// Salva poderes disponíveis da rodada atual.
		foreach( $this->ar_powers_rodada_atual as $power_key => $power_data )
		{
			if ( $this->show_log ) echo "_____ATUAL {$this->ar_powers_rodada_atual[ $power_key ]->power_id} liberado={$this->ar_powers_rodada_atual[ $power_key ]->qtde_liberado} usada={$this->ar_powers_rodada_atual[ $power_key ]->qtde_usada} <br/>\n";

			if ( $this->rodada_base->finalizada
			&&   ( $power_key != QQI // Poderes automáticos não são usados aqui. Eles não são acumulados de uma roadada para outras.
			&&     $power_key != ZEBRA
			     )
			   ) // A rodada atual estando fechada, passamos os poderes não usados para a rodada seguinte.
			{
				if ( $this->ar_powers_rodada_atual[ $power_key ]->qtde_liberado > $this->ar_powers_rodada_atual[ $power_key ]->qtde_usada
				&&   key_exists( $power_key, $this->ar_powers_rodada_seguinte )
				   )
				{
					if ( $this->acumular_poderes )
					{
						$this->ar_powers_rodada_seguinte[ $power_key ]->qtde_liberado		+=	$this->ar_powers_rodada_atual[ $power_key ]->qtde_liberado - $this->ar_powers_rodada_atual[ $power_key ]->qtde_usada;
					
						if ( ( $power_key == GURU
						||     $power_key == BARBADA
						||     $power_key == TJUNTO
						||     $power_key == DUELO
						||     $power_key == ESPIAO
						     )
						&&   $this->ar_powers_rodada_seguinte[ $power_key ]->qtde_liberado > $this->limite_acumulo_poder // LIMITE DE ACUMULO
						   )
						{
							$this->ar_powers_rodada_seguinte[ $power_key ]->qtde_liberado	=	$this->limite_acumulo_poder;
						}
					}
				}
			}

			$this->pessoa_rodada_fase_power->update( $power_data );
		}
		
		// Salva poderes disponíveis da rodada seguinte.
		foreach( $this->ar_powers_rodada_seguinte as $power_key => $power_data )
		{
			$this->pessoa_rodada_fase_power->update( $power_data );
		}
		
		return TRUE;
	}

	protected function salvar_pessoa_campeonato()
	{
		$this->define_melhor_rodada_campeonato();

		$this->pessoa_campeonato_versao->update( $this->pessoa_campeonato_update );
		if ( isset( $this->pessoa_campeonato_update->ar_resumo_poder ) )
		{
			foreach( $this->pessoa_campeonato_update->ar_resumo_poder as $resumo )
			{
				$this->pessoa_campeonato_versao_resumo_power->update( $resumo );
			}
		}

		return TRUE;
	}

	protected function salvar_ranking_grupo()
	{
		foreach( $this->pessoa_ranking_grupo as $key_grupo => $ranking_grupo )
		{
			// Define a melhor rodada para o grupo.
			$this->pessoa_best_rodada_grupo			=	$this->ar_pessoa_best_rodada_grupo[ $key_grupo ];// Pega a melhor rodada para o grupo atual.
			$ranking_grupo->pessoa_rodada_fase_id		=	( !$this->pessoa_best_rodada_grupo->id ) ? $this->last_pessoa_rodada_fase_id : $this->pessoa_best_rodada_grupo->id;
			
			if ( ( $this->pessoa_rodada_update->pontos_gols + $this->pessoa_rodada_update->pontos_kick + $this->pessoa_rodada_update->pontos_power ) > $this->pessoa_best_rodada_grupo->total_pontos )
			{
				$ranking_grupo->pessoa_rodada_fase_id	=	$this->last_pessoa_rodada_fase_id;
			}

			$this->pessoa_ranking_grupo_amigos->update( $ranking_grupo );

			if ( isset( $ranking_grupo->ar_resumo_poder ) )
			{
				foreach( $ranking_grupo->ar_resumo_poder as $resumo )
				{
					$this->pessoa_ranking_grupo_amigos_resumo_power->update( $resumo );
				}
			}
		}

		return TRUE;
	}

	protected function salvar_kick()
	{
		log_message( 'debug', '... salvando Chute='.$this->kick_update->pessoa_id.' Acerto='.$this->kick_update->acerto.' pontos='.$this->kick_update->pontos_kick.' jogo='.$this->kick_update->jogo_id.' kicks='.$this->kick_update->kick_casa.' vis='.$this->kick_update->kick_visitante );
		$this->update( $this->kick_update );

		// Salva todos os poderes.
		foreach( $this->ar_kick_power as $power_data )
		{
			// Se algum poder automático ficou zerado em pontos, então devemos eliminá-lo da base de dados.
			if ( $power_data->pontos == 0
			&&   $power_data->id // indica que o poder foi gravado anteriormente ou não. Null nunca foi.
			&&   $power_data->processado_power == 'A' // Normalmente apenas QQI e ZEBRA
			   )
			{
				$power_data->id			=	$power_data->id * (-1); // ID negativo passa ao model a informação de deletar a linha.
			}
			$this->kick_power->update( $power_data );
		}

		return TRUE;
	}

	/*
	 * Atualiza os arrays de controle de pontos de campeonato, rodada fase e grupos.
	 */
	protected function add_value_rodada_fase( $column, $value )
	{
		$this->set_value_rodada_fase( $column, $value, $force = FALSE );

		return TRUE;
	}
	protected function set_value_rodada_fase( $column, $value, $force = TRUE )
	{
		if ( $value > 0
		||   ( $value < 0 // menos que zero é estorno e só estornamos quando a pessoa tenha jogado a rodada.
		&&     $this->pessoa_rodada_update->jogou_rodada == 'S'
		     )
		   )
		{
			if ( $force )
			{
				$this->pessoa_rodada_update->$column	=	$value;
			}
			elseif ( $column == 'pontos_power' )
			{
				$this->pessoa_rodada_update->$column	=	$this->pessoa_rodada_update->$column + $value;
			}
			else
			{
				$this->pessoa_rodada_update->$column	=	( ( $this->pessoa_rodada_update->$column + $value ) < 0 ) ? 0 : $this->pessoa_rodada_update->$column + $value;
			}
			if ( $this->show_log ) echo " rodada=".$this->pessoa_rodada_update->$column;
		}

		return TRUE;
	}
	protected function add_value_campeonato_versao( $column, $value )
	{
		$this->set_value_campeonato_versao( $column, $value, $force = FALSE );

		return TRUE;
	}
	protected function set_value_campeonato_versao( $column, $value, $force = TRUE )
	{
		if ( $value > 0
		||   ( $value < 0 // menos que zero é estorno e só estornamos quando o campeonato já tinha sido calculado. ID diferente de NULL.
		&&     $this->pessoa_campeonato_update->id !== NULL
		     )
		   )
		{
			if ( $force )
			{
				$this->pessoa_campeonato_update->$column	=	$value;
			}
			elseif ( $column == 'pontos_power' )
			{
				$this->pessoa_campeonato_update->$column	=	$this->pessoa_campeonato_update->$column + $value;
			}
			else
			{
				$this->pessoa_campeonato_update->$column	=	( ( $this->pessoa_campeonato_update->$column + $value ) < 0 ) ? 0 : $this->pessoa_campeonato_update->$column + $value;
			}
			if ( $this->show_log ) echo " campeonato=".$this->pessoa_campeonato_update->$column;
		}

		return TRUE;
	}
	protected function add_value_ranking_grupo( $column, $value )
	{
		$this->set_value_ranking_grupo( $column, $value );

		return TRUE;
	}
	protected function set_value_ranking_grupo( $column, $value )
	{
		if ( is_array( $this->pessoa_ranking_grupo ) )
		{
			foreach( $this->pessoa_ranking_grupo as $key_grupo => $ranking_grupo )
			{
				if ( $value > 0
				||   ( $value < 0 // menos que zero é estorno e só estornamos quando o grupo já tinha sido calculado. ID diferente de NULL.
				&&     $this->pessoa_ranking_grupo[ $key_grupo ]->id !== NULL
				     )
				   )
				{
					if ( $column == 'pontos_power' )
					{
						if ( $this->pessoa_ranking_grupo[ $key_grupo ]->usar_poderes == 'S' ) // O grupo usa ou não os poderes. Se sim, soma, se não, vamos embora.
						{
							$this->pessoa_ranking_grupo[ $key_grupo ]->$column	=	$this->pessoa_ranking_grupo[ $key_grupo ]->$column + $value;
						}
					}
					else
					{
						$this->pessoa_ranking_grupo[ $key_grupo ]->$column	=	( ( $this->pessoa_ranking_grupo[ $key_grupo ]->$column + $value ) < 0 ) ? 0 : $this->pessoa_ranking_grupo[ $key_grupo ]->$column + $value;
					}
					if ( $this->show_log ) echo " grupo($key_grupo)=".$this->pessoa_ranking_grupo[ $key_grupo ]->$column;
				}
			}
		}

		return TRUE;
	}
	protected function add_value( $column, $value, $power = NULL, $qtde_power = NULL )
	{
if ( $this->show_log ) echo "_____add_value column=$column value=$value ficou(";
		$this->add_value_rodada_fase( $column, $value );
		$this->add_value_campeonato_versao( $column, $value );
		$this->add_value_ranking_grupo( $column, $value );
if ( $this->show_log ) echo ")<br/>\n";
		
		// Atualiza o resumo de poderes
		if ( $power
		&&   $column == 'pontos_power'
		   )
		{
			if ( $this->show_log ) echo "+------------poder=$power valor=$value qtde=$qtde_power ficou( ";
			$this->set_poder_rodada_fase       ( $column, $value, $power, $qtde_power );
			$this->set_poder_campeonato_versao ( $column, $value, $power, $qtde_power );
			$this->set_poder_ranking_grupo     ( $column, $value, $power, $qtde_power );
			if ( $this->show_log ) echo " )<br/>\n";
		}

		return TRUE;
	}
	protected function set_poder_rodada_fase( $column, $pontos, $power, $qtde_power )
	{
		if ( isset( $this->pessoa_rodada_update->ar_resumo_poder ) )
		{
			if ( key_exists( $power, $this->pessoa_rodada_update->ar_resumo_poder ) )
			{
				if ( $qtde_power > 0 // Novo Poder
				||   ( $qtde_power < 0 // Estorno
				&&     $this->pessoa_rodada_update->ar_resumo_poder[ $power ]->id // Só estornamos se a linha já existia.
				     )
				   )
				{
					$this->pessoa_rodada_update->ar_resumo_poder[ $power ]->qtde	=	( ( $this->pessoa_rodada_update->ar_resumo_poder[ $power ]->qtde + $qtde_power ) < 0 ) ? 0 : ( $this->pessoa_rodada_update->ar_resumo_poder[ $power ]->qtde + $qtde_power );
					$this->pessoa_rodada_update->ar_resumo_poder[ $power ]->pontos	+=	$pontos;
				}
			}
			else
			{
				if ( $qtde_power > 0 ) // Só inserimos um novo poder no array se qtde for maior que 0, senão é estorno.
				{
					$new_power						=	new stdClass();
					$new_power->id						=	NULL;
					$new_power->pessoa_rodada_fase_id			=	$this->pessoa_rodada_update->id;
					$new_power->power_id					=	$power;
					$new_power->qtde					=	1;
					$new_power->pontos					=	$pontos;

					$this->pessoa_rodada_update->ar_resumo_poder[ $power ]	=	$new_power;
					unset( $new_power );
				}
			}

			if ( key_exists( $power, $this->pessoa_rodada_update->ar_resumo_poder ) )
			{
				if ( $this->show_log ) echo " rodada [qtde=".$this->pessoa_rodada_update->ar_resumo_poder[ $power ]->qtde." pontos=".$this->pessoa_rodada_update->ar_resumo_poder[ $power ]->pontos."] ";
			}
		}

		// As quantidades dos poderes automáticos são contraladas no cálculo, então as mantemos com o código abaixo.
		// Já os poderes manuais as quantidades são controladas pela página JavaScript.
		if ( ( $power == QQI
		||     ( $power == ZEBRA
		&&       $this->calcular_zebra
		       )
		     )
		&&   ( is_array( $this->ar_powers_rodada_atual )
		&&     key_exists( $power, $this->ar_powers_rodada_atual )
		     )
		&&   ( $qtde_power > 0 // Novo Poder
		||     ( $qtde_power < 0 // Estorno
		&&       $this->ar_powers_rodada_atual[ $power ]->id // Só estornamos se a linha já existia.
		       )
		     )
		   )
		{
			$this->ar_powers_rodada_atual[ $power ]->qtde_usada	+=	$qtde_power;
			if ( $this->show_log ) echo " (ajuste AUTO) rodada [qtde=".$this->ar_powers_rodada_atual[ $power ]->qtde_usada."] ";
		}

		return TRUE;
	}

	protected function set_poder_campeonato_versao( $column, $pontos, $power, $qtde_power )
	{
		if ( isset( $this->pessoa_campeonato_update->ar_resumo_poder ) )
		{
			if ( key_exists( $power, $this->pessoa_campeonato_update->ar_resumo_poder ) )
			{
				if ( $qtde_power > 0 // Novo poder
				||   ( $qtde_power < 0 // Estorno
				&&     $this->pessoa_campeonato_update->ar_resumo_poder[ $power ]->id // Só estornamos se a linha já existia.
				     )
				   )
				{
					$this->pessoa_campeonato_update->ar_resumo_poder[ $power ]->qtde	=	( ( $this->pessoa_campeonato_update->ar_resumo_poder[ $power ]->qtde + $qtde_power ) < 0 ) ? 0 : ( $this->pessoa_campeonato_update->ar_resumo_poder[ $power ]->qtde + $qtde_power );
					$this->pessoa_campeonato_update->ar_resumo_poder[ $power ]->pontos	+=	$pontos;
				}
			}
			else
			{
				if ( $qtde_power > 0 ) // Só inserimos um novo poder no array se qtde for maior que 0, senão é estorno.
				{
					$new_power							=	new stdClass();
					$new_power->id							=	NULL;
					$new_power->pessoa_campeonato_versao_id				=	$this->pessoa_campeonato_update->id;
					$new_power->power_id						=	$power;
					$new_power->qtde						=	1;
					$new_power->pontos						=	$pontos;

					$this->pessoa_campeonato_update->ar_resumo_poder[ $power ]	=	$new_power;
					unset( $new_power );
				}
			}
			if ( key_exists( $power, $this->pessoa_campeonato_update->ar_resumo_poder ) )
			{
				if ( $this->show_log ) echo " campeonato [qtde=".$this->pessoa_campeonato_update->ar_resumo_poder[ $power ]->qtde." pontos=".$this->pessoa_campeonato_update->ar_resumo_poder[ $power ]->pontos."] ";
			}
		}

		return TRUE;
	}

	protected function set_poder_ranking_grupo( $column, $pontos, $power, $qtde_power )
	{
		if ( is_array( $this->pessoa_ranking_grupo ) )
		{
			foreach( $this->pessoa_ranking_grupo as $key_grupo => $ranking_grupo )
			{
				if ( isset( $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder )
				&&   $this->pessoa_ranking_grupo[ $key_grupo ]->usar_poderes == 'S'
				   )
				{
					if ( key_exists( $power, $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder ) )
					{
						if ( $qtde_power > 0 // Novo poder
						||   ( $qtde_power < 0 // Estorno
						&&     $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ $power ]->id // Só estornamos se a linha já existia.
						     )
						   )
						{
							$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ $power ]->qtde	=	( ( $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ $power ]->qtde + $qtde_power ) < 0 ) ? 0 : ( $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ $power ]->qtde + $qtde_power );
							$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ $power ]->pontos	+=	$pontos;
						}
					}
					else
					{
						if ( $qtde_power > 0 ) // Só inserimos um novo poder no array se qtde for maior que 0, senão é estorno.
						{
							$new_power									=	new stdClass();
							$new_power->id									=	NULL;
							$new_power->pessoa_ranking_grupo_amigos_id					=	$this->pessoa_ranking_grupo[ $key_grupo ]->id;
							$new_power->power_id								=	$power;
							$new_power->qtde								=	1;
							$new_power->pontos								=	$pontos;
		
							$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ $power ]		=	$new_power;
							unset( $new_power );
						}
					}
					if ( key_exists( $power, $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder ) )
					{
						if ( $this->show_log ) echo " grupo($key_grupo) [qtde=".$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ $power ]->qtde." pontos=".$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ $power ]->pontos."] ";
					}
				}
			}
		}

		return TRUE;
	}
	
	/**
	 * Retorna as zebras da rodada anterior.
	 */
	public function get_zebras()
	{
		$this->ar_equipes_zebra					=	array();
		if ( $this->calcular_zebra )
		{
			$this->query_zebras	=	$this->db->query	(
										"
										select	clas.*
										from	campeonato_versao_classificacao			AS	clas
										where  	clas.rodada_fase_id = {$this->rodada_anterior->id}
										order by clas.posicao DESC, clas.total_ponto DESC, clas.total_vitoria DESC, ( clas.gol_favor - clas.gol_contra ) DESC, clas.gol_favor DESC
										"
										);
	
			$count_zebra					=	0;
			foreach( $this->query_zebras->result_object() as $zebra )
			{
				if ( $this->show_log ) echo "ZEBRA={$zebra->equipe_id}<br/>\n";
	
				$this->ar_equipes_zebra[ $zebra->equipe_id ]	=	$zebra;
	
				$count_zebra					=	$count_zebra + 1;
				if ( $count_zebra >= 2 )
				{
					break;
				}
			}
	
			$this->query_zebras->free_result();
		}

		return TRUE;
	}

	/**
	 * 
	 * Calcula o ranking das pessoas do KIKBOOK.
	 * 
	 * @param INT		$rodada_fase_id
	 * @param BOOLEAN	$show_log
	 * @param INT		$pessoa_id
	 * @param BOOLEAN	$calcular_power
	 * 
		///Applications/MAMP/bin/php5.3/bin/php /Work/Projeto/Bolao/trunk/site/public_html/index.php /integracao/campeonato_versao/9/false/true
	 */
	public function calcular_kicks( $rodada_fase_id, $show_log = FALSE, $pessoa_id = NULL, $calcular_power = FALSE )
	{
		/*
			Sequencia do processo.

			01 - Seleciona os dados da rodada a ser calculada.

			02 - Busca a rodada anterior.
				a) Pega os times zebras da rodada anterior.

			03 - Pega a rodada seguinte.
		*/
		$this->show_log			=	$show_log;
		$this->calcular_power		=	$calcular_power;
		
		$this->kick->set_cons_disable( 'kick_ck_1' );

		log_message( 'debug', "iniciando calcular_kiks((($rodada_fase_id)))" );
		if ( $this->show_log ) echo "iniciando calcular_kiks((($rodada_fase_id)))<br/>\n";

		// A Zebra só pode ser calculada a partir da rodada 28 do brasileirão 2012.
		if ( $rodada_fase_id > 27 )
		{
			$this->calcular_zebra	=	TRUE;
		}
		else
		{
			$this->calcular_zebra	=	FALSE;
		}

		// Os poderes só podem ser acumulados a partir da rodada 27 do brasileirão 2012.
		if ( $rodada_fase_id >= 27 )
		{
			$this->acumular_poderes	=	TRUE;
		}
		else
		{
			$this->acumular_poderes	=	FALSE;
		}
		
		/*
		 * --- 01 --- Seleciona os dados da rodada do jogo.
		 * 
		 */
		$this->rodada_base				=	$this->rodada_fase->get_one_by_id( $rodada_fase_id );
		if ( is_object( $this->rodada_base )
		&&   isset( $this->rodada_base->campeonato_versao_id )
		   )
		{
			$campeonato_versao_id		=	$this->rodada_base->campeonato_versao_id;
			$campeonato_versao		=	$this->campeonato_versao->get_one_by_id( $campeonato_versao_id );
			$campeonato_id			=	$campeonato_versao->campeonato_id;
			
			// Registra se a rodada está ou não encerrada.
			$jogos_nao_encerrado_base	=	$this->jogo->get_all_by_where	(	"jogo.rodada_fase_id				=	{$this->rodada_base->id}
												and	( ( jogo.data_hora + interval 120 minute )	>=	now()
												or	  jogo.resultado_casa				IS NULL
												or	  jogo.resultado_visitante			IS NULL
													)
													"
												);
			if ( count( $jogos_nao_encerrado_base ) > 0 )
			{
				$this->rodada_base->finalizada	=	FALSE;
			}
			else
			{
				$this->rodada_base->finalizada	=	TRUE;
			}
		}
		else
		{
			if ( $this->show_log ) echo "Rodada não existe<br/>\n";
			return FALSE;
		}
		if ( $this->show_log ) echo '...rodada_fase.id=' . $this->rodada_base->id;
		if ( $this->show_log ) if ( $this->rodada_base->finalizada ) { echo " ABERTA <br/>\n"; } else { echo " FECHADA <br/>\n"; };

		/* 
		 * --- 02 --- Registra as informações dos times zebras para a rodada que será calculada.
		 */
		$this->rodada_anterior		=	$this->rodada_fase->get_rodada_anterior( $this->rodada_base->data_inicio, $this->rodada_base->campeonato_versao_id, $this->rodada_base->tipo_fase );
		if ( is_object( $this->rodada_anterior ) )
		{
			if ( $this->show_log ) echo "Rodada Anterior={$this->rodada_anterior->id}<br/>\n";
			// a)
			$this->get_zebras();
		}
		
		/*
		 * --- 03 --- Obtem a rodada seguinte para preparar a qtde de poderes disponíveis.
		 */
		$this->proxima_rodada		=	$this->rodada_fase->get_rodada_proxima( $this->rodada_base->data_inicio, $this->rodada_base->campeonato_versao_id );
		if ( $this->show_log ) echo "Proxima rodada={$this->proxima_rodada->id}<br/>\n";
		
		// Se não existe próxima rodada... preparamos o cálculo para não tratar a próxima.
		if ( !is_object( $this->proxima_rodada ))
		{
			$this->proxima_rodada	=	NULL;
		}
		
		/**
		 * 
		 * Pesos e pontos
		 * 
		 */
		
		/*
		 * --- 04 --- Retorna a lista de kicks.
		 */
		$select	=	"select	 kick.id			AS	kick_id
					,kick.kick_casa			AS	kick_casa
					,kick.kick_visitante		AS	kick_visitante
					,kick.kick_casa_auto		AS	kick_casa_auto
					,kick.kick_visitante_auto	AS	kick_visitante_auto
					,kick.pontos_kick		AS	pontos_kick
					,kick.pontos_gols		AS	pontos_gols
					,kick.pontos_power		AS	pontos_power
					,IFNULL( kick.acerto, 'N' )	AS	acerto
					,pes.id				AS	pessoa_id
					,jogo.id			AS	jogo_id
					,jogo.rodada_fase_id		AS	rodada_fase_id
					,jogo.resultado_casa
					,jogo.resultado_visitante
					,jogo.equipe_id_casa
					,jogo.equipe_id_visitante
					,jogo.data_hora			AS	data_hora_jogo
					,case
						when DATE_ADD( jogo.data_hora, INTERVAL '120:0' MINUTE_SECOND ) < now()
						and  jogo.resultado_casa IS NOT NULL
						and  jogo.resultado_visitante IS NOT NULL
						then
							'S'
						else
							'N'
					 end				AS	jogo_encerrado
					,IFNULL( kick.processado, 'N' ) AS	processado
				from		jogo			AS	jogo
				join		rodada_fase		AS	rod	ON	rod.id  = jogo.rodada_fase_id
				join		pessoa			AS	pes
				";
		// Se foi passado um ID pessoa, então calculamos só para esta pessoa.
		if ( $pessoa_id )
		{
			$select	=	$select."					ON	pes.id	= {$pessoa_id}";
		}

		$select	=	$select.
				"
				join		kick			AS	kick	ON	kick.jogo_id = jogo.id
											AND	kick.pessoa_id = pes.id
				where		jogo.rodada_fase_id		=	{$rodada_fase_id}
				and		jogo.data_hora			<	now()
				and		exists	(
							select	kick2.id
							from	kick			AS	kick2
							join	jogo			AS	jogo2	ON	jogo2.rodada_fase_id		=	{$rodada_fase_id}
							and	kick2.jogo_id	=	jogo2.id
							)
				order by	 pes.id
						,jogo.rodada_fase_id
						,jogo.id
				";

		$pessoa_id_ant					=	0;
		
		$query_kicks					=	$this->db->query( $select );
		/*
		 * --- 05 --- LOOP de Kicks.
		 */
		foreach( $query_kicks->result_object() as $kick )
		{
			// Oficial
			if ( date( $kick->data_hora_jogo ) < date( '2013-02-20 00:00:00' ) )
			{
				$this->fator_kiks			=	1;
				$this->peso_vitoria			=	5;
				$this->peso_empate			=	5;
				$this->acrescimo_por_cheio		=	5;
			}
			else // if (now() >= date( '2013-02-20 00:00:00' ) )
			{
				$this->fator_kiks			=	1;
				$this->peso_vitoria			=	6;
				$this->peso_empate			=	6;
				$this->acrescimo_por_cheio		=	4;
			}

			// Controla a quebra de pessoa.
			// Inicializa as variáveis de cada pessoa.
			if ( $pessoa_id_ant != $kick->pessoa_id )
			{
				if ( $this->show_log ) echo "quebra pessoa((({$kick->pessoa_id}))) <br> \n";
				log_message( 'debug', "quebra pessoa((({$kick->pessoa_id})))<br> \n" );

				$this->count_pessoa		+=	1;
				// Grava os dados.
				if ( $pessoa_id_ant != 0 )
				{
					$this->salvar_pessoa();
				}
				
				// Inicia o controle de transação de Base de dados.
				$this->db->trans_start();
				
				$pessoa_id_ant									=	$kick->pessoa_id;
				if ( $this->show_log ) { echo "(iniciou) PESSOA_ID=".$kick->pessoa_id."<br/>\n"; }

				$this->kick_update								=	new stdClass();
				$this->kick_update->id								=	0;
				$this->kick_update->pessoa_id							=	0;
				$this->kick_update->kick_casa							=	0;
				$this->kick_update->kick_casa_auto						=	0;
				$this->kick_update->kick_visitante						=	0;
				$this->kick_update->kick_visitante_auto						=	0;
				$this->kick_update->jogo_id							=	0;
				$this->kick_update->copiado							=	'N';
				$this->kick_update->automatico							=	'N';
				$this->kick_update->acerto							=	'N'; // Não Chutou.
				$this->kick_update->pontos_kick							=	0;
				$this->kick_update->pontos_gols							=	0;
				$this->kick_update->pontos_power						=	0;
				$this->kick_update->processado							=	'S';
				// Melhor rodada do campeonato.
				$this->pessoa_best_rodada							=	new stdClass();
				$this->pessoa_best_rodada->id							=	NULL;
				$this->pessoa_best_rodada->pontos_kick						=	0;
				$this->pessoa_best_rodada->pontos_gols						=	0;
				$this->pessoa_best_rodada->pontos_power						=	0;
				$this->pessoa_best_rodada->total_pontos						=	0;
				$this->ar_pessoa_best_rodada_grupo						=	array();

				/*
				 * Prepara para registrar o acumulador por campeonato.
				 */
				$this->pessoa_campeonato_update							=	$this->pessoa_campeonato_versao->get_one_by_where( "pessoa_campeonato_versao.pessoa_id = {$kick->pessoa_id} and pessoa_campeonato_versao.campeonato_versao_id = {$campeonato_versao_id}" );
				
				if ( !is_object( $this->pessoa_campeonato_update ) ) // Não existe um pessoa_campeonato_versao, preparamos para criar um.
				{
					$this->pessoa_campeonato_update						=	new stdClass();
					$this->pessoa_campeonato_update->id					=	NULL;
					$this->pessoa_campeonato_update->pessoa_id				=	$kick->pessoa_id;
					$this->pessoa_campeonato_update->campeonato_versao_id			=	$campeonato_versao_id;
					$this->pessoa_campeonato_update->pessoa_rodada_fase_id			=	NULL;
					$this->pessoa_campeonato_update->pontos_kick				=	0;
					$this->pessoa_campeonato_update->pontos_gols				=	0;
					$this->pessoa_campeonato_update->pontos_power				=	0;
					$this->pessoa_campeonato_update->qtde_jogos_com_chute			=	0;
					$this->pessoa_campeonato_update->qtde_jogos_sem_chute			=	0;
					$this->pessoa_campeonato_update->qtde_acertou_vitoria_tudo		=	0;
					$this->pessoa_campeonato_update->qtde_acertou_vitoria_gol_1_equipe	=	0;
					$this->pessoa_campeonato_update->qtde_acertou_vitoria			=	0;
					$this->pessoa_campeonato_update->qtde_acertou_empate_tudo		=	0;
					$this->pessoa_campeonato_update->qtde_acertou_empate			=	0;
					$this->pessoa_campeonato_update->qtde_acertou_apenas_gol_1_equipe	=	0;
					$this->pessoa_campeonato_update->qtde_errou_tudo			=	0;
					$this->pessoa_campeonato_update->qtde_rodada_jogada			=	0;
					$this->pessoa_campeonato_update->ar_resumo_poder			=	array();
					
					// para o cálculo dos poderes ocorrem junto com os kiks é necessário criar as linhas header no começo do cálculo. Há muitas referencias entre as tabelas.
					$this->pessoa_campeonato_update->id					=	$this->pessoa_campeonato_versao->update( $this->pessoa_campeonato_update );
				}
				else
				{
					$this->pessoa_campeonato_update->ar_resumo_poder			=	array();
					foreach( $this->pessoa_campeonato_versao_resumo_power->get_all_by_where( "pessoa_campeonato_versao_resumo_power.pessoa_campeonato_versao_id = {$this->pessoa_campeonato_update->id}" ) as $power )
					{
						$this->pessoa_campeonato_update->ar_resumo_poder[ $power->power_id ]	=	$power;
					}
				}
				
				if ( $this->show_log ) echo '...best campeonato rodada_fase_id='.$this->pessoa_campeonato_update->pessoa_rodada_fase_id."<br/>\n";
				//log_message( 'debug', '>>>best rodada_fase_id='.$this->pessoa_campeonato_update->pessoa_rodada_fase_id );
				
				if ( isset( $this->pessoa_campeonato_update->pessoa_rodada_fase_id )
				&&   $this->pessoa_campeonato_update->pessoa_rodada_fase_id // Existe uma "melhorar rodada".
				&&   $this->pessoa_campeonato_update->pessoa_rodada_fase_id != NULL
				   )
				{
					$this->pessoa_best_rodada						=	$this->pessoa_rodada_fase->get_one_by_id( $this->pessoa_campeonato_update->pessoa_rodada_fase_id );
					if ( !is_object( $this->pessoa_best_rodada ) )
					{
						$this->pessoa_best_rodada					=	new stdClass();
						$this->pessoa_best_rodada->id					=	NULL;
						$this->pessoa_best_rodada->pontos_kick				=	0;
						$this->pessoa_best_rodada->pontos_gols				=	0;
						$this->pessoa_best_rodada->pontos_power				=	0;
						$this->pessoa_best_rodada->total_pontos				=	0;
					}
					$this->pessoa_best_rodada->total_pontos					=	$this->pessoa_best_rodada->pontos_gols + $this->pessoa_best_rodada->pontos_kick + $this->pessoa_best_rodada->pontos_power;
				}
				else // Não existindo, preparamos para que a rodada atual se torne a melhor.
				{
					$this->pessoa_best_rodada						=	new stdClass();
					$this->pessoa_best_rodada->id						=	NULL;
					$this->pessoa_best_rodada->pontos_kick					=	0;
					$this->pessoa_best_rodada->pontos_gols					=	0;
					$this->pessoa_best_rodada->pontos_power					=	0;
					$this->pessoa_best_rodada->total_pontos					=	$this->pessoa_best_rodada->pontos_gols + $this->pessoa_best_rodada->pontos_kick + $this->pessoa_best_rodada->pontos_power;
				}
				
				/*
				 * Prepara para registrar o acumulador por grupos de amigos.
				 */
				$this->ar_pessoa_best_rodada_grupo						=	array();
				$this->pessoa_ranking_grupo							=	array();
				$this->qry_pessoa_ranking_grupo							=	$this->pessoa_ranking_grupo_amigos->get_ranking_rodada( $kick->pessoa_id, $rodada_fase_id, $campeonato_versao_id );
				foreach( $this->qry_pessoa_ranking_grupo as $key_grupo => $ranking_grupo )
				{
					// Para o cálculo de poderes temos que ter todos os header inseridos.
					if ( $ranking_grupo->id == NULL )
					{
						$ranking_grupo->id						=	$this->pessoa_ranking_grupo_amigos->update( $ranking_grupo );
					}

					$this->pessoa_ranking_grupo[ $key_grupo ]				=	$ranking_grupo;

					if ( isset( $ranking_grupo->pessoa_rodada_fase_id )
					&&   $ranking_grupo->pessoa_rodada_fase_id // Existe uma "melhorar rodada".
					&&   $ranking_grupo->pessoa_rodada_fase_id != NULL
					   )
					{
						if ( $this->show_log ) echo '...best grupo('.$key_grupo.') rodada_fase_id='.$ranking_grupo->pessoa_rodada_fase_id."<br/>\n";
						log_message( 'debug', '>>>best grupo rodada_fase_id='.$this->pessoa_campeonato_update->pessoa_rodada_fase_id );

						$this->pessoa_best_rodada_grupo					=	$this->pessoa_rodada_fase->get_one_by_id( $ranking_grupo->pessoa_rodada_fase_id );
						if ( !is_object( $this->pessoa_best_rodada_grupo ) )
						{
							$this->pessoa_best_rodada_grupo				=	new stdClass();
							$this->pessoa_best_rodada_grupo->id			=	NULL;
							$this->pessoa_best_rodada_grupo->pontos_kick		=	0;
							$this->pessoa_best_rodada_grupo->pontos_gols		=	0;
							$this->pessoa_best_rodada_grupo->pontos_power		=	0;
							$this->pessoa_best_rodada_grupo->total_pontos		=	0;
						}
						$this->pessoa_best_rodada_grupo->total_pontos			=	$this->pessoa_best_rodada_grupo->pontos_gols + $this->pessoa_best_rodada_grupo->pontos_kick + $this->pessoa_best_rodada_grupo->pontos_power;
					}
					else // Não existindo, preparamos para que a rodada atual se torne a melhor.
					{
						$this->pessoa_best_rodada_grupo					=	new stdClass();
						$this->pessoa_best_rodada_grupo->id				=	NULL;
						$this->pessoa_best_rodada_grupo->pontos_kick			=	0;
						$this->pessoa_best_rodada_grupo->pontos_gols			=	0;
						$this->pessoa_best_rodada_grupo->pontos_power			=	0;
						$this->pessoa_best_rodada_grupo->total_pontos			=	$this->pessoa_best_rodada_grupo->pontos_gols + $this->pessoa_best_rodada_grupo->pontos_kick + $this->pessoa_best_rodada_grupo->pontos_power;
					}
					$this->ar_pessoa_best_rodada_grupo[ $key_grupo ]			=	$this->pessoa_best_rodada_grupo;

					$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder		=	array();
					if ( $ranking_grupo->id )
					{
						// Registra o resumo de poderes para o grupo.
						$this->pessoa_ranking_grupo[ $key_grupo ]			=	$ranking_grupo;
						foreach( $this->pessoa_ranking_grupo_amigos_resumo_power->get_all_by_where( "pessoa_ranking_grupo_amigos_resumo_power.pessoa_ranking_grupo_amigos_id = {$ranking_grupo->id}" ) as $power )
						{
							$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ $power->power_id ]	=	$power;
						}
					}
				}
				
				/*
				 * Prepara para registrar o acumulador por rodada.
				 */
				$this->pessoa_rodada_update								=	$this->pessoa_rodada_fase->get_one_by_where( "pessoa_rodada_fase.pessoa_id = {$kick->pessoa_id} and pessoa_rodada_fase.rodada_fase_id = {$rodada_fase_id}" );
				if ( !is_object( $this->pessoa_rodada_update ) ) // Não existe um pessoa_campeonato_versao, preparamos para criar um.
				{
					$this->pessoa_rodada_update							=	new stdClass();
					$this->pessoa_rodada_update->id							=	NULL;
					$this->pessoa_rodada_update->pessoa_id						=	$kick->pessoa_id;
					$this->pessoa_rodada_update->rodada_fase_id					=	$rodada_fase_id;
					$this->pessoa_rodada_update->pontos_kick					=	0;
					$this->pessoa_rodada_update->pontos_gols					=	0;
					$this->pessoa_rodada_update->pontos_power					=	0;
					$this->pessoa_rodada_update->qtde_jogos_com_chute				=	0;
					$this->pessoa_rodada_update->qtde_jogos_sem_chute				=	0;
					$this->pessoa_rodada_update->qtde_acertou_vitoria_tudo				=	0;
					$this->pessoa_rodada_update->qtde_acertou_vitoria_gol_1_equipe			=	0;
					$this->pessoa_rodada_update->qtde_acertou_vitoria				=	0;
					$this->pessoa_rodada_update->qtde_acertou_empate_tudo				=	0;
					$this->pessoa_rodada_update->qtde_acertou_empate				=	0;
					$this->pessoa_rodada_update->qtde_acertou_apenas_gol_1_equipe			=	0;
					$this->pessoa_rodada_update->qtde_errou_tudo					=	0;
					$this->pessoa_rodada_update->jogou_rodada					=	'N';
					$this->pessoa_rodada_update->ar_resumo_poder					=	array();
							
					// Cria a linha para a próxima rodada da pessoa.
					$this->pessoa_rodada_update->id							=	$this->pessoa_rodada_fase->update( $this->pessoa_rodada_update );
				}
				else
				{
					$this->pessoa_rodada_update->ar_resumo_poder					=	array();
					foreach( $this->pessoa_rodada_fase_resumo_power->get_all_by_where( "pessoa_rodada_fase_resumo_power.pessoa_rodada_fase_id = {$this->pessoa_rodada_update->id}" ) as $power )
					{
						$this->pessoa_rodada_update->ar_resumo_poder[ $power->power_id ]	=	$power;
					}
				}
				if ( $this->show_log ) echo '...pessoa_rodada_fase.id='.$this->pessoa_rodada_update->id."<br/>\n";

				// Prepara para calcular os poderes disponíveis para a rodada atual.
				$this->qry_powers								=	$this->pessoa_rodada_fase_power->get_all_by_where( "pessoa_rodada_fase_power.pessoa_rodada_fase_id = {$this->pessoa_rodada_update->id}" );
				$this->ar_powers_rodada_atual							=	array();
				foreach( $this->qry_powers as $power )
				{
					$this->ar_powers_rodada_atual[ $power->power_id ]			=	$power;
					if ( $this->ar_powers_rodada_atual[ $power->power_id ]->qtde_usada == 0 )
					{
						$this->ar_powers_rodada_atual[ $power->power_id ]->qtde_usada	=	$this->kick_power->get_qtde_usada( $kick->pessoa_id, $power->power_id, $kick->rodada_fase_id );
					}
					$this->ar_powers_rodada_atual[ $power->power_id ]->qtde_usada_user	=	$this->ar_powers_rodada_atual[ $power->power_id ]->qtde_usada; // registramos o uso que o usuário fez na página de chutes.
				}

				for ( $power_id = 1; $power_id <= 7; $power_id ++ )
				{
					// Inicialza os poderes.
					$this->pessoa_rodada_power_new						=	new stdClass();
					$this->pessoa_rodada_power_new->id					=	NULL;
					$this->pessoa_rodada_power_new->pessoa_rodada_fase_id			=	$this->pessoa_rodada_update->id;
					$this->pessoa_rodada_power_new->power_id				=	NULL; // Anulamos para evitar sobreposição de poderes já gravados.
					$this->pessoa_rodada_power_new->pontos					=	0;
					$this->pessoa_rodada_power_new->qtde_liberado				=	0;
					$this->pessoa_rodada_power_new->qtde_usada				=	0;
					$this->pessoa_rodada_power_new->qtde_usada_user				=	0;

					if ( !key_exists( $power_id, $this->ar_powers_rodada_atual ) )
					{
						if ( $power_id == QQI )
						{
							$this->pessoa_rodada_power_new->qtde_liberado			=	1;
						}
						elseif ( $power_id == GURU )
						{
							$this->pessoa_rodada_power_new->qtde_liberado			=	1;
						}
						elseif ( $power_id == DUELO )
						{
							$this->pessoa_rodada_power_new->qtde_liberado			=	0;
						}
						elseif ( $power_id == TJUNTO )
						{
							$this->pessoa_rodada_power_new->qtde_liberado			=	0;
						}
						elseif ( $power_id == ESPIAO )
						{
							$this->pessoa_rodada_power_new->qtde_liberado			=	0;
						}
						elseif ( $power_id == BARBADA )
						{
							$this->pessoa_rodada_power_new->qtde_liberado			=	1;
						}
						elseif ( $power_id == ZEBRA )
						{
							$this->pessoa_rodada_power_new->qtde_liberado			=	2;
						}
						$this->pessoa_rodada_power_new->power_id				=	$power_id;
						$this->pessoa_rodada_power_new->qtde_usada				=	$this->kick_power->get_qtde_usada( $kick->pessoa_id, $power_id, $kick->rodada_fase_id );
						$this->pessoa_rodada_power_new->qtde_usada_user				=	$this->pessoa_rodada_power_new->qtde_usada;
					}

					if ( $this->pessoa_rodada_power_new->power_id )
					{
						$this->ar_powers_rodada_atual[ $this->pessoa_rodada_power_new->power_id ]
															=	$this->pessoa_rodada_power_new;
					}
				}

				// Prepara para calcular os poderes disponíveis para a rodada seguinte, se ela existir.
				if ( is_object( $this->proxima_rodada )
				&&   $this->proxima_rodada->id 
				   )
				{
					$this->pessoa_rodada_seguinte							=	$this->pessoa_rodada_fase->get_one_by_where	(
																						"	pessoa_rodada_fase.rodada_fase_id = {$this->proxima_rodada->id}
																						and	pessoa_rodada_fase.pessoa_id = {$kick->pessoa_id}
																						"																					
																						);
					if ( !$this->pessoa_rodada_seguinte )
					{
						$this->pessoa_rodada_seguinte						=	new stdClass();
						$this->pessoa_rodada_seguinte->id					=	NULL;
						$this->pessoa_rodada_seguinte->pessoa_id				=	$kick->pessoa_id;
						$this->pessoa_rodada_seguinte->rodada_fase_id				=	$this->proxima_rodada->id;
						$this->pessoa_rodada_seguinte->pontos_kick				=	0;
						$this->pessoa_rodada_seguinte->pontos_gols				=	0;
						$this->pessoa_rodada_seguinte->pontos_power				=	0;
						$this->pessoa_rodada_seguinte->qtde_jogos_com_chute			=	0;
						$this->pessoa_rodada_seguinte->qtde_jogos_sem_chute			=	0;
						$this->pessoa_rodada_seguinte->qtde_acertou_vitoria_tudo		=	0;
						$this->pessoa_rodada_seguinte->qtde_acertou_vitoria_gol_1_equipe	=	0;
						$this->pessoa_rodada_seguinte->qtde_acertou_vitoria			=	0;
						$this->pessoa_rodada_seguinte->qtde_acertou_empate_tudo			=	0;
						$this->pessoa_rodada_seguinte->qtde_acertou_empate			=	0;
						$this->pessoa_rodada_seguinte->qtde_acertou_apenas_gol_1_equipe		=	0;
						$this->pessoa_rodada_seguinte->qtde_errou_tudo				=	0;
						$this->pessoa_rodada_seguinte->jogou_rodada				=	'N';
						
						// Cria a linha para a próxima rodada da pessoa.
						$this->pessoa_rodada_seguinte->id					=	$this->pessoa_rodada_fase->update( $this->pessoa_rodada_seguinte );
					}

					$this->qry_powers								=	$this->pessoa_rodada_fase_power->get_all_by_where( "pessoa_rodada_fase_power.pessoa_rodada_fase_id = {$this->pessoa_rodada_seguinte->id}" );
					$this->ar_powers_rodada_seguinte						=	array();
					foreach( $this->qry_powers as $power )
					{
						$this->ar_powers_rodada_seguinte[ $power->power_id ]			=	$power;
						if ( $this->ar_powers_rodada_seguinte[ $power->power_id ]->qtde_usada == 0 )
						{
							$this->ar_powers_rodada_seguinte[ $power->power_id ]->qtde_usada	=	$this->kick_power->get_qtde_usada( $kick->pessoa_id, $power->power_id, $this->proxima_rodada->id );
						}
						$this->ar_powers_rodada_seguinte[ $power->power_id ]->qtde_usada_user	=	$this->ar_powers_rodada_seguinte[ $power->power_id ]->qtde_usada; // registramos o uso que o usuário fez na página de chutes.
					}

					for ( $power_id = 1; $power_id <= 7; $power_id ++ )
					{
						// Inicialza os poderes.
						$this->pessoa_rodada_power_new						=	new stdClass();
						$this->pessoa_rodada_power_new->id					=	NULL;
						$this->pessoa_rodada_power_new->pessoa_rodada_fase_id			=	$this->pessoa_rodada_seguinte->id;
						$this->pessoa_rodada_power_new->power_id				=	NULL; // Anulamos para evitar sobreposição de poderes já gravados.
						$this->pessoa_rodada_power_new->pontos					=	0;
						$this->pessoa_rodada_power_new->qtde_liberado				=	0;
						$this->pessoa_rodada_power_new->qtde_usada				=	0;
						$this->pessoa_rodada_power_new->qtde_usada_user				=	0;

						if ( !key_exists( $power_id, $this->ar_powers_rodada_seguinte ) )
						{
							if ( $power_id == QQI )
							{
								$this->pessoa_rodada_power_new->qtde_liberado			=	1;
							}
							elseif ( $power_id == GURU )
							{
								$this->pessoa_rodada_power_new->qtde_liberado			=	1;
							}
							elseif ( $power_id == DUELO )
							{
								$this->pessoa_rodada_power_new->qtde_liberado			=	0;
							}
							elseif ( $power_id == TJUNTO )
							{
								$this->pessoa_rodada_power_new->qtde_liberado			=	0;
							}
							elseif ( $power_id == ESPIAO )
							{
								$this->pessoa_rodada_power_new->qtde_liberado			=	0;
							}
							elseif ( $power_id == BARBADA )
							{
								$this->pessoa_rodada_power_new->qtde_liberado			=	1;
							}
							elseif ( $power_id == ZEBRA )
							{
								$this->pessoa_rodada_power_new->qtde_liberado			=	2;
							}
							$this->pessoa_rodada_power_new->power_id				=	$power_id;
							$this->pessoa_rodada_power_new->qtde_usada				=	$this->kick_power->get_qtde_usada( $kick->pessoa_id, $power_id, $this->proxima_rodada->id );
							$this->pessoa_rodada_power_new->qtde_usada_user				=	$this->pessoa_rodada_power_new->qtde_usada;
						}
						
						if ( $this->pessoa_rodada_power_new->power_id )
						{
							$this->ar_powers_rodada_seguinte[ $this->pessoa_rodada_power_new->power_id ]		=	$this->pessoa_rodada_power_new;
						}
					}
				}

				// Estornamos o poder QQI. Ele é por rodada diferente de todos os demais que são por jogo.
				if ( $this->show_log ) echo "...Estorno do QQI<br/>\n";
				
				if ( key_exists( QQI, $this->ar_powers_rodada_atual ) )
				{
					if ( $this->ar_powers_rodada_atual[ QQI ]->id !== NULL
					&&   $this->ar_powers_rodada_atual[ QQI ]->pontos > 0
					   )
					{
						if ( $this->show_log ) echo "......QQI={$this->ar_powers_rodada_atual[ QQI ]->pontos}<br/>\n";
						$this->add_value( 'pontos_power', $this->ar_powers_rodada_atual[ QQI ]->pontos * (-1), QQI, -1 );
						$this->ar_powers_rodada_atual[ QQI ]->pontos			=	0;
					}
					else
					{
						// Verifica se ficou lixo no resumo da rodada. Houve uma perda do poder, o registro do poder, mas está registrado na rodada, então estornamos.
						if ( key_exists( QQI, $this->pessoa_rodada_update->ar_resumo_poder ) )
						{
							// Existe o poder no resumo e ele tem pontos registrados. Falha no estorno. Corrigimos o campeonato, os grupos e a rodada.
							if ( $this->pessoa_rodada_update->ar_resumo_poder[ QQI ]->pontos > 0 )
							{
								// Só pode existir um QQI por rodada, então assumimos os pontos da rodada para estornar o Campeonato e os Grupos.
								$pontos									=	$this->pessoa_rodada_update->ar_resumo_poder[ QQI ]->pontos;
								$this->pessoa_rodada_update->ar_resumo_poder[ QQI ]->qtde		=	( ( $this->pessoa_rodada_update->ar_resumo_poder[ QQI ]->qtde - 1 ) < 0 ) ? 0 : ( $this->pessoa_rodada_update->ar_resumo_poder[ QQI ]->qtde - 1 );
								$this->pessoa_rodada_update->ar_resumo_poder[ QQI ]->pontos		=	$this->pessoa_rodada_update->ar_resumo_poder[ QQI ]->pontos - $pontos;
								if ( $this->show_log ) echo "......Estornou na RODADA ficou({$this->pessoa_rodada_update->ar_resumo_poder[ QQI ]->pontos})";

								// Não usamos a função aqui por segurança.
								$this->pessoa_campeonato_update->ar_resumo_poder[ QQI ]->qtde		=	( ( $this->pessoa_campeonato_update->ar_resumo_poder[ QQI ]->qtde - 1 ) < 0 ) ? 0 : ( $this->pessoa_campeonato_update->ar_resumo_poder[ QQI ]->qtde - 1 );
								$this->pessoa_campeonato_update->ar_resumo_poder[ QQI ]->pontos		=	$this->pessoa_campeonato_update->ar_resumo_poder[ QQI ]->pontos - $pontos;
								if ( $this->show_log ) echo ", CAMPEONATO ficou({$this->pessoa_campeonato_update->ar_resumo_poder[ QQI ]->pontos})";
								
								foreach( $this->pessoa_ranking_grupo as $key_grupo => $ranking_grupo )
								{
									if ( isset( $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder )
									&&   $this->pessoa_ranking_grupo[ $key_grupo ]->usar_poderes == 'S'
									   )
									{
										if ( key_exists( $power, $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder ) )
										{
											if ( 1 > 0 // Novo poder
											||   ( 1 < 0 // Estorno
											&&     $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ QQI ]->id // Só estornamos se a linha já existia.
											     )
											   )
											{
												$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ QQI ]->qtde		=	( ( $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ QQI ]->qtde - 1 ) < 0 ) ? 0 : ( $this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ QQI ]->qtde - 1 );
												$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ QQI ]->pontos	=	$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ QQI ]->pontos - $pontos;
												if ( $this->show_log ) echo ", GRUPO=$key_grupo ficou({$this->pessoa_ranking_grupo[ $key_grupo ]->ar_resumo_poder[ QQI ]->pontos})";
											}
										}
									}
								}
								if ( $this->show_log ) echo "<br/>\n";
							}
							else
							{
								if ( $this->show_log ) echo "......Não estornou (2) id({$this->ar_powers_rodada_atual[ QQI ]->id}) pontos({$this->ar_powers_rodada_atual[ QQI ]->pontos})<br/>\n";
							}
						}
						else
						{
							if ( $this->show_log ) echo "......Não estornou id({$this->ar_powers_rodada_atual[ QQI ]->id}) pontos({$this->ar_powers_rodada_atual[ QQI ]->pontos})<br/>\n";
						}
					}
				}
				else
				{
					if ( $this->show_log ) echo "......Não está na lista de poderes<br/>\n";
				}
			} // fim: quebra pessoa.

			if ( $this->show_log ) echo "...jogo={$kick->jogo_id}<br/>\n";
			
			// Prepara a linha para atualizar o kick.
			$this->kick_update->id						=	$kick->kick_id;
			$this->kick_update->pessoa_id					=	$kick->pessoa_id;
			$this->kick_update->jogo_id					=	$kick->jogo_id;
			$this->kick_update->kick_casa					=	$kick->kick_casa;
			$this->kick_update->kick_visitante				=	$kick->kick_visitante;
			$this->kick_update->kick_casa_auto				=	$kick->kick_casa_auto;
			$this->kick_update->kick_visitante_auto				=	$kick->kick_visitante_auto;
			//$this->kick_update->copiado					=	$kick->copiado;
			$this->kick_update->acerto					=	$kick->acerto;
			$this->kick_update->pontos_kick					=	$kick->pontos_kick;
			$this->kick_update->pontos_gols					=	$kick->pontos_gols;
			$this->kick_update->pontos_power				=	$kick->pontos_power;
			$this->kick_update->processado					=	$kick->processado;
			
			// Inicializa todos os poderes.
			$this->ar_kick_power						=	array();
			$this->kick_guru_power_update					=	NULL;
			$this->kick_barbada_power_update				=	NULL;
			$this->kick_zebra_power_update					=	NULL;
			$this->kick_tjunto_power_update					=	NULL;
			$this->kick_duelo_power_update					=	NULL;
			$this->kick_espiao_power_update					=	NULL;
			
			// Carrega os dados de todos os poderes.
			if ( $this->kick_update->id )
			{
				// Carrega o poder "Zebra".
				$this->kick_zebra_power_update				=	$this->kick_power->get_one_by_where( "kick_power.kick_id = {$this->kick_update->id} and kick_power.power_id = ". ZEBRA );
				if ( is_object( $this->kick_zebra_power_update ) )
				{
					$this->ar_kick_power[ ZEBRA ]			=	$this->kick_zebra_power_update;
				}
				// Carrega o poder "Barbada".
				$this->kick_barbada_power_update			=	$this->kick_power->get_one_by_where( "kick_power.kick_id = {$this->kick_update->id} and kick_power.power_id = ". BARBADA );
				if ( is_object( $this->kick_barbada_power_update ) )
				{
					$this->ar_kick_power[ BARBADA ]			=	$this->kick_barbada_power_update;
				}
				// Carrega o poder "Guru".
				$this->kick_guru_power_update				=	$this->kick_power->get_one_by_where( "kick_power.kick_id = {$this->kick_update->id} and kick_power.power_id = ". GURU );
				if ( is_object( $this->kick_guru_power_update ) )
				{
					$this->ar_kick_power[ GURU ]			=	$this->kick_guru_power_update;
				}
			}
			if ( $this->show_log )
			{
				foreach( $this->ar_kick_power as $power_data )
				{
					if ( $this->show_log ) echo "......(kick_power) poder=$power_data->power_id pontos=$power_data->pontos<br/>\n";		
				}
			}
			
			if ( $this->show_log ) echo "...jogo Processado={$kick->processado}<br/>\n";
			
			// Estorna os valores já calculados para o kick.
			if ( $kick->processado == 'S' // Já processado, estorna.
			   )
			{
				if ( $this->show_log ) echo "---estorno<br/>\n";

				// Estorna o poder "Zebra".
				if ( is_object( $this->kick_zebra_power_update )
				&&   $this->kick_zebra_power_update->pontos > 0
				   )
				{
					if ( $this->kick_zebra_power_update->processado_power == 'A' // Só estorno se já estiver processado.
					||   $this->kick_zebra_power_update->processado_power == 'S'
					   )
					{
						$this->add_value( 'pontos_power', $this->kick_zebra_power_update->pontos * (-1), ZEBRA, -1 );
						$this->kick_update->pontos_power	=	$this->kick_update->pontos_power - $this->kick_zebra_power_update->pontos;
					}
					$this->kick_zebra_power_update->pontos		=	0;
					$this->ar_kick_power[ ZEBRA ]			=	$this->kick_zebra_power_update;
				}
				// Estorna o poder "Barbada".
				if ( is_object( $this->kick_barbada_power_update )
				&&   $this->kick_barbada_power_update->pontos > 0
				   )
				{
					if ( $this->kick_barbada_power_update->processado_power == 'A' // Só estorno se já estiver processado.
					||   $this->kick_barbada_power_update->processado_power == 'S'
					   )
					{
						$this->add_value( 'pontos_power', $this->kick_barbada_power_update->pontos * (-1), BARBADA, -1 );
						$this->kick_update->pontos_power	=	$this->kick_update->pontos_power - $this->kick_barbada_power_update->pontos;
					}
					$this->kick_barbada_power_update->pontos	=	0;
					$this->ar_kick_power[ BARBADA ]			=	$this->kick_barbada_power_update;
				}

				// Estorna o poder "Guru".
				if ( is_object( $this->kick_guru_power_update )
				&&   $this->kick_guru_power_update->pontos > 0
				   )
				{
					if ( $this->kick_guru_power_update->processado_power == 'A' // Só estorno se já estiver processado.
					||   $this->kick_guru_power_update->processado_power == 'S'
					   )
					{
						$this->add_value( 'pontos_power', $this->kick_guru_power_update->pontos * (-1), GURU, -1 );
						$this->kick_update->pontos_power	=	$this->kick_update->pontos_power - $this->kick_guru_power_update->pontos;
					}
					$this->kick_guru_power_update->pontos		=	0;
					$this->ar_kick_power[ GURU ]			=	$this->kick_guru_power_update;
				}
				if ( $this->show_log )
				{
					foreach( $this->ar_kick_power as $power_data )
					{
						if ( $this->show_log ) echo "......(kick_power.2) poder=$power_data->power_id pontos=$power_data->pontos<br/>\n";		
					}
				}

				// Estorno de kiks.
				if ( $this->kick_update->acerto == 'N' ) // Não chutou.
				{
					$this->add_value( 'qtde_jogos_sem_chute', -1 );
				}
				else
				{
					$this->add_value( 'pontos_kick', $this->kick_update->pontos_kick * (-1) );
					
					$this->add_value( 'pontos_gols', $this->kick_update->pontos_gols * (-1) );
					
					$this->add_value( 'pontos_power', $this->kick_update->pontos_power * (-1) );

					$this->add_value( 'qtde_jogos_com_chute', -1 );

					if ( $this->kick_update->acerto == 'VT' ) // Vitoria e tudo
					{
						$this->add_value( 'qtde_acertou_vitoria_tudo', -1 );
					}
					elseif ( $this->kick_update->acerto == 'V1' ) // Vitoria e 1 gol
					{
						$this->add_value( 'qtde_acertou_vitoria_gol_1_equipe', -1 );
					}
					elseif ( $this->kick_update->acerto == 'V' ) // Vitoria apenas
					{
						$this->add_value( 'qtde_acertou_vitoria', -1 );
					}
					elseif ( $this->kick_update->acerto == 'PT' ) // Empate e os gols.
					{
						$this->add_value( 'qtde_acertou_empate_tudo', -1 );
					}
					elseif ( $this->kick_update->acerto == 'P' ) // Empate apenas
					{
						$this->add_value( 'qtde_acertou_empate', -1 );
					}
					elseif ( $this->kick_update->acerto == 'G1' ) // Apenas o gol de uma equipe.
					{
						$this->add_value( 'qtde_acertou_apenas_gol_1_equipe', -1 );
					}
					elseif ( $this->kick_update->acerto == 'E' ) // Errou tudo.
					{
						$this->add_value( 'qtde_errou_tudo', -1 );
					}
				}
			} // fim: Estorno

			if ( $this->show_log ) echo "---calculando<br/>\n";

			// Inicializa os valores que serão calculados.
			$this->kick_update->acerto					=	'N'; // Não Chutou.
			$this->kick_update->pontos_kick					=	0;
			$this->kick_update->pontos_gols					=	0;
			$this->kick_update->pontos_power				=	0;
			$this->kick_update->processado					=	'S';

			if ( ( $kick->kick_casa
			||     $kick->kick_visitante
			||     $kick->kick_casa != NULL
			||     $kick->kick_visitante != NULL
			     )
			&&   ( $kick->resultado_casa
			||     $kick->resultado_visitante
			||     $kick->resultado_casa != NULL
			||     $kick->resultado_visitante != NULL
			     )
			   )
			{
				$this->kick_update->acerto				=	'E'; // Errou tudo.
				
				// Pessoa chutou apenas em um dos times. Colocamos zero no outro.
				$kick_casa						=	( $kick->kick_casa == NULL )      ? 0 : $kick->kick_casa;
				$kick_visitante						=	( $kick->kick_visitante == NULL ) ? 0 : $kick->kick_visitante;
				/*
				 *  Calcula os novos pontos.
				 */
				// Acertou quem ganhou?
					// 16 para acertar quem ganhou
					// Casa ganhou
				if ( ( $kick->resultado_casa > $kick->resultado_visitante
				&&     $kick_casa > $kick_visitante
				     )
					// Visitante Ganhou
				||   ( $kick->resultado_casa < $kick->resultado_visitante
				&&     $kick_casa < $kick_visitante
				     )
				   )
				{
					$this->kick_update->pontos_kick			=	$this->kick_update->pontos_kick + ( $this->fator_kiks * $this->peso_vitoria );
					$this->kick_update->acerto			=	'V'; // Acertou vitória.
				}
	
				// Acertou empate?
					// 10 para empate
				if ( ( ( $kick_casa
				&&       $kick_visitante
				       )
				||     ( $kick_casa != NULL
				&&       $kick_visitante != NULL
				       )
				     )
				&&   $kick->resultado_casa   ==  $kick->resultado_visitante
				&&   $kick_casa        ==  $kick_visitante
				   )
				{
					$this->kick_update->pontos_kick			=	$this->kick_update->pontos_kick + ( $this->fator_kiks * $this->peso_vitoria );
					$this->kick_update->acerto			=	'P'; // Acertou empate.
				}

				$acertou_gols						=	'E';
				// Acertou qtde gol 1 ou as 2 equipes?
					// Acertar CASA
				if ( $kick_casa == $kick->resultado_casa )
				{
					if ( $kick_casa == 0 )
					{
						$this->kick_update->pontos_gols		=	$this->kick_update->pontos_gols + $this->fator_kiks;
					}
					else
					{
						$this->kick_update->pontos_gols		=	$this->kick_update->pontos_gols + ( $this->fator_kiks * $kick_casa );
					}
					
					$acertou_gols					=	'G1';
				}
				// Não acertou os gols em cheio, mas acerto que era Vitório ou Empate, leve pontos proporcionais aos gols chutados.
				// Nova regra para Gols. A partir de 20/02/2013.
				// 	Antes todos levavam os gols proporcionais.
				elseif ( date( $kick->data_hora_jogo ) < date( '2013-02-20 00:00:00' )
				||       ( $this->kick_update->acerto == 'V'
				||         $this->kick_update->acerto == 'P'
				         )
				       )
				{
					$this->kick_update->pontos_gols			=	$this->kick_update->pontos_gols + ( $this->fator_kiks * ( ( $kick_casa > $kick->resultado_casa ) ? $kick->resultado_casa : $kick_casa ) );
				}

					// Acertar visitante
				if ( $kick_visitante == $kick->resultado_visitante )
				{
					if ( $kick_visitante == 0 )
					{
						$this->kick_update->pontos_gols		=	$this->kick_update->pontos_gols + $this->fator_kiks;
					}
					else
					{
						$this->kick_update->pontos_gols		=	$this->kick_update->pontos_gols + ( $this->fator_kiks * $kick_visitante );
					}

					if ( $acertou_gols == 'G1' ) // Já estava G1, então é uma G2.
					{
						$acertou_gols				=	'G2'; // Acertou os 2 gols. Vira VT ou PT abaixo.
					}
				}
				// Não acertou os gols em cheio, mas acerto que era Vitório ou Empate, leve pontos proporcionais aos gols chutados.
				// Nova regra para Gols. A partir de 20/02/2013.
				// 	Antes todos levavam os gols proporcionais.
				elseif ( date( $kick->data_hora_jogo ) < date( '2013-02-20 00:00:00' )
				||       ( $this->kick_update->acerto == 'V'
				||         $this->kick_update->acerto == 'P'
				         )
				       )
				{
					$this->kick_update->pontos_gols			=	$this->kick_update->pontos_gols + ( $this->fator_kiks * ( ( $kick_visitante > $kick->resultado_visitante ) ? $kick->resultado_visitante : $kick_visitante ) );
				}

				// Ajusta acertos
				if ( $this->kick_update->acerto == 'V' )
				{
					if ( $acertou_gols == 'G2' )
					{
						$this->kick_update->acerto		=	'VT'; // Acertou os 2 gols. Vira VT.
					}
					elseif ( $acertou_gols == 'G1' )
					{
						$this->kick_update->acerto		=	'V1'; // Acertou o 1 gol. Vira V1.
					}
				}
				elseif ( $this->kick_update->acerto == 'P' )
				{
					if ( $acertou_gols == 'G2' )
					{
						$this->kick_update->acerto		=	'PT'; // Acertou os 2 gols. Vira PT.
					}
					elseif ( $acertou_gols == 'G1' ) // Nunca vai ocorrer
					{
						$this->kick_update->acerto		=	'P'; // Não existe P1.
					}
				}
				elseif ( $acertou_gols == 'G1' ) // Nunca vai ocorrer
				{
					$this->kick_update->acerto			=	'G1'; // Errou tudo, mas acertou os gols de uma equipe.
				}
				
				if ( $this->kick_update->acerto == 'VT' || $this->kick_update->acerto == 'PT' )
				{
					$this->kick_update->pontos_kick			=	$this->kick_update->pontos_kick + $this->acrescimo_por_cheio;
				}
			}

			// Registra as qtdes atuais para os acumuladores de rodada e campeonato.
			if ( $this->kick_update->acerto == 'N' ) // Não chutou.
			{
				$this->add_value( 'qtde_jogos_sem_chute', 1 );
			}
			else
			{
				$this->add_value( 'pontos_kick', $this->kick_update->pontos_kick );

				$this->add_value( 'pontos_gols', $this->kick_update->pontos_gols );

				$this->add_value( 'pontos_power', $this->kick_update->pontos_power );
				
				$this->add_value( 'qtde_jogos_com_chute', 1 );

				if ( $this->kick_update->acerto == 'VT' ) // Vitoria e tudo
				{
					$this->add_value( 'qtde_acertou_vitoria_tudo', 1 );
				}
				elseif ( $this->kick_update->acerto == 'V1' ) // Vitoria e 1 gol
				{
					$this->add_value( 'qtde_acertou_vitoria_gol_1_equipe', 1 );
				}
				elseif ( $this->kick_update->acerto == 'V' ) // Vitoria apenas
				{
					$this->add_value( 'qtde_acertou_vitoria', 1 );
				}
				elseif ( $this->kick_update->acerto == 'PT' ) // Empate e os gols.
				{
					$this->add_value( 'qtde_acertou_empate_tudo', 1 );
				}
				elseif ( $this->kick_update->acerto == 'P' ) // Empate apenas
				{
					$this->add_value( 'qtde_acertou_empate', 1 );
				}
				elseif ( $this->kick_update->acerto == 'G1' ) // Apenas o gol de uma equipe.
				{
					$this->add_value( 'qtde_acertou_apenas_gol_1_equipe', 1 );
				}
				elseif ( $this->kick_update->acerto == 'E' ) // Errou tudo.
				{
					$this->add_value( 'qtde_errou_tudo', 1 );
				}
			}
			// fim: Cálculo padrão de pontos.

			// Cálculo de poderes.
			/**
			 *  Calcula o poder "Zebra".
			 */
			if ( $this->kick_update->acerto == 'V'
			||   $this->kick_update->acerto == 'VT'
			||   $this->kick_update->acerto == 'V1'
			   )
			{
				if ( ( $kick->resultado_casa > $kick->resultado_visitante
				&&     ( isset( $this->ar_equipes_zebra )
				&&       key_exists( $kick->equipe_id_casa, $this->ar_equipes_zebra )
				       )
				     )
					// Visitante Ganhou
				||   ( $kick->resultado_casa < $kick->resultado_visitante
				&&     ( isset( $this->ar_equipes_zebra )
				&&       key_exists( $kick->equipe_id_visitante, $this->ar_equipes_zebra )
				       )
				     )
				   )
				{
					if ( is_object( $this->kick_zebra_power_update ) )
					{
						$this->kick_zebra_power_update->pontos			=	$this->kick_update->pontos_kick; // Damos a mesma quantidade de pontos obtidas no chute. Isso dobra os pontos.
					}
					else
					{
						$this->kick_zebra_power_update				=	new stdClass();
						$this->kick_zebra_power_update->id			=	NULL;
						$this->kick_zebra_power_update->kick_id			=	$this->kick_update->id;
						$this->kick_zebra_power_update->power_id		=	ZEBRA;
						$this->kick_zebra_power_update->pontos_duelo		=	NULL;
						$this->kick_zebra_power_update->pontos			=	$this->kick_update->pontos_kick; // Damos a mesma quantidade de pontos obtidas no chute. Isso dobra os pontos.
					}
					$this->kick_update->pontos_power				=	$this->kick_update->pontos_power + $this->kick_zebra_power_update->pontos;
					$this->add_value( 'pontos_power', $this->kick_zebra_power_update->pontos, ZEBRA, 1 );

					$this->kick_zebra_power_update->processado_power		=	'A'; // Criamos como automático.
					$this->ar_kick_power[ ZEBRA ]					=	$this->kick_zebra_power_update;
				}
			} // fim: Zebra

			/**
			 * Calcula o poder "Barbada".
			 */
			if ( is_object( $this->kick_barbada_power_update ) // Foi colocado o poder no jogo.
			&&   $kick->resultado_casa !== NULL // e o jogo tem algum resultado.
			&&   $kick->resultado_visitante !== NULL
			   )
			{
				if ( ( $this->kick_update->acerto == 'V'
				||     $this->kick_update->acerto == 'VT'
				||     $this->kick_update->acerto == 'V1'
				||     $this->kick_update->acerto == 'P'
				||     $this->kick_update->acerto == 'PT'
				     )
//				&&   $this->ar_powers_rodada_atual[ BARBADA ]->qtde_usada < $this->ar_powers_rodada_atual[ BARBADA ]->qtde_liberado
				   )
				{
					// A pessoa acertou o resultado
					// Damos a mesma quantidade de pontos obtidas no chute. Isso dobra os pontos.
					$this->kick_barbada_power_update->pontos		=	$this->kick_update->pontos_kick;
					$this->kick_barbada_power_update->anulado		=	'N'; 
					
					$this->kick_update->pontos_power			=	$this->kick_update->pontos_power + $this->kick_barbada_power_update->pontos;
					$this->add_value( 'pontos_power', $this->kick_barbada_power_update->pontos, BARBADA, 1 );
				}
				else
				{
					// Se o jogo já terminou então podemos indicar que o poder não gerou kiks ao kiker.
					if ( $kick->jogo_encerrado == 'S' )
					{
						$this->kick_barbada_power_update->anulado		=	'S'; 
						//$this->ar_powers_rodada_atual[ BARBADA ]->qtde_usada	-=	1; // Devolve o poder. Não devolve mais, desativei.
					}
/* Retirado a pedidos dos usuários	// A pessou ERROU. Perde ponto.
					// Damos a mesma quantidade de pontos obtidas no chute, mas negativo. Isso zera os pontos.
					$this->kick_barbada_power_update->pontos		=	$this->peso_vitoria * (-1);
*/
/*se voltar a punição, tirar esta linha*/$this->kick_barbada_power_update->pontos		=	0;
				}

				$this->kick_barbada_power_update->processado_power		=	'S';
				$this->ar_kick_power[ BARBADA ]					=	$this->kick_barbada_power_update;
			} // fim: Barbada

			/**
			 * Calcula o poder "Guru".
			 */

			if ( is_object( $this->kick_guru_power_update ) ) // Foi colocado o poder no jogo.
			{
				// Usaremos o poder se a pessoa errar tudo.
				// Para este caso ele receberá um acerto parcial.
				if ( ( $this->kick_update->acerto != 'V'
				&&     $this->kick_update->acerto != 'VT'
				&&     $this->kick_update->acerto != 'V1'
				&&     $this->kick_update->acerto != 'P'
				&&     $this->kick_update->acerto != 'PT'
				&&     $this->kick_update->acerto != 'N' // Se a pessoa não jogar o poder não será ativado.69862
				     )
//				&&   $this->ar_powers_rodada_atual[ GURU ]->qtde_usada < $this->ar_powers_rodada_atual[ GURU ]->qtde_liberado
				   )
				{
					// Damos o peso vitória e os gols da partida como pontos do chute.
					$this->kick_guru_power_update->pontos			=	$this->peso_vitoria;
					$this->kick_guru_power_update->anulado			=	'N';
					$gols_casa						=	( $kick->resultado_casa == 0 ) ? $this->fator_kiks : $kick->resultado_casa;
					$gols_visitante						=	( $kick->resultado_visitante == 0 ) ? $this->fator_kiks : $kick->resultado_visitante;

					$gols							=	$gols_casa + $gols_visitante;
					
					// Ativo em 28/03/2013
					if ( date( $kick->data_hora_jogo ) > date( '2013-03-28 00:00:00' ) )
					{
						// Retira dos gols os acertos em gols que a pessoa fez.
						$gols						=	$gols - $this->kick_update->pontos_gols;
					}

					$this->kick_guru_power_update->pontos			=	$this->kick_guru_power_update->pontos + $gols;
					unset( $gols_casa );
					unset( $gols_visitante );
					
					$this->kick_update->pontos_power			=	$this->kick_update->pontos_power + $this->kick_guru_power_update->pontos;
					$this->add_value( 'pontos_power', $this->kick_guru_power_update->pontos, GURU, 1 );

				}
				// Usaremos o poder se a pessoa acertar parcialmente o placar.
				// Para este caso ele receberá o complemento para ficar acerto cheio.
				elseif ( ( $this->kick_update->acerto == 'V'
				||         $this->kick_update->acerto == 'V1'
				||         $this->kick_update->acerto == 'P'
				         )
				&&       $this->kick_update->acerto != 'N' // Se a pessoa não jogar o poder não será ativado.69862
				// Ativo em 28/03/2013
				&&       date( $kick->data_hora_jogo ) > date( '2013-03-28 00:00:00' )
				      )
				{
					// Damos o acréscimo por aceito em cheio.
					$this->kick_guru_power_update->pontos			=	$this->acrescimo_por_cheio;
					$this->kick_guru_power_update->anulado			=	'N';
					$gols_casa						=	( $kick->resultado_casa == 0 ) ? $this->fator_kiks : $kick->resultado_casa;
					$gols_visitante						=	( $kick->resultado_visitante == 0 ) ? $this->fator_kiks : $kick->resultado_visitante;

					$gols							=	$gols_casa + $gols_visitante;
					
					// Retira dos gols os acertos em gols que a pessoa fez.
					$gols							=	$gols - $this->kick_update->pontos_gols;
										
					$this->kick_guru_power_update->pontos			=	$this->kick_guru_power_update->pontos + $gols;
					unset( $gols_casa );
					unset( $gols_visitante );
					
					$this->kick_update->pontos_power			=	$this->kick_update->pontos_power + $this->kick_guru_power_update->pontos;
					$this->add_value( 'pontos_power', $this->kick_guru_power_update->pontos, GURU, 1 );

				}
				// fim: Ativo em 28/03/2013
				else
				{
					// Se o jogo já terminou então podemos indicar que o poder não gerou kiks ao kiker.
					if ( $kick->jogo_encerrado == 'S' )
					{
						$this->kick_guru_power_update->anulado		=	'S';
						//$this->ar_powers_rodada_atual[ GURU ]->qtde_usada	-=	1; // Devolve o poder. Não devolve mais, desativei.
					}
					$this->kick_guru_power_update->pontos			=	0;
				}
				
				$this->kick_guru_power_update->processado_power			=	'S';
				$this->ar_kick_power[ GURU ]					=	$this->kick_guru_power_update;
			} // fim: Guru
			
			/*
			 * O Poder QQI é calculado no save da pessoa, pois precisamos olhar todos os jogos da rodada para decidir se a pessoa tem o não o QQI.
			 */
			// fim: Cálculo poderes.
			
			// Salva o cálculo do kick.
			$this->salvar_kick();
		}
		$query_kicks->free_result();

		// Salva a última pessoa.
		$this->salvar_pessoa();

		echo "fim calcular_kiks<br/>\n";
		log_message( 'debug', 'fim calcular_kiks((()))' );

		echo "<br/>\nCALCULANDO POSICAO_GERAL<br/>\n";
		$this->count_pessoa		=	0;
		$this->calcular_posicao_kiks( $rodada_fase_id, $campeonato_versao_id ); // Ativa o cálculo para os poderes.
		echo "fim CALCULANDO POSICAO_GERAL<br/>\n";

		echo "...pico memória=" . round((( memory_get_peak_usage(true) / 1024) / 1024), 2) . "\n";
		if ( $this->show_log ) echo "<br/>\n<br/>\n<br/>\n";
	}
	
	/**
	 * Calcula a posição de cada pessoa dentro do ranking geral do kikbook.
	 */
	public function calcular_posicao_kiks( $rodada_fase_id, $campeonato_versao_id )
	{
		// Atualizar a coluna posicao_geral das tabelas de ranking.
		
		/*
		 *  Rodada Fase
		 */
		
		// Atualizamos o peso da rodada para gerar kiks para as pessoas.
		//		Este cálculo também está no "rodada_fase_model.php" em set_inicio_fim.
		$qry_qtde_jogos			=	$this->db->query	(
										"
										select	count( jogo.id ) qtde_jogos
										from	jogo			AS	jogo
										where  	jogo.rodada_fase_id	=	$rodada_fase_id
										"
										);

		$qtde_jogos			=	0;
		foreach( $qry_qtde_jogos->result_object() as $qtd_jog )
		{
			$qtde_jogos		=	$qtd_jog->qtde_jogos;
		}

		if ( $qtde_jogos > 0 )
		{
			$this->rodada_base->peso_kik	=	10 / $qtde_jogos; // 10 é o max ou min por jogo.
		}
		else
		{
			$this->rodada_base->peso_kik	=	0; // Sem jogos, sem pontos.
		}
		
		$data_inicio_rodada		=	new DateTime( $this->rodada_base->data_inicio );
		// Grava a rodada apenas após o loop.
	
		$posicao			=	0;
		foreach( $this->pessoa_rodada_fase->get_all_by_where( "rodada_fase_id = {$rodada_fase_id} and ativo = 'S'" ) as $ranking )
		{
			// Estorna os movimentos anteriores desta rodada.
			$this->kik_movimento->sub_movto( $ranking->pessoa_id, $rodada_fase_id, 'R' );

			// Conta a posição da pessoa.
			$ranking->posicao_geral	=	++$posicao;
			$this->pessoa_rodada_fase->update( $ranking );
			
			// Só calculamos para rodadas finalizadas. Envolve cálculos e estornos de kiks (milhas).
			if ( $this->rodada_base->finalizada )
			{
				// Kiks sobre pontos.
				$qtde_kik				=	round( ( $ranking->pontos_kick * $this->rodada_base->peso_kik ), 2 ) + $ranking->pontos_power;
				$this->kik_movimento->add_movto( $ranking->pessoa_id, $ranking->rodada_fase_id, 'R', $qtde_kik, "Pontos conquistados.", 'E', $ranking->rodada_data_fim );

				// Kiks para os 3 melhores.
				if ( $posicao == 1
				||   $posicao == 2
				||   $posicao == 3
				   )
				{
					if ( $posicao == 1 )
					{
						$qtde_kik		=	100;
						$titulo_posicao		=	'Primeiro';
					}
					elseif ( $posicao == 2 )
					{
						$qtde_kik		=	50;
						$titulo_posicao		=	'Segundo';
					}
					elseif ( $posicao == 3 )
					{
						$qtde_kik		=	10;
						$titulo_posicao		=	'Terceiro';
					}
					$ar_values			=	 array	(
											 'aqui'			=>	"/ranking/rodada/{$ranking->rodada_fase_id}/null/null/rodada"
											,'pessoa_nome'		=>	$ranking->nome
											,'nome_rodada'		=>	$ranking->rodada_nome
											,'qtde_kiks'		=>	$qtde_kik
											,'titulo_posicao'	=>	$titulo_posicao
											,'a'			=>	'a'
									 		);
	
					$this->kik_movimento->add_movto( $ranking->pessoa_id, $ranking->rodada_fase_id, 'R', $qtde_kik, "$titulo_posicao colocado na rodada.", 'E', $ranking->rodada_data_fim );
					
					// Notifica os kiks recebidos.
					if ( $this->rodada_base->calculo_encerrado == 'N' ) // Não notificamos num recálculo.
					{
						echo "NOTIFICANDO ----> $ranking->nome como '$titulo_posicao' \n";
						$this->notificacao->notificar( $this->notificacao_template_id_melhor_na_rodada, $ranking->pessoa_id, $ar_values, TRUE );
					}
				}
	
				// Perda de kiks por jogo não realizado.
					// Perda 5 kiks por jogo não feito. Mas pondera pelo peso da rodada.
					// Apenas para rodadas iniciadas após a inscrição do usuário.
				$data_inscricao				=	new DateTime( $ranking->user_data_hora_inscricao );
echo 'Pes=' . $ranking->pessoa_id . ' ' . $data_inicio_rodada->format( "d/m/Y" ) . ' > ' . $data_inscricao->format( "d/m/Y" ) . "\n";
				if ( $data_inicio_rodada > $data_inscricao
				&&   ( $qtde_jogos - $ranking->qtde_jogos_com_chute ) > 0
				&&   $ranking->cadastrado_para_jogar == 'S'
				   )
				{
echo " ..... PERDE \n <br/>";
					$qtde_kik			=	round( ( ( 5 * ( $qtde_jogos - $ranking->qtde_jogos_com_chute ) ) * $this->rodada_base->peso_kik ), 2 );
					$this->kik_movimento->add_movto( $ranking->pessoa_id, $ranking->rodada_fase_id, 'R', $qtde_kik, "Jogo sem chutes.", 'S', $ranking->rodada_data_fim );
				}
			}
		}

		// Só calculamos para rodadas finalizadas. Envolve cálculos e estornos de kiks (milhas).
		if ( $this->rodada_base->finalizada )
		{
			// Uma vez que as notificações tenham sido enviadas, damos o cálculo da rodada encerrado.
			$this->rodada_base->calculo_encerrado			=	'S';
	
			// Atualiza a rodada.
			$this->rodada_fase->update( $this->rodada_base );
		}
		// Rodada atualizada.
		
		/*
		 *  Campeonato
		*/
		// Registra se o campeonato está ou não encerrado.
		$jogos_nao_encerrado_base		=	$this->jogo->get_all_by_where	(	"rod.campeonato_versao_id			=	{$campeonato_versao_id}
												and	( ( jogo.data_hora + interval 120 minute )	>=	now()
												or	  jogo.resultado_casa				IS NULL
												or	  jogo.resultado_visitante			IS NULL
													)
													"
												);
		if ( count( $jogos_nao_encerrado_base ) > 0 )
		{
			$camp_finalizado		=	FALSE;
			$notificar_melhor_rodada	=	FALSE;
			$last_rodada			=	NULL;
		}
		else
		{
			$camp_finalizado		=	TRUE;
			$notificar_melhor_rodada	=	FALSE;
			$last_rodada			=	$this->rodada_fase->get_rodada_atual( $campeonato_versao_id );
		}

		$campeonato_versao_base			=	$this->campeonato_versao->get_one_by_id( $campeonato_versao_id );
 
		$posicao				=	0;
		$calculo_campeonato_encerrado		=	'N';
		foreach( $this->pessoa_campeonato_versao->get_all_by_where( "campeonato_versao_id = {$campeonato_versao_id} and ativo = 'S'" ) as $ranking )
		{
			$ranking->posicao_geral	=	++$posicao;
			$this->pessoa_campeonato_versao->update( $ranking );
			
			// Notificar e dar kiks aos melhores.
			if ( $camp_finalizado )
			{
				// Estorna os movimentos anteriores deste campeonato.
				$this->kik_movimento->sub_movto( $ranking->pessoa_id, $campeonato_versao_id, 'P' );

				if ( is_object( $last_rodada )
				&&   $last_rodada->id == $rodada_fase_id // O campeonato já acabou e estou calculando a última rodada?
				   )
				{
					// Kiks para os 3 melhores.
					if ( $posicao == 1
					||   $posicao == 2
					||   $posicao == 3
					   )
					{
						if ( $posicao == 1 )
						{
							$qtde_kik		=	500;
							$titulo_posicao		=	'Primeiro';
						}
						elseif ( $posicao == 2 )
						{
							$qtde_kik		=	250;
							$titulo_posicao		=	'Segundo';
						}
						elseif ( $posicao == 3 )
						{
							$qtde_kik		=	100;
							$titulo_posicao		=	'Terceiro';
						}
						$ar_values			=	 array	(
												 'aqui'			=>	"/ranking/campeonato/{$campeonato_versao_id}/campeonato"
												,'pessoa_nome'		=>	$ranking->nome
												,'nome_rodada'		=>	$ranking->descr_campeonato
												,'qtde_kiks'		=>	$qtde_kik
												,'titulo_posicao'	=>	$titulo_posicao
												,'a'			=>	'o'
										 		);
						$this->kik_movimento->add_movto( $ranking->pessoa_id, $campeonato_versao_id, 'P', $qtde_kik, "$titulo_posicao colocado no campeonato.", 'E' );

						// Notificamos os 3 primeiros.
						if ( $campeonato_versao_base->calculo_encerrado == 'N' ) // Não notificamos no recalculo.
						{
							$notificar_melhor_rodada	=	TRUE;
							echo "NOTIFICANDO CAMP ----> $ranking->nome como '$titulo_posicao' \n";
							$this->notificacao->notificar( $this->notificacao_template_id_melhor_na_rodada, $ranking->pessoa_id, $ar_values, TRUE );
						}
					}
				}
			}
		}
		if ( $camp_finalizado )
		{
			$campeonato_versao_base->calculo_encerrado			=	'S';
			// Atualiza o campeonato.
			$this->campeonato_versao->update( $campeonato_versao_base );
		}

		/*
		 *  Grupos
		 */
		$grupo_amigos_fase_id_ant			=	-1;
		foreach( $this->pessoa_ranking_grupo_amigos->get_ranking_rodada_existente( $rodada_fase_id, $campeonato_versao_id ) as $ranking )
		{
			if ( $ranking->grupo_amigos_fase_id != $grupo_amigos_fase_id_ant )
			{
				$posicao			=	0;
				$grupo_amigos_fase_id_ant	=	$ranking->grupo_amigos_fase_id;
			}
			$ranking->posicao_geral			=	++$posicao;
			$this->pessoa_ranking_grupo_amigos->update( $ranking );
		}

		/*
		 *  Melhor rodada
		 */
		if ( $camp_finalizado
		&&   $campeonato_versao_base->calculo_encerrado == 'S' // Já foi calculado campeonato, só neste caso calculamos a melhor rodada.
		   )
		{
			$posicao				=	0;
			foreach( $this->pessoa_rodada_fase->get_all_by_where	( "pessoa_rodada_fase.id in	(
														select	pescam.pessoa_rodada_fase_id
														from	pessoa_campeonato_versao pescam
														where	pescam.campeonato_versao_id = {$campeonato_versao_base->id}
														)"
										) as $ranking )
			{
				$posicao			=	$posicao + 1;
				// Kiks para os 3 melhores.
				if ( $posicao == 1
				||   $posicao == 2
				||   $posicao == 3
				   )
				{
					if ( $posicao == 1 )
					{
						$qtde_kik		=	500;
						$titulo_posicao		=	'Primeiro';
					}
					elseif ( $posicao == 2 )
					{
						$qtde_kik		=	250;
						$titulo_posicao		=	'Segundo';
					}
					elseif ( $posicao == 3 )
					{
						$qtde_kik		=	100;
						$titulo_posicao		=	'Terceiro';
					}
					$ar_values			=	 array	(
											 'aqui'			=>	"/ranking/campeonato/{$campeonato_versao_id}/melhor-rodada"
											,'pessoa_nome'		=>	$ranking->nome
											,'nome_rodada'		=>	$ranking->descr_campeonato
											,'qtde_kiks'		=>	$qtde_kik
											,'titulo_posicao'	=>	$titulo_posicao
											,'a'			=>	'o'
									 		);
					$this->kik_movimento->add_movto( $ranking->pessoa_id, $campeonato_versao_id, 'P', $qtde_kik, "$titulo_posicao colocado na Melhor Rodada do campeonato.", 'E' );

					// Notificamos os 3 primeiros.
					if ( $notificar_melhor_rodada )
					{
						echo "NOTIFICANDO MELHOR ----> $ranking->nome como '$titulo_posicao' \n";
						$this->notificacao->notificar( $this->notificacao_template_id_melhor_rodada, $ranking->pessoa_id, $ar_values, TRUE );
					}
				}
				else
				{
					break; // Aqui só precisamos dos 3 primeiros.
				}
			}
		}
	}
	
	/**
	 * Elimina o ranking de uma determinada rodada.
	 */
	public function estorna_calculo( $campeonato_versao_id = NULL )
	{
		if ( $campeonato_versao_id
		&&   is_numeric( $campeonato_versao_id )
		   )
		{
			// Inicializa os kicks das pessoas.
			$this->db->query	(
						"
						update	kick
						set	 processado	=	'N'
							,acerto		=	'N'
							,pontos_kick	=	0
							,pontos_gols	=	0
							,pontos_power	=	0
						where	( processado	=	'S'
						or	  processado	=	'P'
							)
						and    kick.jogo_id	in	(
										select	jogo.id
										from	 jogo
											,rodada_fase AS rod
										where	rod.campeonato_versao_id	= $campeonato_versao_id
										and	jogo.rodada_fase_id		= rod.id
										)
						"
						);

			// Poderes
			$this->db->query	(
						"
						delete from kick_power
						where  processado = 'A'
						and    kick_power.kick_id in	(
										select	kick.id
										from	 kick
											,jogo
											,rodada_fase AS rod
										where	rod.campeonato_versao_id	= $campeonato_versao_id
										and	jogo.rodada_fase_id		= rod.id
										and	kick.jogo_id			= jogo.id
										)
						"
						);

			$this->db->query	(
						"
						update kick_power
						set	 processado	=	'N'
							,pontos		=	0
						where	processado	=	'S'
						and    kick_power.kick_id in	(
										select	kick.id
										from	 kick
											,jogo
											,rodada_fase AS rod
										where	rod.campeonato_versao_id	= $campeonato_versao_id
										and	jogo.rodada_fase_id		= rod.id
										and	kick.jogo_id			= jogo.id
										)
						"
						);

			$this->db->query	(
						"
						update	kick_power
						set	anulado	=	'N'
						where	anulado	=	'S'
						and	kick_power.kick_id in	(
										select	kick.id
										from	 kick
											,jogo
											,rodada_fase AS rod
										where	rod.campeonato_versao_id	= $campeonato_versao_id
										and	jogo.rodada_fase_id		= rod.id
										and	kick.jogo_id			= jogo.id
										)
						"
						);

			// Resumos dos grupos.
			$this->db->query	(
						"
						delete from pessoa_ranking_grupo_amigos_resumo_power
						where	pessoa_ranking_grupo_amigos_id in	(
												select	pesrak.id
												from	 pessoa_ranking_grupo_amigos	AS	pesrak
													,grupo_amigos_fase_rodadas	AS	grpfas
												where	grpfas.campeonato_versao_id	=	$campeonato_versao_id
												and	pesrak.grupo_amigos_fase_id	=	grpfas.grupo_amigos_fase_id
												)
						"
						);

			$this->db->query	(
						"
						delete from pessoa_ranking_grupo_amigos
						where  grupo_amigos_fase_id		in	(
												select	grpfas.grupo_amigos_fase_id
												from	 grupo_amigos_fase_rodadas	AS	grpfas
												where	grpfas.campeonato_versao_id	=	$campeonato_versao_id
												)
						"
						);

			// Resumos das Rodadas.
			$this->db->query	(
						"
						delete from pessoa_rodada_fase_resumo_power
						where	pessoa_rodada_fase_resumo_power.pessoa_rodada_fase_id in	(
															select	pessoa_rodada_fase.id
															from	 pessoa_rodada_fase
																,rodada_fase AS rod
															where	pessoa_rodada_fase.rodada_fase_id	= rod.id
															and	rod.campeonato_versao_id		= $campeonato_versao_id
															)
						"
						);

			$this->db->query	(
						"
						delete from pessoa_rodada_fase_power
						where	pessoa_rodada_fase_power.pessoa_rodada_fase_id	in	(
														select	pessoa_rodada_fase.id
														from	 pessoa_rodada_fase
															,rodada_fase AS rod
														where	pessoa_rodada_fase.rodada_fase_id		= rod.id
														and	rod.campeonato_versao_id			= $campeonato_versao_id
														)
						"
						);

			// Resumos dos Campeonatos.
			$this->db->query	(
						"
						delete from pessoa_campeonato_versao_resumo_power
						where pessoa_campeonato_versao_id 			in	(
														select	id
														from	pessoa_campeonato_versao
														where	campeonato_versao_id				= $campeonato_versao_id
														)
						"
						);

			$this->db->query	(
						"
						update	pessoa_campeonato_versao
						set	 pontos_kick				= 0
							,pessoa_rodada_fase_id			= null
							,pontos_gols				= 0
							,pontos_power				= 0
							,qtde_jogos_com_chute			= 0
							,qtde_jogos_sem_chute			= 0
							,qtde_acertou_vitoria_tudo		= 0
							,qtde_acertou_vitoria_gol_1_equipe	= 0
							,qtde_acertou_vitoria			= 0
							,qtde_acertou_empate_tudo		= 0
							,qtde_acertou_empate			= 0
							,qtde_acertou_apenas_gol_1_equipe	= 0
							,qtde_errou_tudo			= 0
							,qtde_rodada_jogada			= 0
							,posicao_geral				= 0
						where campeonato_versao_id			= $campeonato_versao_id
						"
						);

			// Rodada base da pessoa.
			$this->db->query	(
						"
						update pessoa_rodada_fase
						set	 pontos_kick				= 0
							,pontos_gols				= 0
							,pontos_power				= 0
							,qtde_jogos_com_chute			= 0
							,qtde_jogos_sem_chute			= 0
							,qtde_acertou_vitoria_tudo		= 0
							,qtde_acertou_vitoria_gol_1_equipe	= 0
							,qtde_acertou_vitoria			= 0
							,qtde_acertou_empate_tudo		= 0
							,qtde_acertou_empate			= 0
							,qtde_acertou_apenas_gol_1_equipe	= 0
							,qtde_errou_tudo			= 0
							,jogou_rodada				= 'N'
							,posicao_geral				= 0
						where	pessoa_rodada_fase.rodada_fase_id in	(
												select	rod.id
												from	rodada_fase		AS	rod
												where	rod.campeonato_versao_id	=	$campeonato_versao_id
												)
						"
						);

			// Kiks saldos / movto.
			$movto_base				=		$this->kik_movimento->get_all_by_where	(
															"(	kik_movimento.rodada_fase_id		in	(
																						select	rod.id
																						from	rodada_fase	AS	rod
																						where	rod.campeonato_versao_id	=	$campeonato_versao_id
																						)
															or	kik_movimento.campeonato_versao_id	=	$campeonato_versao_id
															)"
															);
			foreach( $movto_base as $key => $movto )
			{
				if ( $movto->tipo == 'S' )
				{
					$this->db->query	(
								"
								update	kik_saldo
								set	saldo_kik	=	saldo_kik + {$movto->qtde}
								where	id		=	{$movto->kik_saldo_id}
								"
								);
				}
				else
				{
					$this->db->query	(
								"
								update	kik_saldo
								set	saldo_kik	=	saldo_kik - {$movto->qtde}
								where	id		=	{$movto->kik_saldo_id}
								"
								);
				}

				$this->db->query	(
							"
							delete	from kik_movimento
							where	id			=	{$movto->id}
							"
							);
			}
		}
	}
}
/* End of file kick_model.php */
