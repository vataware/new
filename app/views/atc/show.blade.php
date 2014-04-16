@section('content')

<div class="container">
	<div class="page-header"><h1>{{ $controller->callsign }}</h1></div>

	<div class="atc-progress hidden-xs hidden-sm">
		<ul>
			<li class="{{ $controller->facility_id == 0 ? 'controlled' : '' }}">Observer</li>
		</ul>
		<ul>
			<li class="{{ $controller->facility_id == 1 ? 'controlled' : '' }}">FSS</li>
		</ul>
		<ul>
			<li class="{{ in_array(2, $otherControllers) ? 'controlled' : '' }}">Clearance</li>
			<li class="{{ in_array(3, $otherControllers) ? 'controlled' : '' }}">Ground</li>
			<li class="{{ in_array(4, $otherControllers) ? 'controlled' : '' }}">Tower</li>
			<li class="{{ in_array(5, $otherControllers) ? 'controlled' : '' }}">Approach/Departure</li>
		</ul>
		<ul>
			<li class="{{ $controller->facility_id == 6 ? 'controlled' : '' }}">Center</li>
		</ul>
	</div>
	<hr />
	<div class="row">
		<div class="col-md-5">
			<div class="row">
				<div class="col-sm-6 col-md-12">
					<table class="table table-striped">
						<tr>
							<th>Status</th>
							<td><img src="{{ asset('assets/images/flightstates/' . (is_null($controller->end) ? 'departing' : 'arrived') . '.png') }}"> {{ (is_null($controller->end)) ? 'On Duty' : 'Finished Duty' }}</td>
						</tr>
						<tr>
							<th>Controller</th>
							<td><a href="{{ URL::route('controller.show', $controller->vatsim_id) }}">{{ $controller->pilot->name }} ({{ $controller->vatsim_id }})</a></td>
						</tr>
						<tr>
							<th>Rating</th>
							<td>{{ $controller->rating }}</td>
						</tr>
						<tr>
							<th>Frequency</th>
							<td>{{ $controller->frequency }}</td>
						</tr>
						<tr>
							<th>On Duty Since</th>
							<td>{{ $controller->start->format('j M Y - H:i') }}</td>
						</tr>
						<tr>
							<th>Time Controlled</th>
							<td>{{ $controller->duration }}</td>
						</tr>
						@if(!is_null($controller->end))
						<tr>
							<th>Finished Duty</th>
							<td>{{ $controller->end->format('j M Y - H:i') }}</td>
						</tr>
						@endif
					</table>
				</div>
				<div class="col-sm-6 visible-xs visible-sm atc-progress-small">
					<span class="btn btn-primary btn-block {{ $controller->facility_id == 0 ? 'controlled' : 'hidden' }}">Observer</span>
					<span class="btn btn-primary btn-block {{ $controller->facility_id == 1 ? 'controlled' : 'hidden' }}">Flight Service Station</span>
					<div class="btn-group-vertical btn-block">
						<span class="btn btn-primary btn-block {{ in_array(2, $otherControllers) ? 'controlled' : 'hidden' }}">Clearance</span>
						<span class="btn btn-primary btn-block {{ in_array(3, $otherControllers) ? 'controlled' : 'hidden' }}">Ground</span>
						<span class="btn btn-primary btn-block {{ in_array(4, $otherControllers) ? 'controlled' : 'hidden' }}">Tower</span>
						<span class="btn btn-primary btn-block {{ in_array(5, $otherControllers) ? 'controlled' : 'hidden' }}">Approach/Departure</span>
					</div>
					<span class="btn btn-primary btn-block {{ $controller->facility_id == 6 ? 'controlled' : 'hidden' }}">Center</span>
				</div>
			</div>
		</div>
		<div clas="col-md-7">
			<div class="visible-xs visible-sm"><hr /></div>
			[Map here of coordinates with visual range circle]
		</div>
	</div>
</div>

@stop