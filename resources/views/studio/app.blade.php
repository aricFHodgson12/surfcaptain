@extends('layouts.main')

@section('head')
    @parent
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta property="og:image" content="">
    <meta property="og:title" content="">
    <meta property="og:description" content="">

    <meta name="twitter:image" content="">
    <meta name="twitter:title" content="">
    <meta name="twitter:description" content="">
    <meta name="twitter:card" content="summary">

    <link rel="canonical">

    <script src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.18.1/build/highlight.min.js"></script>
    <script src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.18.1/build/styles/github.min.css">
@endsection

@section('end-body')
    <script>
        window.Studio = @json($scripts);
    </script>
    @parent
    <sc-blog></sc-blog>
@endsection

@section('main-content')
    <div id="studio">
        <router-view></router-view>
    </div>
@endsection
