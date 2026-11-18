@foreach (['success' => 'success', 'error' => 'danger'] as $key => $variant)
    @if (session()->has($key))
        <div class="alert alert-{{ $variant }}" role="{{ $key === 'error' ? 'alert' : 'status' }}">
            {{ session($key) }}
        </div>
    @endif
@endforeach
