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
	<h4 class="section-header">25 Most Recent Flights</h4>
	<table class="table table-striped table-hover">
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