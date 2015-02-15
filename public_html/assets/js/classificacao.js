(function($){})(window.jQuery);

function hidechart()
{
	$.each( $( "table.clas tr.clas" ), function()	{
															$( this ).attr( 'show_chart', 'false' );
															$( this ).show();
															$( this ).children( 'td.chart-btn' ).children( 'button.show-chart' ).children( 'i' ).removeClass( "icon-ok" ).addClass( "icon-signal" );
													}
			);
	$( "table.clas tr.clas.chart" ).hide();
}
function showchart( $eqp_id, $rod_id, $button_used )
{
	icon = $( "table.clas tr.clas[eqp_id='" + $eqp_id + "']" ).children( 'td.chart-btn' ).children( 'button.show-chart' ).children( 'i' );
	if ( $( icon ).hasClass( "icon-signal" ) )
	{
		$( "table.clas tr.clas[eqp_id='" + $eqp_id + "']" ).show();
		$( "table.clas tr.clas[eqp_id='" + $eqp_id + "']" ).attr( 'show_chart', 'true' );
		$( "table.clas tr.clas.chart[eqp_id='" + $eqp_id + "']" ).show();

		if ( !$button_used )
		{
			$.each( $( 'table.clas tr.clas[eqp_id!="' + $eqp_id + '"]' ), function()	{
																								if ( $( this ).attr( 'show_chart' ) == 'false' )
																								{
																									$( this ).hide();
																								}
																						});
		}
		$( "div#chartcontainer" + $eqp_id ).text( "" );
	
		//https://github.com/mbostock/d3/wiki/Gallery
		var margin = {top: 20, right: 80, bottom: 30, left: 50},
				width = 330 - margin.left - margin.right,
				height = 200 - margin.top - margin.bottom;
		
		var x = d3.scale.linear()
				.range([0, width]);
		
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

		var svg = d3.select( "div#chartcontainer" + $eqp_id ).append("svg")
				.attr("width", width + margin.left + margin.right)
				.attr("height", height + margin.top + margin.bottom)
					.append("g")
				.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

		d3.csv	(
				 "/classificacao/xml/equipe/" + $eqp_id + "/" + $rod_id
				,function(error, data)
						{
					 		if ( data.length > 0 )
					 		{
								color.domain(d3.keys(data[0]).filter(function(key) { return key !== "rodada"; }));
				
								data.forEach(function(d)	{
																d.rodada = ( d.rodada / 10 );
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
								//svg.append("g")
								//			.attr("class", "x axis")
								//			.attr("transform", "translate(0," + height + ")")
								//			.call(xAxis);
	
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
					 		else
					 		{
					 			$( "div#chartcontainer" + $eqp_id ).html( '<b class="label">Sem dados para o gráfico.</b>' );
					 		}
						}
				);

		$( icon ).removeClass( "icon-signal" );
		$( icon ).addClass( "icon-ok" );
	}
	else
	{
		$( "table.clas tr.clas.chart[eqp_id='" + $eqp_id + "']" ).hide();
		$( "table.clas tr.clas[eqp_id='" + $eqp_id + "']" ).attr( 'show_chart', 'false' );

		if ( !$button_used )
		{
			var $alg_ch = false;
			$.each( $( "table.clas tr.clas" ), function()	{
																	if ( $( this ).attr( 'show_chart' ) == 'true' )
																	{
																		$( this ).hide();
																		$alg_ch = true;
																	}
															});
			if ( $alg_ch )
			{
				$( "table.clas tr.clas" ).show();
				$( "table.clas tr.clas.chart" ).hide();
			}
		}

		$( icon ).removeClass( "icon-ok" );
		$( icon ).addClass( "icon-signal" );
	}
}

$('tr.clas button.show-chart').click	(
			function(event){
						event.preventDefault();
						
						td = $( this ).parent( 'td.chart-btn' );
						tr = $( td ).parent( 'tr.clas' );
						eqp_id = $( tr ).attr( 'eqp_id' );
						rod_id =  $( tr ).attr( 'rod_id' );
						
						showchart( eqp_id, rod_id, true );
					}
		);
