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
 * @filesource		/application/controllers/chute.php
 * 
 * $Id: chute.php,v 1.25 2013-04-14 12:50:27 junior Exp $
 * 
 */

class Chute extends JX_Page
{
	protected $_revision	=	'$Id: chute.php,v 1.25 2013-04-14 12:50:27 junior Exp $';
	
	function __construct()
	{
		$_config		=	array	(
							 'kick'					=>	array	(
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
														,'master'		=>	TRUE
														,'part_of_view'		=>	'vw_kick_power'
														)
							,'kick_power'				=>	array	(
														 'read_write'		=>	'write'
														,'r_table_name'		=>	'kick'
														,'show'			=>	TRUE
														,'show_style'		=>	'none'
														,'hide_columns'		=>	''
														,'readonly_columns'	=>	''
														,'where'		=>	''
														,'orderby'		=>	''
														,'max_rows'		=>	999999
														,'delete_rule'		=>	'restrict'
														,'master'		=>	FALSE
														,'part_of_view'		=>	'vw_kick_power'
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
														)
							,'pessoa_rodada_fase_power'		=>	array	(
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
							,'pessoa_campeonato_versao'		=>	array	(
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
							,'pessoa'		=>	array	(
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
							);
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	/**
	 * Página princial do site.
	 */
	public function index( $rodada_fase_id = NULL, $campeonato_versao_id = NULL )
	{
		if ( $this->singlepack->user_connected() )
		{
			$this->show( $rodada_fase_id, $campeonato_versao_id, 1 );
		}
		else
		{
			$this->load->view( 'chute_index.html' );
		}
	}
	
	public function crono( $data_selecionada = NULL, $campeonatos = 'M' )
	{
		$data_agora						=	new DateTime( date( 'Y-m-d' ) );
		
		if ( $data_selecionada == 'null' )
		{
			$data_selecionada				=	NULL;
		}
		
		$data_selecionada					=	new DateTime( $data_selecionada );

		// Obtém dados para o calendário.
		if ( empty( $data_selecionada ) )
		{
			$data_selecionada				=	$this->singlepack->get_sessao( 'chute_data_inicio' );
			if ( empty( $data_selecionada ) )
			{
				$data_selecionada			=	$data_agora;
			}
			else
			{
				$data_selecionada			=	new DateTime( $data_selecionada );
			}
		}
		
		$this->singlepack->set_sessao( 'chute_data_inicio', $data_selecionada->format( 'Y-m-d' ) );
		$this->singlepack->set_sessao( 'chute_exibicao', '1' );
		
		$data_fim						=	new DateTime( $data_selecionada->format( 'Y-m-d' ) );
		$data_fim->add( new DateInterval( 'P7D' ) );

		// Liberamos, neste tipo de página, apenas 7 dias de chutes entre todos os campeonatos.
		$this->show( NULL, NULL, 0, $data_selecionada, $data_fim, strtoupper( $campeonatos ) );
	}

	public function campeonato( $campeonato_versao_id = NULL, $exibicao = 1 )
	{
		// Confirma se a entrada é numérica.
		if ( !is_numeric( $campeonato_versao_id ) )
		{
			$campeonato_versao_id			=	NULL;
		}
		$this->rodada_fase->set_id_sessao( NULL );
		$this->singlepack->set_sessao( 'chute_exibicao', $exibicao );

		$this->show( NULL, $campeonato_versao_id, $exibicao );
	}

	public function dialog( $rodada_fase_id = NULL, $pessoa_id = NULL )
	{
		// Confirma se a entrada é numérica.
		if ( !is_numeric( $rodada_fase_id ) )
		{
			$rodada_fase_id				=	NULL;
		}
		if ( !is_numeric( $pessoa_id ) )
		{
			$pessoa_id				=	NULL;
		}
		$campeonato_versao_id_sess			=	$this->campeonato_versao->get_id_sessao();

		$this->kick->_prep_show( $rodada_fase_id, $campeonato_versao_id_sess, $pessoa_id );
		
		$this->load->view( 'chute.html', array( 'readonly' => 'TRUE', 'show_header' => 'FALSE', 'dialog' => 'TRUE' ) );
	}

	public function rodada( $rodada_fase_id = NULL, $campeonato_versao_id = NULL, $exibicao = 0, $data_inicio = NULL, $data_fim = NULL, $campeonatos = 'M' )
	{
		$this->show( $rodada_fase_id, $campeonato_versao_id, $exibicao = '1', $data_inicio, $data_fim, $campeonatos );
	}

	protected function show( $rodada_fase_id = NULL, $campeonato_versao_id = NULL, $exibicao = NULL, $data_inicio = NULL, $data_fim = NULL, $campeonatos = 'M' )
	{
		$campeonato_versao_id							=	$this->campeonato_versao->get_id_selecionado( $campeonato_versao_id, $rodada_fase_id );
		$rodada_fase_id								=	$this->rodada_fase->get_id_selecionado( $rodada_fase_id, $campeonato_versao_id, TRUE ); // TRUE para rodada aberta.
		$campeonato_versao_id							=	$this->rodada_fase->get_id_campeonato( $campeonato_versao_id );

		if ( empty( $exibicao ) && $exibicao !== 0 )
		{
			$exibicao							=	$this->singlepack->get_sessao( 'chute_exibicao' );
			if ( empty( $exibicao ) )
			{
				$exibicao						=	0; // crono
			}
		}
		$this->singlepack->set_sessao( 'chute_exibicao', $exibicao );

		if ( $exibicao == 0 ) // Chute cronológico
		{
			if ( empty( $data_inicio ) )
			{
				$data_agora						=	new DateTime( date( 'Y-m-d' ) );
				// Obtém dados para o calendário.
				$data_inicio						=	$this->singlepack->get_sessao( 'chute_data_inicio' );
				if ( empty( $data_inicio ) )
				{
					$data_inicio					=	$data_agora;
				}
				else
				{
					$data_inicio					=	new DateTime( $data_inicio );
				}
				
				$this->singlepack->set_sessao( 'chute_data_inicio', $data_inicio->format( 'Y-m-d' ) );
				$this->singlepack->set_sessao( 'chute_exibicao', '1' );
				
				$data_fim						=	new DateTime( $data_inicio->format( 'Y-m-d' ) );
				$data_fim->add( new DateInterval( 'P7D' ) );
			}
			
			$this->kick->_prep_show( NULL, NULL, $pessoa_id = NULL, $data_inicio->format( 'Y-m-d' ), $data_fim->format( 'Y-m-d' ), NULL, NULL, $controller = 'Chute Crono', $campeonatos );
			$rows_chutes							=	$this->load->get_var( 'rows_chutes' );
			$images								=	$this->load->get_var( 'images' );

			$data								=	array	(
													 'rows_chutes'			=> $rows_chutes
													,'total_rows'			=> count( $rows_chutes )
													,'images'			=> $images
													);
			$this->load->vars( $data );
			$this->load->view( 'chute_crono.html' );
		}
		elseif ( $exibicao == 1 ) // Chute com classificação
		{
			/*
			 * Exibição em colunas:
			 * 	- Classificação e chutes em colunas
			 * 	- Criamos um array de grupos
			 * 		- Chutes e classificação.
			 */
			$rodada_fase_sel						=	$this->rodada_fase->get_rodada_selecionada();
			$rodada_fase_anterior						=	$this->rodada_fase->get_rodada_anterior( $rodada_fase_sel->data_inicio, $campeonato_versao_id, $rodada_fase_sel->tipo_fase );
			if ( $rodada_fase_anterior )
			{
				$rodada_fase_anterior_id				=	$rodada_fase_anterior->id;
			}
			else
			{
				$rodada_fase_anterior_id				=	$rodada_fase_id;
			}

			$this->campeonato_versao_classificacao->_prep_show( $rodada_fase_anterior_id, FALSE, FALSE );
			$rows_classif							=	$this->load->get_var( 'rows_classif' );
			if ( count( $rows_classif ) == 0 ) // Não tem classificação calculada para esta rodada.
			{
				$this->campeonato_versao_classificacao->copy_rodada_to( $rodada_fase_id );
				$this->campeonato_versao_classificacao->_prep_show( $rodada_fase_id, FALSE, FALSE );
				$rows_classif						=	$this->load->get_var( 'rows_classif' );
			}

			$this->kick->_prep_show( $rodada_fase_id, $campeonato_versao_id );
			
			$rows_grupos							=	array();
			$grupo_id_ant							=	-1;
			$images								=	$this->load->get_var( 'images' );

			// Coloca as linhas da classificação aos grupos já criados pelos chutes.
			foreach( $rows_classif as $row )
			{
				if ( ( ( is_null( $row->grupo_id ) ) ? 999999999 : $row->grupo_id ) != $grupo_id_ant )
				{
					$grupo_id_ant					=	( is_null( $row->grupo_id ) ) ? 999999999 : $row->grupo_id;
					
					$rows_grupos[ $grupo_id_ant ]			=	new stdClass();
					$rows_grupos[ $grupo_id_ant ]->grupo_id		=	$grupo_id_ant;
					$rows_grupos[ $grupo_id_ant ]->nome_grupo	=	$row->nome_grupo;
					$rows_grupos[ $grupo_id_ant ]->rodada_tipo	=	$row->rodada_tipo;
					$rows_grupos[ $grupo_id_ant ]->rodada_tipo_fase	=	$row->rodada_tipo_fase;
					$rows_grupos[ $grupo_id_ant ]->rows_chutes	=	array();
					$rows_grupos[ $grupo_id_ant ]->rows_classif	=	array();
				}

				$rows_grupos[ $grupo_id_ant ]->rows_classif[]		=	$row;
			}
			// Cria a quebra de grupos a partir das linhas dos chutes.
		   	$rodada_mista							=	FALSE;
		   	$total_rows_chutes						=	0;
			foreach( $this->load->get_var( 'rows_chutes' ) as $row )
			{
				$grupo_id						=	( is_null( $row->grupo_id ) ) ? 999999999 : $row->grupo_id;
				
				if ( !key_exists( $grupo_id, $rows_grupos ) )
				{
					$rows_grupos[ $grupo_id ]->grupo_id		=	$grupo_id;
					$rows_grupos[ $grupo_id ]->nome_grupo		=	$row->nome_grupo;
					$rows_grupos[ $grupo_id ]->rodada_tipo		=	$row->rodada_tipo;
					$rows_grupos[ $grupo_id ]->rodada_tipo_fase	=	$row->rodada_tipo_fase;
					$rows_grupos[ $grupo_id ]->rows_chutes		=	array();
					$rows_grupos[ $grupo_id ]->rows_classif		=	array();
				}

				$rows_grupos[ $grupo_id ]->rows_chutes[]		=	$row;
			   	$total_rows_chutes					+=	1;

				if ( $row->rodada_tipo == 'G'
				&&   $row->rodada_tipo_fase == "M"
				   )
				{
				   	$rodada_mista					=	TRUE;
				}
			}
			
			$data								=	array	(
													 'rows_grupos'			=> $rows_grupos
													,'total_rows'			=> count( $rows_grupos )
													,'total_rows_chutes'		=> $total_rows_chutes
													,'images'			=> $images
													);
			$this->load->vars( $data );

			if ( $rodada_mista )
			{
				$this->load->view( 'chute_clas_mista.html' );
			}
			else
			{
				$this->load->view( 'chute_clas.html' );
			}
		}
		else
		{
			$this->kick->_prep_show( $rodada_fase_id, $campeonato_versao_id );
			$this->load->view( 'chute.html' );
		}
	}

	public function salvar()
	{
		// Pega a rodada selecionada pelo usuário.
		$rodada_fase_id						=	$this->rodada_fase->get_id_sessao(); // TRUE para rodada aberta.

		// Prepara retorno ao json.
		$this->ret_array					=	array();
		$this->ret_array['fail']				=	array();
		$this->ret_array['ok']					=	array();
		$this->ret_array['warning']				=	array();
		$this->ret_array['kick']				=	array();
		$ar_rodada						=	array();
		$this->ret_array['rodada']				=	$ar_rodada;
//		$this->ret_array['rodada']['qtde_kicks']		=	array();
//		$this->ret_array['rodada']['pessoa_rodada_fase_power']	=	array();
		$this->ret_array['kick_power']				=	array();

		if ( !$this->singlepack->user_connected() ) // Se não estiver conectado, forçamos um realodo ao retornar o XML(json) ao JS.
		{
			$this->ret_array['reload']			=	'TRUE';
			$this->ret_array[ 'fail' ][]			=	array	(
											 'message_type'	=>	'error'
											,'message'	=>	'Você não está mais conectado. Os chutes NÃO foram salvos e vamos alterar para a página de Classificação.'
											);
		}
		else
		{
			$pessoa_base					=	$this->pessoa->get_one_by_id( $this->singlepack->get_pessoa_id() );

			$this->ret_array['reload']			=	'FALSE';

			// Tabela de Chutes
			if ( $this->input->get_post_multi( 'kick' ) )
			{
				$kick_fail				=	FALSE;
				foreach( $this->input->extract_rows( $this->kick->get_fields_name(), 'kick' ) as $row )
				{
					$ret				=	$this->kick->update( $row->data, $row->cube_keys, $from_batch = FALSE, $row->record_status, $row->record_valid );
					
					if ( !$ret )
					{
						$ret_msg		=	$this->kick->get_field_msg( /* $_field */null, /* $_type */ null, $this->kick->get_curr_seq_id(), TRUE );
						$kick_fail		=	TRUE;
					}
					else
					{
						$ret_msg		=	NULL;
						$row->data->id		=	( is_numeric( $ret ) ) ? $ret : NULL;
					}
					
					$jogo_base			=	$this->jogo->get_one_by_id( $row->data->jogo_id );

					$this->ret_array[ 'kick' ][]	=	array	(
											 'id'			=>	$row->data->id
											,'jogo_id'		=>	$row->data->jogo_id
											,'rodada_fase_id'	=>	$jogo_base->rodada_fase_id
											,'kick_casa'		=>	$row->data->kick_casa
											,'kick_visitante'	=>	$row->data->kick_visitante
											,'msg_error'		=>	$ret_msg
											);
				}
				
				if ( $kick_fail )
				{
					$this->ret_array[ 'warning' ][]		=	array	(
												 'message_type'	=>	'error'
												,'message'	=>	'Um ou mais chutes não foram salvos. Veja a mensagem abaixo.'
												);
				}
				else
				{
					$this->ret_array[ 'ok' ][]		=	array	(
												 'message_type'	=>	'success'
												,'message'	=>	"Chutes salvos."
												);
				}
			}
			else 
			{
				// Não recebeu dados para processar.
				$this->ret_array[ 'fail' ][]			=	array	(
												 'id'		=>	null
												,'message_type'	=>	'warning'
												,'message'	=>	'Nenhum chute enviado.'
												);
			}

			foreach( $this->ret_array[ 'kick' ] as $kick )
			{
				if ( !key_exists( $kick[ 'rodada_fase_id' ], $ar_rodada ) )
				{
					$obj_rodada				=	new stdClass();
					$obj_rodada->rodada_fase_id		=	$kick[ 'rodada_fase_id' ];
					$obj_rodada->qtde_kicks			=	NULL;
					$obj_rodada->pessoa_rodada_fase_power	=	NULL;

					$ar_rodada[ $kick[ 'rodada_fase_id' ] ]	=	$obj_rodada;
				}
			}

			// Salva os poderes digitados na Tabela de poderes do chute.
			if ( $this->input->get_post_multi( 'kick_power' ) )
			{
				$kick_power_fail				=	FALSE;
				foreach( $this->input->extract_rows( array( 'id', 'kick_id', 'power_id' ), 'kick_power' ) as $row )
				{
					$ret_msg				=	NULL;
					if ( ( $row->data->kick_id // Tem que ter chute
					&&     $row->data->power_id // tem que ter poder para salvar.
					     )
					||   $row->data->id < 0 // Ou estarmos deletando o poder
					   )
					{
						// Verifica se já existe o poder na base antes de inserir.
						//	Está ocorrendo um problemas, sem este código, que faz o mesmo poder ser grava N vezes.
						if ( empty( $row->data->id ) )
						{
							$kick_power_base		=	$this->kick_power->get_one_by_where( "kick_id = {$row->data->kick_id} and power_id = {$row->data->power_id}" );
							if ( $kick_power_base )
							{
								$row->data->id		=	$kick_power_base->id;
							}
						}
						// Grava o poder.
						$ret				=	$this->kick_power->update( $row->data, $row->cube_keys, $from_batch = FALSE, $row->record_status, $row->record_valid );

						if ( !$ret )
						{
							$ret_msg		=	$this->kick_power->get_field_msg( /* $_field */null, /* $_type */ null, $this->kick_power->get_curr_seq_id(), TRUE );
							$kick_power_fail	=	TRUE;
						}
						else
						{
							$ret_msg		=	NULL;
							$row->data->id		=	( is_numeric( $ret ) ) ? $ret : NULL;
						}
					}

					// Preenche com todos os chutes um poder, mesmo que seja nulo.
					$this->ret_array[ 'kick_power' ][]	=	array	(
												 'id'			=>	$row->data->id
												,'kick_id'		=>	$row->data->kick_id
												,'power_id'		=>	$row->data->power_id
												,'msg_error'		=>	$ret_msg
												);
				}
				if ( $kick_power_fail )
				{
					$this->ret_array[ 'warning' ][]		=	array	(
												 'message_type'	=>	'error'
												,'message'	=>	'Falha ao salvar os poderes dos chutes.'
												);
				}
				else
				{
					$this->ret_array[ 'ok' ][]		=	array	(
												 'message_type'	=>	'success'
												,'message'	=>	"Poderes salvos."
												);
				}
			}
			// fim: Salva os poderes digitados na Tabela de poderes do chute.

			foreach( $ar_rodada as $rod_id => $values )
			{
				$rodada_fase_base				=	$this->rodada_fase->get_one_by_id( $rod_id );
				// Verifica se a pessoa está cadastrada no campeonato. Não estando cadastramos ela para que sejam enviadas as notificações.
				// Assumimos aqui que, se a pessoa está chutando na rodada, ela quer fazer parte do campeonato.
				// Atualiza os Campeonatos
				$atualizar					=	FALSE;
				$pes_camp					=	$this->pessoa_campeonato_versao->get_one_by_where	(
																		"	pessoa_campeonato_versao.campeonato_versao_id	in	(
																										select	rod.campeonato_versao_id
																										from	rodada_fase	AS	rod
																										where	rod.id		=	{$rod_id}
																										)
																		and	pessoa_campeonato_versao.pessoa_id		=	{$pessoa_base->id}
																		"
																		);
				if ( is_object( $pes_camp ) )
				{	// Existe a linha no campeonato versão.
					if ( $pes_camp->cadastrado_para_jogar == 'N' )
					{
						$pes_camp->cadastrado_para_jogar	=	'S'; // Ativa a pessoa.
						$atualizar				=	TRUE;
					}
				}
				else
				{	// Não existe, vamos criar.
					$pes_camp					=	new stdClass();
					$pes_camp->id					=	NULL; // Insert
					$pes_camp->pessoa_id				=	$pessoa_base->id;
					$pes_camp->campeonato_versao_id			=	$rodada_fase_base->campeonato_versao_id;
					$pes_camp->cadastrado_para_jogar		=	'S'; // Ativa a pessoa.
					$atualizar					=	TRUE;
				}
				
				if ( $atualizar )
				{
					$this->pessoa_campeonato_versao->update( $pes_camp );
				}

				/*
				 * Registra os poderes usados pelo usuário.
				 */
				// Carrega os poderes da base de dados.
				$pessoa_rodada_fase_power_DB				=	$this->pessoa_rodada_fase_power->get_all_by_where( "pessoa_rodada_fase_id	in	(
																							select	pesrod.id
																							from	pessoa_rodada_fase pesrod
																							where	pesrod.rodada_fase_id = {$rod_id}
																							and	pesrod.pessoa_id = {$pessoa_base->id}
																							)
																		" );
				if ( !$pessoa_rodada_fase_power_DB )
				{
					$this->pessoa_rodada_fase_power->libera_poderes( $rod_id, $pessoa_base->id );
					$pessoa_rodada_fase_power_DB			=	$this->pessoa_rodada_fase_power->get_all_by_where( "pessoa_rodada_fase_id	in	(
																							select	pesrod.id
																							from	pessoa_rodada_fase pesrod
																							where	pesrod.rodada_fase_id = {$rod_id}
																							and	pesrod.pessoa_id = {$pessoa_base->id}
																							)
																		" );
				}
	
				$pessoa_rodada_fase_power					=	array();
				foreach( $pessoa_rodada_fase_power_DB as $row_power )
				{
					$power							= new stdClass();
					$power->nome						= $row_power->nome_power;
					$power->id						= $row_power->id;
					$power->pessoa_rodada_fase_id				= $row_power->pessoa_rodada_fase_id;
					$power->descr						= $row_power->descr_power;
					$power->cod						= $row_power->cod_power;
					$power->css_class					= $row_power->css_class;
					$power->power_id					= $row_power->power_id;
					$power->qtde_liberado					= $row_power->qtde_liberado;
					$power->qtde_usada					= 0; // Zeramos, pois sempre contamos todos os poderes novamente.
					$pessoa_rodada_fase_power[ $row_power->power_id ]	= $power;
				}
				// fim: Carrega os poderes da base de dados.

				// Ajusta o controle de poderes.
				$kick_power_base					=	$this->kick_power->get_all_by_where	(
																	"kick_id	in	(
																				select	kick.id
																				from	 kick
																					,jogo
																				where	jogo.rodada_fase_id	=	{$rod_id}
																				and	kick.jogo_id		=	jogo.id
																				and	kick.pessoa_id		=	{$pessoa_base->id}
																				)
																	"
																	);
				foreach( $kick_power_base as $powers )
				{
					if ( key_exists( $powers->power_id, $pessoa_rodada_fase_power ) )
					{
						$pessoa_rodada_fase_power[ $powers->power_id ]->qtde_usada		+=	1;
	
						// Se ultrapassarmos a qtde total liberada, excluímos o poder que acabou de ser inserido.
						if ( $pessoa_rodada_fase_power[ $powers->power_id ]->qtde_usada > $pessoa_rodada_fase_power[ $powers->power_id ]->qtde_liberado )
						{
							$pessoa_rodada_fase_power[ $powers->power_id ]->qtde_usada	-=	1;
							$this->kick_power->delete( $powers->id );
							$this->ret_array[ 'warning' ][]					=	array	(
																	 'message_type'	=>	'block'
																	,'message'	=>	"Um poder não foi inserido, pois excedeu o limite liberado."
																	);
							// ajusta o retorno
							foreach( $this->ret_array[ 'kick_power' ] as $key => $kick_power )
							{
								if ($this->ret_array[ 'kick_power' ][ $key ][ 'power_id' ] == $powers->power_id )
								{
									$this->ret_array[ 'kick_power' ][ $key ][ 'id' ]	=	$this->ret_array[ 'kick_power' ][ $key ][ 'id' ] * (-1); // Marcamos como negativo para que o Javascript exclua o poder da página.
									break;
								}
							}
						}
					}
				}
				// fim: Ajusta o controle de poderes.
				$ar_rodada[ $rod_id ]->pessoa_rodada_fase_power				=	$pessoa_rodada_fase_power;
				
				// Tabela de controle de poderes.
				foreach( $pessoa_rodada_fase_power as $ctrl_power )
				{
					if ( $this->pessoa_rodada_fase_power->update( $ctrl_power ) )
					{
						$this->ret_array[ 'ok' ][]				=	array	(
															 'message_type'	=>	'success'
															,'message'	=>	"Controle de Poderes salvos."
															);
						$this->ret_array[ 'pessoa_rodada_fase_power' ][]	=	array	(
															 'id'				=>	$ctrl_power->id
															,'qtde_liberado'		=>	$ctrl_power->qtde_liberado
															,'qtde_usada'			=>	$ctrl_power->qtde_usada
															,'power_id'			=>	$ctrl_power->power_id
															,'pessoa_rodada_fase_id'	=>	$ctrl_power->pessoa_rodada_fase_id
															);
					}
					else
					{
						$this->ret_array[ 'fail' ][]				=	array	(
															 'message_type'	=>	'error'
															,'message'	=>	'Falha ao salvar os controles de poderes dos chutes.'
															);
					}
				}
				
				// Lê a base de dados para determinar as qtdes de chutes feitos e não feitos.
				$qtde_chutes_total							=	0;
				$qtde_chutes_feitos							=	0;
				foreach( $this->kick->get_all_by_where	(
									"    rod.id = {$rod_id}
									and  ( kick.pessoa_id = {$pessoa_base->id}
									or     kick.pessoa_id IS NULL
									     )
									"
									) as $row_kick )
				{
					$qtde_chutes_total						+=	2;
		
					if ( !is_null( $row_kick->kick_casa ) )
					{
						$qtde_chutes_feitos					+=	1;
					}
		
					if ( !is_null( $row_kick->kick_visitante ) )
					{
						$qtde_chutes_feitos					+=	1;
					}
				}
				$ar_rodada[ $rod_id ]->qtde_kicks	=	array	(
											 'qtde_chutes_feitos'	=>	$qtde_chutes_feitos
											,'qtde_chutes_total'	=>	$qtde_chutes_total
											);
			}
			foreach( $ar_rodada as $rodada )
			{
				$this->ret_array['rodada'][]		=	$rodada;
			}

			// Atualiza a pessoa com o horário do último save dos chutes.
			$pessoa_base->data_hora_ultimo_chute		=	'CURRENT_TIMESTAMP';
			$this->pessoa->update( $pessoa_base );
		}

		// Se ocorrer algum erro na gravação dos chutes, envio um e-mail para mim mesmo para analisar.
		if ( count( $this->ret_array['fail'] ) > 0 )
		{
			if ( isset( $pessoa_base )
			&&   is_object( $pessoa_base )
			   )
			{
				$this->singlepack->send_email( 'jrfurini@gmail.com', 'AUTOSAVE CHUTES ERRO: Kikbook ', "A pessoa {$pessoa_base->nome} id({$pessoa_base->id}) com os dados " . json_encode( $this->ret_array ) );
			}
			else
			{
				$browser			=	 $this->singlepack->getBrowser();
				$this->singlepack->send_email( 'jrfurini@gmail.com', 'AUTOSAVE CHUTES ERRO: Kikbook ', "A pessoa 'ANONIMO' com os dados " . json_encode( $this->ret_array ) . "\n Browser (" . $browser[ 'name' ] . " version=" . $browser[ 'version' ] . " plataform=" . $browser[ 'platform' ] . ")" );
			}
		}

		echo json_encode( $this->ret_array );
	}
	
	/*
	 * Retorna os dados para montar os gráficos.
	 */
	public function xml( $eqp_casa_id, $eqp_vis_id, $rod_id, $jogo_id )
	{
		$ret				=	new stdClass();
		$ret->rodadas			=	array();
		$ret->maior_classificacao	=	20;
		$ret->dados_gerais		=	array();
		$ret->chutes_kiker		=	array();
		$obj_equipe_casa		=	new stdClass();
		$obj_equipe_vis			=	new stdClass();
		$obj_empate			=	new stdClass();

		// Dados das Equipes.
		$classif_eqp_casa		=	$this->campeonato_versao_classificacao->get_xml_chart( 'OBJ', $eqp_casa_id, $rod_id );
		$classif_eqp_vis		=	$this->campeonato_versao_classificacao->get_xml_chart( 'OBJ', $eqp_vis_id, $rod_id );

		// Maior classificação
		$ret->maior_classificacao	=	$classif_eqp_casa->maior_classificacao;

		// Dados de chutes para o jogo
		$this->kick->set_pessoa_id( -3 ); // O -3 enviado ao model kick força a leitura de todos os chutes do jogo.
		$rows_chutes			=	$this->kick->get_all_by_where( "jogo.id = $jogo_id" );

		$total_chutes			=	0;
		$total_casa			=	0;
		$total_vis			=	0;
		$total_empata			=	0;
		$ar_placar			=	array();
		$cod_placar			=	NULL;
		foreach( $rows_chutes as $chute )
		{
			$total_chutes++;
			$chute_casa		=	( $chute->kick_casa === 0 ) ? '0' : $chute->kick_casa;
			$chute_vis		=	( $chute->kick_visitante === 0 ) ? '0' : $chute->kick_visitante;
			
			$cod_placar		=	$chute_casa . " x " . $chute_vis;
			if ( !key_exists( $cod_placar, $ar_placar ) )
			{
				$obj_placar			=	new stdClass();
				$obj_placar->tipo		=	'E';
				$obj_placar->total		=	0;
				$ar_placar[ $cod_placar ]	=	$obj_placar;
				unset( $obj_placar );
			}
			
			$ar_placar[ $cod_placar ]->total++;
			
			if ( $chute->kick_casa == $chute->kick_visitante ) // Empate
			{
				$total_empata++;
				$ar_placar[ $cod_placar ]->tipo		=	'E';
			}
			elseif ( $chute->kick_casa > $chute->kick_visitante ) // Casa ganha
			{
				$total_casa++;
				$ar_placar[ $cod_placar ]->tipo		=	'C';
			}
			elseif ( $chute->kick_casa < $chute->kick_visitante ) // Visitante ganha
			{
				$total_vis++;
				$ar_placar[ $cod_placar ]->tipo		=	'V';
			}
		}
		
		if ( $total_chutes )
		{
			$obj_empate->total_chutes	=	round( ( $total_empata * 100 ) / $total_chutes );
			$obj_equipe_vis->total_chutes	=	round( ( $total_vis * 100 ) / $total_chutes );
			$obj_equipe_casa->total_chutes	=	round( ( $total_casa * 100 ) / $total_chutes );
		}
		else
		{
			$obj_empate->total_chutes	=	round( ( $total_empata * 100 ) );
			$obj_equipe_vis->total_chutes	=	round( ( $total_vis * 100 ) );
			$obj_equipe_casa->total_chutes	=	round( ( $total_casa * 100 ) );
		}

		$obj_equipe_casa->donut			=	new stdClass();
		$obj_equipe_casa->donut->chutes		=	array();
		$obj_equipe_casa->donut->qtde_chute	=	array();
		$obj_equipe_vis->donut			=	new stdClass();
		$obj_equipe_vis->donut->chutes		=	array();
		$obj_equipe_vis->donut->qtde_chute	=	array();
		$obj_empate->donut			=	new stdClass();
		$obj_empate->donut->chutes		=	array();
		$obj_empate->donut->qtde_chute		=	array();
		foreach( $ar_placar as $key => $placar )
		{
			if ( $placar->tipo == 'C' )
			{
				$obj_equipe_casa->donut->chutes[]	=	$key;
				$obj_equipe_casa->donut->qtde_chute[]	=	round( ( $placar->total * 100 ) / $total_chutes );
			}
			elseif ( $placar->tipo == 'V' )
			{
				$obj_equipe_vis->donut->chutes[]	=	$key;
				$obj_equipe_vis->donut->qtde_chute[]	=	round( ( $placar->total * 100 ) / $total_chutes );
			}
			else
			{
				$obj_empate->donut->chutes[]		=	$key;
				$obj_empate->donut->qtde_chute[]	=	round( ( $placar->total * 100 ) / $total_chutes );
			}
		}
		
		// Objeto equipe da Casa
		$obj_equipe_casa->equipe_sigla		=	$classif_eqp_casa->equipe_sigla;
		$obj_equipe_casa->equipe_nome		=	$classif_eqp_casa->equipe_nome;
		$obj_equipe_casa->equipe_id		=	$classif_eqp_casa->equipe_id;
		// Rodadas e posição da equipe da casa
		$obj_equipe_casa->posicao_rodada	=	array();
		$obj_equipe_casa->posicao_rodada_real	=	array();
		
		if ( count( $classif_eqp_casa->rows ) == 1 )
		{
			$ret->rodadas[]				=	'Ini';
			$obj_equipe_casa->posicao_rodada[]	=	0;
			$obj_equipe_casa->posicao_rodada_real[]	=	0;
		}
		
		foreach( $classif_eqp_casa->rows as $key => $row )
		{
			$ret->rodadas[]				=	$row->rodada_fase_cod;
			$obj_equipe_casa->posicao_rodada[]	=	$classif_eqp_casa->ar_pos[ $row->posicao ];
			$obj_equipe_casa->posicao_rodada_real[]	=	$row->posicao;
		}
		
		
		// Objeto equipe visitante
		$obj_equipe_vis->equipe_sigla		=	$classif_eqp_vis->equipe_sigla;
		$obj_equipe_vis->equipe_nome		=	$classif_eqp_vis->equipe_nome;
		$obj_equipe_vis->equipe_id		=	$classif_eqp_vis->equipe_id;
		// Empate e posição da equipe da visitante
		$obj_equipe_vis->posicao_rodada		=	array();
		$obj_equipe_vis->posicao_rodada_real	=	array();
		$obj_empate->posicao_rodada		=	array();
		$obj_empate->posicao_rodada_real	=	array();
		
		if ( count( $classif_eqp_vis->rows ) == 1 )
		{
			$obj_equipe_vis->posicao_rodada[]	=	0;
			$obj_equipe_vis->posicao_rodada_real[]	=	0;
			$obj_empate->posicao_rodada[]		=	null; // Só fazemos isso para criar o número certo de ocorrencias para o empate.
			$obj_empate->posicao_rodada_real[]	=	null;
		}

		foreach( $classif_eqp_vis->rows as $key => $row )
		{
			$obj_equipe_vis->posicao_rodada[]	=	$classif_eqp_vis->ar_pos[ $row->posicao ];
			$obj_equipe_vis->posicao_rodada_real[]	=	$row->posicao;
			$obj_empate->posicao_rodada[]		=	null; // Só fazemos isso para criar o número certo de ocorrencias para o empate.
			$obj_empate->posicao_rodada_real[]	=	null;
		}
		
		// Objeto do empate.
		$obj_empate->equipe_sigla		=	'EMP';
		$obj_empate->equipe_nome		=	'Empate';
		$obj_empate->equipe_id			=	-1;
		
		// Carrega os objetos para serem enviados à página.
		$ret->dados_gerais[]			=	$obj_equipe_casa;
		$ret->dados_gerais[]			=	$obj_equipe_vis;
		$ret->dados_gerais[]			=	$obj_empate;

		// Estatistica de chutes do kiker.
		$obj_chute_vitoria				=	new stdClass();
		$obj_chute_vitoria->name			=	'Vitória';
		$obj_chute_vitoria->data			=	array();
		$obj_chute_empate				=	new stdClass();
		$obj_chute_empate->name				=	'Empate';
		$obj_chute_empate->data				=	array();
		$obj_chute_derrota				=	new stdClass();
		$obj_chute_derrota->name			=	'Derrota';
		$obj_chute_derrota->data			=	array();

		if ( $this->singlepack->get_pessoa_id() )
		{
			$query_kicks				=	$this->db->query	(
												"select	 eqp.id			AS	equipe_id
													,sum(	case
															when	( jog.equipe_id_casa = eqp.id
															and	  kick.kick_casa > kick.kick_visitante
																)
															then
																1
															when	( jog.equipe_id_visitante = eqp.id
															and	  kick.kick_casa < kick.kick_visitante
																)
															then
																1
															else
																0
														 end
														)		AS	total_vitoria
													,sum(	case
															when	( jog.equipe_id_casa = eqp.id
															and	  kick.kick_casa < kick.kick_visitante
																)
															then
																1
															when	( jog.equipe_id_visitante = eqp.id
															and	  kick.kick_casa > kick.kick_visitante
																)
															then
																1
															else
																0
														 end
														)		AS	total_derrota
													,sum(	case
															when 	kick.kick_casa = kick.kick_visitante
															then
																1
															else
																0
														 end
														)		AS	total_empate
												 from	 kick
												 	,jogo			AS	jog
												 	,equipe			AS	eqp
												 where	jog.id			=	kick.jogo_id
												 and	eqp.id			in	( jog.equipe_id_casa, jog.equipe_id_visitante )
												 and	eqp.id			in	( $eqp_casa_id, $eqp_vis_id )
												 and	kick.pessoa_id		=	{$this->singlepack->get_pessoa_id()}
												 group by eqp.id
												"
												);

			foreach( $query_kicks->result_object() as $row )
			{
				$obj_chute_vitoria->data[]		=	(int) $row->total_vitoria;
				$obj_chute_empate->data[]		=	(int) $row->total_empate;
				$obj_chute_derrota->data[]		=	(int) $row->total_derrota;
			}
		}
		
		$ret->chutes_kiker				=	array	(
										 $obj_chute_vitoria
										,$obj_chute_empate
										,$obj_chute_derrota
										);
		
		echo json_encode( $ret );
	}
}
/* End of file chute.php */
/* Location: /application/controllers/chute.php */
