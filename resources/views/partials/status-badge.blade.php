@php
    $statusClass = match ($status) {
        'active' => 'text-bg-success',
        'inactive' => 'text-bg-secondary',
        default => 'text-bg-warning',
    };
@endphp
<span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
