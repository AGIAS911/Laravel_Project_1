@extends('layouts.info')

@section('title', 'Policies and terms')

@section('content')
<div class="container"><section class="page-hero"><div class="crumbs"><a href="{{ url('/') }}">Home</a> / Policies and terms</div><h1>Policies and terms</h1></section></div>
<section class="section"><div class="container" style="max-width:860px"><div class="stack">
  <div class="panel"><h3 style="color:var(--navy)">Privacy</h3><p class="muted mt-s">License, receipt and home images are stored privately with temporary links. Retention is defined before launch.</p></div><div class="panel"><h3 style="color:var(--navy)">Deposit and payment</h3><p class="muted mt-s">Pay the deposit via CliQ for manual review. Uploading a receipt alone does not prove receipt of funds.</p></div><div class="panel"><h3 style="color:var(--navy)">Cancellation and refunds</h3><p class="muted mt-s">Cancellation and refund eligibility will follow an approved policy before live bookings begin.</p></div><div class="panel"><h3 style="color:var(--navy)">User activity</h3><p class="muted mt-s">Both parties must provide accurate details and honor agreed times.</p></div>
  <span class="demo-note">Draft terms — replace with approved policies</span></div></div></section>
@endsection
