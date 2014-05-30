<li>
	<a href="{{ URL::route('admin.index') }}">
		<i class="fa fa-dashboard"></i> <span>Dashboard</span>
	</a>
</li>
<li>
	<a href="{{ URL::route('admin.airline.index') }}">
		<i class="fa fa-plane"></i> <span>Airlines</span>
		@if($airlineRequestCount > 0)<small class="badge pull-right bg-yellow">{{ $airlineRequestCount }}</small> @endif
	</a>
</li>
<li>
	<a href="{{ URL::route('admin.airport.index') }}">
		<i class="fa fa-building-o"></i> <span>Airports</span>
		@if($airportRequestCount > 0)<small class="badge pull-right bg-yellow">{{ $airportRequestCount }}</small> @endif
	</a>
</li>
<li>
	<a href="{{ URL::route('admin.team.index') }}">
		<i class="fa fa-users"></i> <span>Team</span>
	</a>
</li>
<!-- <li class="treeview">
	<a href="#">
		<i class="fa fa-bug"></i> <span>Issues</span>
		<i class="fa fa-angle-left pull-right"></i>
	</a>
	<ul class="treeview-menu">
		<li><a href="pages/examples/invoice.html"><i class="fa fa-user"></i> Assigned to Me</a></li>
		<li><a href="pages/examples/login.html"><i class="fa fa-folder-open"></i> Projects</a></li>
		<li><a href="pages/examples/login.html"><i class="fa fa-eye"></i> Watched</a></li>
	</ul>
</li> -->
<li>
	<a href="{{ URL::route('admin.activity') }}">
		<i class="fa fa-clock-o"></i> <span>Activity</span>
	</a>
</li>
<li>
	<a href="{{ URL::route('home') }}">
		<i class="fa fa-reply"></i> <span>Return to Vataware</span>
	</a>
</li>