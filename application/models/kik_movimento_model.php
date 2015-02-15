<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Kik Book
 *
 *	Movimentos de Kiks Model
 *
 * @package		Kik Book
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012, kikbook.com
 * @license		http://kikbook.com/licence
 * @link		http://kikbook.com
 * @since		Version 0.1
 * @filesource		/application/models/kik_movimento_model.php
 * 
 * $Id: kik_movimento_model.php,v 1.8 2013-04-07 13:59:54 junior Exp $
 * 
 */

class Kik_movimento_model extends JX_Model
{
	protected $_revision				=	'$Id: kik_movimento_model.php,v 1.8 2013-04-07 13:59:54 junior Exp $';
	
	protected $template_notificacao_perda_kik	=	9;
	
	function __construct()
	{
		$_config		=	array	(
							 'kik_saldo'				=>	array	(
														 'model_name'	=>	'kik_saldo'
												 		)
							,'kik_movimento'			=>	array	(
														 'model_name'	=>	'kik_movimento'
												 		)
							,'kik_movimento_uso'			=>	array	(
														 'model_name'	=>	'kik_movimento_uso'
												 		)
							,'notificacao'				=>	array	(
														 'model_name'	=>	'notificacao'
												 		)
			
							);

		parent::__construct( $_config );

		log_message('debug', "(jx)Model ".$this->table." initialized.");
	}

	public function get_select_for_index()
	{
		return	"
			 kik_movimento.*
			,( kik_movimento.qtde - kik_movimento.qtde_perda )				AS	qtde_final
			,sld.saldo_kik									AS	saldo
			,sld.data_hora_atualizacao							AS	saldo_atualizado_em
			,sld.pessoa_id									AS	pessoa_id
			,concat( pes.nome, ' ', pes.sobrenome )						AS	pessoa_nome
			,pes.imagem_facebook								AS	pessoa_imagem
			,case
				when rod.id IS NOT NULL then
					concat( kik_movimento.descr, ' ', camp_rod.descr, ' - ', rod.cod, ' (', case kik_movimento.tipo when 'S' then '-' else '' end, kik_movimento.qtde, ')' )
				when camp.id IS NOT NULL then
					concat( kik_movimento.descr, ' ', camp.descr, ' (', case kik_movimento.tipo when 'S' then '-' else '' end, kik_movimento.qtde, ')' )
				when ads.id IS NOT NULL then
					CONCAT( ads.descr, '( ', ads.short_url, ' )', ' (', case kik_movimento.tipo when 'S' then '-' else '' end, kik_movimento.qtde, ')' )
				else
					kik_movimento.id		
			 end										AS	title
			,case
				when rod.id IS NOT NULL then
					concat( kik_movimento.descr, ' ', camp_rod.descr, ' - ', rod.cod )
				when camp.id IS NOT NULL then
					concat( kik_movimento.descr, ' ', camp.descr )
				when ads.id IS NOT NULL then
					ads.descr
				when cpr.id IS NOT NULL then
					'Troca por brinde'
				else
					kik_movimento.id		
			 end										AS	title_extrato
			 ,kik_movimento.data_hora							AS	when_field
			 ,date_format( kik_movimento.data_hora, '%e/%m/%Y' )				AS	data_fmt
			 ,date_format( kik_movimento.data_hora_validade, '%e/%m/%Y' )			AS	data_validade_fmt
			 ";
	}
		
	public function set_from_join()
	{
		$this->db->from( 'kik_movimento' );
		$this->db->join( 'kik_saldo		AS	sld',		'sld.id		=	kik_movimento.kik_saldo_id',		'' );
		$this->db->join( 'pessoa		AS	pes',		'pes.id		=	sld.pessoa_id',				'' );
		$this->db->join( 'campeonato_versao	AS	camp',		'camp.id	=	kik_movimento.campeonato_versao_id',	'LEFT' );
		$this->db->join( 'campanha		AS	ads',		'ads.id		=	kik_movimento.campanha_id',		'LEFT' );
		$this->db->join( 'rodada_fase		AS	rod',		'rod.id		=	kik_movimento.rodada_fase_id',		'LEFT' );
		$this->db->join( 'campeonato_versao	AS	camp_rod',	'camp_rod.id	=	rod.campeonato_versao_id',		'LEFT' );
		$this->db->join( 'compra		AS	cpr',		'cpr.id		=	kik_movimento.compra_id',		'LEFT' );
	}
	
