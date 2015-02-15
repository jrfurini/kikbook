<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Jogo Controller
 *
 * @package		Kik book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, Kikbook.com.br
 * @license		http://kikbook.com.br/licence
 * @link		http://kikbook.com.br
 * @since		Version 0.1
 * @filesource		/application/controler/campeonato.php
 *
 * $Id: campeonato.php,v 1.7 2013-01-28 22:11:11 junior Exp $
 *
 */

class Campeonato extends JX_Page
{
	protected $_revision	=	'$Id: campeonato.php,v 1.7 2013-01-28 22:11:11 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'imagem'			=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'hide_columns'		=>	'mime_type,file_extension'
													,'readonly_columns'	=>	'size'
													,'where'		=>	'imagem.id in ( select cmpimg.imagem_id from campeonato_imagem cmpimg where cmpimg.campeonato_id = ##id## )'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'campeonato'			=>	array	(
													 'read_write'		=>	'write'
													,'master'		=>	TRUE
													,'r_table_name'		=>	''
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'where'		=>	'campeonato.id = ##id##'
													,'orderby'		=>	'descr'
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													)
							,'campeonato_imagem'		=>	array	(
													 'read_write'		=>	'write'
													,'r_table_name'		=>	'campeonato,imagem'
													,'show'			=>	TRUE
													,'show_style'		=>	'form'
													,'hide_columns'		=>	'tamanho'
													,'where'		=>	'campeonato_imagem.campeonato_id = ##id##'
													,'orderby'		=>	''
													,'max_rows'		=>	999999
													,'delete_rule'		=>	'restrict'
													,'force_copy_from'	=>	TRUE
													)
							,'campeonato_versao'		=>	array	(
													 'read_write'		=>	'readonly'
													,'r_table_name'		=>	'campeonato'
													,'show'			=>	FALSE
													,'show_style'		=>	'none'
													,'where'		=>	''
													,'max_rows'		=>	99999
													,'orderby'		=>	'descr'
													)
							,'equipe'			=>	array	(
													 'read_write'		=>	'readonly'
													,'r_table_name'		=>	''
													,'show'			=>	FALSE
													,'show_style'		=>	'none'
													,'where'		=>	''
													,'max_rows'		=>	99999
													,'orderby'		=>	'nome'
													)
							,'campeonato_versao_equipe'	=>	array	(
													 'read_write'		=>	'readonly'
													,'r_table_name'		=>	'equipe,campeonato_versao'
													,'show'			=>	FALSE
													,'show_style'		=>	'none'
													,'where'		=>	''
													,'max_rows'		=>	99999
													,'orderby'		=>	''
													)
							,'rodada_fase'			=>	array	(
													 'read_write'		=>	'readonly'
													,'r_table_name'		=>	'campeonato_versao'
													,'show'			=>	FALSE
													,'show_style'		=>	'none'
													,'where'		=>	''
													,'max_rows'		=>	99999
													,'orderby'		=>	'cod'
													)
							,'grupo'			=>	array	(
													 'read_write'		=>	'readonly'
													,'r_table_name'		=>	'rodada_fase'
													,'show'			=>	FALSE
													,'show_style'		=>	'none'
													,'where'		=>	''
													,'max_rows'		=>	99999
													,'orderby'		=>	'cod'
													)
							,'grupo_equipe'			=>	array	(
													 'read_write'		=>	'readonly'
													,'r_table_name'		=>	'grupo,equipe'
													,'show'			=>	FALSE
													,'show_style'		=>	'none'
													,'where'		=>	''
													,'max_rows'		=>	99999
													,'orderby'		=>	''
													)
							,'arena'			=>	array	(
													 'read_write'		=>	'readonly'
													,'r_table_name'		=>	'equipe'
													,'show'			=>	FALSE
													,'show_style'		=>	'none'
													,'where'		=>	''
													,'max_rows'		=>	99999
													,'orderby'		=>	''
													)
							,'jogo'				=>	array	(
													 'read_write'		=>	'readonly'
													,'r_table_name'		=>	'grupo,rodada_fase,equipe,arena'
													,'show'			=>	FALSE
													,'show_style'		=>	'none'
													,'where'		=>	''
													,'max_rows'		=>	99999
													,'orderby'		=>	'cod'
													)
							,'pessoa_campeonato_versao'	=>	array	(
													 'read_write'		=>	'readonly'
													,'r_table_name'		=>	'campeonato_versao'
													,'show'			=>	FALSE
													,'show_style'		=>	'none'
													,'where'		=>	''
													,'max_rows'		=>	99999
													,'orderby'		=>	'descr'
													)
							);
		
		parent::__construct( $_config );

		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}
	
	/**
	 * Funções para registrar e excluir pessoa aos campeonatos.
	 */
	public function checkin( $campeonato_versao_id, $onde_estou = NULL )
	{
		// Marca o campeonato para a pessoa jogar.
		$camp		=	$this->pessoa_campeonato_versao->get_one_by_where	(	'pessoa_campeonato_versao.pessoa_id = ' . $this->singlepack->get_pessoa_id() .
												' and	pessoa_campeonato_versao.campeonato_versao_id = ' . $campeonato_versao_id
												);
		if ( !$camp ) // Não exsite, incluímos
		{
			$pessoa_campeonato_versao_id		=	NULL;
		}
		else // Existe, alteramos.
		{
			$pessoa_campeonato_versao_id		=	$camp->id;
		}
		$data		=	array	(
						 'id'					=>	$pessoa_campeonato_versao_id
						,'pessoa_id'				=>	$this->singlepack->get_pessoa_id()
						,'campeonato_versao_id'			=>	$campeonato_versao_id
						,'cadastrado_para_jogar'	=>	'S'
						);
		$this->pessoa_campeonato_versao->update( $data );
		
		// Registra o novo campeonato como selecionado.
		$this->campeonato_versao->set_id_sessao( $campeonato_versao_id );
		$this->rodada_fase->set_id_sessao( NULL );
		
		if ( !$onde_estou )
		{
			$onde_estou				=	 "/chute/campeonato/$campeonato_versao_id";
		}
		redirect( $onde_estou );
	}
	
	public function checkout( $campeonato_versao_id, $onde_estou = NULL )
	{
		$pessoa_id		=	$this->singlepack->get_pessoa_id();
		$pes_camp_ver_base	=	$this->pessoa_campeonato_versao->get_one_by_where	(	"pessoa_campeonato_versao.pessoa_id = $pessoa_id
													 and	pessoa_campeonato_versao.campeonato_versao_id = $campeonato_versao_id"
													);

		if ( is_object( $pes_camp_ver_base )
		&&   $pes_camp_ver_base->cadastrado_para_jogar == 'S'
		   )
		{
			// Marca o campeonato para a pessoa jogar.
			$pes_camp_ver_base->cadastrado_para_jogar	=	'N';
			$this->pessoa_campeonato_versao->update( $pes_camp_ver_base );

			// Anula a seleção para que a pessoa seja levada ao próximo campeonato aberto.
			$campeonato_versao_id_sess			=	$this->campeonato_versao->get_id_sessao();
			if ( $campeonato_versao_id_sess == $campeonato_versao_id )
			{
				$this->campeonato_versao->set_id_sessao( NULL );
				$this->rodada_fase->set_id_sessao( NULL );
			}
		}
		
		if ( !$onde_estou )
		{
			$onde_estou				=	  "/classificacao";
		}
		redirect( $onde_estou );
	}
	
	/**
	 * Instala um campeonato a partir de um array de jogos.
	 */
	public function install( $jogos_campeonato )
	{
		/**
		 * Comandos para inicializar a base de dados.
			delete from kikdb.jogo where id > 0;
			delete from kikdb.arena where id > 0;
			delete from kikdb.equipe where id > 0;
			delete from kikdb.rodada_fase where id > 0;
			delete from kikdb.campeonato_versao where id > 0;
			delete from kikdb.campeonato where id > 0;
			commit;
			
			ALTER TABLE kikdb.jogo AUTO_INCREMENT = 1;
			ALTER TABLE kikdb.arena AUTO_INCREMENT = 1;
			ALTER TABLE kikdb.equipe AUTO_INCREMENT = 1;
			ALTER TABLE kikdb.rodada_fase AUTO_INCREMENT = 1;
			ALTER TABLE kikdb.campeonato_versao AUTO_INCREMENT = 1;
			ALTER TABLE kikdb.campeonato AUTO_INCREMENT = 1;
		 */
		if ( !$jogos_campeonato )
		{
			echo 'NÃO FOI INFORMADO O ARRAY DE JOGOS.</BR>';
		}
		else
		{
			/*
			 * Cria o campeonato
			 */
			$campeonato_data	=	array	(
								 'id'		=>	NULL
								,'sigla'	=>	'BRASILEIRO'
								,'nome'		=>	'Brasileirão'
								,'descr'	=>	'Campeonato Brasileiro'
								);
			$campeonato_id		=	$this->model_master->insert( $campeonato_data );
			
			$versao_data		=	array	(
								 'id'				=>	NULL
								,'campeonato_id'		=>	$campeonato_id
								,'data_inicio'			=>	'19/05/2012'
								,'data_fim'			=>	'02/12/2012'
								,'descr'			=>	'Brasileirão 2012'
								,'entidade_organizadora'	=>	'CBF'
								);
			$campeonato_versao_id	=	$this->campeonato_versao->insert( $versao_data );
	
			$equipes		=	array();
			$times_visitante	=	array();
			$arenas			=	array();
			$rodadas		=	array();
			$jogos			=	array();
			
			$count_equipe		=	0;
			$count_arena		=	0;
			$count_jogo		=	0;
			foreach( $jogos_campeonato as $jogo )
			{
				// Monta Array de Equipes
				if ( !array_key_exists( $jogo[3], $equipes ) )
				{
					$count_equipe++;
					$new_time			=	new stdClass();
					$new_time->id			=	NULL;
					$new_time->nome			=	$jogo[3];
					$new_time->sigla		=	$count_equipe;
					$new_time->nro			=	$count_equipe;
					$new_time->id_facebook		=	NULL;
					$equipes[ $jogo[3] ]		=	$new_time;
					unset( $new_time );
				}
	
				if ( !array_key_exists( $jogo[4], $equipes ) )
				{
					$count_equipe++;
					$new_time			=	new stdClass();
					$new_time->id			=	NULL;
					$new_time->nome			=	$jogo[4];
					$new_time->sigla		=	$count_equipe;
					$new_time->nro			=	$count_equipe;
					$new_time->id_facebook		=	NULL;
					$equipes[ $jogo[4] ]		=	$new_time;
					unset( $new_time );
				}
	
				// Monta Array de Arenas
				if ( !array_key_exists( $jogo[5], $arenas ) )
				{
					$count_arena++;
					$new_arena			=	new stdClass();
					$new_arena->id			=	NULL;
					$new_arena->nome		=	$jogo[5];
					$new_arena->cidade		=	$jogo[6];
					$new_arena->equipe_name		=	$jogo[3];
					$arenas[ $jogo[5] ]		=	$new_arena;
					unset( $new_arena );
				}
				
				// Monta Array de Rodadas
				if ( !array_key_exists( $jogo[0], $rodadas ) )
				{
					$new_rodada				=	new stdClass();
					$new_rodada->id				=	NULL;
					$new_rodada->campeonato_versao_id	=	$campeonato_versao_id;
					$new_rodada->cod			=	$jogo[0];
					$new_rodada->data_inicio_timestamp	=	9999999999;
					$new_rodada->data_fim_timestamp		=	0000000000;
					$new_rodada->data_inicio		=	9999999999;
					$new_rodada->data_fim			=	0000000000;
					$new_rodada->obs			=	'';
					$rodadas[ $jogo[0] ]			=	$new_rodada;
					unset( $new_rodada );
				}
	
				// Monta Array de Jogos
				if ( !array_key_exists( $jogo[5], $jogos ) )
				{
					$data_hora			=	$this->singlepack->input_to_date( $jogo[1].'/2012 '.$jogo[2].':00', $hour = TRUE );
					$count_jogo++;
					$new_jogo			=	new stdClass();
					$new_jogo->id			=	NULL;
					$new_jogo->rodada_fase_id	=	$jogo[0];
					$new_jogo->cod			=	$count_jogo;
					$new_jogo->data_hora		=	$jogo[1].'/2012 '.$jogo[2].':00';//$data_hora->getTimestamp();//format( 'Y-m-d H:i:s' );
					$new_jogo->data_hora_timestamp	=	$data_hora->getTimestamp();//format( 'Y-m-d H:i:s' );
					$new_jogo->equipe_id_casa	=	$jogo[3];
					$new_jogo->resultado_casa	=	NULL;
					$new_jogo->equipe_id_visitante	=	$jogo[4];
					$new_jogo->resultado_visitante	=	NULL;
					$new_jogo->arena_id		=	$jogo[5];
					$jogos[ $count_jogo ]		=	$new_jogo;
	
					// Ajustar início e fim da rodada e fase
					if ( $rodadas[ $jogo[0] ]->data_inicio_timestamp > $new_jogo->data_hora_timestamp )
					{
						$rodadas[ $jogo[0] ]->data_inicio		=	$new_jogo->data_hora;
						$rodadas[ $jogo[0] ]->data_inicio_timestamp	=	$new_jogo->data_hora_timestamp;
					}
					if ( $rodadas[ $jogo[0] ]->data_fim_timestamp < $new_jogo->data_hora_timestamp )
					{
						$rodadas[ $jogo[0] ]->data_fim			=	$new_jogo->data_hora;
						$rodadas[ $jogo[0] ]->data_fim_timestamp	=	$new_jogo->data_hora_timestamp;
					}
	
					unset( $new_jogo );
				}
			}
		
			// show Equipes
			foreach( $equipes as $key => $equipe )
			{
				$equipe->id				=	$this->equipe->insert	( array	(
														 'id'		=>	$equipe->id
														,'nome'		=>	$equipe->nome
														,'sigla'	=>	$equipe->sigla
														,'id_facebook'	=>	$equipe->id_facebook
														)
													);
				$equipes[ $key ]->id			=	$equipe->id;
				echo "Equipe Nome={$equipe->nome} ID={$equipe->id} Count={$equipe->nro}</br>";
			}
			
			// show Arenas
			foreach( $arenas as $key => $arena )
			{
				$arena->id				=	$this->arena->insert	( array	(
														 'id'		=>	NULL
														,'nome'		=>	$arena->nome
														,'cidade'	=>	$arena->cidade
														,'equipe_id'	=>	$equipes[ $arena->equipe_name ]->id
														)
													);
				$arenas[ $key ]->id			=	$arena->id;
				echo "Arena={$arena->nome} cidade={$arena->cidade} equipe={$arena->equipe_name}</br>";
			}
			
			// show Arenas
			foreach( $rodadas as $key => $rodada )
			{
				$rodada->id			=	$this->rodada_fase->insert	( array	(
														 'id'			=>	$rodada->id
														,'campeonato_versao_id'	=>	$rodada->campeonato_versao_id
														,'cod'			=>	$rodada->cod
														,'data_inicio'		=>	$rodada->data_inicio
														,'data_fim'		=>	$rodada->data_fim
														,'obs'			=>	$rodada->obs
														)
													);
				$arenas[ $key ]->id			=	$arena->id;
				echo "Rodada={$rodada->cod} Inicio={$rodada->data_inicio} Fim={$rodada->data_fim}</br>";
			}
			
			// Show Jogos
			foreach( $jogos as $jogo )
			{
				$jogo->id				=	$this->jogo->insert	( array	(
														 'id'			=>	NULL
														,'rodada_fase_id'	=>	$rodadas[ $jogo->rodada_fase_id ]->id
														,'cod'			=>	$jogo->cod
														,'data_hora'		=>	$jogo->data_hora //$data_hora->getTimestamp();//format( 'Y-m-d H:i:s' );
														,'equipe_id_casa'	=>	$equipes[ $jogo->equipe_id_casa ]->id
														,'resultado_casa'	=>	NULL
														,'equipe_id_visitante'	=>	$equipes[ $jogo->equipe_id_visitante ]->id
														,'resultado_visitante'	=>	NULL
														,'arena_id'		=>	$arenas[ $jogo->arena_id ]->id
														)
													);
				$jogos[ $key ]->id			=	$jogo->id;
				echo "rodada={$jogo->rodada_fase_id} jogo_nro={$jogo->cod} quando={$jogo->data_hora} Quem={$jogo->equipe_id_casa} X {$jogo->equipe_id_visitante}</br>";
			}
		}
	}
	
	public function install_brasileirao_2012()
	{
		$brasileirao	=	array	 (
							 array( '01', '19/05', '18:30', 'Vasco', 'Grêmio', 'São Januário', 'Rio de Janeiro'	        )
							,array( '01', '19/05', '18:30', 'Bahia', 'Santos', 'Pituaçu', 'Salvador'                           )
							,array( '01', '19/05', '18:30', 'Palmeiras', 'Portuguesa', 'Pacaembu', 'São Paulo'                 )
							,array( '01', '19/05', '21:00', 'Figueirense', 'Náutico', 'Orlando Scarpelli', 'Florianópolis'     )
							,array( '01', '20/05', '16:00', 'Corinthians', 'Fluminense', 'Pacaembu', 'São Paulo'               )
							,array( '01', '20/05', '16:00', 'Internacional', 'Coritiba', 'Beira Rio', 'Porto Alegre'           )
							,array( '01', '20/05', '16:00', 'Botafogo', 'São Paulo', 'João Havelange', 'Rio de Janeiro'        )
							,array( '01', '20/05', '16:00', 'Ponte Preta', 'Atlético-MG', 'Moisés Lucarelli', 'Campinas'       )
							,array( '01', '20/05', '18:30', 'Cruzeiro', 'Atlético-GO', 'João Havelange', 'Uberlândia'          )
							,array( '01', '20/05', '18:30', 'Sport', 'Flamengo', 'Ilha do Retiro', 'Recife'                    )
							,array( '02', '26/05', '18:30', 'Atlético-GO', 'Ponte Preta', 'Serra Dourada', 'Goiânia'           )
							,array( '02', '26/05', '18:30', 'Flamengo', 'Internacional', 'João Havelange', 'Rio de Janeiro'    )
							,array( '02', '26/05', '18:30', 'Portuguesa', 'Vasco', 'Canindé', 'São Paulo'                      )
							,array( '02', '26/05', '21:00', 'Náutico', 'Cruzeiro', 'Aflitos', 'Recife'	                        )
							,array( '02', '27/05', '16:00', 'São Paulo', 'Bahia', 'Morumbi', 'São Paulo'                       )
							,array( '02', '27/05', '16:00', 'Atlético-MG', 'Corinthians', 'Arena do Jacaré', 'Sete Lagoas'     )
							,array( '02', '27/05', '16:00', 'Coritiba', 'Botafogo', 'Couto Pereira', 'Curitiba'                )
							,array( '02', '27/05', '16:00', 'Santos', 'Sport', 'Vila Belmiro', 'Santos'                        )
							,array( '02', '27/05', '18:30', 'Grêmio', 'Palmeiras', 'Olímpico', 'Porto Alegre'                  )
							,array( '02', '27/05', '18:30', 'Fluminense', 'Figueirense', 'João Havelange', 'Rio de Janeiro'    )
							,array( '03', '06/06', '19:30', 'Atlético-GO', 'Grêmio', 'Serra Dourada', 'Goiânia'                  )
							,array( '03', '06/06', '19:30', 'Sport', 'Palmeiras', 'Ilha do Retiro', 'Recife'                     )
							,array( '03', '06/06', '20:30', 'Vasco', 'Náutico', 'São Januário', 'Rio de Janeiro'                 )
							,array( '03', '06/06', '20:30', 'Atlético-MG', 'Bahia', 'Arena do Jacaré', 'Sete Lagoas'             )
							,array( '03', '06/06', '20:30', 'Coritiba', 'Portuguesa', 'Couto Pereira', 'Curitiba'                )
							,array( '03', '06/06', '21:50', 'Internacional', 'São Paulo', 'Beira Rio', 'Porto Alegre'            )
							,array( '03', '06/06', '21:50', 'Santos', 'Fluminense', 'Vila Belmiro', 'Santos'                     )
							,array( '03', '06/06', '21:50', 'Ponte Preta', 'Flamengo', 'Moisés Lucarelli', 'Campinas'            )
							,array( '03', '07/06', '20:30', 'Corinthians', 'Figueirense', 'Pacaembu', 'São Paulo'                )
							,array( '03', '07/06', '20:30', 'Botafogo', 'Cruzeiro', 'João Havelange', 'Rio de Janeiro'           )
							,array( '04', '09/06', '18:30', 'Portuguesa', 'Atlético-GO', 'Canindé', 'São Paulo'                  )
							,array( '04', '09/06', '18:30', 'Flamengo', 'Coritiba', 'João Havelange', 'Rio de Janeiro'           )
							,array( '04', '09/06', '21:00', 'Palmeiras', 'Atlético-MG', 'Pacaembu', 'São Paulo'                  )
							,array( '04', '10/06', '16:00', 'Grêmio', 'Corinthians', 'Olímpico', 'Porto Alegre'                 )
							,array( '04', '10/06', '16:00', 'Fluminense', 'Internacional', 'João Havelange', 'Rio de Janeiro'   )
							,array( '04', '10/06', '16:00', 'Bahia', 'Vasco', 'Pituaçu', 'Salvador'                             )
							,array( '04', '10/06', '16:00', 'São Paulo', 'Santos', 'Morumbi', 'São Paulo'                       )
							,array( '04', '10/06', '18:30', 'Figueirense', 'Ponte Preta', 'Orlando Scarpelli', 'Florianópolis'  )
							,array( '04', '10/06', '18:30', 'Cruzeiro', 'Sport', 'João Havelange', 'Uberlândia'                 )
							,array( '04', '10/06', '18:30', 'Náutico', 'Botafogo', 'Aflitos', 'Recife'                          )
							,array( '05', '16/06', '18:30', 'Fluminense', 'Portuguesa', 'João Havelange', 'Rio de Janeiro'     )
							,array( '05', '16/06', '18:30', 'São Paulo', 'Atlético-MG', 'Morumbi', 'São Paulo'                 )
							,array( '05', '16/06', '18:30', 'Internacional', 'Botafogo', 'Beira Rio', 'Porto Alegre'           )
							,array( '05', '16/06', '21:00', 'Cruzeiro', 'Figueirense', 'Arena do Jacaré', 'Sete Lagoas'        )
							,array( '05', '17/06', '16:00', 'Palmeiras', 'Vasco', 'Pacaembu', 'São Paulo'                      )
							,array( '05', '17/06', '16:00', 'Bahia', 'Sport', 'Pituaçu', 'Salvador'                            )
							,array( '05', '17/06', '16:00', 'Flamengo', 'Santos', 'João Havelange', 'Rio de Janeiro'          )
							,array( '05', '17/06', '18:30', 'Coritiba', 'Atlético-GO', 'Couto Pereira', 'Curitiba'             )
							,array( '05', '17/06', '18:30', 'Ponte Preta', 'Corinthians', 'Moisés Lucarelli', 'Campinas'      )
							,array( '05', '17/06', '18:30', 'Náutico', 'Grêmio', 'Aflitos', 'Recife'                           )
							,array( '06', '23/06', '18:30', 'Atlético-MG', 'Náutico', 'Arena do Jacaré', 'Sete Lagoas'         )
							,array( '06', '23/06', '18:30', 'Vasco', 'Cruzeiro', 'São Januário', 'Rio de Janeiro'              )
							,array( '06', '23/06', '18:30', 'Portuguesa', 'São Paulo', 'Canindé', 'São Paulo'                  )
							,array( '06', '23/06', '21:00', 'Sport', 'Internacional', 'Ilha do Retiro', 'Recife'               )
							,array( '06', '24/06', '16:00', 'Corinthians', 'Palmeiras', 'Pacaembu', 'São Paulo'                )
							,array( '06', '24/06', '16:00', 'Grêmio', 'Flamengo', 'Olímpico', 'Porto Alegre'                   )
							,array( '06', '24/06', '16:00', 'Figueirense', 'Bahia', 'Orlando Scarpelli', 'Florianópolis'       )
							,array( '06', '24/06', '18:30', 'Botafogo', 'Ponte Preta', 'João Havelange', 'Rio de Janeiro'      )
							,array( '06', '24/06', '18:30', 'Santos', 'Coritiba', 'Vila Belmiro', 'Santos'                     )
							,array( '06', '24/06', '18:30', 'Atlético-GO', 'Fluminense', 'Serra Dourada', 'Goiânia'            )
							,array( '07', '30/06', '16:00', 'Corinthians', 'Botafogo', 'Pacaembu', 'São Paulo'                 )
							,array( '07', '30/06', '16:00', 'Cruzeiro', 'São Paulo', 'Arena do Jacaré', 'Sete Lagoas'          )
							,array( '07', '30/06', '18:30', 'Vasco', 'Ponte Preta', 'São Januário', 'Rio de Janeiro'           )
							,array( '07', '30/06', '18:30', 'Náutico', 'Fluminense', 'Aflitos', 'Recife'                       )
							,array( '07', '01/07', '16:00', 'Coritiba', 'Sport', 'Couto Pereira', 'Curitiba'                     )
							,array( '07', '01/07', '16:00', 'Portuguesa', 'Santos', 'Canindé', 'São Paulo'                       )
							,array( '07', '01/07', '16:00', 'Bahia', 'Internacional', 'Pituaçu', 'Salvador'                      )
							,array( '07', '01/07', '18:30', 'Grêmio', 'Atlético-MG', 'Olímpico', 'Porto Alegre'                  )
							,array( '07', '01/07', '18:30', 'Palmeiras', 'Figueirense', 'Pacaembu', 'São Paulo'                  )
							,array( '07', '01/07', '18:30', 'Flamengo', 'Atlético-GO', 'João Havelange', 'Rio de Janeiro'        )
							,array( '08', '07/07', '18:30', 'Botafogo', 'Bahia', 'João Havelange', 'Rio de Janeiro'              )
							,array( '08', '07/07', '18:30', 'Internacional', 'Cruzeiro', 'Beira Rio', 'Porto Alegre'             )
							,array( '08', '07/07', '18:30', 'Ponte Preta', 'Palmeiras', 'Moisés Lucarelli', 'Campinas'           )
							,array( '08', '07/07', '21:00', 'Atlético-GO', 'Náutico', 'Serra Dourada', 'Goiânia'                 )
							,array( '08', '08/07', '16:00', 'Fluminense', 'Flamengo', 'João Havelange', 'Rio de Janeiro'         )
							,array( '08', '08/07', '16:00', 'São Paulo', 'Coritiba', 'Morumbi', 'São Paulo'                	)
							,array( '08', '08/07', '16:00', 'Figueirense', 'Vasco', 'Orlando Scarpelli', 'Florianópolis'        )
							,array( '08', '08/07', '16:00', 'Santos', 'Grêmio', 'Vila Belmiro', 'Santos'                         )
							,array( '08', '08/07', '18:30', 'Sport', 'Corinthians', 'Ilha do Retiro', 'Recife'                  )
							,array( '08', '08/07', '18:30', 'Atlético-MG', 'Portuguesa', 'Arena do Jacaré', 'Sete Lagoas'        )
							,array( '09', '14/07', '18:30', 'Figueirense', 'Atlético-MG', 'Orlando Scarpelli', 'Florianópolis' )
							,array( '09', '14/07', '18:30', 'Corinthians', 'Náutico', 'Pacaembu', 'São Paulo'                  )
							,array( '09', '14/07', '21:00', 'Ponte Preta', 'Coritiba', 'Moisés Lucarelli', 'Campinas'          )
							,array( '09', '15/07', '16:00', 'Botafogo', 'Fluminense', 'João Havelange', 'Rio de Janeiro'       )
							,array( '09', '15/07', '16:00', 'Internacional', 'Santos', 'Beira Rio', 'Porto Alegre'             )
							,array( '09', '15/07', '16:00', 'Cruzeiro', 'Grêmio', 'Arena do Jacaré', 'Sete Lagoas'             )
							,array( '09', '15/07', '16:00', 'Bahia', 'Flamengo', 'Pituaçu', 'Salvador'                         )
							,array( '09', '15/07', '18:30', 'Palmeiras', 'São Paulo', 'Pacaembu', 'São Paulo'                  )
							,array( '09', '15/07', '18:30', 'Vasco', 'Atlético-GO', 'São Januário', 'Rio de Janeiro'           )
							,array( '09', '15/07', '18:30', 'Sport', 'Portuguesa', 'Ilha do Retiro', 'Recife'                  )
							,array( '10', '18/07', '19:30', 'Santos', 'Botafogo', 'Vila Belmiro', 'Santos'                    )
							,array( '10', '18/07', '19:30', 'Grêmio', 'Sport', 'Olímpico', 'Porto Alegre'                     )
							,array( '10', '18/07', '20:30', 'Portuguesa', 'Cruzeiro', 'Canindé', 'São Paulo'                  )
							,array( '10', '18/07', '20:30', 'Náutico', 'Ponte Preta', 'Aflitos', 'Recife'                     )
							,array( '10', '18/07', '21:50', 'Atlético-MG', 'Internacional', 'Arena do Jacaré', 'Sete Lagoas'  )
							,array( '10', '18/07', '21:50', 'Flamengo', 'Corinthians', 'João Havelange', 'Rio de Janeiro'     )
							,array( '10', '18/07', '21:50', 'São Paulo', 'Vasco', 'Morumbi', 'São Paulo'                      )
							,array( '10', '19/07', '20:30', 'Atlético-GO', 'Figueirense', 'Serra Dourada', 'Goiânia'          )
							,array( '10', '19/07', '20:30', 'Fluminense', 'Bahia', 'João Havelange', 'Rio de Janeiro'         )
							,array( '10', '19/07', '20:30', 'Coritiba', 'Palmeiras', 'Couto Pereira', 'Curitiba'              )
							,array( '11', '21/07', '18:30', 'Sport', 'Atlético-MG', 'Ilha do Retiro', 'Recife'                )
							,array( '11', '21/07', '18:30', 'Vasco', 'Santos', 'São Januário', 'Rio de Janeiro'               )
							,array( '11', '21/07', '21:00', 'Corinthians', 'Portuguesa', 'Pacaembu', 'São Paulo'              )
							,array( '11', '22/07', '16:00', 'Internacional', 'Atlético-GO', 'Beira Rio', 'Porto Alegre'       )
							,array( '11', '22/07', '16:00', 'Cruzeiro', 'Flamengo', 'Arena do Jacaré', 'Sete Lagoas'          )
							,array( '11', '22/07', '16:00', 'Figueirense', 'São Paulo', 'Orlando Scarpelli', 'Florianópolis'  )
							,array( '11', '22/07', '16:00', 'Palmeiras', 'Náutico', 'Pacaembu', 'São Paulo'                   )
							,array( '11', '22/07', '18:30', 'Botafogo', 'Grêmio', 'João Havelange', 'Rio de Janeiro'          )
							,array( '11', '22/07', '18:30', 'Ponte Preta', 'Fluminense', 'Moisés Lucarelli', 'Campinas'       )
							,array( '11', '22/07', '18:30', 'Bahia', 'Coritiba', 'Pituaçu', 'Salvador'                        )
							,array( '12', '25/07', '19:30', 'Figueirense', 'Internacional', 'Orlando Scarpelli', 'Florianópolis'        )
							,array( '12', '25/07', '19:30', 'Ponte Preta', 'Sport', 'Moisés Lucarelli', 'Campinas'		          )
							,array( '12', '25/07', '20:30', 'Vasco', 'Botafogo', 'João Havelange', 'Rio de Janeiro'                     )
							,array( '12', '25/07', '20:30', 'Náutico', 'Coritiba', 'Aflitos', 'Recife'                                  )
							,array( '12', '25/07', '21:50', 'Corinthians', 'Cruzeiro', 'Pacaembu', 'São Paulo'                          )
							,array( '12', '25/07', '21:50', 'Grêmio', 'Fluminense', 'Olímpico', 'Porto Alegre'                          )
							,array( '12', '25/07', '21:50', 'Atlético-GO', 'São Paulo', 'Serra Dourada', 'Goiânia'                      )
							,array( '12', '26/07', '20:30', 'Palmeiras', 'Bahia', 'Pacaembu', 'São Paulo'                               )
							,array( '12', '26/07', '20:30', 'Flamengo', 'Portuguesa', 'João Havelange', 'Rio de Janeiro'                )
							,array( '12', '26/07', '20:30', 'Atlético-MG', 'Santos', 'Arena do Jacaré', 'Sete Lagoas'                   )
							,array( '13', '28/07', '18:30', 'Coritiba', 'Grêmio', 'Couto Pereira', 'Curitiba'                           )
							,array( '13', '28/07', '18:30', 'Internacional', 'Vasco', 'Beira Rio', 'Porto Alegre'                       )
							,array( '13', '28/07', '21:00', 'Botafogo', 'Figueirense', 'João Havelange', 'Rio de Janeiro'               )
							,array( '13', '29/07', '16:00', 'Sport', 'Atlético-GO', 'Ilha do Retiro', 'Recife'                          )
							,array( '13', '29/07', '16:00', 'São Paulo', 'Flamengo', 'Morumbi', 'São Paulo'                             )
							,array( '13', '29/07', '16:00', 'Fluminense', 'Atlético-MG', 'João Havelange', 'Rio de Janeiro'             )
							,array( '13', '29/07', '16:00', 'Cruzeiro', 'Palmeiras', 'Arena do Jacaré', 'Sete Lagoas'                  )
							,array( '13', '29/07', '18:30', 'Bahia', 'Corinthians', 'Pituaçu', 'Salvador'                              )
							,array( '13', '29/07', '18:30', 'Portuguesa', 'Náutico', 'Canindé', 'São Paulo'                             )
							,array( '13', '29/07', '18:30', 'Santos', 'Ponte Preta', 'Vila Belmiro', 'Santos'                           )
							,array( '14', '04/08', '18:30', 'Flamengo', 'Atlético-MG', 'João Havelange', 'Rio de Janeiro'                 )
							,array( '14', '04/08', '18:30', 'Palmeiras', 'Internacional', 'Pacaembu', 'São Paulo'                         )
							,array( '14', '04/08', '18:30', 'Atlético-GO', 'Botafogo', 'Serra Dourada', 'Goiânia'                         )
							,array( '14', '04/08', '21:00', 'Portuguesa', 'Figueirense', 'Canindé', 'São Paulo'                           )
							,array( '14', '05/08', '16:00', 'Vasco', 'Corinthians', 'São Januário', 'Rio de Janeiro'                      )
							,array( '14', '05/08', '16:00', 'Grêmio', 'Bahia', 'Olímpico', 'Porto Alegre'                                 )
							,array( '14', '05/08', '16:00', 'Coritiba', 'Fluminense', 'Couto Pereira', 'Curitiba'                         )
							,array( '14', '05/08', '16:00', 'São Paulo', 'Sport', 'Morumbi', 'São Paulo'                                  )
							,array( '14', '05/08', '18:30', 'Cruzeiro', 'Ponte Preta', 'Arena do Jacaré', 'Sete Lagoas'                   )
							,array( '14', '05/08', '18:30', 'Náutico', 'Santos', 'Aflitos', 'Recife'                                      )
							,array( '15', '08/08', '19:30', 'Internacional', 'Náutico', 'Beira Rio', 'Porto Alegre'                       )
							,array( '15', '08/08', '19:30', 'Figueirense', 'Flamengo', 'Orlando Scarpelli', 'Florianópolis'              )
							,array( '15', '08/08', '20:30', 'Corinthians', 'Atlético-GO', 'Pacaembu', 'São Paulo'                         )
							,array( '15', '08/08', '20:30', 'Bahia', 'Portuguesa', 'Pituaçu', 'Salvador'                                  )
							,array( '15', '08/08', '21:50', 'Sport', 'Vasco', 'Ilha do Retiro', 'Recife'                                  )
							,array( '15', '08/08', '21:50', 'Santos', 'Cruzeiro', 'Vila Belmiro', 'Santos'                                )
							,array( '15', '08/08', '21:50', 'Botafogo', 'Palmeiras', 'João Havelange', 'Rio de Janeiro'                  )
							,array( '15', '09/08', '20:30', 'Fluminense', 'São Paulo', 'João Havelange', 'Rio de Janeiro'                )
							,array( '15', '09/08', '20:30', 'Atlético-MG', 'Coritiba', 'Arena do Jacaré', 'Sete Lagoas'                   )
							,array( '15', '09/08', '20:30', 'Ponte Preta', 'Grêmio', 'Moisés Lucarelli', 'Campinas'                       )
							,array( '16', '11/08', '18:30', 'Sport', 'Figueirense', 'Ilha do Retiro', 'Recife'                           )
							,array( '16', '11/08', '18:30', 'Bahia', 'Cruzeiro', 'Pituaçu', 'Salvador'                                   )
							,array( '16', '11/08', '21:00', 'Flamengo', 'Náutico', 'João Havelange', 'Rio de Janeiro'                    )
							,array( '16', '12/08', '16:00', 'Internacional', 'Ponte Preta', 'Beira Rio', 'Porto Alegre'                  )
							,array( '16', '12/08', '16:00', 'São Paulo', 'Grêmio', 'Morumbi', 'São Paulo'                                )
							,array( '16', '12/08', '16:00', 'Atlético-MG', 'Vasco', 'Arena do Jacaré', 'Sete Lagoas'                     )
							,array( '16', '12/08', '16:00', 'Coritiba', 'Corinthians', 'Couto Pereira', 'Curitiba'                       )
							,array( '16', '12/08', '18:30', 'Fluminense', 'Palmeiras', 'João Havelange', 'Rio de Janeiro'                )
							,array( '16', '12/08', '18:30', 'Portuguesa', 'Botafogo', 'Canindé', 'São Paulo'                             )
							,array( '16', '12/08', '18:30', 'Santos', 'Atlético-GO', 'Vila Belmiro', 'Santos'                            )
							,array( '17', '15/08', '19:30', 'Grêmio', 'Portuguesa', 'Olímpico', 'Porto Alegre'                          )
							,array( '17', '15/08', '19:30', 'Cruzeiro', 'Fluminense', 'Arena do Jacaré', 'Sete Lagoas'                  )
							,array( '17', '15/08', '20:30', 'Atlético-GO', 'Atlético-MG', 'Serra Dourada', 'Goiânia'                    )
							,array( '17', '15/08', '20:30', 'Ponte Preta', 'Bahia', 'Moisés Lucarelli', 'Campinas'                      )
							,array( '17', '15/08', '21:50', 'Botafogo', 'Sport', 'João Havelange', 'Rio de Janeiro'                     )
							,array( '17', '15/08', '21:50', 'Palmeiras', 'Flamengo', 'Pacaembu', 'São Paulo'                            )
							,array( '17', '15/08', '21:50', 'Náutico', 'São Paulo', 'Ilha do Retiro', 'Recife'                          )
							,array( '17', '16/08', '20:30', 'Vasco', 'Coritiba', 'São Januário', 'Rio de Janeiro'                       )
							,array( '17', '16/08', '20:30', 'Corinthians', 'Internacional', 'Pacaembu', 'São Paulo'                     )
							,array( '17', '16/08', '20:30', 'Figueirense', 'Santos', 'Orlando Scarpelli', 'Florianópolis'               )
							,array( '18', '18/08', '18:30', 'Náutico', 'Bahia', 'Aflitos', 'Recife'                                     )
							,array( '18', '18/08', '18:30', 'Fluminense', 'Sport', 'João Havelange', 'Rio de Janeiro'                   )
							,array( '18', '18/08', '21:00', 'São Paulo', 'Ponte Preta', 'Morumbi', 'São Paulo'                          )
							,array( '18', '19/08', '16:00', 'Santos', 'Corinthians', 'Vila Belmiro', 'Santos'                           )
							,array( '18', '19/08', '16:00', 'Atlético-MG', 'Botafogo', 'Arena do Jacaré', 'Sete Lagoas'                 )
							,array( '18', '19/08', '16:00', 'Coritiba', 'Cruzeiro', 'Couto Pereira', 'Curitiba'                         )
							,array( '18', '19/08', '16:00', 'Grêmio', 'Figueirense', 'Olímpico', 'Porto Alegre'                         )
							,array( '18', '19/08', '18:30', 'Portuguesa', 'Internacional', 'Canindé', 'São Paulo'                       )
							,array( '18', '19/08', '18:30', 'Flamengo', 'Vasco', 'João Havelange', 'Rio de Janeiro'                     )
							,array( '18', '19/08', '18:30', 'Atlético-GO', 'Palmeiras', 'Serra Dourada', 'Goiânia'                      )
							,array( '19', '25/08', '18:30', 'Vasco', 'Fluminense', 'João Havelange', 'Rio de Janeiro'                   )
							,array( '19', '25/08', '18:30', 'Palmeiras', 'Santos', 'Pacaembu', 'São Paulo'                              )
							,array( '19', '25/08', '21:00', 'Ponte Preta', 'Portuguesa', 'Moisés Lucarelli', 'Campinas'                 )
							,array( '19', '26/08', '16:00', 'Internacional', 'Grêmio', 'Beira Rio', 'Porto Alegre'                      )
							,array( '19', '26/08', '16:00', 'Botafogo', 'Flamengo', 'João Havelange', 'Rio de Janeiro'                  )
							,array( '19', '26/08', '16:00', 'Corinthians', 'São Paulo', 'Pacaembu', 'São Paulo'                         )
							,array( '19', '26/08', '16:00', 'Figueirense', 'Coritiba', 'Orlando Scarpelli', 'Florianópolis'             )
							,array( '19', '26/08', '18:30', 'Cruzeiro', 'Atlético-MG', 'Arena do Jacaré', 'Sete Lagoas'                 )
							,array( '19', '26/08', '18:30', 'Sport', 'Náutico', 'Ilha do Retiro', 'Recife'                              )
							,array( '19', '26/08', '18:30', 'Bahia', 'Atlético-GO', 'Pituaçu', 'Salvador'                               )
							,array( '20', '29/08', '00:00', 'Flamengo', 'Sport', 'João Havelange', 'Rio de Janeiro'                          )
							,array( '20', '29/08', '00:00', 'Fluminense', 'Corinthians', 'João Havelange', 'Rio de Janeiro'                  )
							,array( '20', '29/08', '00:00', 'Santos', 'Bahia', 'Vila Belmiro', 'Santos'                                      )
							,array( '20', '29/08', '00:00', 'São Paulo', 'Botafogo', 'Morumbi', 'São Paulo'                                  )
							,array( '20', '29/08', '00:00', 'Grêmio', 'Vasco', 'Olímpico', 'Porto Alegre'                                    )
							,array( '20', '29/08', '00:00', 'Atlético-MG', 'Ponte Preta', 'Arena do Jacaré', 'Sete Lagoas'                   )
							,array( '20', '29/08', '00:00', 'Portuguesa', 'Palmeiras', 'Canindé', 'São Paulo'                                )
							,array( '20', '29/08', '00:00', 'Coritiba', 'Internacional', 'Couto Pereira', 'Curitiba'                         )
							,array( '20', '29/08', '00:00', 'Náutico', 'Figueirense', 'Aflitos', 'Recife'                                    )
							,array( '20', '29/08', '00:00', 'Atlético-GO', 'Cruzeiro', 'Serra Dourada', 'Goiânia'                            )
							,array( '21', '02/09', '00:00', 'Botafogo', 'Coritiba', 'João Havelange', 'Rio de Janeiro'                         )
							,array( '21', '02/09', '00:00', 'Vasco', 'Portuguesa', 'São Januário', 'Rio de Janeiro'                            )
							,array( '21', '02/09', '00:00', 'Palmeiras', 'Grêmio', 'Pacaembu', 'São Paulo'                                     )
							,array( '21', '02/09', '00:00', 'Corinthians', 'Atlético-MG', 'Pacaembu', 'São Paulo'                              )
							,array( '21', '02/09', '00:00', 'Internacional', 'Flamengo', 'Beira Rio', 'Porto Alegre'                           )
							,array( '21', '02/09', '00:00', 'Cruzeiro', 'Náutico', 'Arena do Jacaré', 'Sete Lagoas'                            )
							,array( '21', '02/09', '00:00', 'Ponte Preta', 'Atlético-GO', 'Moisés Lucarelli', 'Campinas'                       )
							,array( '21', '02/09', '00:00', 'Figueirense', 'Fluminense', 'Orlando Scarpelli', 'Florianópolis'                  )
							,array( '21', '02/09', '00:00', 'Sport', 'Santos', 'Ilha do Retiro', 'Recife'                                      )
							,array( '21', '02/09', '00:00', 'Bahia', 'São Paulo', 'Pituaçu', 'Salvador'                                        )
							,array( '22', '05/09', '00:00', 'Flamengo', 'Ponte Preta', 'João Havelange', 'Rio de Janeiro'                      )
							,array( '22', '05/09', '00:00', 'Fluminense', 'Santos', 'João Havelange', 'Rio de Janeiro'                         )
							,array( '22', '05/09', '00:00', 'Palmeiras', 'Sport', 'Pacaembu', 'São Paulo'                                      )
							,array( '22', '05/09', '00:00', 'São Paulo', 'Internacional', 'Morumbi', 'São Paulo'                               )
							,array( '22', '05/09', '00:00', 'Grêmio', 'Atlético-GO', 'Olímpico', 'Porto Alegre'                                )
							,array( '22', '05/09', '00:00', 'Cruzeiro', 'Botafogo', 'Arena do Jacaré', 'Sete Lagoas'                           )
							,array( '22', '05/09', '00:00', 'Portuguesa', 'Coritiba', 'Canindé', 'São Paulo'                                   )
							,array( '22', '05/09', '00:00', 'Figueirense', 'Corinthians', 'Orlando Scarpelli', 'Florianópolis'                 )
							,array( '22', '05/09', '00:00', 'Náutico', 'Vasco', 'Aflitos', 'Recife'                                            )
							,array( '22', '05/09', '00:00', 'Bahia', 'Atlético-MG', 'Pituaçu', 'Salvador'                                      )
							,array( '23', '09/09', '00:00', 'Botafogo', 'Náutico', 'João Havelange', 'Rio de Janeiro'                          )
							,array( '23', '09/09', '00:00', 'Vasco', 'Bahia', 'São Januário', 'Rio de Janeiro'                                 )
							,array( '23', '09/09', '00:00', 'Santos', 'São Paulo', 'Vila Belmiro', 'Santos'                                    )
							,array( '23', '09/09', '00:00', 'Corinthians', 'Grêmio', 'Pacaembu', 'São Paulo'                                   )
							,array( '23', '09/09', '00:00', 'Internacional', 'Fluminense', 'Beira Rio', 'Porto Alegre'                         )
							,array( '23', '09/09', '00:00', 'Atlético-MG', 'Palmeiras', 'Arena do Jacaré', 'Sete Lagoas'                       )
							,array( '23', '09/09', '00:00', 'Ponte Preta', 'Figueirense', 'Moisés Lucarelli', 'Campinas'                       )
							,array( '23', '09/09', '00:00', 'Coritiba', 'Flamengo', 'Couto Pereira', 'Curitiba'                                )
							,array( '23', '09/09', '00:00', 'Sport', 'Cruzeiro', 'Ilha do Retiro', 'Recife'                                    )
							,array( '23', '09/09', '00:00', 'Atlético-GO', 'Portuguesa', 'Serra Dourada', 'Goiânia'                            )
							,array( '24', '12/09', '00:00', 'Botafogo', 'Internacional', 'João Havelange', 'Rio de Janeiro'                   )
							,array( '24', '12/09', '00:00', 'Vasco', 'Palmeiras', 'São Januário', 'Rio de Janeiro'                            )
							,array( '24', '12/09', '00:00', 'Santos', 'Flamengo', 'Vila Belmiro', 'Santos'                                    )
							,array( '24', '12/09', '00:00', 'Corinthians', 'Ponte Preta', 'Pacaembu', 'São Paulo'                             )
							,array( '24', '12/09', '00:00', 'Grêmio', 'Náutico', 'Olímpico', 'Porto Alegre'                                   )
							,array( '24', '12/09', '00:00', 'Atlético-MG', 'São Paulo', 'Arena do Jacaré', 'Sete Lagoas'                      )
							,array( '24', '12/09', '00:00', 'Portuguesa', 'Fluminense', 'Canindé', 'São Paulo'                                )
							,array( '24', '12/09', '00:00', 'Figueirense', 'Cruzeiro', 'Orlando Scarpelli', 'Florianópolis'                   )
							,array( '24', '12/09', '00:00', 'Sport', 'Bahia', 'Ilha do Retiro', 'Recife'                                      )
							,array( '24', '12/09', '00:00', 'Atlético-GO', 'Coritiba', 'Serra Dourada', 'Goiânia'                             )
							,array( '25', '16/09', '00:00', 'Flamengo', 'Grêmio', 'João Havelange', 'Rio de Janeiro'                         )
							,array( '25', '16/09', '00:00', 'Fluminense', 'Atlético-GO', 'João Havelange', 'Rio de Janeiro'                  )
							,array( '25', '16/09', '00:00', 'Palmeiras', 'Corinthians', 'Pacaembu', 'São Paulo'                              )
							,array( '25', '16/09', '00:00', 'São Paulo', 'Portuguesa', 'Morumbi', 'São Paulo'                                )
							,array( '25', '16/09', '00:00', 'Internacional', 'Sport', 'Beira Rio', 'Porto Alegre'                            )
							,array( '25', '16/09', '00:00', 'Cruzeiro', 'Vasco', 'Arena do Jacaré', 'Sete Lagoas'                            )
							,array( '25', '16/09', '00:00', 'Ponte Preta', 'Botafogo', 'Moisés Lucarelli', 'Campinas'                        )
							,array( '25', '16/09', '00:00', 'Coritiba', 'Santos', 'Couto Pereira', 'Curitiba'                                )
							,array( '25', '16/09', '00:00', 'Náutico', 'Atlético-MG', 'Aflitos', 'Recife'                                    )
							,array( '25', '16/09', '00:00', 'Bahia', 'Figueirense', 'Pituaçu', 'Salvador'                                    )
							,array( '26', '23/09', '00:00', 'Botafogo', 'Corinthians', 'João Havelange', 'Rio de Janeiro'                    )
							,array( '26', '23/09', '00:00', 'Fluminense', 'Náutico', 'João Havelange', 'Rio de Janeiro'                      )
							,array( '26', '23/09', '00:00', 'Santos', 'Portuguesa', 'Vila Belmiro', 'Santos'                                 )
							,array( '26', '23/09', '00:00', 'São Paulo', 'Cruzeiro', 'Morumbi', 'São Paulo'                                  )
							,array( '26', '23/09', '00:00', 'Internacional', 'Bahia', 'Beira Rio', 'Porto Alegre'                            )
							,array( '26', '23/09', '00:00', 'Atlético-MG', 'Grêmio', 'Arena do Jacaré', 'Sete Lagoas'                        )
							,array( '26', '23/09', '00:00', 'Ponte Preta', 'Vasco', 'Moisés Lucarelli', 'Campinas'                           )
							,array( '26', '23/09', '00:00', 'Figueirense', 'Palmeiras', 'Orlando Scarpelli', 'Florianópolis'                 )
							,array( '26', '23/09', '00:00', 'Sport', 'Coritiba', 'Ilha do Retiro', 'Recife'                                  )
							,array( '26', '23/09', '00:00', 'Atlético-GO', 'Flamengo', 'Serra Dourada', 'Goiânia'                            )
							,array( '27', '30/09', '00:00', 'Flamengo', 'Fluminense', 'João Havelange', 'Rio de Janeiro'                     )
							,array( '27', '30/09', '00:00', 'Vasco', 'Figueirense', 'São Januário', 'Rio de Janeiro'                         )
							,array( '27', '30/09', '00:00', 'Palmeiras', 'Ponte Preta', 'Pacaembu', 'São Paulo'                              )
							,array( '27', '30/09', '00:00', 'Corinthians', 'Sport', 'Pacaembu', 'São Paulo'                                  )
							,array( '27', '30/09', '00:00', 'Grêmio', 'Santos', 'Olímpico', 'Porto Alegre'                                   )
							,array( '27', '30/09', '00:00', 'Cruzeiro', 'Internacional', 'Arena do Jacaré', 'Sete Lagoas'                    )
							,array( '27', '30/09', '00:00', 'Portuguesa', 'Atlético-MG', 'Canindé', 'São Paulo'                              )
							,array( '27', '30/09', '00:00', 'Coritiba', 'São Paulo', 'Couto Pereira', 'Curitiba'                             )
							,array( '27', '30/09', '00:00', 'Náutico', 'Atlético-GO', 'Aflitos', 'Recife'                                    )
							,array( '27', '30/09', '00:00', 'Bahia', 'Botafogo', 'Pituaçu', 'Salvador'                                       )
							,array( '28', '06/10', '00:00', 'Flamengo', 'Bahia', 'João Havelange', 'Rio de Janeiro'                           )
							,array( '28', '06/10', '00:00', 'Fluminense', 'Botafogo', 'João Havelange', 'Rio de Janeiro'                      )
							,array( '28', '06/10', '00:00', 'Santos', 'Internacional', 'Vila Belmiro', 'Santos'                               )
							,array( '28', '06/10', '00:00', 'São Paulo', 'Palmeiras', 'Morumbi', 'São Paulo'                                  )
							,array( '28', '06/10', '00:00', 'Grêmio', 'Cruzeiro', 'Olímpico', 'Porto Alegre'                                  )
							,array( '28', '06/10', '00:00', 'Atlético-MG', 'Figueirense', 'Arena do Jacaré', 'Sete Lagoas'                    )
							,array( '28', '06/10', '00:00', 'Portuguesa', 'Sport', 'Canindé', 'São Paulo'                                     )
							,array( '28', '06/10', '00:00', 'Coritiba', 'Ponte Preta', 'Couto Pereira', 'Curitiba'                            )
							,array( '28', '06/10', '00:00', 'Náutico', 'Corinthians', 'Aflitos', 'Recife'                                     )
							,array( '28', '06/10', '00:00', 'Atlético-GO', 'Vasco', 'Serra Dourada', 'Goiânia'                                )
							,array( '29', '14/10', '00:00', 'Botafogo', 'Santos', 'João Havelange', 'Rio de Janeiro'                         )
							,array( '29', '14/10', '00:00', 'Vasco', 'São Paulo', 'São Januário', 'Rio de Janeiro'                           )
							,array( '29', '14/10', '00:00', 'Palmeiras', 'Coritiba', 'Pacaembu', 'São Paulo'                                 )
							,array( '29', '14/10', '00:00', 'Corinthians', 'Flamengo', 'Pacaembu', 'São Paulo'                               )
							,array( '29', '14/10', '00:00', 'Internacional', 'Atlético-MG', 'Beira Rio', 'Porto Alegre'                      )
							,array( '29', '14/10', '00:00', 'Cruzeiro', 'Portuguesa', 'Arena do Jacaré', 'Sete Lagoas'                       )
							,array( '29', '14/10', '00:00', 'Ponte Preta', 'Náutico', 'Moisés Lucarelli', 'Campinas'                         )
							,array( '29', '14/10', '00:00', 'Figueirense', 'Atlético-GO', 'Orlando Scarpelli', 'Florianópolis'               )
							,array( '29', '14/10', '00:00', 'Sport', 'Grêmio', 'Ilha do Retiro', 'Recife'                                    )
							,array( '29', '14/10', '00:00', 'Bahia', 'Fluminense', 'Pituaçu', 'Salvador'                                     )
							,array( '30', '17/10', '00:00', 'Flamengo', 'Cruzeiro', 'João Havelange', 'Rio de Janeiro'                       )
							,array( '30', '17/10', '00:00', 'Fluminense', 'Ponte Preta', 'João Havelange', 'Rio de Janeiro'                  )
							,array( '30', '17/10', '00:00', 'Santos', 'Vasco', 'Vila Belmiro', 'Santos'                                      )
							,array( '30', '17/10', '00:00', 'São Paulo', 'Figueirense', 'Morumbi', 'São Paulo'                               )
							,array( '30', '17/10', '00:00', 'Grêmio', 'Botafogo', 'Olímpico', 'Porto Alegre'                                 )
							,array( '30', '17/10', '00:00', 'Atlético-MG', 'Sport', 'Arena do Jacaré', 'Sete Lagoas'                         )
							,array( '30', '17/10', '00:00', 'Portuguesa', 'Corinthians', 'Canindé', 'São Paulo'                              )
							,array( '30', '17/10', '00:00', 'Coritiba', 'Bahia', 'Couto Pereira', 'Curitiba'                                 )
							,array( '30', '17/10', '00:00', 'Náutico', 'Palmeiras', 'Aflitos', 'Recife'                                      )
							,array( '30', '17/10', '00:00', 'Atlético-GO', 'Internacional', 'Serra Dourada', 'Goiânia'                       )
							,array( '31', '21/10', '00:00', 'Botafogo', 'Vasco', 'João Havelange', 'Rio de Janeiro'                          )
							,array( '31', '21/10', '00:00', 'Fluminense', 'Grêmio', 'João Havelange', 'Rio de Janeiro'                       )
							,array( '31', '21/10', '00:00', 'Santos', 'Atlético-MG', 'Vila Belmiro', 'Santos'                                )
							,array( '31', '21/10', '00:00', 'São Paulo', 'Atlético-GO', 'Morumbi', 'São Paulo'                               )
							,array( '31', '21/10', '00:00', 'Internacional', 'Figueirense', 'Beira Rio', 'Porto Alegre'                      )
							,array( '31', '21/10', '00:00', 'Cruzeiro', 'Corinthians', 'Arena do Jacaré', 'Sete Lagoas'                      )
							,array( '31', '21/10', '00:00', 'Portuguesa', 'Flamengo', 'Canindé', 'São Paulo'                                 )
							,array( '31', '21/10', '00:00', 'Coritiba', 'Náutico', 'Couto Pereira', 'Curitiba'                               )
							,array( '31', '21/10', '00:00', 'Sport', 'Ponte Preta', 'Ilha do Retiro', 'Recife'                               )
							,array( '31', '21/10', '00:00', 'Bahia', 'Palmeiras', 'Pituaçu', 'Salvador'                                      )
							,array( '32', '24/10', '00:00', 'Flamengo', 'São Paulo', 'João Havelange', 'Rio de Janeiro'                      )
							,array( '32', '24/10', '00:00', 'Vasco', 'Internacional', 'São Januário', 'Rio de Janeiro'                       )
							,array( '32', '24/10', '00:00', 'Palmeiras', 'Cruzeiro', 'Pacaembu', 'São Paulo'                                 )
							,array( '32', '24/10', '00:00', 'Corinthians', 'Bahia', 'Pacaembu', 'São Paulo'                                  )
							,array( '32', '24/10', '00:00', 'Grêmio', 'Coritiba', 'Olímpico', 'Porto Alegre'                                 )
							,array( '32', '24/10', '00:00', 'Atlético-MG', 'Fluminense', 'Arena do Jacaré', 'Sete Lagoas'                    )
							,array( '32', '24/10', '00:00', 'Ponte Preta', 'Santos', 'Moisés Lucarelli', 'Campinas'                          )
							,array( '32', '24/10', '00:00', 'Figueirense', 'Botafogo', 'Orlando Scarpelli', 'Florianópolis'                  )
							,array( '32', '24/10', '00:00', 'Náutico', 'Portuguesa', 'Aflitos', 'Recife'                                     )
							,array( '32', '24/10', '00:00', 'Atlético-GO', 'Sport', 'Serra Dourada', 'Goiânia'                               )
							,array( '33', '27/10', '00:00', 'Botafogo', 'Atlético-GO', 'João Havelange', 'Rio de Janeiro'                    )
							,array( '33', '27/10', '00:00', 'Fluminense', 'Coritiba', 'João Havelange', 'Rio de Janeiro'                     )
							,array( '33', '27/10', '00:00', 'Santos', 'Náutico', 'Vila Belmiro', 'Santos'                                    )
							,array( '33', '27/10', '00:00', 'Corinthians', 'Vasco', 'Pacaembu', 'São Paulo'                                  )
							,array( '33', '27/10', '00:00', 'Internacional', 'Palmeiras', 'Beira Rio', 'Porto Alegre'                        )
							,array( '33', '27/10', '00:00', 'Atlético-MG', 'Flamengo', 'Arena do Jacaré', 'Sete Lagoas'                      )
							,array( '33', '27/10', '00:00', 'Ponte Preta', 'Cruzeiro', 'Moisés Lucarelli', 'Campinas'                        )
							,array( '33', '27/10', '00:00', 'Figueirense', 'Portuguesa', 'Orlando Scarpelli', 'Florianópolis'                )
							,array( '33', '27/10', '00:00', 'Sport', 'São Paulo', 'Ilha do Retiro', 'Recife'                                 )
							,array( '33', '27/10', '00:00', 'Bahia', 'Grêmio', 'Pituaçu', 'Salvador'                                         )
							,array( '34', '04/11', '00:00', 'Flamengo', 'Figueirense', 'João Havelange', 'Rio de Janeiro'                     )
							,array( '34', '04/11', '00:00', 'Vasco', 'Sport', 'São Januário', 'Rio de Janeiro'                                )
							,array( '34', '04/11', '00:00', 'Palmeiras', 'Botafogo', 'Pacaembu', 'São Paulo'                                  )
							,array( '34', '04/11', '00:00', 'São Paulo', 'Fluminense', 'Morumbi', 'São Paulo'                                 )
							,array( '34', '04/11', '00:00', 'Grêmio', 'Ponte Preta', 'Olímpico', 'Porto Alegre'                               )
							,array( '34', '04/11', '00:00', 'Cruzeiro', 'Santos', 'Arena do Jacaré', 'Sete Lagoas'                            )
							,array( '34', '04/11', '00:00', 'Portuguesa', 'Bahia', 'Canindé', 'São Paulo'                                     )
							,array( '34', '04/11', '00:00', 'Coritiba', 'Atlético-MG', 'Couto Pereira', 'Curitiba'                            )
							,array( '34', '04/11', '00:00', 'Náutico', 'Internacional', 'Aflitos', 'Recife'                                   )
							,array( '34', '04/11', '00:00', 'Atlético-GO', 'Corinthians', 'Serra Dourada', 'Goiânia'                          )
							,array( '35', '11/11', '00:00', 'Botafogo', 'Portuguesa', 'João Havelange', 'Rio de Janeiro'                     )
							,array( '35', '11/11', '00:00', 'Vasco', 'Atlético-MG', 'São Januário', 'Rio de Janeiro'                         )
							,array( '35', '11/11', '00:00', 'Palmeiras', 'Fluminense', 'Pacaembu', 'São Paulo'                               )
							,array( '35', '11/11', '00:00', 'Corinthians', 'Coritiba', 'Pacaembu', 'São Paulo'                               )
							,array( '35', '11/11', '00:00', 'Grêmio', 'São Paulo', 'Olímpico', 'Porto Alegre'                                )
							,array( '35', '11/11', '00:00', 'Cruzeiro', 'Bahia', 'Arena do Jacaré', 'Sete Lagoas'                            )
							,array( '35', '11/11', '00:00', 'Ponte Preta', 'Internacional', 'Moisés Lucarelli', 'Campinas'                   )
							,array( '35', '11/11', '00:00', 'Figueirense', 'Sport', 'Orlando Scarpelli', 'Florianópolis'                     )
							,array( '35', '11/11', '00:00', 'Náutico', 'Flamengo', 'Aflitos', 'Recife'                                       )
							,array( '35', '11/11', '00:00', 'Atlético-GO', 'Santos', 'Serra Dourada', 'Goiânia'                              )
							,array( '36', '18/11', '00:00', 'Flamengo', 'Palmeiras', 'João Havelange', 'Rio de Janeiro'                      )
							,array( '36', '18/11', '00:00', 'Fluminense', 'Cruzeiro', 'João Havelange', 'Rio de Janeiro'                     )
							,array( '36', '18/11', '00:00', 'Santos', 'Figueirense', 'Vila Belmiro', 'Santos'                                )
							,array( '36', '18/11', '00:00', 'São Paulo', 'Náutico', 'Morumbi', 'São Paulo'                                   )
							,array( '36', '18/11', '00:00', 'Internacional', 'Corinthians', 'Beira Rio', 'Porto Alegre'                      )
							,array( '36', '18/11', '00:00', 'Atlético-MG', 'Atlético-GO', 'Arena do Jacaré', 'Sete Lagoas'                   )
							,array( '36', '18/11', '00:00', 'Portuguesa', 'Grêmio', 'Canindé', 'São Paulo'                                   )
							,array( '36', '18/11', '00:00', 'Coritiba', 'Vasco', 'Couto Pereira', 'Curitiba'                                 )
							,array( '36', '18/11', '00:00', 'Sport', 'Botafogo', 'Ilha do Retiro', 'Recife'                                  )
							,array( '36', '18/11', '00:00', 'Bahia', 'Ponte Preta', 'Pituaçu', 'Salvador'                                    )
							,array( '37', '25/11', '00:00', 'Botafogo', 'Atlético-MG', 'João Havelange', 'Rio de Janeiro'                    )
							,array( '37', '25/11', '00:00', 'Vasco', 'Flamengo', 'João Havelange', 'Rio de Janeiro'                          )
							,array( '37', '25/11', '00:00', 'Palmeiras', 'Atlético-GO', 'Pacaembu', 'São Paulo'                              )
							,array( '37', '25/11', '00:00', 'Corinthians', 'Santos', 'Pacaembu', 'São Paulo'                                 )
							,array( '37', '25/11', '00:00', 'Internacional', 'Portuguesa', 'Beira Rio', 'Porto Alegre'                       )
							,array( '37', '25/11', '00:00', 'Cruzeiro', 'Coritiba', 'Arena do Jacaré', 'Sete Lagoas'                         )
							,array( '37', '25/11', '00:00', 'Ponte Preta', 'São Paulo', 'Moisés Lucarelli', 'Campinas'                       )
							,array( '37', '25/11', '00:00', 'Figueirense', 'Grêmio', 'Orlando Scarpelli', 'Florianópolis'                    )
							,array( '37', '25/11', '00:00', 'Sport', 'Fluminense', 'Ilha do Retiro', 'Recife'                                )
							,array( '37', '25/11', '00:00', 'Bahia', 'Náutico', 'Pituaçu', 'Salvador'                                        )
							,array( '38', '02/12', '00:00', 'Flamengo', 'Botafogo', 'João Havelange', 'Rio de Janeiro'                        )
							,array( '38', '02/12', '00:00', 'Fluminense', 'Vasco', 'João Havelange', 'Rio de Janeiro'                         )
							,array( '38', '02/12', '00:00', 'Santos', 'Palmeiras', 'Vila Belmiro', 'Santos'                                   )
							,array( '38', '02/12', '00:00', 'São Paulo', 'Corinthians', 'Morumbi', 'São Paulo'                                )
							,array( '38', '02/12', '00:00', 'Grêmio', 'Internacional', 'Olímpico', 'Porto Alegre'                             )
							,array( '38', '02/12', '00:00', 'Atlético-MG', 'Cruzeiro', 'Arena do Jacaré', 'Sete Lagoas'                       )
							,array( '38', '02/12', '00:00', 'Portuguesa', 'Ponte Preta', 'Canindé', 'São Paulo'                               )
							,array( '38', '02/12', '00:00', 'Coritiba', 'Figueirense', 'Couto Pereira', 'Curitiba'                            )
							,array( '38', '02/12', '00:00', 'Náutico', 'Sport', 'Aflitos', 'Recife'                                           )
							,array( '38', '02/12', '00:00', 'Atlético-GO', 'Bahia', 'Serra Dourada', 'Goiânia'                                )
							//       0     1    2        3            4           5              6
							);
		$this->install( $brasileirao );
	}
}
/* End of file campeonato.php */
