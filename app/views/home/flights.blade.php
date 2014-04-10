@section('content')
<div class="container">
	<h2>Current Flights <small class="pull-right" style="margin-top: 10px;"><span class="hidden-inline-xs hidden-inline-sm">Showing flights </span>{{ $flights->getFrom() }} - {{ $flights->getTo() }} of {{ $flights->getTotal() }}</small></h2>
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
				<td><a href="{{ URL::route('flight', $flight->id) }}">{{ $flight->callsign }}</a>
					@if($flight->callsign_type == 1)
					<br /><img src="{{ asset('assets/images/airlines/' . $flight->airline_id . '.png') }}"></td>
					@elseif($flight->callsign_type == 2)
					<br /><img src="{{ asset('assets/images/flags/' . $flight->airline_id . '.png') }}"> <em>Private</em>
					@else
					<br />&nbsp;
					@endif
				<td>{{ $flight->aircraft_id }}</td>
				<td>{{ $flight->pilot->name }}</td>
				<td><img src="{{ asset('assets/images/flags/' . $flight->departure_country_id . '.png') }}"> {{ $flight->departure->id or '' }} {{ $flight->departure->city or ''}}
					@if($flight->state > 1)
					<br /><small>Depart at: {{ $flight->departure_time->format('H:i') }}</small>
					@endif
				</td>
				<td><img src="{{ asset('assets/images/flags/' . $flight->arrival_country_id . '.png') }}"> {{ $flight->arrival->id or '' }} {{ $flight->arrival->city or '' }}
					@if($flight->state > 1)
					<br /><small>Arrive at: {{ $flight->arrival_time->format('H:i') }}</small></td>
					@endif
				<td>{{ ($flight->state == 0) ? '<em>Departing</em>' : $flight->duration }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<div class="text-center">{{ $flights->links() }}</div>
</div>
@stop