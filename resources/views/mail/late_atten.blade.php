<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
    {{-- @php
        dd($data);
    @endphp --}}
    <h3>Hello Mr Luv Bhatia</h3>
    <p>Welcome to the Powertech family.</p>
    <p>Attach Document is data of Late Employee in time frame of {{$data == 1?"relaxed time":""}}{{$data == 2?"after relaxed time but less then 1.5 hour ":""}}{{$data == 3?"After 1.5 hour":""}}.</p>
	<p>Read the attached document carefully before signing it.</p> 
</body>
</html>
