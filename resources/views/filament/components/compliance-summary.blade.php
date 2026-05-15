<span class="text-sm flex items-center gap-2 flex-wrap">

    <span>Total: <strong>{{ $total }}</strong></span>

    <span class="flex items-center gap-1 text-success-600">
        ✅
        <strong class="text-success-600">{{ $yes }}</strong>
    </span>

    <span class="flex items-center gap-1 text-danger-600">
        <x-heroicon-o-x-circle class="w-4 h-4" />
        <strong>{{ $no }}</strong>
    </span>

    <span class="flex items-center gap-1">
        <x-heroicon-o-minus-circle class="w-4 h-4" />
        <strong>{{ $na }}</strong>
    </span>

    @if ($pending > 0)
        <span class="flex items-center gap-1 text-warning-600">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
            <strong>{{ $pending }}</strong>
        </span>
    @endif

</span>