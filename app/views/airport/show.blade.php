@section('content')
<div class="container">
	<div class="page-header"><h1>{{ $airport->id }} - {{ $airport->name }}</h1></div>
	<div class="row">
		<div class="col-md-3 hidden-xs hidden-sm">
			<h4 class="section-header">Location</h4>
			<table class="table table-condensed table-striped">
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
			</table>
		</div>
		<div class="col-md-9">
			<div class="row">
				<div class="col-md-6">
					<h4 class="section-header">Departures</h4>
					<table class="table table-striped table-hover">
						<tbody class="rowlink" data-link="row">
							@foreach($departures as $departure)
							<tr>
								<td><a href="{{ URL::route('flight.show', $departure->id) }}">{{ $departure->callsign }}</a></td>
								<td>
									<img src="{{ flag($departure->arrival_country_id) }}">&nbsp;{{ $departure->arrival_id }} - {{ $departure->arrival->city ? $departure->arrival->city . ', ' : '' }}{{ $departure->arrival->country->country }}<br />
									<em>{{ $departure->pilot->name }}</em>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<div class="col-md-6">
					<h4 class="section-header">Arrivals</h4>
					<table class="table table-striped table-hover">
						<tbody class="rowlink" data-link="row">
							@foreach($arrivals as $arrival)
							<tr>
								<td><a href="{{ URL::route('flight.show', $arrival->id) }}">{{ $arrival->callsign }}</a></td>
								<td>
									<img src="{{ flag($arrival->departure_country_id) }}">&nbsp;{{ $arrival->departure_id }} - {{ $arrival->departure->city ? $arrival->departure->city . ', ' : '' }}{{ $arrival->departure->country->country }}<br />
									<em>{{ $arrival->pilot->name }}</em>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@stop