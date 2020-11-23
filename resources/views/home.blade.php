@extends('layouts.main')

@section('title', 'Surf Captain')

@section('head')
    @parent

    <meta property="og:url"                content="<?=(config('app.url'))?>" />
    <meta property="og:type"               content="website" />
    <meta property="og:title"              content="Surf Captain" />
    <meta property="og:description"        content="Surf Forecasts made Awesome" />
    <meta property="og:image"              content="<?=(config('app.url'))?>/images/logo/sc_logo_mark_blue_1060x1060.png" />
@endsection

@section('main-content')
    <sc-home
        :reload-fcst="reloadFcstData"
        blog-posts="{{{ $blogPosts ?? '' }}}"
        @togglelocationmenu="toggleLocationMenu()"
    ></sc-home>
@endsection
