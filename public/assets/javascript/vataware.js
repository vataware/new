// (c) vataware. all rights reserved.

var googleMapStyles = [{featureType:"transit.station.airport",stylers:[{visibility:"on"},{hue:"#2c3e50"},{saturation:10},{gamma:0.3}]},{featureType:"landscape",stylers:[{color:"#2c5a71"}]},{featureType:"water",stylers:[{color:"#2c3e50"}]},{featureType:"road",stylers:[{visibility:"off"}]},{featureType:"administrative.country",elementType:"geometry",stylers:[{visibility:"on"},{color:"#ffffff"},{weight:0.5}]},{featureType:"administrative.country",elementType:"labels",stylers:[{visibility:"on"},{color:"#FFFFFF"},{weight:0.1}]},{featureType:"administrative.locality",elementType:"labels",stylers:[{visibility:"off"}]},{featureType:"administrative.province",stylers:[{visibility:"off"}]},{featureType:"poi",elementType:"labels",stylers:[{visibility:"off"}]},{featureType:"poi",stylers:[{visibility:"off"}]},{featureType:"administrative.locality",elementType:"labels",stylers:[{visibility:"on"},{weight:0.1},{color:"#dddddd"}]},{featureType:"transit.line",stylers:[{visibility:"off"}]}];

function getMapHeight() {
	return Math.max(400, ($(window).height()-$('.navbar-leader').outerHeight()-$('.navbar-vataware').outerHeight()));
}

function isVisible(elem) {
	var docViewTop = $(window).scrollTop();
	var docViewBottom = docViewTop + $(window).height();

	var elemTop = $(elem).offset().top;
	var elemBottom = elemTop + $(elem).height();

	return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}

$(window).load(function() {
	$('body').css('margin-top', '0px');
	$('.vataware-map-container').height(getMapHeight());
	$(window).scrollTop(Math.max(400, $(window).height()-$('.navbar-leader').outerHeight()-$('.navbar-vataware').outerHeight()));
	if($(document).height() - $('.vataware-map-container').height() < $(window).height()) {
		$('footer').outerHeight("+=" + Math.max(0, $('.vataware-map-container').height() - $(window).scrollTop()));
	}
	$(window).scrollTop(Math.max(400, $(window).height()-$('.navbar-leader').outerHeight()-$('.navbar-vataware').outerHeight()));
});

$(document).ready(function() {
	$('.vataware-map-container').hover(function() {
		$('body').css('overflow','hidden');
	}, function() {
		$('body').css('overflow','scroll');
	});

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

$(window).on('beforeunload', function() { $(window).scrollTop(getMapHeight()); });
$(window).resize(function() { $('.vataware-map-container').height(getMapHeight()); });

function createPieChart(element, data, legend) {
	if(typeof legend == 'undefined') {
		legend = false;

	}
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
					width: 0
				},
				// startAngle: 0,
				label: false
			}
		},
		legend: {
			show: legend,
			labelFormatter: function(label, series) {
				if(legend) {
					return '<div class="chart-legend">' + label + '<br /><small>' + series.data[0][1] + ' minutes </small></div>';
				} else {
					return '<div class="chart-label">' + label + '</div>';
				}
			},
		},
		grid: {
			hoverable: true,
		},
	});
}