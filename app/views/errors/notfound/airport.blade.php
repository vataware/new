@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-6">
			<h1>Captain, we're lost.</h1>
			<p class="lead">The airport you're trying to find cannot be found, if you're sure this airport exists, you can add it using the form on the right hand side.</p>
		</div>
		<div class="col-md-6" style="border-left: 1px dotted #2c3e50;">
			<h2 class="section-header">Add airport</h2>
			@if(Auth::guest())
				<p class="lead">Please <a href="{{ URL::route('user.intend', ['vataware_callback' => URL::current()]) }}">login with your VATSIM ID</a> to add a new airport.</p>
			@else
				{{ Form::open(['url' => URL::route('airport.store'), 'class' => 'form-horizontal']) }}
					<div class="form-group">
						<div class="col-md-10 col-md-offset-2">
							<p class="form-control-static">All submissions will be checked manually to ensure accuracy.</p>
						</div>
					</div>
					@if($exists)
					<div class="form-group">
						<div class="col-md-10 col-md-offset-2">
							<p class="form-control-static text-success">We have received a submission for this airport and are in the process of checking it.</p>
						</div>
					</div>
					@endif
					<div class="form-group">
						<label class="col-md-2 control-label">ICAO</label>
						<div class="col-md-10">
							{{ Form::text('icao', $airport, ['class' => 'form-control', 'required']) }}
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">IATA</label>
						<div class="col-md-10">
							{{ Form::text('iata', null, ['class' => 'form-control', 'placeholder' => 'N/A']) }}
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Name</label>
						<div class="col-md-10">
							{{ Form::text('name', null, ['class' => 'form-control', 'required']) }}
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">City</label>
						<div class="col-md-10">
							{{ Form::text('city', null, ['class' => 'form-control', 'required']) }}
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Country</label>
						<div class="col-md-10">
							{{ Form::select('country_id', $countries, null, ['class' => 'form-control', 'required']) }}
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Latitude</label>
						<div class="col-md-10">
							{{ Form::text('lat', null, ['class' => 'form-control', 'required']) }}
							<span class="help-block">Latitude must be presented in decimal format, prepend negative sign (-) for south.</span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Longitude</label>
						<div class="col-md-10">
							{{ Form::text('lon', null, ['class' => 'form-control', 'required']) }}
							<span class="help-block">Longitude must be presented in decimal format, prepend negative sign (-) for west.</span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Elevation</label>
						<div class="col-md-10">
							{{ Form::text('elevation', null, ['class' => 'form-control', 'required']) }}
							<span class="help-block">Elevation must be in feet.</span>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-10 col-md-offset-2">
							{{ Form::submit('Save', ['class' => 'btn btn-success']) }}
						</div>
					</div>
				{{ Form::close() }}
			@endif
		</div>
	</div>
</div>
@stop