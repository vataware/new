<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<title>{{ $title or 'vataware' }}</title>

	@if(isset($stylesheets) && count($stylesheets) > 0)
	@foreach($stylesheets as $stylesheet)
	<link type="text/css" rel="stylesheet" href="{{ asset($stylesheet) }}" />
	@endforeach
	@endif

	@if(isset($javascripts) && count($javascripts) > 0)
	@foreach($javascripts as $javascript)
	<script src="{{ asset($javascript) }}"></script>
	@endforeach
	@endif
</head>
<body>
	@yield('content')
</body>
</html>