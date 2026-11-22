@extends('layouts.info')

@section('title', 'Contact us')

@section('content')
<div class="container"><section class="page-hero"><div class="crumbs"><a href="{{ url('/') }}">Home</a> / Contact us</div><h1>Contact us</h1><p class="lead mt-s">Ask about a booking, payment or driver application.</p></section></div>
<section class="section"><div class="container"><div class="product-guide">
  <section class="panel product-contact" aria-label="Send a message">
    @if (session('success')) <p class="alert" role="status">{{ session('success') }}</p> @endif
    @if ($errors->any())
        <ul class="alert warn" role="alert">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    @endif
    <form method="POST" action="{{ route('info.contact.store') }}">
        @csrf
        <div class="form-row">
            <div class="field"><label for="contact-name">Name</label><input id="contact-name" name="name" autocomplete="name" value="{{ old('name') }}" maxlength="100" required @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>@error('name') <p id="contact-name-error" class="field-error">{{ $message }}</p> @enderror</div>
            <div class="field"><label for="contact-email">Email address</label><input id="contact-email" type="email" name="email" autocomplete="email" value="{{ old('email') }}" maxlength="255" required @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>@error('email') <p id="contact-email-error" class="field-error">{{ $message }}</p> @enderror</div>
        </div>
        <div class="field"><label for="contact-subject">Subject</label><input id="contact-subject" name="subject" value="{{ old('subject') }}" maxlength="100" required @error('subject') aria-invalid="true" aria-describedby="contact-subject-error" @enderror>@error('subject') <p id="contact-subject-error" class="field-error">{{ $message }}</p> @enderror</div>
        <div class="field"><label for="contact-message">Message</label><textarea id="contact-message" name="message" maxlength="3000" required @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('message') }}</textarea>@error('message') <p id="contact-message-error" class="field-error">{{ $message }}</p> @enderror</div>
        <button class="btn btn-primary" type="submit">Send message</button>
    </form>
  </section>
  <aside class="stack product-guide-aside">
  <div class="kpi o"><div class="ic"><i class="fa-solid fa-location-dot"></i></div><div><b style="font-size:1rem">Amman, Jordan</b><span>Initial coverage</span></div></div>
  <div class="kpi g"><div class="ic"><i class="fa-solid fa-clock"></i></div><div><b style="font-size:1rem">Sat - Thu</b><span>Support hours</span></div></div></aside></div></div></section>
@endsection
