@section('content')

<div class="container">
	<div class="page-header"><h1>Donations</h1></div>
	<div class="row">
		<div class="col-md-6">
			<p>You may be wondering why we are asking for donations? vataware always has and always will be a free service, but we do have to cover our server costs. So any help towards those costs would be a huge help for us.</p>
			<h3>what does my donation cover?</h3>
			<p>vataware currently has three servers, one for the website, one for the database that holds over 7 years worth of data and a backup server should anything go wrong. As you can imagine, this isn't cheap but we feel this is essential for the running of vataware to keep it reliable and fast.</p>
			<h3>what do I get</h3>
			<ul>
				<li>Highlighted name on the forums</li>
				<li><strong>$10</strong> Place on this page</li>
				<li><strong>$20</strong> Shoutout on Facebook</li>
				<li><strong>$30</strong> Early access to our development site (coming soon)</li>
			</ul>
		</div>
		<div class="col-md-3">
			<div class="row providers">
				@foreach($gateways as $gateway)
				<div class="col-xs-12"><a href="{{ $gateway->link }}">{{ strtolower($gateway->name) }} {{ $gateway->note ? '<small>' . strtolower($gateway->note) . '</small>' : '' }}</a></div>
				@endforeach
			</div>
		</div>
		<div class="col-md-3">
		<p>We would like to say thank you to the following people for their donations:</p>
		<ul class="list-group donations">
			@foreach($donations as $donation)
			<li class="list-group-item">{{ $donation }}</li>
			@endforeach
		</ul>
		</div>
	</div>
</div>
@stop