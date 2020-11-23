@extends('layouts.main')

@section('title', 'Surf Captain FAQs')

@section('head')
    @parent
    <link href="{{ mix('/css/faq.css') }}" rel="stylesheet"/>
@endsection

@section('main-content')
    <div id="faq">
        <div id="faq-header">
            <div id="faq-header-img">
                <img src="/images/surfer-cliffs-looking-medium.jpg" />
                <div id="faq-header-img-overlay"></div>
            </div>
            <h1>Frequently Asked Questions</h1>
        </div>
        <div id="faq-faqs">
            <ul>
            @foreach ($faqs as $faq)
                <li>
                    <h3 id="{{ $faq->slug }}">{{ $faq->question }}:</h3>
                    <p>{!! $faq->answer !!}</p>
                </li>
            @endforeach
            </ul>
        </div>
        <section id="faq-contact">
            <div class="faq-contact-content">
                <div class="faq-contact-label">Have an unanswered question?</div>
                <h1>We'd love to hear from you!</h1>
                <sc-contact-form></sc-contact-form>
            </div>
        </section>
    </div>
@endsection
