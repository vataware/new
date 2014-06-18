@section('breadcrumb')
<li><a href="{{ URL::route('admin.airport.index') }}">Airports</a></li>
<li class="active">{{ $airport->icao }}</li>
@stop
@section('content')
<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title">{{ $airport->icao }} - {{ $airport->name }}</h3>
	</div><!-- /.box-header -->
	<!-- form start -->
	@if($hasChange)
	{{ Form::open(['url' => URL::route('admin.airport.change', $airport->icao), 'method' => 'put', 'role' => 'form']) }}
		<div class="box-body">
			@foreach($changes as $key => $values)
				@if(count($values['Requests']) > 0)
				<div class="form-group">
					{{ Form::label($key, $columns[$key], ['class' => 'control-label']) }}
					{{ Form::select($key, $values, -1, ['class' => 'form-control']) }}
				</div>
				@endif
			@endforeach
		</div><!-- /.box-body -->
		<div class="box-footer">
			{{ Form::submit('Save changes', ['class' => 'btn btn-primary']) }}
		</div>
	{{ Form::close() }}
	@else
	No are no change requests for this airport.
	@endif
</div><!-- /.box -->

@stop