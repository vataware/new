@section('content')

<div class="container">
	<div class="page-header"><h1>{{ $pilot->name}} <small>{{ $pilot->vatsim_id }}</small></h1></div>
	<div class="row">
		<div class="col-md-8">
			<h2 class="section-header">Controller Information</h2>
			<div class="row">
				<div class="col-md-5 col-sm-6 col-xs-12">
					<h2>{{ $pilot->rating }}</h2>
					<p class="lead">Rating</p>
				</div>
				<div class="col-md-3 col-sm-6 col-xs-12">
					<h2>{{ number_format($duties->count() + $actives->count()) }}</h2>
					<p class="lead"><small class="text-muted"><i class="glyphicon glyphicon-briefcase"></i></small> Duties</p>
				</div>
				<div class="col-md-4 col-sm-6 col-xs-12">
					<h2>{{ $hours }} <small>h</small> {{ $minutes }} <small>min</small></h2>
					<p class="lead"><small class="text-muted"><i class="glyphicon glyphicon-time"></i></small> Time Controlled</p>
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
			<h2 class="section-header">Facility Information</h2>
			<div class="row">
				<div class="col-sm-4 col-xs-12 ">
					[Chart]
				</div>
				<div class="col-sm-8">
					<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>Facility</th>
								<th width="15%" class="text-center">#</th>
								<th width="15%" class="text-center">%</th>
							</tr>
						</thead>
						@foreach($facilities as $facility => $counter)
						<tr>
							<td>{{ is_string($facility) ? '<em>' . $facility . '</em>' : $counter['data'] }}</td>
							<td class="text-center">{{ $counter['count'] }}</td>
							<td class="text-center">{{ $counter['percent'] }}%</td>
						</tr>
						@endforeach
					</table>
				</div> 
			</div>
		</div>
		<div class="col-md-4">
			<a href="{{ URL::route('pilot.show', $pilot->vatsim_id) }}" class="btn btn-vataware btn-lg btn-block">Pilot's profile</a>
			@if($actives->count() > 0)
			<h2 class="section-header">Active duty</h2>
			<table class="table table-hover">
				<tbody class="rowlink" data-link="row">
					@foreach($actives as $active)
					<tr>
						<td><a href="{{ URL::route('atc.show', $active->id) }}"><strong>{{ ($active->airport_id) ? '<img src="' . flag($active->airport->country_id) . '"> ' . $active->airport_id : $active->callsign }}</strong></a></td>
						<td>{{ $active->facility }}</td>
						<td>{{ $active->duration }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
			@endif
			<h2 class="section-header">Last duties {{-- <small class="pull-right" style="margin-top: 12px;"><a href="{{ URL::route('pilot.flights', $pilot->vatsim_id) }}">More &raquo;</a></small>--}}</h2>
			<table class="table table-striped table-hover">
				<tbody class="rowlink" data-link="row">
					@foreach($duties as $duty)
					<tr>
						<td><a href="{{ URL::route('atc.show', $duty->id) }}"><strong>{{ ($duty->airport_id) ? '<img src="' . flag($duty->airport->country_id) . '"> ' . $duty->airport_id : $duty->callsign }}</strong></a></td>
						<td>{{ $duty->facility }}</td>
						<td>{{ $duty->duration }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>

@stop