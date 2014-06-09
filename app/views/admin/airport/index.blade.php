@section('breadcrumb')
<li class="active">Airports</li>
@stop
@section('content')
<div class="box box-danger">
	<div class="box-header">
		<h3 class="box-title">Change Requests</h3>
	</div><!-- /.box-header -->
	<div class="box-body table-responsive">
		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>ICAO</th>
					<th>Name</th>
					<th>Fields</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach($airportChanges as $airportChange)
				<tr>
					<td>{{ $airportChange['airport']->icao }}</td>
					<td>{{ $airportChange['airport']->name }}</td>
					<td>{{ implode(', ', $airportChange['fields']) }}</td>
					<td><a href="{{ URL::route('admin.airport.requests', $airportChange['airport']->icao) }}">View</a></td>
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th>ICAO</th>
					<th>Name</th>
					<th>Fields</th>
					<th></th>
				</tr>
			</tfoot>
		</table>
	</div><!-- /.box-body -->
</div><!-- /.box -->
<div class="box box-danger">
	<div class="box-header">
		<h3 class="box-title">New Airports</h3>
	</div><!-- /.box-header -->
	<div class="box-body table-responsive">
		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>ICAO</th>
					<th>Name</th>
					<th>Fields</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach($airportAdditions as $airportAddition)
				<tr>
					<td>{{ $airportAddition['airport']->icao }}</td>
					<td>{{ $airportAddition['airport']->name }}</td>
					<td>{{ implode(', ', $airportAddition['fields']) }}</td>
					<td><a href="{{ URL::route('admin.airport.requests', $airportAddition['airport']->icao) }}">View</a></td>
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th>ICAO</th>
					<th>Name</th>
					<th>Fields</th>
					<th></th>
				</tr>
			</tfoot>
		</table>
	</div><!-- /.box-body -->
</div><!-- /.box -->
@stop
@section('javascript')
@stop