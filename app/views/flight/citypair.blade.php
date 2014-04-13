@section('content')

<div class="container">
	<div class="page-header"><h1>Routes {{ $departureId }} - {{ $arrivalId }}</h1></div>
	@if(is_null($departure) && is_null($arrival))
	<p class="lead">Sorry, we could not load the routes between these airports, because both airports do not exist.</p>
	@elseif(is_null($departure))
	<p class="lead">Sorry, we could not load the routes between these airports, because the departure airport ({{ $departureId }}) does not exist.</p>
	@elseif(is_null($arrival))
	<p class="lead">Sorry, we could not load the routes between these airports, because the arrival airport ({{ $arrivalId }}) does not exist.</p>
	@else
	<div class="row">
		<div class="col-md-3 hidden-xs hidden-sm">
			<h4 class="section-header">Departure airport</h4>
			<strong><img src="{{ flag($departure->country_id) }}">&nbsp;{{ $departure->name }}</strong><br />
			{{ $departure->city ? $departure->city . ', ' : '' }}{{ $departure->country->country }}
			<hr />
			<h4 class="section-header">Arrival airport</h4>
			<strong><img src="{{ flag($arrival->country_id) }}">&nbsp;{{ $arrival->name }}</strong><br />
			{{ $arrival->city ? $arrival->city . ', ' : '' }}{{ $arrival->country->country }}
		</div>
		<div class="col-md-9">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Route</th>
						<th>Flights</th>
					</tr>
				</thead>
				<tbody class="rowlink" data-link="row">
					@foreach($routes as $route)
					<tr>
						<td><a href="{{ URL::route('flight.show', $route->id) }}">{{ $route->route }}</a></td>
						<td>{{ $route->count }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
	@endif
</div>

@stop