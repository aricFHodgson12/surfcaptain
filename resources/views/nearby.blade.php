@extends('layouts.main')

@section('title', $localName.'Surf Forecast Locations - Surf Captain')

@section('head')
    @parent
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css"
          integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
          crossorigin=""/>
@endsection

@section('main-content')
    <sc-nearby
        :reloaddata="reloadFcstData">
    </sc-nearby>
@endsection
