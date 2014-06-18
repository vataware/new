@section('content')
<div class="container">
	<h2 class="section-header">Flight Information - {{ $flight->callsign }} - {{ $flight->departure_id }} - {{ $flight->arrival_id }}</h2>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#home" data-toggle="tab">Flight Info</a></li>
		<li><a href="#posreps" data-toggle="tab">Position Reports</a></li>
		<li><a href="#flightplan" data-toggle="tab">Flight Plan</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="home">
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
			<div id="profile" style="height: 200px;"></div>
			<hr />
			<div id="map" style="height: 500px;"></div>
		</div>
		<div class="tab-pane" id="posreps">
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
					<h3>Route Points <small>// beta</small></h3>
					<p class="text-muted"><strong>Warning:</strong> Feature still in beta, waypoints can be shown incorrectly on the map.</p>
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
							@foreach($flightplan->toArray() as $i => $part)
							<tr>
								<td><small>{{ $i + 1 }}</small></td>
								<td>{{ $part['ident'] }}</td>
								<td>{{ $part['type'] }}</td>
								<td>{{ $part['airway'] }}</td>
								<td>{{ $part['name'] }}<br /><small>{{ $part['freq'] }}</small></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="flightplan">
			<div class="visible-xs visible-sm"><p style="margin-top: 10px;">Flight plans not available on mobile browsers.</p></div>
			<div class="hidden-xs hidden-sm" id="flightplan-container">
				<div id="flightplan-title">
					FLIGHT PLAN
				</div>
				<div id="flightplan-section1" class="clearfix">
					<div id="flightplan-priority">
						PRIORITY<br />
						<big>&lt;&lt; = FF &#10142;</big>
					</div>
					<div id="flightplan-addressee">
						ADDRESSEE(S)<br />
						<span style="border-style: solid; border-color: black; border-width: 1px 0 1px 1px; display: block; padding: 3px;" class="flightplan-field">&nbsp;</span>
						<span style="border-style: solid; border-color: black; border-width: 0 0 1px 1px; display: block; padding: 3px;" class="flightplan-field">&nbsp;</span>
						<span><span style="border-style: solid; border-color: black; border-width: 0 1px 1px 1px; display: block; padding: 3px; width: 80%; float: left;" class="flightplan-field">&nbsp;</span><span style="display:block; float: left; margin-left: 20px;"><big>&lt;&lt; =</big></span></span>
					</div>
				</div>
				<div id="flightplan-section2" class="clearfix">
					<div class="flightplan-section2-row clearfix">
						<div id="flightplan-messagetype">
							3. MESSAGE TYPE<br />
							<big>&lt;&lt; = (FPL</big>
						</div>
						<div id="flightplan-aircraftidentification">
							7. AIRCRAFT IDENTIFICATION<br />
							<span><span class="display: inline-block;"><strong>&mdash;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; width: 180px;" class="flightplan-monospace flightplan-spacing flightplan-field">{{ $flight->callsign }}</span></span>
						</div>
						<div id="flightplan-flightrules">
							8. FLIGHT RULES<br />
							<span><span class="display: inline-block;"><strong>&mdash;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center;" class="flightplan-monospace flightplan-field">{{ $flight->flighttype }}</span></span>
						</div>
						<div id="flightplan-flighttype">
							TYPE OF FLIGHT<br />
							<span style="float: right;"><span style="border: 1px solid black; display: inline-block; float: left; padding: 3px 10px; text-align: center;" class="flightplan-monospace flightplan-field">&nbsp;</span><span style="display: inline-block; float: left; margin-left: 20px;"><big>&lt;&lt; =</big></span></span>
						</div>
					</div>
					<div class="flightplan-section2-row clearfix">
						<div id="flightplan-number">
							9. NUMBER<br />
							<span><span class="display: inline-block;"><strong>&mdash;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center;" class="flightplan-monospace flightplan-spacing flightplan-field">&nbsp;</span></span>
						</div>
						<div id="flightplan-aircrafttype">
							TYPE OF AIRCRAFT<br />
							<span><span class="display: inline-block;"><strong>&mdash;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px;" class="flightplan-monospace flightplan-spacing flightplan-field">{{ $flight->aircraft_id }}</span></span>
						</div>
						<div id="flightplan-turbulence">
							WAKE TURBULENCE CAT<br />
							<span><span class="display: inline-block;"><strong><big>/</big></strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center; margin-left: 10px;" class="flightplan-monospace flightplan-field">{{ $flight->turbulence ?: '&nbsp;' }}</span></span>
						</div>
						<div id="flightplan-equipment">
							10. EQUIPMENT<br />
							<span style="float: right;"><span><span class="display: inline-block;"><strong>&mdash;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center;" class="flightplan-monospace flightplan-spacing flightplan-field">{{ $flight->equipment ?: '&nbsp;' }}</span><span style="display: inline-block; margin-left: 20px;"><big>&lt;&lt; =</big></span></span>
						</div>
					</div>
					<div class="flightplan-section2-row clearfix">
						<div id="flightplan-departure">
							13. DEPARTURE AERODROME<br />
							<span><span class="display: inline-block;"><strong>&mdash;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center;" class="flightplan-monospace flightplan-spacing flightplan-field">{{ $flight->departure_id }}</span></span>
						</div>
						<div id="flightplan-departuretime">
							<span style="margin-left: 25px;">TIME</span><br />
							<span><span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center; width: 95px;" class="flightplan-monospace flightplan-spacing flightplan-field">{{ $flight->departure_time->format('Hi') }}</span><span style="display: inline-block; margin-left: 20px;"><big>&lt;&lt; =</big></span></span>
						</div>
					</div>
					<div class="flightplan-section2-row clearfix">
						<div id="flightplan-cruisingspeed">
							15. CRUISING SPEED<br />
							<span><span class="display: inline-block;"><strong>&mdash;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; width: 150px; text-align: center;" class="flightplan-monospace flightplan-spacing flightplan-field">{{ $flight->speed }}</span></span>
						</div>
						<div id="flightplan-flightlevel">
							LEVEL<br />
							<span><span style="border: 1px solid black; display: inline-block; padding: 3px 10px; width: 150px; text-align: center;" class="flightplan-monospace flightplan-spacing flightplan-field">{{ $flight->altitude }}</span> <span class="display: inline-block;"><strong>&#10142;</strong></span> </span>
						</div>
						<div id="flightplan-route">
							ROUTE<br />
							<span style="border-style: solid; border-color: black; border-bottom-style: dotted; border-width: 1px 0 0 1px; display: inline-block; padding: 3px 10px; width: 100%;" class="flightplan-monospace flightplan-field">{{ sentenceSplitter($flight->route, 73) }}</span>
						</div>
						<div class="flightplan-route-cont">
							<span style="border-style: dotted; border-color: black; border-width: 1px 0 0 0; display: inline-block; padding: 3px 10px; width: 100%;" class="flightplan-monospace flightplan-field">{{ sentenceSplitter($flight->route, 124, 73, '&nbsp;') }}</span>
						</div>
						<div class="flightplan-route-cont">
							<span style="border-style: dotted; border-color: black; border-width: 1px 0 1px 0; display: inline-block; padding: 3px 10px; width: 100%;" class="flightplan-monospace flightplan-field">{{ sentenceSplitter($flight->route, 124, 197, '&nbsp;') }}</span>
						</div>
						<div class="flightplan-route-cont">
							<span><span style="border-right-style: solid; border-bottom-style: solid; border-color: black; border-width: 0 1px 1px 0; display: inline-block; padding: 3px 10px; width: 90%; margin-right: 10px;" class="flightplan-monospace flightplan-field">{{ sentenceSplitter($flight->route, 124, 321, '&nbsp;') }}</span> <span class="display: inline-block;"><big>&lt;&lt; =</big></span> </span>
						</div>
					</div>
					<div class="flightplan-section2-row clearfix">
						<div id="flightplan-arrival">
							16. DESTINATION AERODROME<br />
							<span><span class="display: inline-block;"><strong>&mdash;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center; width: 110px;" class="flightplan-monospace flightplan-spacing flightplan-field">{{ $flight->arrival_id }}</span></span>
						</div>
						<div id="flightplan-totaleet">
							<span style="margin-left: 25px;">TOTAL EET</span><br />
							<span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center;" class="flightplan-monospace flightplan-spacing flightplan-field">{{ $flight->totaleet }}</span>
						</div>
						<div id="flightplan-alternate">
							ALTN AERODROME<br />
							<span><span class="display: inline-block;"><strong>&#10142;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center; width: 110px;" class="flightplan-monospace flightplan-spacing flightplan-field">ZZZZ</span></span>
						</div>
						<div id="flightplan-alternate">
							2ND ALTN AERODROME<br />
							<span><span class="display: inline-block;"><strong>&#10142;</strong></span> <span style="border: 1px solid black; display: inline-block; padding: 3px 10px; text-align: center; width: 110px;" class="flightplan-monospace flightplan-spacing flightplan-field">ZZZZ</span><span style="display: inline-block; margin-left: 20px;"><big>&lt;&lt; =</big></span></span>
						</div>
					</div>
					<div class="flightplan-section2-row clearfix">
						<div id="flightplan-remarks">
							<span style="padding-left: 5px;">18. OTHER INFORMATION</span><br />
							<span style="border-style: solid; border-color: black; border-bottom-style: dotted; border-width: 1px 0 0 0; display: inline-block; padding: 3px 10px; width: 100%;" class="flightplan-monospace flightplan-field">{{ sentenceSplitter($flight->remarks, 124) }}</span>
						</div>
						<div class="flightplan-remarks-cont">
							<span style="border-style: dotted; border-color: black; border-width: 1px 0 0 0; display: inline-block; padding: 3px 10px; width: 100%;" class="flightplan-monospace flightplan-field">{{ sentenceSplitter($flight->remarks, 124, 124, '&nbsp;') }}</span>
						</div>
						<div class="flightplan-remarks-cont">
							<span style="border-style: dotted; border-color: black; border-width: 1px 0 1px 0; display: inline-block; padding: 3px 10px; width: 100%;" class="flightplan-monospace flightplan-field">{{ sentenceSplitter($flight->remarks, 124, 248, '&nbsp;') }}</span>
						</div>
						<div class="flightplan-remarks-cont">
							<span><span style="border-right-style: solid; border-bottom-style: solid; border-color: black; border-width: 0 1px 1px 0; display: inline-block; padding: 3px 10px; width: 90%; margin-right: 10px;" class="flightplan-monospace flightplan-field">{{ sentenceSplitter($flight->remarks, 112, 372, '&nbsp;') }}</span> <span class="display: inline-block;"><big>) &lt;&lt; =</big></span> </span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop
