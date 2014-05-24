<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>vataware</title>

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
	<link href="{{ asset('assets/stylesheets/map-rotate.css') }}" rel="stylesheet">
	@if(isset($stylesheets) && count($stylesheets) > 0)
	@foreach($stylesheets as $stylesheet)
	<link type="text/css" rel="stylesheet" href="{{ asset($stylesheet) }}" />
	@endforeach
	@endif
</head>
<body class="no-flightmap">
	<section class="wrapper">
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
						<li><a href="{{ URL::route('flight.index') }}">Flights</a></li>
						<li><a href="{{ URL::route('pilot.index') }}">Pilots</a></li>
						<li><a href="{{ URL::route('atc.index') }}">ATC</a></li>
						<li><a href="{{ URL::to('forums') }}">Forum</a></li>
						<li class="visible-xs"><a href="{{ URL::route('donations') }}">Donations</a></li>
						<li class="visible-xs"><a href="{{ URL::route('team') }}">Team</a></li>
						<li class="hidden-xs"><a class="nohover" href="{{ URL::route('donations') }}"><img style="margin-top: -7px;" height="30" src="{{ asset('assets/images/donate.png') }}" alt="Donate" /></a></li>
					</ul>
				</div><!-- /.navbar-collapse -->
			</div><!-- /.container-fluid -->
		</nav>
		@yield('content')
	</section>
	<footer class="footer">
		<div class="container">
			<div class="col-lg-12" style="margin-top:20px;">
				{{-- <div class="pull-right" style="font-size:29px; position:absolute; right:0;">
					<a href="#" style="color:white;"><i class="fa fa-facebook" style="margin-right: 10px;"></i></a><a href="#" style="color:white;"><i class="fa fa-twitter" style="margin-right: 10px;"></i></a><a href="#" style="color:white;"><i class="fa fa-rss" style="margin-right: 10px;"></i></a>
				</div> --}}
				<div class="hidden-xs"><a href="{{ URL::route('home') }}" class="footerActive">Home</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="{{ URL::route('pilot.index') }}">Pilots</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="{{ URL::route('atc.index') }}">ATC</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="{{ URL::to('forums') }}">Forum</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="{{ URL::route('team') }}">Team</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="{{ URL::route('donations') }}">Donate</a><br /><br /></div>
				&copy; 2014 <a href="{{ URL::route('home') }}" style="color:white; font-weight:bold;">vataware</a> All rights reserved.<br />
				<small>version 1.1-{{ $build }}</small>
			</div>
		</div>
	</footer>
	
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
</body>
</html>