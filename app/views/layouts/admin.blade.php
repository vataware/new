<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>{{ isset($title) ? $title . ' | ' : '' }}Vataware Cockpit</title>
		<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
		<link href="{{ asset('assets/admin/stylesheets/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/admin/stylesheets/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/admin/stylesheets/ionicons.min.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/admin/stylesheets/AdminLTE.css') }}" rel="stylesheet" type="text/css" />
		@if(isset($stylesheets) && count($stylesheets) > 0)
		@foreach($stylesheets as $stylesheet)
		<link type="text/css" rel="stylesheet" href="{{ asset($stylesheet) }}" />
		@endforeach
		@endif
	
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
	</head>
	<body class="skin-blue">
		<!-- header logo -->
		<header class="header">
			<a href="{{ URL::route('admin.index') }}" class="logo">
				Vataware
			</a>
			<!-- Header Navbar -->
			<nav class="navbar navbar-static-top" role="navigation">
				<!-- Sidebar toggle button-->
				<a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<div class="navbar-right">
					<ul class="nav navbar-nav">
						<!-- Messages -->
						
						<!-- Notifications -->
						
						<!-- Tasks -->
						@include('admin._partials.tasks')
						<!-- User Account -->
						<li class="dropdown user user-menu">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<i class="glyphicon glyphicon-user"></i>
								<span>{{ $user['name'] }} <i class="caret"></i></span>
							</a>
							<ul class="dropdown-menu">
								<!-- User image -->
								<li class="user-header bg-light-blue">
									@if($user['photo'])
									<img src="{{ $user['photo'] }}" class="img-circle" alt="User Image" />
									@endif
									<p>
										{{ $user['name'] }}
										<small>{{ $user['job'] }}</small>
									</p>
								</li>
								<!-- Menu Body -->
								<!-- <li class="user-body">
									<div class="col-xs-4 text-center">
										<a href="#">Followers</a>
									</div>
									<div class="col-xs-4 text-center">
										<a href="#">Sales</a>
									</div>
									<div class="col-xs-4 text-center">
										<a href="#">Friends</a>
									</div>
								</li> -->
								<!-- Menu Footer-->
								<li class="user-footer">
									<!-- <div class="pull-left">
										<a href="#" class="btn btn-default btn-flat">Profile</a>
									</div> -->
									<div class="pull-right">
										<a href="{{ URL::route('user.logout') }}" class="btn btn-default btn-flat">Sign out</a>
									</div>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</nav>
		</header>
		<div class="wrapper row-offcanvas row-offcanvas-left">
			<!-- Left side column. contains the logo and sidebar -->
			<aside class="left-side sidebar-offcanvas">                
				<!-- sidebar: style can be found in sidebar.less -->
				<section class="sidebar">
					<!-- Sidebar user panel -->
					<div class="user-panel">
						@if($user['photo'])
						<div class="pull-left image">
							<img src="{{ $user['photo'] }}" class="img-circle" alt="User Image" />
						</div>
						@endif
						<div class="pull-left info">
							<p>Hello, {{ $user['firstname'] }}</p>

							<!-- <a href="#"><i class="fa fa-circle text-success"></i> Online</a> -->
						</div>
					</div>
					<!-- search form -->
					<!-- <form action="#" method="get" class="sidebar-form">
						<div class="input-group">
							<input type="text" name="q" class="form-control" placeholder="Search..."/>
							<span class="input-group-btn">
								<button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
							</span>
						</div>
					</form> -->
					<!-- /.search form -->
					<!-- sidebar menu: : style can be found in sidebar.less -->
					<ul class="sidebar-menu">
						@include('admin._partials.sidebar')
					</ul>
				</section>
				<!-- /.sidebar -->
			</aside>

			<!-- Right side column. Contains the navbar and content of the page -->
			<aside class="right-side">                
				<!-- Content Header (Page header) -->
				<section class="content-header">
					@if(isset($title))<h1>{{ $title }}
						@if(isset($subtitle))<small>{{ $subtitle }}</small> @endif
					</h1> @endif
					<ol class="breadcrumb">
						<li><a href="#"><i class="fa fa-dashboard"></i> Cockpit</a></li>
						@yield('breadcrumb')
					</ol>
				</section>

				<!-- Main content -->
				<section class="content">
				@yield('content')
				</section><!-- /.content -->
			</aside><!-- /.right-side -->
		</div><!-- ./wrapper -->
		<div class="modal fade" id="modal-confirm">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title confirm-title">Action confirmation</h4>
					</div>
					<div class="modal-body" style="padding-bottom: 0">
						<p class="confirm-message"></p>
					</div>
					<div class="modal-footer">
						<a class="btn" data-dismiss="modal" aria-hidden="true" href="#">Cancel</a>
						<a href="#" class="btn btn-primary confirm-button">Confirm</a>
					</div>
				</div>
			</div>
		</div>

		<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
		<script src="{{ asset('assets/admin/javascript/bootstrap.min.js') }}" type="text/javascript"></script>
		<script src="{{ asset('assets/admin/javascript/AdminLTE/app.js') }}" type="text/javascript"></script>
		<script src="{{ asset('assets/admin/javascript/vataware.js') }}" type="text/javascript"></script>
		@if(isset($javascripts) && count($javascripts) > 0)
		@foreach($javascripts as $javascript)
		<script src="{{ asset($javascript) }}"></script>
		@endforeach
		@endif
		<script type="text/javascript">
		$("[rel='tooltip']").tooltip();
		</script>
		@yield('javascript')
	</body>
</html>