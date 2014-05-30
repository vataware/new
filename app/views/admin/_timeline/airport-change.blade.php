<li>
	<i class="fa fa-building-o bg-green"></i>
	<div class="timeline-item">
		<span class="time"><i class="fa fa-clock-o"></i> {{ $timeline->created_at->format('H:i') }}</span>
		<h3 class="timeline-header"><a href="#">{{ $timeline->user->name }}</a> updated {{ $timeline->activity->airport }}</h3>
		<div class="timeline-body">
			<ul>
				@foreach($timeline->activity->fields as $field => $values)
				<li><strong>{{ $field }}</strong>: <em>{{ $values[0] }}</em> to <em>{{ $values[1] }}</em></li>
				@endforeach
			</ul>
		</div>
	</div>
</li>