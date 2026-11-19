@include('booking._status', [
    'state' => $payment->status,
    'icon' => $payment->status === 'pending' ? 'fa-clock' : null,
])
