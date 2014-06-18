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