@extends('layouts.info')

@section('title', 'Policies and terms')

@section('content')
<div class="container"><section class="page-hero"><div class="crumbs"><a href="{{ url('/') }}">Home</a> / Policies and terms</div><h1>Policies and terms</h1></section></div>
<section class="section"><div class="container" style="max-width:860px"><div class="stack">
  <div class="panel"><h3 style="color:var(--navy)">Privacy</h3><p class="muted mt-s">Driver licenses, receipts and inventory images are stored privately and require permission to view. Retention is defined before launch.</p></div><div class="panel"><h3 style="color:var(--navy)">Deposit and payment</h3><p class="muted mt-s">Pay your deposit via CliQ and upload your receipt for review. MoveIt looks for a driver after verifying the full deposit.</p></div><div class="panel"><h3 style="color:var(--navy)">Cancellation and refunds</h3><p class="muted mt-s">Cancellation and refund eligibility will follow an approved policy before live bookings begin.</p></div><div class="panel"><h3 style="color:var(--navy)">User activity</h3><p class="muted mt-s">Both parties must provide accurate details and honor agreed times.</p></div>
  <span class="demo-note">Draft terms — replace with approved policies</span></div></div></section>
@endsection
