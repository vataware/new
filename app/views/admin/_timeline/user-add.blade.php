<li>
	<i class="fa fa-user bg-maroon"></i>
	<div class="timeline-item">
		<span class="time"><i class="fa fa-clock-o"></i> {{ $timeline->created_at->format('H:i') }}</span>
		<h3 class="timeline-header"><a href="#">{{ !is_null($timeline->user) ? $timeline->user->name : User::find($timeline->user_id)->name }}</a> added {{ $timeline->activity->user }} as a team member</h3>
		<div class="timeline-body">
			<ul>
				@foreach($timeline->activity->fields as $field => $value)
				<li><strong>{{ Lang::get('timeline.user.' . $field) }}</strong>: <em>{{ $value }}</em></em></li>
				@endforeach
			</ul>
		</div>
	</div>
</li>