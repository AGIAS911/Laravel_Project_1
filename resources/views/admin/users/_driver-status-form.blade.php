<form action="{{ route('admin.users.driver-status', $driver) }}" method="POST" class="d-flex flex-wrap gap-2 mb-2">
    @csrf @method('PATCH')
    <input type="hidden" name="status" value="{{ $decision }}">
    <input class="form-control form-control-sm w-auto" name="reason" aria-label="{{ $label }} reason" maxlength="1000" placeholder="Reason" required>
    <button class="btn btn-outline-danger btn-sm">{{ $label }}</button>
</form>
