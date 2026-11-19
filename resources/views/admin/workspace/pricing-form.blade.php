<section class="card card-body mb-3">
    <h2 class="h5 workspace-heading">{{ $rule ? 'Edit rule #'.$rule->id : 'New pricing rule' }}</h2>
    <form class="row g-2 align-items-end" data-workflow-form method="POST"
        action="{{ $rule ? route('admin.pricing-rules.update', $rule, false) : route('admin.pricing-rules.store', [], false) }}">
        @csrf
        @if ($rule) @method('PUT') @endif
        <label class="col-md-3">Name <input class="form-control" name="name" maxlength="100" value="{{ $rule?->name }}" required></label>
        <label class="col-md-2">Day
            <select name="day_of_week" class="form-select">
                <option value="">Every day</option>
                @foreach (['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day => $label)
                    <option value="{{ $day }}" @selected($rule?->day_of_week === $day)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="col-6 col-md">From <input class="form-control" name="start_time" type="time" value="{{ substr($rule?->start_time ?? '', 0, 5) }}" required></label>
        <label class="col-6 col-md">To <input class="form-control" name="end_time" type="time" value="{{ substr($rule?->end_time ?? '', 0, 5) }}" required></label>
        <label class="col-6 col-md">Multiplier <input class="form-control" name="multiplier" type="number" min="0.001" max="999.999" step="0.001" value="{{ $rule?->multiplier ?? '1' }}" required></label>
        <div class="col-auto">
            <input type="hidden" name="is_active" value="0">
            <label><input type="checkbox" name="is_active" value="1" @checked($rule?->is_active ?? true)> Active</label>
        </div>
        <div class="col-auto"><button class="btn btn-primary">Save rule</button></div>
    </form>
    @if ($rule)
        <form class="mt-2" method="POST" data-workflow-form action="{{ route('admin.pricing-rules.destroy', $rule, false) }}">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">Delete rule</button>
        </form>
    @endif
</section>
