@section('breadcrumb')
<li><a href="{{ URL::route('admin.team.index') }}">Team</a></li>
<li class="active">{{ $user->name }}</li>
@stop
@section('content')
<div class="clearfix">@if($user->photo)<img src="{{ $user->photo }}" class="pull-left" style="margin-right: 10px;">@endif <p class="lead">{{ $user->description }} <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#basicModal" rel="tooltip" data-original-title="Edit"><i class="fa fa-pencil"></i></button></p></div>
<hr />
<div class="row">
	<div class="col-md-4">
		<div class="box box-primary box-solid">
			<div class="box-header">
				<h3 class="box-title">Social</h3>
				<div class="box-tools pull-right">
					<button class="btn btn-primary" data-toggle="modal" data-target="#socialModal" rel="tooltip" title="" data-original-title="Edit"><i class="fa fa-pencil"></i></button>
				</div>
			</div>
			<div class="box-body">
				<table class="table table-condensed table-striped">
					<tr>
						<th>Facebook</th>
						<td>{{ $user->facebook }}</td>
					</tr>
					<tr>
						<th>Twitter</th>
						<td>{{ $user->twitter }}</td>
					</tr>
					<tr>
						<th>Email</th>
						<td>{{ $user->email }}</td>
					</tr>
					<tr>
						<th>VATSIM ID</th>
						<td>{{ $user->vatsim_id }}</td>
					</tr>
					<tr>
						<th>JIRA</th>
						<td>{{ $user->jira }}</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="box box-warning box-solid">
			<div class="box-header">
				<h3 class="box-title">Assigned issues</h3>
			</div>
			<div class="box-body">
				@if($issues)
				<table class="table">
					<thead>
						<tr>
							<th>Issue</th>
							<th>Summary</th>
							<th>Type</th>
						</tr>
					</thead>
					<tbody>
					@foreach($issues as $issue)
						<tr>
							<td><img src="{{ $priorities[$issue->priority]->icon }}" title="{{ $priorities[$issue->priority]->name }}" /> {{ $issue->key }}</td>
							<td>{{ $issue->summary }}</td>
							<td><img src="{{ $types[$issue->type]->icon }}" /> {{ $types[$issue->type]->name }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
				@else
				<p>This team member does not have any open issues.</p>
				@endif
			</div>
		</div>
		
	</div>
	
	<div class="col-md-8">
		<div class="box box-success box-solid">
			<div class="box-header">
				<h3 class="box-title">Recent Activity</h3>
				<div class="box-tools pull-right">
					<a href="{{ URL::route('admin.team.activity', $user->id) }}" class="btn btn-success" rel="tooltip" title="" data-original-title="More"><i class="fa fa-plus" style="color: white;"></i></a>
				</div>
			</div>
			<div class="box-body">
				<div class="row">
					<div class="col-md-12">
						<ul class="timeline">
							@foreach($timelines as $date => $events)
							<li class="time-label">
								<span class="bg-black">
									{{ $date }}
								</span>
							</li>
								@foreach($events as $event)
								{{ $event->item }}
								@endforeach
							@endforeach
							<li>
								<i class="fa fa-clock-o"></i>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>



<div class="modal fade" id="basicModal" data-backdrop="static" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-yellow">
				<a type="button" class="close" data-dismiss="modal" aria-hidden="true" href="#">×</a>
				<h4 class="modal-title">Basic info</h4>
			</div>
			{{ Form::open(['class' => 'form-horizontal', 'method' => 'PUT', 'role' => 'form', 'url' => URL::route('admin.team.update', $user->id)]) }}
			<div class="modal-body">
				<div class="form-group">
					<label class="col-md-2 control-label">Name</label>
					<div class="col-md-10">
						{{ Form::text('name', $user->name, ['class' => 'form-control']) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">First name</label>
					<div class="col-md-10">
						{{ Form::text('firstname', $user->firstname, ['class' => 'form-control']) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">Description</label>
					<div class="col-md-10">
						{{ Form::textarea('description', $user->description, ['class' => 'form-control', 'rows' => 5]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">Job</label>
					<div class="col-md-10">
						{{ Form::text('job', $user->job, ['class' => 'form-control']) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">Importance</label>
					<div class="col-md-10">
						{{ Form::text('priority', $user->priority, ['class' => 'form-control col-xs-3']) }}
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a class="btn" data-dismiss="modal" aria-hidden="true" href="#">Cancel</a>
				<input class="btn btn-success" value="Save" type="submit" />
			</div>
			{{ Form::close() }}
		</div>

	</div>
</div>
<div class="modal fade" id="socialModal" data-backdrop="static" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-light-blue">
				<a type="button" class="close" data-dismiss="modal" aria-hidden="true" href="#">×</a>
				<h4 class="modal-title">Social</h4>
			</div>
			{{ Form::open(['class' => 'form-horizontal', 'method' => 'PUT', 'role' => 'form', 'url' => URL::route('admin.team.social', $user->id)]) }}
			<div class="modal-body">
				<div class="form-group">
					<label class="col-md-2 control-label">Facebook</label>
					<div class="col-md-10">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-facebook"></i></span>
							{{ Form::text('facebook', $user->facebook, ['class' => 'form-control']) }}
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">Twitter</label>
					<div class="col-md-10">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-twitter"></i></span>
							{{ Form::text('twitter', $user->twitter, ['class' => 'form-control']) }}
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">Email</label>
					<div class="col-md-10">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
							{{ Form::text('email', $user->email, ['class' => 'form-control']) }}
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">VATSIM ID</label>
					<div class="col-md-10">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-plane"></i></span>
							{{ Form::text('vatsim', $user->vatsim_id, ['class' => 'form-control']) }}
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">JIRA</label>
					<div class="col-md-10">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-bug"></i></span>
							{{ Form::text('jira', $user->jira, ['class' => 'form-control']) }}
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a class="btn" data-dismiss="modal" aria-hidden="true" href="#">Cancel</a>
				<input class="btn btn-success" value="Save" type="submit" />
			</div>
			{{ Form::close() }}
		</div>
	</div>
</div>
@stop