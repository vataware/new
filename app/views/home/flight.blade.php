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
					<td>{{ $flight->pilot->name }} ({{ $flight->vatsim_id }})</td>
				</tr>
				<tr>
					<th>Operator</th>
					<td>
						@if($flight->callsign_type == 1)
						<img src="{{ asset('assets/images/airlines/' . $flight->airline_id . '.png') }}">&nbsp;&nbsp;{{ $flight->airline->name }}
						@elseif($flight->callsign_type == 2)
						Private ({{ $flight->privateCountry->name }})
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
						@if(!is_null($flight->arrival))
						{{ $flight->departure->id }} - {{ $flight->departure->name }}<br />
						<img src="{{ asset('assets/images/flags/' . $flight->departure_country_id . '.png') }}">&nbsp;{{ $flight->departure->city ? $flight->departure->city . ', ' : '' }}{{ $flight->departureCountry->country }}
						@else
						{{ $flight->departure_id }}
						@endif
					</td>
				</tr>
				<tr>
					<th>Destination</th>
					<td>
						@if(!is_null($flight->arrival))
						{{ $flight->arrival->id }} - {{ $flight->arrival->name }}<br />
						<img src="{{ asset('assets/images/flags/' . $flight->arrival_country_id . '.png') }}">&nbsp;{{ $flight->arrival->city ? $flight->arrival->city . ', ' : '' }}{{ $flight->arrivalCountry->country }}
						@else
						{{ $flight->arrival_id }}
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
					<td>{{ number_format($flight->positions->last()->altitude) }}ft / {{ $flight->flighttype == 'V' ? 'VFR' : (is_numeric($flight->altitude) ? number_format($flight->altitude) . 'ft' : $flight->altitude) }}</td>
				</tr>
				<tr>
					<th>Speed<br /><small>(Current/Filed)</small></th>
					<td>{{ number_format($flight->positions->last()->speed) }}kts / {{ number_format($flight->speed) }}kts</td>
				</tr>
				<tr>
					<th>Departed</th>
					<td>{{ ($flight->state != 0 && $flight->state != 4) ? (!is_null($flight->departure_time) ? $flight->departure_time->format('j M Y - H:i') : 'Unknown') : 'Departing' }}</td>
				</tr>
				
				<tr>
					<th>Distance/Time traveled</th>
					<td>{{ $flight->traveled_time }}</td>
				</tr>
				@if($flight->state != 2)
				<tr>
					<th>Distance/Time to go</th>
					<td>{{ $flight->togo_time }}</td>
				</tr>
				<tr>
					<th>ETA</th>
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
	<p>[Map here]</p>
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
				@foreach($flight->positions as $position)
					<tr>
						<td>{{ $position->time->format('j M Y - H:i') }}</td>
						<td>{{ $position->latS }}<br />
							{{ $position->lonS }}</td>
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