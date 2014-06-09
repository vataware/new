@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-6">
			<h1>Captain, we're lost.</h1>
			<p class="lead">The airline you're trying to find cannot be found, if you're sure this airline exists, you can add it using the form on the right hand side.</p>
		</div>
		<div class="col-md-6" style="border-left: 1px dotted #2c3e50;">
			<h2 class="section-header">Add airline</h2>
			@if(Auth::guest())
				<p class="lead">Please <a href="{{ URL::route('user.intend', ['vataware_callback' => URL::current()]) }}">login with your VATSIM ID</a> to add a new airline.</p>
			@else
				{{ Form::open(['url' => URL::route('airline.store'), 'class' => 'form-horizontal']) }}
					<div class="form-group">
						<div class="col-md-10 col-md-offset-2">
							<p class="form-control-static">All submissions will be checked manually to ensure accuracy.</p>
						</div>
					</div>
					@if($exists)
					<div class="form-group">
						<div class="col-md-10 col-md-offset-2">
							<p class="form-control-static text-success">We have received a submission for this airline and are in the process of checking it.</p>
						</div>
					</div>
					@endif
					<div class="form-group">
						<label class="col-md-2 control-label">ICAO</label>
						<div class="col-md-10">
							{{ Form::text('icao', $airline, ['class' => 'form-control', 'required']) }}
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Name</label>
						<div class="col-md-10">
							{{ Form::text('name', null, ['class' => 'form-control', 'required']) }}
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Callsign</label>
						<div class="col-md-10">
							{{ Form::text('radio', null, ['class' => 'form-control']) }}
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Website</label>
						<div class="col-md-10">
							{{ Form::text('website', null, ['class' => 'form-control']) }}
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