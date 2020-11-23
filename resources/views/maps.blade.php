@extends('layouts.main')

@section('title', 'Surf Captain Weather and Swell Maps')

@section('head')
    @parent
    <link href="{{ mix('/css/maps.css') }}" rel="stylesheet"/>
@endsection

@section('main-content')
    <div id="maps">
        <div id="maps-coming-soon">
            <img src="/images/maps-coming-soon.jpg" />
            <div id="coming-soon-overlay"></div>
            <h1>Wind, Swell, and Buoy Maps made awesome <br> Coming soon...</h1>
        </div>
    </div>
@endsection
