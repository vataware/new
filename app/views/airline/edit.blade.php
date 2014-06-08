{{ Form::open(['url' => URL::route('airline.update', $airline->icao), 'class' => 'form-horizontal', 'method' => 'PUT']) }}
<div class="modal-header">
	<a type="button" class="close" data-dismiss="modal" aria-hidden="true" href="#">×</a>
	<h4 class="modal-title">Request data change</h4>
</div>
<div class="modal-body">
	<p>Please change the incorrect information here. All submissions will be checked manually to ensure accuracy.</p>
	<hr />
	<div class="form-group">
		<label class="col-md-2 control-label">ICAO</label>
		<div class="col-md-10">
			{{ Form::text('icao', $airline->icao, ['class' => 'form-control', 'required']) }}
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">Name</label>
		<div class="col-md-10">
			{{ Form::text('name', $airline->name, ['class' => 'form-control', 'required']) }}
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">Callsign</label>
		<div class="col-md-10">
			{{ Form::text('radio', $airline->radio, ['class' => 'form-control', 'required']) }}
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">Website</label>
		<div class="col-md-10">
			{{ Form::text('website', $airline->website, ['class' => 'form-control', 'required']) }}
		</div>
	</div>
</div>
<div class="modal-footer">
	<a class="btn" data-dismiss="modal" aria-hidden="true" href="#">Cancel</a>
	<input class="btn btn-success" value="Save" type="submit" />
</div>
{{ Form::close() }}