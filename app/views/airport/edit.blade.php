{{ Form::open(['url' => URL::route('airport.update', $airport->icao), 'class' => 'form-horizontal', 'method' => 'PUT']) }}
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
			{{ Form::text('icao', $airport->icao, ['class' => 'form-control', 'required']) }}
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">IATA</label>
		<div class="col-md-10">
			{{ Form::text('iata', $airport->iata, ['class' => 'form-control', 'placeholder' => 'N/A']) }}
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">Name</label>
		<div class="col-md-10">
			{{ Form::text('name', $airport->name, ['class' => 'form-control', 'required']) }}
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">City</label>
		<div class="col-md-10">
			{{ Form::text('city', $airport->city, ['class' => 'form-control', 'required']) }}
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">Country</label>
		<div class="col-md-10">
			{{ Form::select('country_id', $countries, $airport->country_id, ['class' => 'form-control', 'required']) }}
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">Latitude</label>
		<div class="col-md-10">
			{{ Form::text('lat', $airport->lat, ['class' => 'form-control', 'required']) }}
			<span class="help-block">Latitude must be presented in decimal format, prepend negative sign (-) for south.</span>
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">Longitude</label>
		<div class="col-md-10">
			{{ Form::text('lon', $airport->lon, ['class' => 'form-control', 'required']) }}
			<span class="help-block">Longitude must be presented in decimal format, prepend negative sign (-) for west.</span>
		</div>
	</div>
	<div class="form-group">
		<label class="col-md-2 control-label">Elevation</label>
		<div class="col-md-10">
			{{ Form::text('elevation', $airport->elevation, ['class' => 'form-control', 'required']) }}
			<span class="help-block">Elevation must be in feet.</span>
		</div>
	</div>
</div>
<div class="modal-footer">
	<a class="btn" data-dismiss="modal" aria-hidden="true" href="#">Cancel</a>
	<input class="btn btn-success" value="Save" type="submit" />
</div>
{{ Form::close() }}