@section('content')

<div class="container">
	<div class="page-header"><h1>Airports <small class="pull-right" style="margin-top: 10px;"><span class="hidden-inline-xs hidden-inline-sm">Showing airports </span>{{ $airports->getFrom() }} - {{ $airports->getTo() }} of {{ $airports->getTotal() }}</small></h1></div>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>ICAO</th>
				<th>Name/IATA</th>
				<th>City, Country</th>
				<th># Departures</th>
				<th># Arrivals</th>
			</tr>
		</thead>
		<tbody class="rowlink" data-link="row">
		@foreach($airports as $airport)
			<tr>
				<td><a href="{{ URL::route('airport.show', $airport->id) }}">{{ $airport->id }}</a></td>
				<td>{{ $airport->name }}{{ !is_null($airport->iata) ? ' (' . $airport->iata . ')' : '' }}</td>
				<td><img src="{{ flag($airport->country_id) }}">&nbsp;{{ $airport->city ? $airport->city . ', ' : '' }}{{ $airport->country->country }}</td>
				<td>{{ number_format($airport->departures->count()) }}</td>
				<td>{{ number_format($airport->arrivals->count()) }}</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	<div class="text-center">{{ $airports->links() }}</div>
</div>
@stop