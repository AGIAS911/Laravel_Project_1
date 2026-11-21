<ol @class(['move-steps list-unstyled mb-4', 'inventory-progress' => $line ?? false]) aria-label="Booking sections">
    @foreach (['inventory' => 'Inventory', 'locations' => 'Route, time and vehicle type', 'review' => 'Services and review'] as $id => $label)
        <li @class(['is-current' => $current === $id]) @if ($current === $id) aria-current="step" @endif>
            <span aria-hidden="true">{{ $loop->iteration }}</span>
            @if (isset($links[$id]))
                <a href="{{ $links[$id] }}">{{ $label }}</a>
            @else
                {{ $label }}
            @endif
        </li>
    @endforeach
</ol>
