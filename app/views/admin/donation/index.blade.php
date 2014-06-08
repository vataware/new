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
					<td colspan="100%" class="text-center"><a href="#" data-toggle="modal" data-target="#modal" data-remote="{{ URL::route('admin.donation.gateway.create') }}">new gateway +</a></td>
				</tr>
			</thead>
			<tbody>
				@foreach($gateways as $gateway)
				<tr>
					<td>{{ $gateway->name }}</td>
					<td class="text-right"><a href="#" data-toggle="modal" data-target="#modal" data-remote="{{ URL::route('admin.donation.gateway.edit', $gateway->id) }}" class="btn btn-warning btn-flat btn-xs">Edit</a> <a href="{{ URL::route('admin.donation.gateway.destroy', $gateway->id) }}" class="btn btn-xs btn-flat btn-confirm btn-danger" data-title="Delete Gateway" data-message="Are you sure you want to delete gateway <strong>{{ $gateway->name }}</strong>?<br />This action cannot be undone." data-type="danger" data-confirm="Delete" data-method="DELETE">Delete</a></td>
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
					<td colspan="100%" class="text-center"><a href="#" data-toggle="modal" data-target="#modal" data-remote="{{ URL::route('admin.donation.create') }}">new donation +</a></td>
				</tr>
			</thead>
			<tbody>
				@foreach($donations as $donation)
				<tr>
					<td>{{ $donation->name }} <small class="text-muted">{{ $donation->vatsim_id }}</small></td>
					<td>{{ $donation->amount }}</td>
					<td class="text-right"><a href="#" data-toggle="modal" data-target="#modal" data-remote="{{ URL::route('admin.donation.edit', $donation->id) }}" class="btn btn-warning btn-flat btn-xs">Edit</a> <a href="{{ URL::route('admin.donation.destroy', $donation->id) }}" class="btn btn-xs btn-flat btn-confirm btn-danger" data-title="Delete Donation" data-message="Are you sure you want to delete the donation by <strong>{{ $donation->name }}</strong>?<br />This action cannot be undone." data-type="danger" data-confirm="Delete" data-method="DELETE">Delete</a></td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
<div class="modal fade" id="modal" data-backdrop="static" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
		</div>
	</div>
</div>
@stop