	public function set_from_join_one()
	{
		$this->set_from_join();
	}

	public function get_order_by()
	{
		return "kik_movimento.data_hora DESC, kik_movimento.id";
	}
	
	/**
	 * 
	 * Functions de registro de movimentos e saldos.
	 * 
	 * @param int		$pes_id
	 * @param int		$fk_id
	 * @param string	$fk_type
	 * @param int 		$qtd
	 * @param timestamp	$when
	 * @param string	$io
	 * @param string	$descr
	 */
	public function add_movto( $pes_id, $fk_id, $fk_type, $qtd, $descr, $io = 'E', $when = NULL )
	{
		if ( $when == NULL )
		{
			$when					=	new DateTime( 'now' );
		}
		else
		{
			$when					=	DateTime::createFromFormat( 'Y-m-d H:i:s', $when );
		}
		if ( is_numeric( $qtd )
		&&   $qtd > 0
		   )
		{
			// Atualizar saldo e movimentação da pessoa.
			$saldo_base					=	$this->kik_saldo->get_one_by_where	(
															"pessoa_id = {$pes_id}"
															);
			if ( !$saldo_base )
			{ // Pessoa sem saldo na base, criamos um novo saldo.
				$saldo_base				=	new stdClass();
				$saldo_base->id				=	NULL;
				$saldo_base->pessoa_id			=	$pes_id;
				$saldo_base->saldo_kik			=	0;
				$saldo_base->data_hora_atualizacao	=	'CURRENT_TIMESTAMP';
			}
			// Acrescenta os bonus da campanha ao saldo da pessoa.
			if ( strtoupper ($io ) == 'E' )// Entrada
			{
				$saldo_base->saldo_kik			=	$saldo_base->saldo_kik + $qtd;
			}
			else //Saída
			{
				$saldo_base->saldo_kik			=	$saldo_base->saldo_kik - $qtd;
			}
				
			// Não deixa o saldo ficar negativo
			if ( $saldo_base->saldo_kik < 0 )
			{
				$saldo_base->saldo_kik		=	0;
			}

			$saldo_base->data_hora_atualizacao		=	'CURRENT_TIMESTAMP';
			$saldo_base->id					=	$this->kik_saldo->update( $saldo_base );
log_message('debug', "(add_movto).1");
			
			// Registra o movimento de kiks.
			$movimento_base					=	new stdClass();
			$movimento_base->id				=	NULL;
			$movimento_base->kik_saldo_id			=	$saldo_base->id;
			$movimento_base->data_hora			=	$when->format( 'Y-m-d H:i:s' );
			$movimento_base->tipo				=	strtoupper ($io );
			$movimento_base->qtde				=	$qtd;
			$movimento_base->descr				=	$descr;
log_message('debug', "(add_movto).2");

			// FKs
			if ( strtoupper( $fk_type ) == 'C' ) // campanha
			{
				$movimento_base->campanha_id		=	$fk_id;
				$movimento_base->rodada_fase_id		=	NULL;
				$movimento_base->campeonato_versao_id	=	NULL;
				$movimento_base->compra_id		=	NULL;
			}
			elseif ( strtoupper( $fk_type ) == 'R' ) // rodada_fase
			{
				$movimento_base->campanha_id		=	NULL;
				$movimento_base->rodada_fase_id		=	$fk_id;
				$movimento_base->campeonato_versao_id	=	NULL;
				$movimento_base->compra_id		=	NULL;
			}
			elseif ( strtoupper( $fk_type ) == 'P' ) // campeonato
			{
				$movimento_base->campanha_id		=	NULL;
				$movimento_base->rodada_fase_id		=	NULL;
				$movimento_base->campeonato_versao_id	=	$fk_id;
				$movimento_base->compra_id		=	NULL;
			}
			elseif ( strtoupper( $fk_type ) == 'S' ) // compra / shop
			{
				$movimento_base->campanha_id		=	NULL;
				$movimento_base->rodada_fase_id		=	NULL;
				$movimento_base->campeonato_versao_id	=	NULL;
				$movimento_base->compra_id		=	$fk_id;
			}
log_message('debug', "(add_movto).3");

			if ( strtoupper ($io ) == 'E' )// Entrada
			{
				$data_hora_validade			=	$when;
				if ( $when > date( '2013-05-20 00:00:00' ) )
				{
					$data_hora_validade->add( new DateInterval( 'P6M' ) ); // Kiks válidos por 6 meses.
				}
				else
				{
					$data_hora_validade->add( new DateInterval( 'P11M' ) ); // Kiks válidos por 11 meses.
				}
				$movimento_base->data_hora_validade	=	$data_hora_validade->format( 'Y-m-d H:i:s' );
			}
			else //Saída
			{
				$movimento_base->data_hora_validade	=	NULL;
			}
			$movimento_base->qtde_usada			=	0;
			$movimento_base->qtde_perda			=	0;

			$this->kik_movimento->update( $movimento_base );
			// Saldo e movimento Kiks atualizado.
		}
	}

