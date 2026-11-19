<ol class="move-steps list-unstyled mb-4" aria-label="Booking sections">
    @foreach (['inventory' => 'Inventory', 'locations' => 'Locations and schedule', 'vehicle' => 'Vehicle', 'services' => 'Services', 'review' => 'Review'] as $id => $label)
        <li @class(['is-current' => $current === $id]) @if ($current === $id) aria-current="step" @endif>
            <span aria-hidden="true">{{ $loop->iteration }}</span>{{ $label }}
        </li>
    @endforeach
</ol>
