<li>
	<i class="fa fa-usd bg-blue"></i>
	<div class="timeline-item">
		<span class="time"><i class="fa fa-clock-o"></i> {{ $timeline->created_at->format('H:i') }}</span>
		<h3 class="timeline-header"><a href="#">{{ !is_null($timeline->user) ? $timeline->user->name : User::find($timeline->user_id)->name }}</a> deleted donation gateway {{ $timeline->activity->name }}</h3>
	</div>
</li>