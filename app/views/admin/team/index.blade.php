@section('content')

<table class="table table-striped">
	<thead>
		<tr>
			<th>Name</th>
			<th>Job</th>
			<th>Email</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	@foreach($teams as $team)
	<tr>
		<td>{{ $team->name }}</td>
		<td>{{ $team->job }}</td>
		<td>{{ $team->email }}</td>
		<td class="text-right"><a href="{{ URL::route('admin.team.show', $team->id) }}" class="btn btn-primary btn-xs btn-flat">More info</a> <a href="#" class="btn btn-warning btn-xs btn-flat">Edit</a> <a href="#" class="btn btn-danger btn-xs btn-flat">Delete</a></td>
	</tr>
	@endforeach
	</tbody>
</table>

@stop