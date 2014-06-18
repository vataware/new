@section('content')
<div class="container">
	<div class="page-header"><h1>{{ $airline->icao }} - {{ $airline->name }}</h1></div>
	<div class="row">
		<div class="col-md-3">
			<h4 class="section-header">Information</h4>
			<table class="table table-condensed table-striped">
				<tr>
					<th>Callsign</th>
					<td>{{ $airline->radio }}</td>
				</tr>
				@if(!is_null($airline->website))
				<tr>
					<th>Website</th>
					<td><a href="{{ $airline->website }}">{{ $airline->website_clean }}</a></td>
				</tr>
				@endif
			</table>
		</div>
		<div class="col-md-9">
			<div class="row">
				<div class="col-md-6">
					<h4 class="section-header">Pilot Activity</h4>
					<div id="chart-pilots"></div>
				</div>
				<div class="col-md-6">
					<h4 class="section-header">Type Activity</h4>
					<div id="chart-aircraft"></div>
				</div>
			</div>
		</div>
	</div>
	<h4 class="section-header">Current Flights</h4>
	<table class="table table-striped table-hover">
		<tbody class="rowlink" data-link="row">
			@foreach($activeFlights as $flight)
			<tr>
				<td><a href="{{ URL::route('flight.show', $flight->id) }}">{{ $flight->callsign }}</a></td>
				<td>{{ $flight->aircraft_id }}</td>
				<td>{{ $flight->name }}</td>
				<td>
					@if($flight->departure)
					<img src="{{ asset('assets/images/flags/' . $flight->departure_country_id . '.png') }}"> {{ $flight->departure->icao }} {{ $flight->departure->city }}
					@endif
				</td>
				<td>
					@if($flight->arrival)
					<img src="{{ asset('assets/images/flags/' . $flight->arrival_country_id . '.png') }}"> {{ $flight->arrival->icao }} {{ $flight->arrival->city }}
					@endif
				<td>{{ ($flight->state == 0) ? '<em>Departing</em>' : $flight->traveled_time }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<h4 class="section-header">25 Most Recent Flights</h4>
	<table class="table table-striped table-hover">
		<tbody class="rowlink" data-link="row">
			@foreach($historicFlights as $flight)
			<tr>
				<td><a href="{{ URL::route('flight.show', $flight->id) }}">{{ $flight->callsign }}</a></td>
				<td>{{ $flight->aircraft_id }}</td>
				<td class="rowlink-skip"><a href="{{ URL::route('pilot.show', $flight->vatsim_id) }}">{{ $flight->name }}</a></td>
				<td>
					@if($flight->departure)
					<img src="{{ asset('assets/images/flags/' . $flight->departure_country_id . '.png') }}"> {{ $flight->departure->icao }} {{ $flight->departure->city }}
					@endif
				</td>
				<td>
					@if($flight->arrival)
					<img src="{{ asset('assets/images/flags/' . $flight->arrival_country_id . '.png') }}"> {{ $flight->arrival->icao }} {{ $flight->arrival->city }}
					@endif
				<td>{{ ($flight->state == 0) ? '<em>Departing</em>' : $flight->total_time }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<hr />
	@if(Auth::check())
	<p><strong>Spotted incorrect airline information?</strong> <a href="#" data-target="#airlineModal" data-remote="{{ URL::route('airline.edit', $airline->icao) }}" data-toggle="modal">Report it here!</a></p>
	@else
	<p><strong>Spotted incorrect airline information?</strong> <a href="{{ URL::route('user.intend', ['vataware_callback' => URL::current()]) }}">Please login with your VATSIM ID.</a></p>
	@endif
</div>
<div class="modal fade" id="airlineModal" data-backdrop="static" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
		</div>
	</div>
</div>
@stop
@section('javascript')
<script type="text/javascript">
	$(function() {  
		var data = [
			{{ $pilots }}
		];
	
		var placeholder = $('#chart-pilots').css({'width':'100%' , 'min-height':'260px'});
		createPieChart(placeholder, data, true);

		var data = [
			{{ $aircraft }}
		];
	
		var placeholder = $('#chart-aircraft').css({'width':'100%' , 'min-height':'260px'});
		createPieChart(placeholder, data, true);
	});
</script>
@stop