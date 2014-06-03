@section('content')

<div class="row">
	<div class="col-md-6">
		<h2>Gateways</h2>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Name</th>
					<th></th>
				</tr>
				<tr class="success">
					<td colspan="100%" class="text-center"><a href="#">new gateway +</a></td>
				</tr>
			</thead>
			<tbody>
				@foreach($gateways as $gateway)
				<tr>
					<td>{{ $gateway->name }}</td>
					<td class="text-right"><a href="#" class="btn btn-warning btn-flat btn-xs">Edit</a> <a href="#" class="btn btn-danger btn-flat btn-xs">Delete</a></td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	<div class="col-md-6">
		<h2>Donations</h2>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Name</th>
					<th>Amount <small class="text-muted">USD</small></th>
					<th></th>
				</tr>
				<tr class="success">
					<td colspan="100%" class="text-center"><a href="#">new donation +</a></td>
				</tr>
			</thead>
			<tbody>
				@foreach($donations as $donation)
				<tr>
					<td>{{ $donation->name }} <small class="text-muted">{{ $donation->vatsim_id }}</small></td>
					<td>{{ $donation->amount }}</td>
					<td class="text-right"><a href="#" class="btn btn-warning btn-flat btn-xs">Edit</a> <a href="#" class="btn btn-danger btn-flat btn-xs">Delete</a></td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>

@stop