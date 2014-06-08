<div class="modal-dialog">
	<div class="modal-content">
		{{ Form::model($gateway, ['url' => URL::route('admin.donation.gateway.update', $gateway->id), 'class' => 'form-horizontal', 'method' => 'PUT']) }}
		<div class="modal-header">
			<a type="button" class="close" data-dismiss="modal" aria-hidden="true" href="#">×</a>
			<h4 class="modal-title">Edit gateway</h4>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-12">	
					<div class="form-group">
						<label class="col-md-2 control-label" for="name">Name</label>
						<div class="col-md-10">
							{{ Form::text('name', null, ['placeholder' => 'eg. Paypal','class' => 'form-control', 'required']) }}
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">	
					<div class="form-group">
						<label class="col-md-2 control-label" for="vatsim">Note</label>
						<div class="col-md-10">
							{{ Form::text('note', null, ['placeholder' => 'eg. monthly (optional)','class' => 'form-control']) }}
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">	
					<div class="form-group">
						<label class="col-md-2 control-label" for="amount">Link</label>
						<div class="col-md-10">
							{{ Form::text('link', null, ['placeholder' => 'include http://', 'class' => 'form-control', 'required']) }}
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