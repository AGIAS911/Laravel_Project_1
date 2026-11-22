@extends('layouts.info')

@section('title', 'FAQ')

@section('content')
<div class="container">
    <header class="page-hero">
        <h1>Frequently asked questions</h1>
        <p class="lead mt-s">Find answers about inventory, deposits and driver assignment.</p>
    </header>
</div>
<section class="section">
    <div class="container product-guide">
        <div class="product-faq-groups">
            @foreach ([
                'Planning your move' => [
                    ['Do I need to enter furniture weight or dimensions?', 'Add the item type and quantity. MoveIt recommends a vehicle size based on your inventory.'],
                    ['What if my camera is unavailable?', 'Add items manually to the same inventory. Items you already saved will still be there.'],
                    ['How is distance calculated?', 'Your quote uses the driving distance between the pickup and destination you set on the map.'],
                ],
                'Payments and drivers' => [
                    ['When do I pay the deposit?', 'After reviewing your quote, transfer the deposit via CliQ and upload your receipt.'],
                    ['Does uploading a receipt confirm my booking?', 'The team verifies the full deposit first. Your booking is confirmed when a driver accepts.'],
                    ['When can I see driver details?', 'The booking page shows your assigned driver and vehicle once the driver accepts.'],
                    ['What if the driver rejects my request?', 'MoveIt looks for another driver for the same booking. Follow its status from My bookings.'],
                    ['Can I cancel after payment?', 'Contact support after submitting a payment so the team can review your request.'],
                ],
            ] as $heading => $questions)
                <section>
                    <h2>{{ $heading }}</h2>
                    @foreach ($questions as [$question, $answer])
                        <details class="faq">
                            <summary>{{ $question }}</summary>
                            <p>{{ $answer }}</p>
                        </details>
                    @endforeach
                </section>
            @endforeach
        </div>
        <aside class="panel product-guide-aside">
            <h2>Need help with a booking?</h2>
            <p class="muted">Include the booking number in your message so the team can find it.</p>
            <a class="btn btn-outline" href="{{ route('info.contact') }}">Contact support</a>
        </aside>
    </div>
</section>
@endsection
