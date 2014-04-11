<div class="searchFieldContainer">
	<div class="container">
		{{ Form::open(array('route' => 'search', 'role' => 'form', 'method' => 'GET'))}}
		<div class="col-md-3" style="text-align: center;">
			<img src="{{ asset('assets/images/separator.png') }}" class="searchSeparator" />
			<div style="margin-top:10px;">
			<small>who are you</small><br />
			looking for?
			</div>

		</div>
		<div class="col-md-1" style="text-align:center; margin-top:10px;">
			find<br />them!
			</div>
		<div class="col-md-6">
			<input type="text" name="q" placeholder="enter name, ID or callsign..." value="{{ Input::get('q') }}" class="homeSearchBox">
		</div>
		<div class="col-md-1">
			<button type="submit" class="btn btn-primary" style="margin-top: 26px; ">Search</button>
		</div>
		{{ Form::close() }}
	</div>
</div>