<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Anuncio Model
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/jx/anuncio_model.php
 * 
 * $Id: anuncio_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $
 * 
 */

class Anuncio_model extends JX_Model
{
	protected $_revision	=	'$Id: anuncio_model.php,v 1.1 2013-03-12 00:13:07 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'anuncio'					=>	array	(
															 'model_name'	=>	'anuncio'
													 		)
							,'area_anuncio'					=>	array	(
															 'model_name'	=>	'area_anuncio'
															)
							,'anuncio_hist'					=>	array	(
															 'model_name'	=>	'anuncio_hist'
															)
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 anuncio.*
			,case
				when anuncio.data_fim IS NULL
				or   anuncio.data_fim >= now() then
					'S'
				else
					'N'
			  end	AS	ativo
			 ,concat( anuncio.nome, ' ', anuncio.codigo_url, ' ',	case
											when anuncio.data_fim IS NULL
											or   anuncio.data_fim >= now() then
												' (Ativo)'
											else
												' (Fora do ar)'
										end
			  )	AS	title
			,anuncio.data_inicio		AS	when_field
			";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'anuncio' );
	}

	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	public function get_order_by()
	{
		return "anuncio.prioridade DESC, anuncio.data_inicio DESC";
	}
	
	public function get_column_title()
	{
		return "concat( anuncio.nome, ' ', anuncio.codigo_url, ' ',	case
											when anuncio.data_fim IS NULL
											or   anuncio.data_fim >= now() then
												' (Ativo)'
											else
												' (Fora do ar)'
										end
			  )";
	}
	/**
	 * Funções de controle para exibição dos anúncios.
	 */
	/*
	 * Retorna a URL cadastrada para o próximo anúncio no tamanho selecionado.
	 */
	public function get_next( $cod_area_anuncio, $ar_values = array() )
	{
		/*
		 * 1 - Localizamos a área do anuncio.
		 * 2 - Buscamos os anuncios cadastrados para esta area.
		 * 	a. Buscamos os anuncios que estejam:
		 * 		  i. Com o carimbo menor que o carimbo do tamanho;
		 * 		 ii. Dentro do período de exibição;
		 * 		iii. Ordemos pelo prioridade DESC;
		 * 	b. Pegamos o primeiro;
		 * 2.a - Se não encontramos anuncios;
		 * 	a. Incrementamos o carimbo do tamanho;
		 * 2.b - Fazemos uma seleção randomica do anuncio.
		 * 3 - Criamos um anuncio_hist para a pessoa conectada.
		 * 	a. Se for anonimo criamos um sem a pessoa
		 * 4 - Montamos as variáveis para o html;
		 * 5 - Executamos o html com retorna em variável;
		 * 6 - Retornamos o html;
		 * 7 - O HTML usado precisa ter:
		 * 	a. <?php echo $codigo_url; ?> que conterá o codigo_url do area_anuncio;
		 * 	b. Uma div com a classe "ads-feedback" para que o JavaScript registre o clique antes de enviar o usuário ao site do patrocinador;
		 */
		if ( $cod_area_anuncio )
		{
			$ad_html						=	NULL;
			// (1)
			$area_base						=	$this->area_anuncio->get_one_by_where( "cod = '$cod_area_anuncio'" ); // cod é UK em tamanho.
			if ( $area_base )
			{
				// Se não foi configurado o arquivo do tamanho, geramos erro.
				if ( !$area_base->html_file )
				{
					return "NÃO FOI CONFIGURADO O .HTML DO TAMANHO";
				}
				// Se o carimbo do tamanho estiver 0 (empty) mudamos para 1. É a primeira exibição.
				if ( empty( $area_base->carimbo_exibicao ) )
				{
					$area_base->carimbo_exibicao		=	1;
				}
				
				// (2) Buscamos os anuncios.
				$anuncio_where					=	
				$anuncio_all					=	$this->get_all_by_where	(
															"anuncio.area_anuncio_id	=	{$area_base->id}
														and	anuncio.carimbo_exibicao	<	{$area_base->carimbo_exibicao}
														and	anuncio.data_inicio		<=	now()
														and	( anuncio.data_fim		IS NULL
														or	  ( anuncio.data_fim		IS NOT NULL
														and	    anuncio.data_fim		>=	now()
															  )
															)
															"
														);
														
				// (2.a) Incrementamos o carimbo do tamanho se não achou acima.
				if ( !$anuncio_all )
				{
					$area_base->carimbo_exibicao		=	$area_base->carimbo_exibicao + 1;
					$anuncio_all				=	$this->get_all_by_where	(
															"anuncio.area_anuncio_id	=	{$area_base->id}
														and	anuncio.carimbo_exibicao	<	{$area_base->carimbo_exibicao}
														and	anuncio.data_inicio		<=	now()
														and	( anuncio.data_fim		IS NULL
														or	  ( anuncio.data_fim		IS NOT NULL
														and	    anuncio.data_fim		>=	now()
															  )
															)
															"
														);
				}
				
				// Exibe o anuncio.
				if ( $anuncio_all )
				{
					// (2.b) Seleção radomica.
					$anuncio_base					=	$anuncio_all[0];
	
					$key_sel					=	rand( 0, count( $anuncio_all ) );
					$key_atu					=	0;
					foreach( $anuncio_all as $a_base )
					{
						if ( $key_atu == $key_sel
						||   ( empty( $key_atu )
						&&     empty( $key_sel )
						     )
						   )
						{
							$anuncio_base			=	$a_base;
							break;
						}
						$key_atu++;
					}

					// Atualiza o carimbo do tamanho.
					$this->area_anuncio->update( $area_base );
					
					// Atualiza o caimbo do anuncio.
					$anuncio_base->carimbo_exibicao		=	$area_base->carimbo_exibicao;
					$this->update( $anuncio_base );
					
					// (3) Criamos o histórico.
					$pessoa_id				=	$this->singlepack->get_pessoa_id();
					$anuncio_hist_base			=	new stdClass();
					$anuncio_hist_base->id			=	NULL;
					$anuncio_hist_base->data_hora_view	=	'CURRENT_TIMESTAMP';
					$anuncio_hist_base->data_hora_click	=	NULL;
					$anuncio_hist_base->anuncio_id		=	$anuncio_base->id;
					$anuncio_hist_base->pessoa_id		=	$pessoa_id;
					
					$anuncio_hist_base->id			=	$this->anuncio_hist->insert( $anuncio_hist_base );
					
					$view_data				=	array_merge	(
													 $ar_values
													,array	(
														 'codigo_url'		=>	$anuncio_base->codigo_url
														,'ad_hist_id'		=>	$anuncio_hist_base->id
														)
													);
										
					$ad_html				=	$this->load->view	(
														 $area_base->html_file
														,$view_data
														,true // Retorna o resultado para uma variável.
														);
				}
			}
			else
			{
				return "TAMANHO DE ANUNCIO NÃO CADASTRADO";
			}

			return $ad_html;
		}
		else
		{
			return "TAMANHO DE ANUNCIO NÃO CADASTRADO";
		}
	}
	
	// Marca como clicada.
	public function set_click( $id_hist )
	{
		if ( is_numeric( $id_hist ) )
		{
			$anuncio_hist_base					=	$this->anuncio_hist->get_one_by_id( $id_hist );
			if ( $anuncio_hist_base )
			{
				$anuncio_hist_base->data_hora_click		=	'CURRENT_TIMESTAMP';
				$this->anuncio_hist->update( $anuncio_hist_base );
			}
		}
		
		return TRUE;
	}
}

/* End of file anuncio_model.php */