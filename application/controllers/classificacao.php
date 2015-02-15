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
 * @filesource		/application/controllers/classificacao.php
 * 
 * $Id: classificacao.php,v 1.13 2013-04-07 14:02:33 junior Exp $
 * 
 */

class Classificacao extends JX_Page
{
	protected $_revision	=	'$Id: classificacao.php,v 1.13 2013-04-07 14:02:33 junior Exp $';

	function __construct()
	{
		$_config		=	array	(
							 'campeonato_versao_classificacao'	=>	array	(
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
							);
		parent::__construct( $_config );
		
		log_message('debug', "Controller ".get_class( $this )." initialized.");
	}

	/**
	 * Página princial do site.
	 */
	public function index()
	{
		$this->atual();
	}
	
	/**
	 * Exibe uma classificação personalizada pelo usuário.
	 */
	public function personalizada( $rodada_fase_id = NULL, $rodada_fase_id_fim = NULL, $campeonato_versao_id = NULL, $personalizado = FALSE, $exibicao = 9 )
	{
		$campeonato_versao_id				=	$this->campeonato_versao->get_id_selecionado( $campeonato_versao_id, $rodada_fase_id );
		$rodada_fase_id					=	$this->rodada_fase->get_id_selecionado( $rodada_fase_id, $campeonato_versao_id );
		$campeonato_versao_id				=	$this->rodada_fase->get_id_campeonato( $campeonato_versao_id );
		$rodada_fase_id_fim				=	$this->rodada_fase->get_id_fim_selecionado( $rodada_fase_id_fim, $rodada_fase_id, $campeonato_versao_id );

		$this->campeonato_versao_classificacao->_prep_show( $rodada_fase_id, $rodada_fase_id_fim, ( strtoupper( $personalizado ) == 'TRUE' ? TRUE : FALSE ) );

		if ( $exibicao == 0 )
		{
			$this->load->view( 'portlet/classificacao.html' );
		}
		else
		{
			$this->load->view( 'classificacao_atual.html' );
		}
	}

	public function atual()
	{
		$this->rodada();
	}

	public function campeonato( $campeonato_versao_id = NULL, $exibicao = 9 )
	{
		// Confirma se a entrada é numérica.
		if ( !is_numeric( $campeonato_versao_id ) )
		{
			$campeonato_versao_id			=	NULL;
		}
		$this->rodada_fase->set_id_sessao( NULL );
		
		$this->rodada( NULL, $campeonato_versao_id, $exibicao );
	}

	public function rodada( $rodada_fase_id = NULL, $campeonato_versao_id = NULL, $exibicao = 9 )
	{
		$campeonato_versao_id				=	$this->campeonato_versao->get_id_selecionado( $campeonato_versao_id, $rodada_fase_id );
		$rodada_fase_id					=	$this->rodada_fase->get_id_selecionado( $rodada_fase_id, $campeonato_versao_id );
		$campeonato_versao_id				=	$this->rodada_fase->get_id_campeonato( $campeonato_versao_id );
		
		$this->campeonato_versao_classificacao->_prep_show( $rodada_fase_id );

		if ( $exibicao == 0 )
		{
			$this->load->view( 'portlet/classificacao.html' );
		}
		else
		{
			$this->load->view( 'classificacao_atual.html' );
		}
	}
	
	/**
	 * Gera XML
	 */
	public function xml( $tipo, $id1, $id2 )
	{
		$ret					=	NULL;
		if ( strtolower( $tipo ) == "equipe" )
		{
			$ret				=	$this->campeonato_versao_classificacao->get_xml_chart( 'CSV', $id1, $id2 );
		}
		elseif ( $tipo == "pequeno" )
		{
		}
		elseif ( $tipo == "grande" )
		{
			$ret	=	'
<!DOCTYPE html>
<meta charset="utf-8">
<style>

body {
  font: 10px sans-serif;
}

.axis path,
.axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.x.axis path {
  display: none;
}

.line {
  fill: none;
  stroke: steelblue;
  stroke-width: 3px;
}

</style>
<body>
<script src="/assets/js/d3.v3.min.js"></script>
<script>
		var margin = {top: 20, right: 80, bottom: 30, left: 50},
				width = 960 - margin.left - margin.right,
				height = 500 - margin.top - margin.bottom;
		
		var parseDate = d3.time.format("%Y%m%d").parse;

//		var x = d3.time.scale()
//				.range([width, 0]);
		var x = d3.scale.linear()
				.range([0, width]);
//		var x = d3.scale.ordinal()
//				.range([0, width]);
				
		var y = d3.scale.linear()
				.range([height, 0]);
		
		var color = d3.scale.category20();
		
		var xAxis = d3.svg.axis()
				.scale(x)
				.orient("bottom");
		
		var yAxis = d3.svg.axis()
				.scale(y)
				.orient("left")
				.tickFormat(d3.format(".2s"));
		
		var line = d3.svg.line()
				.interpolate("basis")
				.x(function(d) { return x(d.rodada); })
				.y(function(d) { return y(d.indicador); });

		var svg = d3.select( "body" ).append("svg")
				.attr("width", width + margin.left + margin.right)
				.attr("height", height + margin.top + margin.bottom)
					.append("g")
				.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

		d3.csv	(
				 "/classificacao/xml/equipe/' . $id1 . '/' . $id2 . '"
				,function(error, data)
						{
							color.domain(d3.keys(data[0]).filter(function(key) { return key !== "rodada"; }));
			
							data.forEach(function(d)	{
															d.rodada = d.rodada / 10;//parseDate(d.rodada);
														}
										);
			
							var valores = color.domain().map(function(name) {
																				return	{
																							name: name,
																							values: data.map(function(d)	{
																																return {rodada: d.rodada, indicador: +d[name]};
																															}
																											)
																						};
																			});

							// desenha informações de X
							x.domain(d3.extent(data, function(d) { return d.rodada; }));
//							x.domain(d3.extent(data,[ 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,30,31,32,33,34,35,36,37,38]) );
//							x.domain([ 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,30,31,32,33,34,35,36,37,38] );
//							x.domain([ 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,30,31,32,33,34,35,36,37,38] );
//alert( \'x=\' + x( 20 ) );
							svg.append("g")
										.attr("class", "x axis")
										.attr("transform", "translate(0," + height + ")")
										.call(xAxis);

							// desenha informações de Y
							y.domain(	[
										 d3.min( valores, function(c) { return Math.min(   0, d3.min( c.values, function( v ) { return v.indicador; } ) ); } )
										,d3.max( valores, function(c) { return Math.max( 100, d3.max( c.values, function( v ) { return v.indicador; } ) ); } )
									]
								);
							svg.append("g")
										.attr("class", "y axis")
										.call(yAxis)
									.append("text")
										.attr("transform", "rotate(-90)")
										.attr("y", 6)
										.attr("dy", ".71em")
										.style("text-anchor", "end")
										.text("");
							// desenha a linha
							var valor = svg.selectAll(".valor")
										.data(valores)
									.enter().append("g")
										.attr("class", "city");
							valor.append("path")
									.attr("class", "line")
									.attr("d", function(d) { return line(d.values); })
									.style("stroke", function(d) { return color(d.name); });

							// Coloca a leganda em cada linha.
							valor.append("text")
									.datum(function(d) { return {name: d.name, value: d.values[d.values.length - 1]}; })
									.attr("transform", function(d) { return "translate(" + x(d.value.rodada) + "," + y(d.value.indicador) + ")"; })
									.attr("x", 3)
									.attr("dy", ".35em")
									.text(function(d) { return d.name; });
						}
				);
</script>
</body>
</html>
				';
		}
		
		print $ret;
	}
}
/* End of file classificacao.php */
/* Location: /application/controllers/classificacao.php */
