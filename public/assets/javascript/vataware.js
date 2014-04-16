// (c) vataware. all rights reserved.

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