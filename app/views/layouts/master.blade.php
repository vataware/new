<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $title or 'The Pinnacle of VATSIM Statistics' }} | vataware</title>

	<!-- Bootstrap -->
	<link href="{{ asset('assets/stylesheets/bootstrap.min.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/stylesheets/jasny-bootstrap.min.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/stylesheets/bootstrap.mod.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/stylesheets/vataware.css') }}" rel="stylesheet">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800|Lato:400,700,400italic" rel="stylesheet" type="text/css">
	@if(isset($stylesheets) && count($stylesheets) > 0)
	@foreach($stylesheets as $stylesheet)
	<link type="text/css" rel="stylesheet" href="{{ asset($stylesheet) }}" />
	@endforeach
	@endif
</head>
<body>
	<div class="vataware-map-container">
		<div id="flightRadar" class="vataware-map"></div>
		<div class="vataware-map-stats">PILOTS ONLINE: <span style="color:#138995;">{{ $statsPilots }}</span>&nbsp; &nbsp; ATC ONLINE: <span style="color:#138995;">{{ $statsAtc }}</span></div>
	</div>
	<section class="wrapper">
		{{-- Temporarily hide leader navigation --}}
		@if(false)
		<div class="navbar navbar-default navbar-leader">
			<div class="container">
				<div class="navbar-header visible-xs">
					<a href="#" class="navbar-brand"><i class="fa fa-user"></i> Pilot Login</a> | <a href="#" class="navbar-brand"><i class="fa fa-users"></i> Airline Login</a> <a href="#" class="navbar-brand"><i class="fa fa-file-o"></i> Register</a>
				</div>
				<div class="navbar-collapse collapse" id="navbar-main">

					<ul class="nav navbar-nav">
						<li>
							<a href="#"><i class="fa fa-user"></i> Pilot Login</a>
						</li>
						<li>
							<a href="#"><i class="fa fa-users"></i> Airline Login</a>
						</li>
						<li>
							<a href="#"><i class="fa fa-file-o"></i> Not a member? <span style="color:#18bc9c;">Register</span></a>
						</li>

					</ul>

					<ul class="nav navbar-nav navbar-right">
						<li><a href="http://builtwithbootstrap.com/">Hello, <span style="color:#18bc9c;">Liam!</span></a></li>
						<li><a href="https://www.facebook.com/vataware" target="_blank" style="font-size:24px;"><i class="fa fa-facebook"></i></a></li>
						<li><a href="https://www.twitter.com/vataware" target="_blank" style="font-size:24px;"><i class="fa fa-twitter"></i></a></li>
					</ul>

				</div>
			</div>
		</div>
		@endif
		<div class="navbar navbar-default navbar-leader">
			<div class="container">
				<div style="color: white; text-align: center; margin-top: 10px;"><strong>Did you know?</strong> You can scroll up for the live map</div>
			</div>
		</div>
		<nav class="navbar navbar-vataware" role="navigation">
			<div class="container">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#vataware-navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="{{ URL::route('home') }}">vataware</a>
				</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="vataware-navbar-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li><a href="{{ URL::route('home') }}" class="active">Home</a></li>
						@if(false)
						<li><a href="#">News</a></li>
						<li class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#" id="themes"><i class="fa fa-angle-down" style="color:#18bc9c;"></i> Services</a>
							<ul class="dropdown-menu" aria-labelledby="themes">
								<li><a href="#"><i class="fa fa-angle-right"></i>&nbsp; Route Finder</a></li>
								<li><a href="#"><i class="fa fa-angle-right"></i>&nbsp; Weather Centre</a></li>
								<li><a href="#"><i class="fa fa-angle-right"></i>&nbsp; Resources</a></li>
							</ul>
						</li>
						@endif
						<li><a href="{{ URL::route('flight.index') }}">Flights</a></li>
						<li><a href="{{ URL::route('pilot.index') }}">Pilots</a></li>
						<li><a href="{{ URL::route('atc.index') }}">ATC</a></li>
						<li><a href="{{ URL::to('forums') }}">Forum</a></li>
						<li><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="8F5ETJJRV7HHL">
							<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
						</form></li>
					</ul>
				</div><!-- /.navbar-collapse -->
			</div><!-- /.container-fluid -->
		</nav>
		@include('search.bar')
		<div class="container">
			{{ Messages::get() }}
		</div>
		@yield('content')
	</section>
	<footer class="footer">
		<div class="container">
			<div class="col-lg-12" style="margin-top:20px;">
				<div class="pull-right" style="font-size:29px; position:absolute; right:0;">
					<a href="#" style="color:white;"><i class="fa fa-facebook" style="margin-right: 10px;"></i></a><a href="#" style="color:white;"><i class="fa fa-twitter" style="margin-right: 10px;"></i></a><a href="#" style="color:white;"><i class="fa fa-rss" style="margin-right: 10px;"></i></a>
				</div>
				<a href="{{ URL::route('home') }}" class="footerActive">Home</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="{{ URL::route('pilot.index') }}">Pilots</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="{{ URL::route('atc.index') }}">ATC</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="{{ URL::to('forums') }}">Forum</a><br />
				<br />&copy; 2014 <a href="{{ URL::route('home') }}" style="color:white; font-weight:bold;">vataware</a> All rights reserved.<br />
				<small>version 1.0-{{ $build }}</small>
			</div>
		</div>
	</footer>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="{{ asset('assets/javascript/bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/javascript/jasny-bootstrap.min.js') }}"></script>
	<script src="{{ asset('https://maps.googleapis.com/maps/api/js?sensor=true') }}"></script>
	<script src="{{ asset('assets/javascript/vataware.js') }}"></script>
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-49973764-1', 'vataware.com');
	  ga('require', 'linkid', 'linkid.js');
	  ga('require', 'displayfeatures');
	  ga('send', 'pageview');

	</script>
	@if(isset($javascripts) && count($javascripts) > 0)
	@foreach($javascripts as $javascript)
	<script src="{{ asset($javascript) }}"></script>
	@endforeach
	@endif
	<script type="text/javascript">
		function globalMap() {
			var map = new google.maps.Map(document.getElementById("flightRadar"), { styles: googleMapStyles, zoom: {{ Session::has('map.zoom') ? Session::get('map.zoom') : 2 }}, center: new google.maps.LatLng({{ Session::has('map.coordinates') ? Session::get('map.coordinates') : '30, 0' }}), streetViewControl: false, minZoom: 2, maxZoom: 14 });

			var flights = [];
			var polylines = [];

			updateMap = function(firstload) {
				if(typeof firstload == 'undefined') firstload = 0;
				if(!isVisible($('.vataware-map-container')) && firstload == 0) return;
				$.get('{{ URL::route('map.api') }}', {z: map.getZoom(), lat: map.getCenter().k, lon: map.getCenter().A, force: firstload}, function(data) {
					for(i = 0; i < data.length; i++) {
						var flight = data[i];
						if(!(flight.id in flights)) {
							var marker = new google.maps.Marker({ position: new google.maps.LatLng(flight.lat, flight.lon), map: map, icon: {
									url: '{{ asset('assets/images/mapicon-red.png') }}?deg=' + flight.heading,
									anchor: new google.maps.Point(10,10),
									size: new google.maps.Size(20,20),
									origin: new google.maps.Point(0,0)
								},
								optimized: false,
								flightId: flight.id,
								heading: flight.heading,
							});

							google.maps.event.addListener(marker, 'click', function() {
								for(i=0; i < polylines.length; i++) {
									polylines[i].setMap(null);
								}
								$.get('{{ URL::route('map.flight') }}', {id: this.flightId}, function(data) {
									for (i = 0; i < data.coordinates.length - 1; i++) {
										var flightPath = new google.maps.Polyline({
											path: [new google.maps.LatLng(data.coordinates[i][0], data.coordinates[i][1]), new google.maps.LatLng(data.coordinates[i+1][0], data.coordinates[i+1][1])],
											strokeColor: data.colours[i],
											strokeOpacity: 1.0,
											strokeWeight: 3,
											map: map
										});

										polylines.push(flightPath);
									}
								});
								
						 	});

							flights[flight.id] = marker;
						} else {
							flights[flight.id].setPosition(new google.maps.LatLng(flight.lat, flight.lon));
						}
					}
				});
			};

			google.maps.event.addListener(map, 'idle', function() {
				bounds = map.getBounds();
				updateMap(1);
			});

			setInterval(updateMap, 60000);

			google.maps.event.addDomListener(window, 'scroll', function() {
				if($(window).scrollTop() <= 0) {
					map.setOptions({ scrollwheel: true });
				} else {
					map.setOptions({ scrollwheel: false });
					$('body').css('overflow','scroll');
				}
			});
		}

		google.maps.event.addDomListener(window, 'load', globalMap);
	</script>
	@yield('javascript')
</body>
</html>