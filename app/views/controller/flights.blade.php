@section('content')
<div class="container">
	<div class="page-header"><h1>{{ $pilot->name}} <small>{{ $pilot->vatsim_id }}</small></h1></div>
	<h2 class="section-header">Flights <small class="pull-right" style="margin-top: 10px;"><span class="hidden-inline-xs hidden-inline-sm">Showing flights </span>{{ $flights->getFrom() }} - {{ $flights->getTo() }} of {{ $flights->getTotal() }}</small></h2>
	<table class="table table-striped table-hover" style="margin-top: 20px;">
		<thead>
			<tr>
				<th>Callsign</th>
				<th>Type</th>
				<th>From</th>
				<th>To</th>
				<th>Duration</th>
			</tr>
		</thead>
		<tbody class="rowlink" data-link="row">
			@foreach($flights as $flight)
			<tr class="{{ $flight->missing ? 'danger' : ($flight->state != 2 ? 'success' : '') }}">
				<td><a href="{{ URL::route('flight.show', $flight->id) }}">{{ $flight->callsign }}</a>
					@if($flight->callsign_type == 1)
					<br /><img src="{{ asset('assets/images/airlines/' . $flight->airline_id . '.png') }}"></td>
					@elseif($flight->callsign_type == 2)
					<br /><img src="{{ flag($flight->airline_id) }}"> <em>Private</em>
					@else
					<br />&nbsp;
					@endif
				<td>{{ $flight->aircraft_id }}</td>
				<td><img src="{{ flag($flight->departure_country_id) }}"> {{ $flight->departure->icao or '' }} {{ $flight->departure->city or ''}}
					@if($flight->state > 1)
					<br /><small>Depart at: {{ $flight->departure_time->format('H:i') }}</small>
					@endif
				</td>
				<td><img src="{{ flag($flight->arrival_country_id) }}"> {{ $flight->arrival->icao or '' }} {{ $flight->arrival->city or '' }}
					@if($flight->state > 1)
					<br /><small>Arrive at: {{ $flight->arrival_time->format('H:i') }}</small></td>
					@endif
				<td>{{ ($flight->state == 0) ? '<em>Departing</em>' : $flight->traveled_time }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<div class="text-center">{{ $flights->links() }}</div>
</div>
@stop