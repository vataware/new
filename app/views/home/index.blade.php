@section('content')

<div class="mapContainer" id="flightRadar"></div>
<div class="smallMapStats">
PILOTS ONLINE: <span style="color:#138995;">{{ $pilots }}</span>&nbsp; &nbsp; ATC ONLINE: <span style="color:#138995;">{{ $atc }}</span>
</div>
<div style="margin-top: -5px;">
@include('search.bar')
</div>
<div class="container"><br /><br />
	<div class="tiles" style="text-align: center;">
		<a href="#" class="tile" style="background-color:#138995;">
			<div style="margin-top: 50px;">
				<i class="fa fa-user" style="font-size:50px; margin-bottom: 15px;"></i><br />
				Pilot Information
			</div>
		</a>
		<a href="#" class="tile" style="background-color:#199caa;">
			<div style="margin-top: 50px;">
				<i class="fa fa-desktop" style="font-size:50px; margin-bottom: 15px;"></i><br />
				ATC Information
			</div>
		</a>
		<a href="#" class="tile" style="background-color:#1cb1c1;">
			<div style="margin-top: 50px;">
				<i class="fa fa-sun-o" style="font-size:50px; margin-bottom:15px;"></i><br />
				Weather Information
			</div>
		</a>
		<a href="#" class="tile" style="background-color:#1fbfcf;">
			<div style="margin-top: 50px;">
				<i class="fa fa-globe" style="font-size:50px; margin-bottom: 15px;"></i><br />
				Statistics
			</div>
		</a>
		<a href="#" class="tile" style="background-color:#22cbdc;">
			<div style="margin-top: 50px;">
				<i class="fa fa-cloud-download" style="font-size:50px; margin-bottom: 15px;"></i><br />
				Resources
			</div>
		</a>
	</div>
	<div>
		<h2 class="section-header">Statistics</h2>
		<div class="well well-sm">
			<div class="row homeStats">
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-1">
					<h2>{{ $users }}</h2>
					<small>Users Online</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-2">
					<h2>{{ $day }}</h2>
					<small>Flights Today</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-3">
					<h2>{{ $month }}</h2>
					<small>Flights This Month</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-4">
					<h2>{{ $year }}</h2>
					<small>Flights This Year</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-5">
					<h2>{{ $change }}%<sup><i style="color:#138995; font-size: 17px;" class="glyphicon glyphicon-arrow-{{ $changeArrow }}"></i> </sup></h2>
					<small>Compared to last year</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-6">
					<h2>{{ $distance }}</h2>
					<small>Miles flown today</small>
				</div>
			</div>
		</div>
	</div>
	<h2 class="section-header">Active flights</h2>
	<table class="table table-striped table-hover" style="margin-top: 20px;">
		<thead>
			<tr>
				<th>Callsign</th>
				<th>Type</th>
				<th>Pilot</th>
				<th>From</th>
				<th>To</th>
				<th>Duration</th>
			</tr>
		</thead>
		<tbody class="rowlink" data-link="row">
			@foreach($flights as $flight)
			<tr>
				<td><a href="{{ URL::route('flight.show', $flight->id) }}">{{ $flight->callsign }}</a></td>
				<td>{{ $flight->aircraft_id }}</td>
				<td>{{ $flight->pilot->name }}</td>
				<td>
					@if($flight->departure)
					<img src="{{ asset('assets/images/flags/' . $flight->departure_country_id . '.png') }}"> {{ $flight->departure->id }} {{ $flight->departure->city }}
					@endif
				</td>
				<td>
					@if($flight->arrival)
					<img src="{{ asset('assets/images/flags/' . $flight->arrival_country_id . '.png') }}"> {{ $flight->arrival->id }} {{ $flight->arrival->city }}
					@endif
				<td>{{ ($flight->state == 0) ? '<em>Departing</em>' : $flight->traveled_time }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>

@stop
@section('javascript')
<script type="text/javascript">
	function initialize() {
		var map = new google.maps.Map(document.getElementById("flightRadar"), { styles: googleMapStyles, zoom: {{ Session::has('map.zoom') ? Session::get('map.zoom') : 2 }}, center: new google.maps.LatLng({{ Session::has('map.coordinates') ? Session::get('map.coordinates') : '30, 0' }}), scrollwheel: false, streetViewControl: false, minZoom: 2, maxZoom: 14 });

		var flights = [];
		var polylines = [];

		updateMap = function(firstload) {
			if(typeof firstload == 'undefined') firstload = 0;
			$.get('{{ URL::route('map.api') }}', {z: map.getZoom(), lat: map.getCenter().k, lon: map.getCenter().A, force: firstload}, function(data) {
				for(i = 0; i < data.length; i++) {
					var flight = data[i];
					if(!(flight.id in flights)) {
						var marker = new google.maps.Marker({ position: new google.maps.LatLng(flight.lat, flight.lon), map: map, icon: {
								url: '{{ asset('assets/images/mapicon-red.png') }}?deg=' + flight.heading,
								anchor: new google.maps.Point(10,10),
								size: new google.maps.Size(20,20),
								origin: new google.maps.Point(0,0)
							},
							optimized: false,
							flightId: flight.id,
							heading: flight.heading,
						});

						google.maps.event.addListener(marker, 'click', function() {
							for(i=0; i < polylines.length; i++) {
								polylines[i].setMap(null);
							}
							$.get('{{ URL::route('map.flight') }}', {id: this.flightId}, function(data) {
								for (i = 0; i < data.coordinates.length - 1; i++) {
									var flightPath = new google.maps.Polyline({
										path: [new google.maps.LatLng(data.coordinates[i][0], data.coordinates[i][1]), new google.maps.LatLng(data.coordinates[i+1][0], data.coordinates[i+1][1])],
										strokeColor: data.colours[i],
										strokeOpacity: 1.0,
										strokeWeight: 3,
										map: map
									});

									polylines.push(flightPath);
								}
							});
							
					 	});

						flights[flight.id] = marker;
					} else {
						flights[flight.id].setPosition(new google.maps.LatLng(flight.lat, flight.lon));
					}
				}
			});
		};

		google.maps.event.addListener(map, 'idle', function() {
			bounds = map.getBounds();
			updateMap(1);
		});

		setInterval(updateMap, 60000);
	}

	google.maps.event.addDomListener(window, 'load', initialize);
</script>
@stop