	// Registra o uso de parte do movimento.
	public function set_movto_uso( $movto_id, $qtde_usada )
	{
		$movto_base						=	$this->kik_movimento->get_one_by_id( $movto_id );
		
		if ( $movto_base )
		{
			$movto_base->qtde_usada				=	$movto_base->qtde_usada + $qtde_usada;
			$this->kik_movimento->update( $movto_base );
		}
	}
	
	// Estorno / Exclui movimento.
	public function sub_movto( $pes_id, $fk_id, $fk_type )
	{
		// Busca o saldo da pessoa.
		$saldo_base					=	$this->kik_saldo->get_one_by_where	(
														"pessoa_id = {$pes_id}"
														);
		if ( !$saldo_base )
		{ // Pessoa sem saldo na base, criamos um novo saldo.
			$saldo_base				=	new stdClass();
			$saldo_base->id				=	NULL;
			$saldo_base->pessoa_id			=	$pes_id;
			$saldo_base->saldo_kik			=	0;
			$saldo_base->data_hora_atualizacao	=	date( 'Y-m-d H:i:s' );
			$saldo_base->id				=	$this->kik_saldo->update( $saldo_base );
		}

		// Busca os movtos.
		if ( strtoupper( $fk_type ) == 'C' ) // campanha
		{
			$where					=	"kik_movimento.campanha_id = $fk_id";
		}
		elseif ( strtoupper( $fk_type ) == 'R' ) // rodada_fase
		{
			$where					=	"kik_movimento.rodada_fase_id = $fk_id";
		}
		elseif ( strtoupper( $fk_type ) == 'P' ) // campeonato
		{
			$where					=	"kik_movimento.campeonato_versao_id = $fk_id";
		}
		elseif ( strtoupper( $fk_type ) == 'S' ) // compra / shop
		{
			$where					=	"kik_movimento.compra_id = $fk_id";
		}
		$where						.=	" and kik_movimento.kik_saldo_id = $saldo_base->id";

		$movto_rows					=	$this->kik_movimento->get_all_by_where	(
														$where
														);
		foreach( $movto_rows as $movto )
		{
			// Atualiza o saldo.
			if ( $movto->tipo == 'E' )// Entrada (invertido)
			{
				$saldo_base->saldo_kik			=	$saldo_base->saldo_kik - $movto->qtde;
			}
			else //Saída
			{
				$saldo_base->saldo_kik			=	$saldo_base->saldo_kik + $movto->qtde;
			}
			
			// Não deixa o saldo ficar negativo
			if ( $saldo_base->saldo_kik < 0 )
			{
				$saldo_base->saldo_kik		=	0;
			}

			// Exclui o movimento de kiks.
			$this->kik_movimento->delete( $movto->id );
		}

		// Registra na base de dados.
		$saldo_base->data_hora_atualizacao		=	date( 'Y-m-d H:i:s' );
		$saldo_base->id					=	$this->kik_saldo->update( $saldo_base );
	}

