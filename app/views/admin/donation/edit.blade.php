<div class="modal-dialog">
	<div class="modal-content">
		{{ Form::model($donation, ['url' => URL::route('admin.donation.update', $donation->id), 'class' => 'form-horizontal', 'method' => 'PUT']) }}
		<div class="modal-header">
			<a type="button" class="close" data-dismiss="modal" aria-hidden="true" href="#">×</a>
			<h4 class="modal-title">Edit donation</h4>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-12">	
					<div class="form-group">
						<label class="col-md-2 control-label" for="name">Name</label>
						<div class="col-md-10">
							{{ Form::text('name', null, ['placeholder' => 'eg. Joe Patroni','class' => 'form-control', 'required']) }}
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">	
					<div class="form-group">
						<label class="col-md-2 control-label" for="vatsim_id">VATSIM ID</label>
						<div class="col-md-10">
							{{ Form::text('vatsim_id', null, ['placeholder' => 'eg. 1169898 (optional)','class' => 'form-control']) }}
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">	
					<div class="form-group">
						<label class="col-md-2 control-label" for="amount">Amount (USD)</label>
						<div class="col-md-10">
							{{ Form::text('amount', null, ['placeholder' => 'eg. 20','class' => 'form-control', 'required']) }}
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a class="btn" data-dismiss="modal" aria-hidden="true" href="#">Cancel</a>
			<input class="btn btn-success" value="Save" type="submit" />
		</div>
		{{ Form::close() }}
	</div>
</div>