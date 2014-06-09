@section('content')
<div class="container">
	<div class="page-header"><h1>My Account</h1></div>
	{{ Form::open(['url' => URL::route('user.update'), 'method' => 'PUT', 'class' => 'form-horizontal', 'role' => 'form']) }}
		<div class="form-group">
			{{ Form::label('name', 'Full Name', ['class' => 'col-sm-3 control-label']) }}
			<div class="col-sm-9">
				<p class="form-control-static">{{ $user->name }}</p>
			</div>
		</div>
		<div class="form-group">
			{{ Form::label('vatsimid', 'Vatsim ID', ['class' => 'col-sm-3 control-label']) }}
			<div class="col-sm-9">
				<p class="form-control-static">{{ $user->vatsim_id }}</p>
			</div>
		</div>
		<div class="form-group">
			{{ Form::label('anonymous', 'Privacy', ['class' => 'col-sm-3 control-label']) }}
			<div class="col-sm-9">
				<div class="checkbox">
					{{ Form::checkbox('anonymous', 1, $user->anonymous) }} Appear as 'Anonymous'
				</div>
				<span class="help-block">This setting only affects your name, your VATSIM ID will remain public.</span>
			</div>
		</div>
		<div class="form-group">
			{{ Form::label('anonymous', 'Map Style', ['class' => 'col-sm-3 control-label']) }}
			<div class="col-sm-9">
				{{ Form::select('map', $maps, $user->map, ['class' => 'form-control']) }}
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9">
				{{ Form::submit('Save preferences', ['class' => 'btn btn-success']) }}
			</div>
		</div>
		<hr />
		@if($user->processing == 2)
		<div class="form-group">
			<label class="col-sm-3 control-label"><a href="{{ URL::route('user.processing') }}" class="btn btn-warning">Reset processing</a></label>
			<div class="col-sm-9">
				<span class="help-block">If your profile stuck on the 'The data for this pilot is currently being processed. In a couple of minutes, all statistics will be available.' message, you can reset it here.</span>
			</div>
		</div>
		@endif
		<div class="form-group">
			<label class="col-sm-3 control-label"><a href="{{ URL::route('user.name') }}" class="btn btn-primary">Update Name</a></label>
			<div class="col-sm-9">
				<span class="help-block">Updated your name on the VATSIM Network? Click here to reload it.</span>
			</div>
		</div>
	{{ Form::close() }}
</div>
@stop