	/**
	 * 
	 * Retorna o total de Kiks que a pessoa conectada perderá dentro do Próximo mês.
	 */
	public function get_kik_vencer( $all = FALSE )
	{
		if ( $all )
		{
			$select	=	"select	 sld.pessoa_id
						,pes.nome							AS	pessoa_nome
						,pes.imagem_facebook						AS	pessoa_imagem
						,sum( movto.qtde - movto.qtde_usada - movto.qtde_perda )	AS	total_kik
					from	 kik_movimento		AS	movto
						,kik_saldo		AS	sld
						,pessoa			AS	pes
					where	movto.kik_saldo_id		=	sld.id
					and	movto.data_hora_validade	<	adddate( now(), INTERVAL 1 MONTH )
					and	pes.id				=	sld.pessoa_id
					group by sld.pessoa_id
						,pes.nome
					having	sum( movto.qtde - movto.qtde_usada - movto.qtde_perda )	> 0
					";
		}
		else
		{
			$select	=	"select	 sum( movto.qtde - movto.qtde_usada - movto.qtde_perda )			AS	total_kik
					from	 kik_movimento		AS	movto
						,kik_saldo		AS	sld
					where	sld.pessoa_id			=	{$this->singlepack->get_pessoa_id()}
					and	movto.kik_saldo_id		=	sld.id
					and	movto.data_hora_validade	<	adddate( now(), INTERVAL 1 MONTH )
					";
		}
		
		$query_kik_venc					=	$this->db->query( $select );
		
		return $query_kik_venc->result_object();
	}

	// Registra a perda EM todos OS MOVTOS que já ultrapassaram a validade.
	public function set_movto_perda()
	{
		$movto_rows					=	$this->get_all_by_where	(
												"	kik_movimento.data_hora_validade						<	now()
												and	( kik_movimento.qtde - kik_movimento.qtde_perda - kik_movimento.qtde_usada )	>	0
												and	kik_movimento.tipo								=	'E'
												"
												);
		foreach( $movto_rows as $movto )
		{
			echo "Pessoa {$movto->pessoa_nome}";

			$movto->qtde_perda				=	$movto->qtde - $movto->qtde_usada;
			echo " perdeu {$movto->qtde_perda} \n";

			$ar_values					=	 array	(
										 		 'pessoa_nome'	=>	$movto->pessoa_nome
										 		,'qtde_kiks'	=>	$movto->qtde_perda
										 		,'s'		=>	( ( $movto->qtde_perda == 1 ) ? '' : 's' )
										 	);
			$this->notificacao->notificar( $this->template_notificacao_perda_kik, $movto->pessoa_id, $ar_values, TRUE );

			// Busca o saldo da pessoa.
			$saldo_base					=	$this->kik_saldo->get_one_by_where	(
															"pessoa_id = {$movto->pessoa_id}"
															);
			if ( !$saldo_base )
			{ // Pessoa sem saldo na base, criamos um novo saldo.
				$saldo_base				=	new stdClass();
				$saldo_base->id				=	NULL;
				$saldo_base->pessoa_id			=	$pes_id;
				$saldo_base->saldo_kik			=	0;
				$saldo_base->data_hora_atualizacao	=	date( 'Y-m-d H:i:s' );
				$saldo_base->id				=	$this->kik_saldo->update( $saldo_base );
			}
			// Atualiza o saldo.
			$saldo_base->saldo_kik				=	$saldo_base->saldo_kik - $movto->qtde_perda;
			
			// Não deixa o saldo ficar negativo
			if ( $saldo_base->saldo_kik < 0 )
			{
				$saldo_base->saldo_kik			=	0;
			}
	
			// Registra na base de dados.
			$saldo_base->data_hora_atualizacao		=	date( 'Y-m-d H:i:s' );
			$saldo_base->id					=	$this->kik_saldo->update( $saldo_base );
			
			// Atualiza o movimento.
			$this->update( $movto );
		}
	}

