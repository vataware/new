// (c) vataware. all rights reserved.

var googleMapStyles = [{featureType:"transit.station.airport",stylers:[{visibility:"on"},{hue:"#2c3e50"},{saturation:10},{gamma:0.3}]},{featureType:"landscape",stylers:[{color:"#2c5a71"}]},{featureType:"water",stylers:[{color:"#2c3e50"}]},{featureType:"road",stylers:[{visibility:"off"}]},{featureType:"administrative.country",elementType:"geometry",stylers:[{visibility:"on"},{color:"#ffffff"},{weight:0.5}]},{featureType:"administrative.country",elementType:"labels",stylers:[{visibility:"on"},{color:"#FFFFFF"},{weight:0.1}]},{featureType:"administrative.locality",elementType:"labels",stylers:[{visibility:"off"}]},{featureType:"administrative.province",stylers:[{visibility:"off"}]},{featureType:"poi",elementType:"labels",stylers:[{visibility:"off"}]},{featureType:"poi",stylers:[{visibility:"off"}]},{featureType:"administrative.locality",elementType:"labels",stylers:[{visibility:"on"},{weight:0.1},{color:"#dddddd"}]},{featureType:"transit.line",stylers:[{visibility:"off"}]}];

$(document).ready(function() {
	$('tbody.rowlink').rowlink();

	$('#search .searchField').popover({
		html: true,
		placement: 'bottom',
		trigger: 'focus',
		title: 'Quick Search',
		content: $('#search .searchTips').html(),
		container: 'body'
	});
});

function createPieChart(element, data) {
	$.plot(element, data, {
		series: {
			pie: {
				show: true,
				radius: 110,
				highlight: {
					opacity: 0.25
				},
				stroke: {
					color: '#fff',
					width: 2
				},
				// startAngle: 0,
				label: {
					show: true,
					radius: 3/4,
					formatter: function(label, series) {
					 	return '<div class="chart-label">' + label + '</div>';
					},
					background: { 
						opacity: 0.6,
						color: '#000'
					}
				}
			}
		},
		legend: {
			show: false,
		},
		grid: {
			hoverable: true,
		},
	});
}