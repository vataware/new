@section('content')

<div class="container">
	<div class="page-header"><h1>Pilots <small class="pull-right" style="margin-top: 10px;"><span class="hidden-inline-xs hidden-inline-sm">Showing pilots </span>{{ $pilots->getFrom() }} - {{ $pilots->getTo() }} of {{ $pilots->getTotal() }}</small></h1></div>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>VATSIM ID</th>
				<th>Name</th>
				<th># Flights</th>
				<th>Miles flown</th>
				<th>Time in air</th>
			</tr>
		</thead>
		<tbody class="rowlink" data-link="row">
		@foreach($pilots as $pilot)
			<tr>
				<td><a href="{{ URL::route('pilot.show', $pilot->vatsim_id) }}">{{ $pilot->vatsim_id }}</a></td>
				<td>{{ $pilot->pilot->name }}</td>
				<td>{{ $pilot->aggregate }}</td>
				<td>{{ number_format($pilot->nm) }} nm</td>
				<td>{{ number_format($pilot->hours) }} h {{ $pilot->minutes }} min</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	<div class="text-center">{{ $pilots->links() }}</div>
</div>
@stop