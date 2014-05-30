@section('breadcrumb')
<li class="active">Airlines</li>
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
				@foreach($airlineChanges as $airlineChange)
				<tr>
					<td>{{ $airlineChange['airline']->icao }}</td>
					<td>{{ $airlineChange['airline']->name }}</td>
					<td>{{ implode(', ', $airlineChange['fields']) }}</td>
					<td><a href="{{ URL::route('admin.airline.requests', $airlineChange['airline']->icao) }}">View</a></td>
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
<script type="text/javascript">
    $(function() {
        $("#airlinesTable").dataTable();
    });
</script>
@stop