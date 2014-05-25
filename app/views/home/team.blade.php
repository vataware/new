@section('content')
<div class="container"><br /><br />
	<div class="col-lg-12" style="line-height: 30px;">
		<h2 style="text-align: center;">a few words about vataware...</h2>
		<p><strong>Since vataware was established in 2007, we have provided the VATSIM users with incredibly detailed real-time flight tracking and history as well as recently adding a Dispatch System and a Weather Centre.</strong></p>
		<p>On March 29<sup>th</sup>, 2014 it was announced that vataware would close after 7 years in operation. Tim, the owner decided that vataware should live on just not with him. The search for someone to take over vataware began, only it was seen best that the flight sim community should take on the project.</p>
		<p>The new vataware would require a new website, new systems and new features thus adding to the already amazing name that vataware has achieved over the years. We look forward to many more years providing you with the best possible VATSIM tracking and historical logbooks!</p>
	</div>
</div>
<div class="section" style="background-color: #f6f6f6;">
	<div class="container" style="text-align: center;">
		<div class="col-lg-12">
			<h2>vataware's chronology</h2><br /><br />
			<img src="{{ asset('assets/images/chronology.png') }}" style="width:100%; max-width:843px; margin-bottom: 50px;"/><br /><br />
		</div>
	</div>
</div>
<div class="container">
	<div class="col-lg-12" style="text-align: center;">
		<h2>vataware team</h2>
	</div>
	@foreach($members as $member)
	<div class="row">
		<div class="col-sm-2 profilePic" @if(File::exists(public_path() . '/assets/images/team/' . Str::slug($member->name) . '.jpg')) style="background-image: url({{ asset('assets/images/team/' . Str::slug($member->name) . '.jpg') }});" @endif></div>
		<div class="col-sm-3">
			<div class="teamTitle">
				<small>{{{ strtolower($member->job) }}}</small><br />
				<span>{{{ $member->name }}}</span>
			</div>
			<div class="mediaLinks">
				{{ $member->media }}
			</div>
		</div>
		<div class="col-sm-7 teamBlurb" style="margin-top: 15px;">
			{{ nl2br($member->description) }}
		</div>
	</div>
	@endforeach
	<div class="row">
		<div class="col-lg-12" style="text-align: center; margin-bottom:30px;">
			<h3 id="lowerHeading" style="margin-right: 50px; display:inline-block;">Interested in our opportunities?</h3>
			<a href="http://vataware.com/forums/" class="sendMessageButton">check out the forum!</a>
			</div>
	</div>
</div>
@stop