	// Registra uma compra com kiks.
	public function set_pagamento_compra( $compra, $pessoa )
	{
		echo "Compra para {$pessoa->nome} no total de {$compra->valor_total}\n";

		// Busca o saldo da pessoa.
		$saldo_base					=	$this->kik_saldo->get_one_by_where	(
														"pessoa_id = {$pessoa->id}"
														);
		if ( !$saldo_base )
		{ // Pessoa sem saldo na base, criamos um novo saldo.
			$saldo_base				=	new stdClass();
			$saldo_base->id				=	NULL;
			$saldo_base->pessoa_id			=	$pessoa->id;
			$saldo_base->saldo_kik			=	0;
			$saldo_base->data_hora_atualizacao	=	date( 'Y-m-d H:i:s' );
			$saldo_base->id				=	$this->kik_saldo->update( $saldo_base );
		}

		// Verifica se a pessoa tem saldo para pagar a compra.
		if ( $saldo_base->saldo_kik < $compra->valor_total )
		{
			echo '...sem saldo para pagar compra.';
			return 1;
		}
		else
		{
			$movto_rows					=	$this->select_all	(
													"	( kik_movimento.qtde - kik_movimento.qtde_perda - kik_movimento.qtde_usada )	>	0
													and	kik_movimento.tipo								=	'E'
													and	kik_movimento.kik_saldo_id							=	{$saldo_base->id}
													"
													,'kik_movimento.data_hora ASC, kik_movimento.id ASC'
													);
			$movto_rows					=	$this->get_query_rows( FALSE );

			$qtde_registrada				=	0;
			// Registra em cada movimento a compra.
			foreach( $movto_rows as $movto )
			{
				$qtde_usada				=	0;
				if ( ( ( $movto->qtde - $movto->qtde_usada - $movto->qtde_perda ) + $qtde_registrada ) > $compra->valor_total )
				{
					$qtde_usada			=	$compra->valor_total - $qtde_registrada;
				}
				else
				{
					$qtde_usada			=	$movto->qtde - $movto->qtde_usada - $movto->qtde_perda;
				}
				$movto->qtde_usada			+=	$qtde_usada;
				$qtde_registrada			+=	$qtde_usada;
				echo "... Usou {$movto->qtde_usada} {$movto->data_hora}\n";
				
				// Atualiza o movimento.
				$this->update( $movto );

				// Registra o uso do movimento.
				$uso_base				=	new stdClass();
				$uso_base->id				=	NULL;
				$uso_base->data_hora			=	'CURRENT_TIMESTAMP';
				$uso_base->qtde				=	$qtde_usada;
				$uso_base->kik_movimento_id		=	$movto->id;
				$uso_base->compra_id			=	$compra->id;
				$this->kik_movimento_uso->update( $uso_base );
				
				if ( $qtde_registrada >= $compra->valor_total ) // Usou o suficiente para pagar a compra.
				{
					break;
				}
			}
			echo "... Registrou o uso nos movimentos. \n";

			// Atualiza o saldo.
			//     add_movto( $pes_id,            $fk_id,       $fk_type, $qtd,              $descr,            $io = 'E', $when = NULL )
			$this->add_movto( $compra->pessoa_id, $compra->id, 'S',       $qtde_registrada, 'Troca por brinde', 'S',       NULL );
			echo "... Registrou o movimento para atualizar o saldo. \n";
			
			/*
			$saldo_base->saldo_kik				=	$saldo_base->saldo_kik - $qtde_registrada;
			// Não deixa o saldo ficar negativo
			if ( $saldo_base->saldo_kik < 0 )
			{
				$saldo_base->saldo_kik			=	0;
			}
			$saldo_base->data_hora_atualizacao		=	date( 'Y-m-d H:i:s' );
			$saldo_base->id					=	$this->kik_saldo->update( $saldo_base );
			*/
		}
		
		return 0;
	}
}
/* End of file kik_movimento_model.php */
