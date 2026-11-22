@props(['id' => null, 'label', 'value' => 1])

<div class="service-quantity" data-quantity-stepper role="group" aria-label="{{ $label }} controls">
    <button type="button" data-quantity-delta="-1" aria-label="Decrease {{ $label }}" aria-disabled="{{ $value <= 1 ? 'true' : 'false' }}">
        <i class="fa-solid fa-minus" aria-hidden="true"></i>
    </button>
    <input class="form-control" @if ($id) id="{{ $id }}" @endif type="number" name="quantity"
        aria-label="{{ $label }}" min="1" max="1000" value="{{ $value }}" required>
    <button type="button" data-quantity-delta="1" aria-label="Increase {{ $label }}" aria-disabled="{{ $value >= 1000 ? 'true' : 'false' }}">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>
    </button>
</div>
