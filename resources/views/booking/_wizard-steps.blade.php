@php
    $draftUrl = route('booking.show', $booking);
    $steps = [
        ...($booking->needsInventory() ? ['inventory' => 'Inventory'] : []),
        'type' => 'Vehicle size',
        'route' => 'Locations',
        'time' => 'Date and time',

        'review' => 'Review',
    ];
    $currentIndex = array_search($current, array_keys($steps), true);
@endphp
<ol class="booking-wizard-steps list-unstyled" aria-label="Booking progress">
    @foreach ($steps as $key => $title)
        @php
            $link = match ($key) {
                'inventory' => $draftUrl.'#inventory',
                'review' => $draftUrl.'#review',
                default => route('booking.edit', ['booking' => $booking, 'step' => $key]),
            };
        @endphp
        <li @class(['is-current' => $current === $key, 'is-completed' => $loop->index < $currentIndex]) @if ($current === $key) aria-current="step" @endif>
            <span aria-hidden="true">{{ $loop->iteration }}</span>
            @if ($loop->index < $currentIndex)
                <a href="{{ $link }}">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
        </li>
    @endforeach
</ol>
