@section('content')
<div id="banners">
	<div class="banner" id="competition-may" style="background-image: url({{ asset('assets/images/banners/competition-may.jpg') }});"><a class="banner-link" href="http://vataware.com/forums/viewtopic.php?f=25&t=141"></a></div>
</div>

<div class="container"><br /><br />
	{{--<div class="tiles" style="text-align: center;">
		<a href="#" class="tile" style="background-color:#138995;">
			<div style="margin-top: 50px;">
				<i class="fa fa-user" style="font-size:50px; margin-bottom: 15px;"></i><br />
				Pilot Information
			</div>
		</a>
		<a href="#" class="tile" style="background-color:#199caa;">
			<div style="margin-top: 50px;">
				<i class="fa fa-desktop" style="font-size:50px; margin-bottom: 15px;"></i><br />
				ATC Information
			</div>
		</a>
		<a href="#" class="tile" style="background-color:#1cb1c1;">
			<div style="margin-top: 50px;">
				<i class="fa fa-sun-o" style="font-size:50px; margin-bottom:15px;"></i><br />
				Weather Information
			</div>
		</a>
		<a href="#" class="tile" style="background-color:#1fbfcf;">
			<div style="margin-top: 50px;">
				<i class="fa fa-globe" style="font-size:50px; margin-bottom: 15px;"></i><br />
				Statistics
			</div>
		</a>
		<a href="#" class="tile" style="background-color:#22cbdc;">
			<div style="margin-top: 50px;">
				<i class="fa fa-cloud-download" style="font-size:50px; margin-bottom: 15px;"></i><br />
				Resources
			</div>
		</a>
	</div>--}}
	<div>
		<h2 class="section-header">Statistics</h2>
		<div class="well well-sm">
			<div class="row homeStats">
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-1">
					<h2>{{ $users }}</h2>
					<small>Users Online</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-2">
					<h2>{{ $day }}</h2>
					<small>Flights Today</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-3">
					<h2>{{ $month }}</h2>
					<small>Flights This Month</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-4">
					<h2>{{ $year }}</h2>
					<small>Flights This Year</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-5">
					<h2>{{ $change }}%<sup><i style="color:#138995; font-size: 17px;" class="glyphicon glyphicon-arrow-{{ $changeArrow }}"></i> </sup></h2>
					<small>Compared to last year</small>
				</div>
				<div class="col-xs-6 col-sm-4 col-md-2 homeStat-6">
					<h2>{{ $distance }}</h2>
					<small>Miles flown today</small>
				</div>
			</div>
		</div>
	</div>
	<h2 class="section-header">Active flights</h2>
	<table class="table table-striped table-hover" style="margin-top: 20px;">
		<thead>
			<tr>
				<th>Callsign<span class="visible-xs"><br /><em>Aircraft</em></span></th>
				<th class="hidden-xs">Type</th>
				<th>Pilot<span class="visible-xs"><br /><em>From/To</em></span></th>
				<th class="hidden-xs">From</th>
				<th class="hidden-xs">To</th>
				<th class="hidden-xs">Duration</th>
			</tr>
		</thead>
		<tbody class="rowlink" data-link="row">
			@foreach($flights as $flight)
			<tr>
				<td><a href="{{ URL::route('flight.show', $flight->id) }}">{{ $flight->callsign }}</a><span class="visible-xs"><br /><em>{{ $flight->aircraft_id }}</em></span></td>
				<td class="hidden-xs">{{ $flight->aircraft_id }}</td>
				<td>{{ $flight->pilot->name }}<span class="visible-xs"><br /><em>
					@if($flight->departure)
					<img src="{{ asset('assets/images/flags/' . $flight->departure_country_id . '.png') }}"> {{ $flight->departure->icao }}
					@endif
					&nbsp;-&nbsp;
					@if($flight->arrival)
					<img src="{{ asset('assets/images/flags/' . $flight->arrival_country_id . '.png') }}"> {{ $flight->arrival->icao }}
					@endif
				</em></span></td>
				<td class="hidden-xs">
					@if($flight->departure)
					<img src="{{ asset('assets/images/flags/' . $flight->departure_country_id . '.png') }}"> {{ $flight->departure->icao }} {{ $flight->departure->city }}
					@endif
				</td>
				<td class="hidden-xs">
					@if($flight->arrival)
					<img src="{{ asset('assets/images/flags/' . $flight->arrival_country_id . '.png') }}"> {{ $flight->arrival->icao }} {{ $flight->arrival->city }}
					@endif
				<td class="hidden-xs">{{ ($flight->state == 0) ? '<em>Departing</em>' : $flight->traveled_time }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>

@stop