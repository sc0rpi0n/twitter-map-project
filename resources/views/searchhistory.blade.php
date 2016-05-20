@extends('welcome')

@section('content')
<div class="row">
    <h4>Search History</h4>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="list-group">
        @foreach($searchHistory as $location => $count)
            <a class="list-group-item" href="">{{ $location }} <span class="badge">{{ $count }}</span></a>
        @endforeach
    </div>
	</div>
</div>    
<div class="row">
    <span class="input-group-btn">
        <a href="{{ action('IndexController@mapper') }}" class="btn btn-info"><- Back</a>
    </span>
</div>

@stop
