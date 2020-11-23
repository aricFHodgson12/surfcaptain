@extends('layouts.main')

@section('title')
    {!! $title !!} Surf Forecast - Surf Captain
@endsection

@section('head')
    @parent
    <?php //<link href="/css/forecast.css" rel="stylesheet"/> ?>

    <meta property="og:url"                content="<?=url()->current()?>" />
    <meta property="og:type"               content="website" />
    <meta property="og:title"              content="{!! $title !!} Surf Forecast" />
    <meta property="og:description"        content="Surf Forecasts made Awesome" />
    <meta property="og:image"              content="<?=(config('app.url'))?>/images/logo/sc_logo_mark_blue_1060x1060.png" />
@endsection

@section('end-body')
    @parent
    <?php //<script src="/js/home.min.js"></script> ?>
@endsection

@section('main-content')
    <sc-fcst
        fcstdata="{{{ $fcstData }}}"
        :reloaddata="reloadFcstData"
    ></sc-fcst>
@endsection
