@section('content')

<div class="container">
	<div class="page-header"><h1>{{ $pilot->name}} <small>{{ $pilot->vatsim_id }}</small></h1></div>
	<div class="row">
		<div class="col-md-8">
			<h2 class="section-header">Pilot Information</h2>
			<div class="row">
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>
						@if($citypair->count() > 0)
						<abbr title="{{ $citypair[0]->name }}, {{ (!empty($citypair[0]->city)) ? $citypair[0]->city . ', ' : '' }}{{ $citypair[0]->country->country }}">{{ $citypair[0]->id }}</abbr>-<abbr title="{{ $citypair[1]->name }}, {{ (!empty($citypair[1]->city)) ? $citypair[1]->city . ', ' : '' }}{{ $citypair[1]->country->country }}">{{ $citypair[1]->id }}</abbr>
						@else
						Unknown
						@endif
					</h2>
					<p class="lead"><small class="text-muted"><i class="glyphicon glyphicon-star"></i></small> Popular citypair</p>
				</div>
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>{{ number_format($flights->count() + ((!is_null($active)) ? 1 : 0)) }}</h2>
					<p class="lead"><small class="text-muted"><i class="glyphicon glyphicon-plane"></i></small> Flights</p>
				</div>
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>{{ number_format($distances['ae'],1) }} <small>times</small></h2>
					<p class="lead"><small class="text-muted"><i class="glyphicon glyphicon-globe"></i></small> Around earth</p>
				</div>
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>{{ number_format($distances['km'],0) }}</h2>
					<p class="lead">Kilometres</p>
				</div>
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>{{ number_format($distances['mi'],0) }}</h2>
					<p class="lead">Statute miles</p>
				</div>
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>{{ number_format($distances['nm'],0) }}</h2>
					<p class="lead">Nautical miles</p>
				</div>
				@if($flights->count() > 0)
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>{{ $hours }} <small>h</small> {{ $minutes }} <small>min</small></h2>
					<p class="lead"><small class="text-muted"><i class="glyphicon glyphicon-time"></i></small> Time in air</p>
				</div>
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>{{ $longest->hours }} <small>h</small> {{ $longest->minutes }} <small>min</small></h2>
					<p class="lead"><small class="text-muted"><i class="glyphicon glyphicon-resize-full"></i></small> <em>{{ $longest->departure_id }} - {{ $longest->arrival_id }}</em></p>
				</div>
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>{{ $shortest->hours }} <small>h</small> {{ $shortest->minutes }} <small>min</small></h2>
					<p class="lead"><small class="text-muted"><i class="glyphicon glyphicon-resize-small"></i></small> <em>{{ $shortest->departure_id }} - {{ $shortest->arrival_id }}</em></p>
				</div>
				@endif
			</div>
			<h2 class="section-header">Airline Information</h2>
			<div class="row">
				<div class="col-sm-4 col-xs-12 ">
					[chart]
				</div>
				<div class="col-sm-8">
					<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>Airline</th>
								<th width="15%" class="text-center">#</th>
								<th width="15%" class="text-center">%</th>
							</tr>
						</thead>
						@foreach($airlines as $airline => $counter)
						<tr>
							<td>{{ is_string($airline) ? '<em>' . $airline . '</em>' : '<span data-toggle="tooltip" data-placement="bottom" data-html="true" data-title="' . $counter['data']->location . ' (' . $counter['data']->icao . ')' . '">' . $counter['data']->name . '</span>' }}</td>
							<td class="text-center">{{ $counter['count'] }}</td>
							<td class="text-center">{{ $counter['percent'] }}%</td>
						</tr>
						@endforeach
					</table>
				</div> 
			</div>
			<h2 class="section-header">Airport Information</h2>
			<div class="row">
				<div class="col-sm-4 col-xs-12 ">
					[Chart]
				</div>
				<div class="col-sm-8">
					<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>Airport</th>
								<th width="15%" class="text-center">#</th>
								<th width="15%" class="text-center">%</th>
							</tr>
						</thead>
						@foreach($airports as $airport => $counter)
						<tr>
							<td>{{ is_string($airport) ? '<em>' . $airport . '</em>' : '<span data-toggle="tooltip" data-placement="bottom" data-html="true" data-title="' . $counter['data']->name . ' (' . $counter['data']->id . ')' . '"><img src="' . flag($counter['data']->country_id) . '" /> ' . $counter['data']->icao . '<span class="hidden-inline-xs">&nbsp;&raquo; ' . (($counter['data']->city) ? $counter['data']->city . ', ' : '') . $counter['data']->country->country . '</span></span>' }}</td>
							<td class="text-center">{{ $counter['count'] }}</td>
							<td class="text-center">{{ $counter['percent'] }}%</td>
						</tr>
						@endforeach
					</table>
				</div> 
			</div>
			<h2 class="section-header">Aircraft Information</h2>
			<div class="row">
				<div class="col-sm-4 col-xs-12 ">
					[Chart]
				</div>
				<div class="col-sm-8">
					<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th colspan="2">Aircraft</th>
								<th width="15%" class="text-center">#</th>
								<th width="15%" class="text-center">%</th>
							</tr>
						</thead>
						@foreach($aircraft as $airplane => $counter)
						<tr>
							{{ is_string($airplane) ? '<td colspan="2"><em>' . $airplane . '</em>' : '<td><strong>' . $counter['data'][0]->code . '</strong></td><td> ' . implode('<br />', array_pluck($counter['data'],'name')) }}</td>
							<td class="text-center">{{ $counter['count'] }}</td>
							<td class="text-center">{{ $counter['percent'] }}%</td>
						</tr>
						@endforeach
					</table>
				</div> 
			</div>
		</div>
		<div class="col-md-4">
			<a href="{{ URL::route('controller.show', $pilot->vatsim_id) }}" class="btn btn-vataware btn-lg btn-block">Controller's profile</a>
			@if(!is_null($active))
			<h2 class="section-header">Active flight</h2>
			<table class="table table-hover">
				<tbody class="rowlink" data-link="row">
					<tr>
						<td><a href="{{ URL::route('flight.show', $active->id) }}"><strong>{{ $active->callsign }}</strong></a><br /><em>{{ $active->status }}</em></td>
						<td><img src="{{ asset('assets/images/flags/' . $active->departure_country_id . '.png') }}">&nbsp;{{ $active->departure_id }}<br />
							<img src="{{ asset('assets/images/flags/' . $active->arrival_country_id . '.png') }}">&nbsp;{{ $active->arrival_id }}</td>
						<td>{{ (is_null($active->departure)) ? 'Unknown' : (($active->departure->city) ? $active->departure->city . ', ' . strtoupper($active->departure->country_id) : $active->departure->country->country) }}<br />
							{{ (is_null($active->arrival)) ? 'Unknown' : (($active->arrival->city) ? $active->arrival->city . ', ' . strtoupper($active->arrival->country_id) : $active->arrival->country->country) }}</td>
					</tr>
				</tbody>
			</table>
			@endif
			<h2 class="section-header">Last flights <small class="pull-right" style="margin-top: 12px;"><a href="{{ URL::route('pilot.flights', $pilot->vatsim_id) }}">More &raquo;</a></small></h2>
			<table class="table table-striped table-hover">
				<tbody class="rowlink" data-link="row">
					@foreach($flights as $flight)
					<tr>
						<td><a href="{{ URL::route('flight.show', $flight->id) }}"><strong>{{ $flight->callsign }}</strong></a><br />{{ $flight->departure_time->format('d M') }}</td>
						<td>
							@unless(is_null($flight->departure))
							<img src="{{ asset('assets/images/flags/' . $flight->departure->country_id . '.png') }}">&nbsp;
							@endunless{{ $flight->departure_id }}<br />
							@unless(is_null($flight->arrival))
							<img src="{{ asset('assets/images/flags/' . $flight->arrival->country_id . '.png') }}">&nbsp;
							@endunless{{ $flight->arrival_id }}</td>
						<td>{{ (is_null($flight->departure)) ? 'Unknown' : (($flight->departure->city) ? $flight->departure->city . ', ' . strtoupper($flight->departure->country_id) : $flight->departure->country->country) }}<br />
							{{ (is_null($flight->arrival)) ? 'Unknown' : (($flight->arrival->city) ? $flight->arrival->city . ', ' . strtoupper($flight->arrival->country_id) : $flight->arrival->country->country) }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>

@stop