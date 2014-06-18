@section('breadcrumb')
<li><a href="{{ URL::route('admin.team.index') }}">Team</a></li>
<li><a href="{{ URL::route('admin.team.show', $user->id) }}">{{ $user->name }}</a></li>
<li class="active">Activity</li>
@stop
@section('content')
<div class="row">
	<div class="col-md-12">
		<ul class="timeline">
			@foreach($timelines as $date => $events)
			<li class="time-label">
				<span class="bg-black">
					{{ $date }}
				</span>
			</li>
				@foreach($events as $event)
				{{ $event->item }}
				@endforeach
			@endforeach
			<li>
				<i class="fa fa-clock-o"></i>
			</li>
		</ul>
	</div>
</div>
@stop