@section('javascript')
<script type="text/javascript">
	var flightPlanCoordinates;
	function initialize() {
		var map = new google.maps.Map(document.getElementById("map"), {
			@if($mapstyle != 'google')styles: googleMapStyles.{{ $mapstyle }},
			@endifstreetViewControl: false
		});
		var bounds = new google.maps.LatLngBounds();

		flightPlanCoordinates = [{{ $flight->mapsPositions }}];
		var flightRouteCoordinates = [{{ $flightplan->map($flight->departure, $flight->arrival) }}];
		var flightPlanColours = ['{{ implode("','", $flight->mapsColours) }}'];

		@foreach($flightplan->get() as $marker)
		var routePosition = new google.maps.LatLng({{ $marker->lat }}, {{ $marker->lon }});
		var routeMarker = new google.maps.Marker({ position: routePosition, map: map, icon: {
			url: '{{ asset('assets/images/markers/' . $marker->icon . '.png') }}',
			anchor: new google.maps.Point({{ $marker->anchor }}),
		}});
		@endforeach

		for (var i = 0; i < flightRouteCoordinates.length - 1; i++) {
			var flightRoute = new google.maps.Polyline({
				path: [flightRouteCoordinates[i], flightRouteCoordinates[i+1]],
				strokeColor: '#000000',
				strokeOpacity: 1.0,
				strokeWeight: 3,
				map: map
			});
		}

		for (var i = 0; i < flightPlanCoordinates.length - 1; i++) {
			var flightPath = new google.maps.Polyline({
				path: [flightPlanCoordinates[i], flightPlanCoordinates[i+1]],
				strokeColor: flightPlanColours[i],
				strokeOpacity: 1.0,
				strokeWeight: 3,
				map: map
			});
		}

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
				url: '{{ asset('assets/images/enroute/' . $flight->last_heading . '.png') }}',
				anchor: new google.maps.Point(23,23),
			}
		});
		@endif
		
		for (var i = 0; i < flightPlanCoordinates.length; i++) {
			bounds.extend(flightPlanCoordinates[i]);
		}
		map.fitBounds(bounds);
	}

	google.maps.event.addDomListener(window, 'load', initialize);
