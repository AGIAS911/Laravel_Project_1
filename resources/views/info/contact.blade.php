@extends('layouts.info')

@section('title', 'Contact us')

@section('content')
<div class="container"><section class="page-hero"><div class="crumbs"><a href="{{ url('/') }}">Home</a> / Contact us</div><h1>Contact us</h1><p class="lead mt-s">We welcome your questions and suggestions.</p></section></div>
<section class="section"><div class="container"><div class="grid g-main">
  <div class="panel">
    @if (session('success')) <p class="alert" role="status">{{ session('success') }}</p> @endif
    @if ($errors->any())
        <ul class="alert warn" role="alert">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    @endif
    <form method="POST" action="{{ route('info.contact.store') }}">
        @csrf
        <div class="form-row">
            <div class="field"><label for="contact-name">Name</label><input id="contact-name" name="name" value="{{ old('name') }}" maxlength="100" required></div>
            <div class="field"><label for="contact-email">Email address</label><input id="contact-email" type="email" name="email" value="{{ old('email') }}" maxlength="255" required></div>
        </div>
        <div class="field"><label for="contact-subject">Subject</label><input id="contact-subject" name="subject" value="{{ old('subject') }}" maxlength="100" required></div>
        <div class="field"><label for="contact-message">Message</label><textarea id="contact-message" name="message" maxlength="3000" required>{{ old('message') }}</textarea></div>
        <button class="btn btn-primary">Send message <span class="dot"></span></button>
    </form>
  </div>
  <div class="stack">
  <div class="kpi o"><div class="ic"><i class="fa-solid fa-location-dot"></i></div><div><b style="font-size:1rem">Amman, Jordan</b><span>Initial coverage</span></div></div>
  <div class="kpi g"><div class="ic"><i class="fa-solid fa-clock"></i></div><div><b style="font-size:1rem">Sat - Thu</b><span>Support hours</span></div></div></div></div></div></section>
@endsection
