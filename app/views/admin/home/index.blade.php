@section('content')

<div class="row">
	<!-- <div class="col-lg-3 col-xs-6">
		<div class="small-box bg-aqua">
			<div class="inner">
				<h3>{{-- number_format($issueCount) --}}</h3>
				<p>Open Issues</p>
			</div>
			<div class="icon"><i class="ion ion-bug"></i></div>
			<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
		</div>
	</div> -->
	<div class="col-lg-3 col-xs-6">
		<div class="small-box bg-green">
			<div class="inner">
				<h3>{{ number_format($userCount) }}</h3>
				<p>User Registrations</p>
			</div>
			<div class="icon"><i class="ion ion-person-add"></i></div>
			<a href="#" class="small-box-footer">&nbsp;<!-- More info <i class="fa fa-arrow-circle-right"></i> --></a>
		</div>
	</div>
	<div class="col-lg-3 col-xs-6">
		<div class="small-box bg-yellow">
			<div class="inner">
				<h3>{{ number_format($editRequest) }}</h3>
				<p>Data Edit Requests</p>
			</div>
			<div class="icon"><i class="ion ion-edit"></i></div>
			<a href="#" class="small-box-footer">&nbsp;<!-- More info <i class="fa fa-arrow-circle-right"></i> --></a>
		</div>
	</div>
</div>

@stop