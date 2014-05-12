@section('content')
<div class="container">
	<h2>Flight Information - {{ $flight->callsign }} - {{ $flight->departure_id }} - {{ $flight->arrival_id }}</h2>
	<div class="row">
		<div class="col-md-6">
			<table class="table table-striped">
				<tr>
					<th>Flight status</th>
					<td><img src="{{ asset('assets/images/flightstates/' . $flight->statusIcon . '.png') }}" /> {{ $flight->status }}</td>
				</tr>
				<tr>
					<th>Pilot</th>
					<td><a href="{{ URL::route('pilot.show', $flight->vatsim_id) }}">{{ $flight->pilot->name }} ({{ $flight->vatsim_id }})</a></td>
				</tr>
				<tr>
					<th>Operator</th>
					<td>
						@if($flight->callsign_type == 1)
						<img src="{{ asset('assets/images/airlines/' . $flight->airline_id . '.png') }}">&nbsp;&nbsp;<a href="{{ URL::route('airline.show', $flight->airline_id) }}">{{ $flight->airline->name }}</a>
						@elseif($flight->callsign_type == 2)
						Private ({{ $flight->privateCountry->country }})
						@else
						Unknown
						@endif
					</td>
				</tr>
				<tr>
					<th>Aircraft</th>
					<td>
						@foreach($flight->aircraft as $aircraft)
						{{ $aircraft->manufacturer }} {{ $aircraft->model }}<br />
						@endforeach
						({{ $flight->aircraft_code }})
					</td>
				</tr>
				<tr>
					<th>Origin</th>
					<td>
						@if(!is_null($flight->departure))
						<a href="{{ URL::route('airport.show', $flight->departure_id) }}">{{ $flight->departure->icao }}</a> - {{ $flight->departure->name }}<br />
						<img src="{{ flag($flight->departure->country_id) }}">&nbsp;{{ $flight->departure->city ? $flight->departure->city . ', ' : '' }}{{ $flight->departure->country ? $flight->departure->country->country : $flight->departure->country_id }}
						@else
						<a href="{{ URL::route('airport.show', $flight->departure_id) }}">{{ $flight->departure_id }}</a>
						@endif
					</td>
				</tr>
				<tr>
					<th>Destination</th>
					<td>
						@if(!is_null($flight->arrival))
						<a href="{{ URL::route('airport.show', $flight->arrival_id) }}">{{ $flight->arrival->icao }}</a> - {{ $flight->arrival->name }}<br />
						<img src="{{ flag($flight->arrival->country_id) }}">&nbsp;{{ $flight->arrival->city ? $flight->arrival->city . ', ' : '' }}{{ $flight->arrival->country ? $flight->arrival->country->country : $flight->arrival->country_id }}
						@else
						<a href="{{ URL::route('airport.show', $flight->arrival_id) }}">{{ $flight->arrival_id }}</a>
						@endif
					</td>
				</tr>
				<tr>
					<th>Route</th>
					<td>{{ $flight->route }}</td>
				</tr>
			</table>
		</div>
		<div class="col-md-6">
			<table class="table table-striped">
				<tr>
					<th>Altitude<br /><small>(Current/Filed)</small></th>
					<td>{{ number_format($flight->last_altitude) }}ft / {{ $flight->flighttype == 'V' ? 'VFR' : (is_numeric($flight->altitude) ? number_format($flight->altitude) . 'ft' : $flight->altitude) }}</td>
				</tr>
				<tr>
					<th>Speed<br /><small>(Current/Filed)</small></th>
					<td>{{ number_format($flight->last_speed) }}kts / {{ number_format($flight->speed) }}kts</td>
				</tr>
				<tr>
					<th>Departed</th>
					<td>{{ ($flight->state != 0 && $flight->state != 4) ? (!is_null($flight->departure_time) ? $flight->departure_time->format('j M Y - H:i') : 'Unknown') : 'Departing' }}</td>
				</tr>
				
				<tr>
					<th>Distance/Time traveled</th>
					<td>{{ number_format($flight->miles) }} nm / {{ ($flight->state != 2) ? $flight->traveled_time : $flight->total_time }}</td>
				</tr>
				@if($flight->state != 2)
				<tr>
					<th><abbr title="Estimated Time to Destination">ETD</abbr></th>
					<td>{{ $flight->togo_time }}</td>
				</tr>
				<tr>
					<th><abbr title="Estimated Time of Arrival">ETA</abbr></th>
					<td>{{ ($flight->arrival_time && !is_null($flight->arrival_time)) ? $flight->arrival_time->format('j M Y - H:i') : 'Unknown' }}</td>
				</tr>
				@elseif(!is_null($flight->arrival_time))
				<tr>
					<th>Arrived</th>
					<td>{{ $flight->arrival_time->format('j M Y - H:i') }}</td>
				</tr>
				@endif
				<tr>
					<th>Remarks</th>
					<td>{{ $flight->remarks ?: 'N/A' }}</td>
				</tr>
			</table>
		</div>
	</div>
	<hr />
	<div id="map" style="height: 500px;"></div>
	<hr />
	<div class="row">
		<div class="col-md-6">
			<h3>Route Positions</h3>
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th>Time</th>
						<th>Coordinates</th>
						<th>Heading</th>
						<th>Altitude&nbsp;<small class="text-muted">ft</small></th>
						<th>Speed&nbsp;<small class="text-muted">kts</small></th>
					</tr>
				</thead>
				<tbody>
				@foreach($flight->positions->reverse() as $position)
					<tr>
						<td>{{ $position->time->format('j M Y - H:i') }}</td>
						<td>{{ $position->lat }}<br />
							{{ $position->lon }}</td>
						<td>{{ $position->heading }}</td>
						<td>{{ $position->altitude }}</td>
						<td>{{ $position->speed }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		</div>
		<div class="col-md-6">
			<h3>Route Points</h3>
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th></th>
						<th>Ident</th>
						<th>Type</th>
						<th>Via</th>
						<th>Name</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>
@stop
@section('javascript')
<script type="text/javascript">
	function initialize() {
		var map = new google.maps.Map(document.getElementById("map"), { styles: googleMapStyles, streetViewControl: false });
		var bounds = new google.maps.LatLngBounds();

		@if(!is_null($flight->departure))
		var departurePosition = new google.maps.LatLng({{ $flight->departure->lat }}, {{ $flight->departure->lon }});
		var departureAirport = new google.maps.Marker({ position: departurePosition, map: map, icon: 'http://maps.google.com/mapfiles/marker_green.png' });
		bounds.extend(departurePosition);
		@endif

		@if(!is_null($flight->arrival))
		var arrivalPosition = new google.maps.LatLng({{ $flight->arrival->lat }}, {{ $flight->arrival->lon }});
		var arrivalAirport = new google.maps.Marker({ position: arrivalPosition, map: map, icon: 'http://maps.google.com/mapfiles/marker.png' });
		bounds.extend(arrivalPosition);
		@endif

		@if(in_array($flight->state, [1, 3]))
		var currentPosition = new google.maps.Marker({ position: new google.maps.LatLng({{ $flight->last_lat }}, {{ $flight->last_lon }}), map: map, icon: {
				url: '{{ asset('assets/images/enroute/' . $flight->last_heading . '.png')}}',
				anchor: new google.maps.Point(23,23),
			}
		});
		@endif

		var flightPlanCoordinates = [{{ $flight->mapsPositions }}];
		var flightPlanColours = ['{{ implode("','", $flight->mapsColours) }}'];

		for (var i = 0; i < flightPlanCoordinates.length - 1; i++) {
			var flightPath = new google.maps.Polyline({
				path: [flightPlanCoordinates[i], flightPlanCoordinates[i+1]],
				strokeColor: flightPlanColours[i],
				strokeOpacity: 1.0,
				strokeWeight: 3,
				map: map
			});
		}

		
		for (var i = 0; i < flightPlanCoordinates.length; i++) {
			bounds.extend(flightPlanCoordinates[i]);
		}
		map.fitBounds(bounds);
	}

	google.maps.event.addDomListener(window, 'load', initialize);
</script>
@stop