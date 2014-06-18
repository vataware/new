@section('content')
<div class="container">
	<div class="page-header"><h1>{{ $airport->icao }} - {{ $airport->name }}</h1></div>
	<div class="row">
		<div class="col-md-3">
			<h4 class="section-header">Location</h4>
			<table class="table table-condensed table-striped">
				@if(!is_null($airport->iata))
				<tr>
					<th>IATA</th>
					<td>{{ $airport->iata }}</td>
				</tr>
				@endif
				<tr>
					<th>City</th>
					<td>{{ $airport->city }}</td>
				</tr>
				<tr>
					<th>Country</th>
					<td><img src="{{ flag($airport->country_id) }}">&nbsp;{{ $airport->country->country }}</td>
				</tr>
				<tr>
					<th>Latitude</th>
					<td>{{ $airport->latS }}</td>
				</tr>
				<tr>
					<th>Longitude</th>
					<td>{{ $airport->lonS }}</td>
				</tr>
				<tr>
					<th>Elevation</th>
					<td>{{ $airport->elevation }} ft</td>
				</tr>
				<tr>
					<th>Type</th>
					<td>{{ $airport->type }}</td>
				</tr>
			</table>
		</div>
		<div class="col-md-6">
			<h4 class="section-header">Weather</h4>
			<h5>METAR</h5>
			@if(!is_null($metar))
			<pre>{{ $metar }}</pre>
			@else
			No METAR available
			@endif
			<h5>TAF</h5>
			@if(!is_null($taf))
			<pre>{{ $taf }}</pre>
			@else
			No TAF available
			@endif
		</div>
		<div class="col-md-3">
			<h4 class="section-header">Runways</h4>
			<table class="table table-condensed table-striped">
				@foreach($airport->runways as $runway)
				<tr>
					<th>{{ $runway->ident_a }}{{ !empty($runway->ident_b) ? '/' . $runway->ident_b : '' }}</th>
					<td>{{ $runway->length }} ft / {{ $runway->width }} ft</td>
				</tr>
				@endforeach
			</table>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<h4 class="section-header">Outbound flights <img src="{{ asset('assets/images/flightstates/departing.png') }}"></h4>
			<table class="table table-striped table-hover">
				<tbody class="rowlink" data-link="row">
					@foreach($departures as $departure)
					<tr>
						<td><a href="{{ URL::route('flight.show', $departure->id) }}">{{ $departure->callsign }}</a></td>
						<td>
							@if(!is_null($departure->arrival))
							<img src="{{ flag($departure->arrival_country_id) }}">&nbsp;{{ $departure->arrival_id }} - {{ $departure->arrival->city ? $departure->arrival->city . ', ' : '' }}{{ $departure->arrival->country->country }}
							@else
							{{ $departure->arrival_id }}
							@endif
							<br /><em>{{ $departure->pilot->name }}</em>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<div class="col-md-6">
			<h4 class="section-header">Inbound flights <img src="{{ asset('assets/images/flightstates/arrived.png') }}"></h4>
			<table class="table table-striped table-hover">
				<tbody class="rowlink" data-link="row">
					@foreach($arrivals as $arrival)
					<tr>
						<td><a href="{{ URL::route('flight.show', $arrival->id) }}">{{ $arrival->callsign }}</a></td>
						<td>
							@if(!is_null($arrival->departure))
							<img src="{{ flag($arrival->departure_country_id) }}">&nbsp;{{ $arrival->departure_id }} - {{ $arrival->departure->city ? $arrival->departure->city . ', ' : '' }}{{ $arrival->departure->country->country }}
							@else
							{{ $arrival->departure_id }}
							@endif
							<br /><em>{{ $arrival->pilot->name }}</em>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
	<hr />
	@if(Auth::check())
	<p><strong>Spotted incorrect airport information?</strong> <a href="#" data-target="#airportModal" data-remote="{{ URL::route('airport.edit', $airport->icao) }}" data-toggle="modal">Report it here!</a></p>
	@else
	<p><strong>Spotted incorrect airport information?</strong> <a href="{{ URL::route('user.intend', ['vataware_callback' => URL::current()]) }}">Please login with your VATSIM ID.</a></p>
	@endif
</div>
<div class="modal fade" id="airportModal" data-backdrop="static" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
		</div>
	</div>
</div>
@stop