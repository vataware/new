<div class="searchFieldContainer" id="search">
	<div class="container">
		{{ Form::open(array('route' => 'search', 'role' => 'form', 'method' => 'GET'))}}
		<div class="col-md-10">
			<input type="text" autocomplete="off" name="q" placeholder="Search for pilots, flights, citypairs, airports, and more..." value="{{ Input::get('q') }}" class="homeSearchBox searchField">
		</div>
		<div class="col-md-2">
			<button type="submit" class="btn btn-vataware btn-block" style="margin-top: 21px; padding: 15px;">Search</button>
		</div>
		{{ Form::close() }}
	</div>
	<div class="searchTips hidden">
		<table class="table table-condensed table-striped">
			<tr>
				<th>Pilot</th>
				<td>1234567<br />John Smith</td>
			</tr>
			<tr>
				<th>Citypair</th>
				<td>EHAM - KDTW<br />VHHH to RCTP</td>
			</tr>
			<tr>
				<th>Flights</th>
				<td>DAL188<br />VH-OEQ</td>
			</tr>
			{{--<tr>
				<th>ATC</th>
				<td>EHAM_TWR</td>
			</tr>--}}
			<tr>
				<th>Airport</th>
				<td>TNCM</td>
			</tr>
			<tr>
				<th>Airline</th>
				<td>CPA</td>
			</tr>
		</table>
	</div>
</div>