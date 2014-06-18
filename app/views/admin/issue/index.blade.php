@section('content')

<div class="table-responsive">
	<table class="table table-condensed table-striped">
	@foreach($priorities as $issues)
		<tr>
			<td colspan="100%" style="background-color: {{ $colours[$issues[0]->priority_id]->statusColor }}; color: white; padding: 10px 10px; border-radius: 4px 4px 0 0;"><big><strong>{{ $colours[$issues[0]->priority_id]->name }}</strong></big></td>
		</tr>
		<tr>
			<th>Key</th>
			<th>Summary</th>
			<th>Status</th>
			<th>Assignee</th>
			<th>Project</th>
			<th>Due at</th>
			<th>Type</th>
		</tr>
		@foreach($issues as $issue)
			<tr>
				<td><img src="{{ $issue->status->icon }}" /> {{ $issue->key }}</td>
				<td>{{ $issue->summary }}</td>
				<td>{{ $issue->status->name }}</td>
				<td>{{ $issue->assignee ? $issue->assignee->displayName : 'Unassigned' }}</td>
				<td>{{ $issue->project->name }}</td>
				<td>{{ $issue->duedate ? $issue->duedate->format('F jS, Y') : '&mdash;' }}</td>
				<td><img src="{{ $issue->issuetype->icon }}" /> {{ $issue->issuetype->name }}</td>
			</tr>
			<tr>
				<td colspan="100%">
					<div class="progress progress-striped sm">
						<div class="progress-bar progress-progress-bar-aqua" style="width: {{ $issue->progress->percent }}%" role="progressbar" aria-valuenow="{{ $issue->progress->percent }}" aria-valuemin="0" aria-valuemax="100">
							<span class="sr-only">{{ $issue->progress->percent }}% Complete</span>
						</div>
					</div>
				</td>
			</tr>
		@endforeach
		<tr>
			<td colspan="100%"></td> 
		</tr>
		<tr>
			<td colspan="100%" style="border-top: 3px solid {{ $colours[$issues[0]->priority_id]->statusColor }};">&nbsp;</td> 
		</tr>
	@endforeach
	</table>
</div>
@stop