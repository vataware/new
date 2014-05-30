@section('content')
{{ Form::open(['class' => 'form-horizontal', 'role' => 'form', 'url' => URL::route('admin.team.store')]) }}
<div class="form-group">
	<label class="col-md-2 control-label">Full Name</label>
	<div class="col-md-10">
		{{ Form::text('name', null, ['class' => 'form-control']) }}
	</div>
</div>
<div class="form-group">
	<label class="col-md-2 control-label">VATSIM ID</label>
	<div class="col-md-10">
		{{ Form::text('vatsim_id', null, ['class' => 'form-control col-xs-3']) }}
		<span class="help-block">Associated VATSIM ID must have logged in at least once or have been logged as a pilot/controller.</span>
	</div>
</div>
<div class="form-group">
	<label class="col-md-2 control-label">First name</label>
	<div class="col-md-10">
		{{ Form::text('firstname', null, ['class' => 'form-control']) }}
	</div>
</div>
<div class="form-group">
	<label class="col-md-2 control-label">Job</label>
	<div class="col-md-10">
		{{ Form::text('job', null, ['class' => 'form-control']) }}
	</div>
</div>
<div class="form-group">
	<label class="col-md-2 control-label">Description</label>
	<div class="col-md-10">
		{{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 5]) }}
	</div>
</div>
<div class="form-group">
	<label class="col-md-2 control-label">Importance</label>
	<div class="col-md-10">
		{{ Form::text('priority', 50, ['class' => 'form-control col-xs-3']) }}
	</div>
</div>
<div class="form-group">
	<div class="col-md-10 col-md-offset-2">
		{{ Form::submit('Save', ['class' => 'btn btn-success btn-flat']) }}
	</div>
</div>
{{ Form::close() }}
@stop