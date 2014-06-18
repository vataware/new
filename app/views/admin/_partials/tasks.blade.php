@if($tasks)
<li class="dropdown tasks-menu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		<i class="fa fa-tasks"></i>
		<span class="label label-danger">{{ $tasks->count() }}</span>
	</a>
	<ul class="dropdown-menu">
		<li class="header">You have {{ $tasks->count() }} tasks</li>
		<li>
			<!-- inner menu: contains the actual data -->
			<ul class="menu">
				@foreach($tasks as $task)
				<li><!-- Task item -->
					<a href="#">
						<h3>
							<img src="{{ $task->priority->iconUrl }}" /> {{ $task->summary }}
							<small class="pull-right">{{ $task->progress->percent }}%</small>
						</h3>
						<div class="progress xs">
							<div class="progress-bar progress-bar-aqua" style="width: {{ $task->progress->percent }}%" role="progressbar" aria-valuenow="{{ $task->progress->percent }}" aria-valuemin="0" aria-valuemax="100">
								<span class="sr-only">{{ $task->progress->percent }}% Complete</span>
							</div>
						</div>
					</a>
				</li><!-- end task item -->
				@endforeach
			</ul>
		</li>
		<li class="footer">
			<a href="{{ URL::route('admin.issues.me') }}">View all tasks</a>
		</li>
	</ul>
</li>
@endif