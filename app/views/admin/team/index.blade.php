@section('content')
<p><a href="#" class="btn btn-success btn-flat">New team member</a></p>
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
	<tr class="{{ ($team->trashed()) ? 'text-muted' : '' }}">
		<td>{{ $team->name }}</td>
		<td>{{ $team->job }}</td>
		<td>{{ $team->email }}</td>
		<td class="text-right"><a href="{{ URL::route('admin.team.show', $team->id) }}" class="btn btn-primary btn-xs btn-flat">More info</a>
			@if(!$team->trashed())
			<a href="{{ URL::route('admin.team.destroy', $team->id) }}" class="btn btn-xs btn-flat btn-confirm btn-danger" data-title="Delete team member" data-message="Are you sure you want to delete this person as team member?<br />Please note that this person won't get deleted, only marked as inactive." data-type="danger" data-confirm="Mark as Inactive" data-method="DELETE">Delete</a>
			@else
			<a href="{{ URL::route('admin.team.restore', $team->id) }}" class="btn btn-xs btn-flat btn-confirm btn-warning" data-title="Reactivate team member" data-message="Are you sure you want to reactivate this person as a team member?" data-type="warning" data-confirm="Mark as Active" data-method="POST">Restore</a>
			@endif
		</td>
	</tr>
	@endforeach
	</tbody>
</table>

@stop