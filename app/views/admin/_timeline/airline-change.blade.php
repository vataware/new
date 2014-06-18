<li>
	<i class="fa fa-plane bg-blue"></i>
	<div class="timeline-item">
		<span class="time"><i class="fa fa-clock-o"></i> {{ $timeline->created_at->format('H:i') }}</span>
		<h3 class="timeline-header"><a href="#">{{ !is_null($timeline->user) ? $timeline->user->name : User::find($timeline->user_id)->name }}</a> updated {{ $timeline->activity->airline }}</h3>
		<div class="timeline-body">
			<ul>
				@foreach($timeline->activity->fields as $field => $values)
				<li><strong>{{ Lang::get('timeline.airline.' . $field) }}</strong>: <em>{{ $values[0] }}</em> to <em>{{ $values[1] }}</em></li>
				@endforeach
			</ul>
		</div>
	</div>
</li>