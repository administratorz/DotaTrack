$(document).ready(function() {
	//issuing ajax request and putting info in div tags
	//create date(timestamp) for 2 years ago
	var timestamp = new Date();
	timestamp.setMonth(timestamp.getMonth() - 24);
	timestamp = Math.round(timestamp/1000);

	var matchGraph = function(column, selector) {
		var dataObject = {
			'projection':{'matchId': 'asc'},
			'criteria':[['date', '>', timestamp],['playerId', '=', server['playerId']],['matchType', '!=', '4']]
		};
		dataObject.projection[column] = 'not'; // Hack to add custom column to projection
		$.ajax("statistics/populateGraphs",
			{
				'data': dataObject,
				'method': 'POST',
				dataType: 'json'
			})
			.done(function(response){
				//console.log(column + ": ");
				//console.log(response);
				var data = transformData(response, column);
				constructGraph(selector, data);
				//console.log(responce);
			})
			.fail(function(){
				console.log("Failed to fetch " + column);
			});
	};

	matchGraph("kills", "#kills");
	matchGraph("deaths", "#deaths");
	matchGraph("assists", "#assists");
	matchGraph("lastHits", "#lastHits");
	matchGraph("denies", "#denies");
	matchGraph("xpm", "#xpm");
	matchGraph("gpm", "#gpm");
});

/**
 * Transforms raw data from the server get_statistics into NVD3 graph data.
 *
 * @param data The data from get_statistics as a javascript array of objects
 * containing key/value pairs to represent each field.
 * @param yColumn The name of the field that should be used as the y values.
 * @param xColumn The name of the field that should be used as the x values.
 *
 * @return An array containing a single datum for a NVD3 graph.
 */
function transformData(data, yColumn, xColumn)
{
	var valueArray = [];
	var yFieldName = yColumn;

	// Iterate over each of the data elements
	$.each(data, function(index, element) {
		valueArray.push({});
		// Iterate over each of the properties of element
		$.each(element, function(name, property) {
			// If this is the field we've designated as the y values or if we haven't designated a y value field
			if((yColumn && yColumn == name) || !yColumn)
			{
				valueArray[valueArray.length-1].y = parseInt(property);

				// If no field was designated as the y values, then store the field name
				if(!yColumn)
				{
					yFieldName = name;
				}

				// If no field was designated as the x values, use the index of the element
				if(!xColumn)
				{
					valueArray[valueArray.length-1].x = index;
				}
			}
			// If this is the field we've designated as the x values
			else if(xColumn && xColumn == name)
			{
				valueArray[valueArray.length-1].x = property;
			}
		});
	});

	return [{
		key: yFieldName,
		values: valueArray,
		color: "#ff0000"
	}];
}

/**
 * Constructs a basic line graph using the given data, placing it in the html
 * element with the given id.
 *
 * @param selector The css selector of an SVG element which will contain the graph.
 * @param data The data which will construct the graph.
 */
function constructGraph(selector, data)
{
	nv.addGraph(function() {
		var chart = nv.models.lineChart();
		
		var max = d3.max
			(data, function(d) 
				{return d3.max
					(d.values, function(e) 
						{return e.y;}
					);
				}
			);
		chart.yDomain([0,(max+10)])

		chart.xAxis
			.axisLabel("Matches");
			
		chart.useInteractiveGuideline(true);

		chart.yAxis
			.axisLabel(data[0].key)
			.tickFormat(d3.format("d"));

		d3.select(selector)
			.datum(data)
			.transition().duration(500).call(chart);

		nv.utils.windowResize(function() {
			chart.update();
		});

		return chart;
	});
}
