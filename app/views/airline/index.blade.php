@section('content')

<div class="container">
	<div class="page-header"><h1>Airlines <small class="pull-right" style="margin-top: 10px;"><span class="hidden-inline-xs hidden-inline-sm">Showing airlines </span>{{ $airlines->getFrom() }} - {{ $airlines->getTo() }} of {{ $airlines->getTotal() }}</small></h1></div>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>ICAO</th>
				<th>Name</th>
				<th>Callsign</th>
			</tr>
		</thead>
		<tbody class="rowlink" data-link="row">
		@foreach($airlines as $airline)
			<tr>
				<td><a href="{{ URL::route('airline.show', $airline->icao) }}">{{ $airline->icao }}</a></td>
				<td>{{ $airline->name }}</td>
				<td>{{ $airline->radio }}</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	<div class="text-center">{{ $airlines->links() }}</div>
</div>
@stop