</script>
<script type="text/javascript">

	$(function() {
		var speed = [{{ $flight->profileSpeed }}];
		var altitude = [{{ $flight->profileAltitude }}];
		var elevations = [{{ $flight->profileElevations }}];

		$.plot("#profile", [
			{ data: speed, label: "Speed (kts)" },
			{ data: altitude, label: "Altitude (ft)", yaxis: 2 },
			{ data: elevations, label: "Ground Elevation (ft)", yaxis: 2, lines: { fill: true }, color: 'green' }
		], {
			xaxes: [ { mode: "time" } ],
			yaxes: [ { min: 0 }, {
				min: 0,
				alignTicksWithAxis: 1,
				position: "right",
			} ],
			legend: { position: "nw" },
			series: {
				lines: { show: true },
				points: { show: true }
			},
			grid: { hoverable: true }
		});

		var regex = /^([^\(\)]+)\(([^\(\)]+)\)$/;
		var now = new Date();

		$("#profile").bind("plothover", function (event, pos, item) {
			if (item) {
				x = item.datapoint[0];
				y = item.datapoint[1].toFixed(0);
				label = item.series.label.match(regex);
				date = new Date(x + (now.getTimezoneOffset() * 60000));
				$("#tooltip").html(label[1].trim() + ": " + y + " " + label[2].trim() + " at " + date.getHours() + ':' + date.getMinutes())
					.css({top: item.pageY+5, left: item.pageX+5})
					.fadeIn(200);
			} else {
				$("#tooltip").hide();
			}
		});

		$("<div id='tooltip'></div>").css({
			position: "absolute",
			display: "none",
			border: "1px solid #fdd",
			padding: "2px",
			"background-color": "#fee",
			opacity: 0.80
		}).appendTo("body");
	});

	</script>
@stop