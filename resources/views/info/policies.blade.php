@extends('layouts.info')

@section('title', 'Policies and terms')

@section('content')
<div class="container">
    <header class="page-hero"><h1>Policies and terms</h1></header>
</div>
<section class="section">
    <div class="container product-guide">
        <div class="panel product-policy">
            <p class="alert warn" role="note">Draft terms awaiting approval.</p>
            <section id="privacy">
                <h2>Privacy</h2>
                <p>Driver licenses, receipts and inventory images are stored privately and require permission to view. Retention is defined before launch.</p>
            </section>
            <section id="deposit">
                <h2>Deposit and payment</h2>
                <p>Pay your deposit via CliQ and upload your receipt for review. MoveIt looks for a driver after verifying the full deposit.</p>
            </section>
            <section id="cancellations">
                <h2>Cancellation and refunds</h2>
                <p>Cancellation and refund eligibility will follow an approved policy before live bookings begin.</p>
            </section>
            <section id="activity">
                <h2>User activity</h2>
                <p>Both parties must provide accurate details and honor agreed times.</p>
            </section>
        </div>
        <nav class="panel product-guide-aside product-policy-nav" aria-label="On this page">
            <h2>On this page</h2>
            <a href="#privacy">Privacy</a>
            <a href="#deposit">Deposit and payment</a>
            <a href="#cancellations">Cancellation and refunds</a>
            <a href="#activity">User activity</a>
        </nav>
    </div>
</section>
@endsection
