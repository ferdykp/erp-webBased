@php
    $flashNotifications = [];
    $flashStyles = [
        'success' => ['border-emerald-200 bg-emerald-50 text-emerald-900', 'fa-circle-check'],
        'status' => ['border-emerald-200 bg-emerald-50 text-emerald-900', 'fa-circle-check'],
        'error' => ['border-red-200 bg-red-50 text-red-900', 'fa-circle-exclamation'],
        'warning' => ['border-amber-200 bg-amber-50 text-amber-900', 'fa-triangle-exclamation'],
        'info' => ['border-blue-200 bg-blue-50 text-blue-900', 'fa-circle-info'],
    ];

    $errorMessages = [];
    foreach (array_keys($flashStyles) as $type) {
        $message = session($type);
        if (is_string($message) && trim($message) !== '') {
            if ($type === 'error') {
                $errorMessages[] = $message;
            } else {
                $flashNotifications[] = ['type' => $type, 'message' => $message];
            }
        }
    }

    if ($errors->any()) {
        $errorMessages = array_merge($errorMessages, $errors->all());
    }
    $errorMessages = array_values(array_unique(array_filter($errorMessages, fn ($message) => trim($message) !== '')));
    if ($errorMessages) {
        $flashNotifications[] = ['type' => 'error', 'message' => implode("\n", $errorMessages)];
    }

    // A repeated flash message should be rendered only once, even if several
    // session keys or the validation bag contain the same text.
    $seenFlashMessages = [];
    $flashNotifications = array_values(array_filter($flashNotifications, function ($notification) use (&$seenFlashMessages) {
        $key = mb_strtolower(trim($notification['message']));
        if (isset($seenFlashMessages[$key])) {
            return false;
        }
        $seenFlashMessages[$key] = true;
        return true;
    }));
@endphp

@if ($flashNotifications)
    <div class="pointer-events-none fixed right-4 top-4 z-[180] flex w-[calc(100%-2rem)] max-w-md flex-col gap-3 sm:right-6 sm:top-6 print:hidden"
        aria-live="polite" aria-relevant="additions">
        @foreach ($flashNotifications as $notification)
            @php
                [$classes, $icon] = $flashStyles[$notification['type']];
            @endphp
            <div x-data="{ show: false }"
                x-init="requestAnimationFrame(() => show = true); setTimeout(() => show = false, {{ $notification['type'] === 'error' ? 8000 : 5000 }})"
                x-cloak x-show="show"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                role="{{ $notification['type'] === 'error' ? 'alert' : 'status' }}"
                class="pointer-events-auto flex items-start gap-3 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg {{ $classes }}">
                <i class="fa-solid {{ $icon }} mt-0.5 shrink-0" aria-hidden="true"></i>
                <span class="min-w-0 flex-1 whitespace-pre-line break-words">{{ $notification['message'] }}</span>
                <button type="button" @click="show = false" aria-label="Dismiss notification"
                    class="shrink-0 rounded p-1 leading-none opacity-70 hover:opacity-100">✕</button>
            </div>
        @endforeach
    </div>
@endif
