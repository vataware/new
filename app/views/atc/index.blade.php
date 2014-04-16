@section('content')

<div class="container">
	<div class="page-header"><h1>ATC <small class="pull-right" style="margin-top: 10px;"><span class="hidden-inline-xs hidden-inline-sm">Showing controllers </span>{{ $atc->getFrom() }} - {{ $atc->getTo() }} of {{ $atc->getTotal() }}</small></h1></div>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>Callsign</th>
				<th>Controller</th>
				<th>Facility</th>
				<th>Location</th>
			</tr>
		</thead>
		<tbody class="rowlink" data-link="row">
		@foreach($atc as $controller)
			<tr>
				<td><a href="{{ URL::route('atc.show', $controller->id) }}">{{ $controller->callsign }}</a><br /><small>Freq: {{ $controller->frequency }}</small></td>
				<td>{{ $controller->pilot->name }}<br /><small><strong>{{ $controller->rating }}</strong> / VATSIM ID: {{ $controller->vatsim_id }}</small></td>
				<td>{{ $controller->facility }}</td>
				<td>
					@if(!is_null($controller->airport))
					<img src="{{ flag($controller->airport->country_id) }}">&nbsp;{{ $controller->airport->id }} - {{ $controller->airport->name }}<br />
					<small>{{ $controller->airport->city ? $controller->airport->city . ', ' : '' }}{{ $controller->airport->country->country }}</small>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	<div class="text-center">{{ $atc->links() }}</div>
</div>
@stop