@extends('admin.layout.app')

@section('title', 'Receive Goods')

@section('content')
    @php
        $product = $booking->products->first();

        $expected = (int) $booking->products->sum('quantity');

        $unit = $product?->unit ?: 'units';

        $slotOptions = $slots
            ->map(
                fn($slot) => [
                    'id' => $slot->id,
                    'line' => (string) $slot->line,
                    'slot' => (string) $slot->slot_section,
                    'occupied' => (int) ($slot->active_quantity ?? 0),
                    'summary' => $slot->contents->groupBy(fn($row) => $row->unit ?? 'unverified unit')->map(fn($rows, $unit) => $rows->sum('quantity').' '.$unit)->implode(', '),
                ],
            )
            ->values();
    @endphp

    <div x-data="receivingAssistant" data-expected="{{ $expected }}" data-unit="{{ $unit }}"
        data-slots="{{ $slotOptions->toJson() }}" class="max-w-6xl pb-12 mx-auto space-y-6">

        <header><a href="{{ route('admin.workspace.show', ['booking' => $booking, 'stage' => 'receiving']) }}"
                class="text-sm font-medium text-blue-600">← Back to Order</a>
            <h1 class="mt-3 text-3xl font-bold text-slate-900">Receive Goods</h1>
            <p class="mt-2 text-sm text-slate-500">{{ $booking->booking_code }} · {{ $booking->customer?->company_name }} ·
                Verify the physical goods before saving.</p>
        </header>
        @if ($errors->any())
            <div class="p-4 text-sm text-red-800 border border-red-200 rounded-xl bg-red-50">
                @foreach ($errors->all() as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 text-sm text-red-800 border border-red-200 rounded-xl bg-red-50">{{ session('error') }}</div>
        @endif
        <div x-show="errorMessage" x-cloak class="p-4 text-sm border rounded-xl border-amber-200 bg-amber-50 text-amber-800"
            x-text="errorMessage"></div>
        <details class="rounded-2xl border bg-white p-5"><summary class="cursor-pointer font-semibold">Current Location Contents</summary><div class="mt-4 grid gap-3 sm:grid-cols-2">@foreach($slots as $slot)<div class="rounded-lg border p-3 text-sm"><strong>Line {{ $slot->line }} / Slot {{ $slot->slot_section }}</strong>@forelse($slot->contents as $content)<p class="mt-1">{{ $content->booking?->booking_code }} · {{ $content->product_name }} · {{ $content->quantity }} {{ $content->unit ?? 'unverified unit' }}</p>@empty<p class="mt-1 text-slate-500">Empty</p>@endforelse</div>@endforeach</div></details>
        <form method="POST" action="{{ route('admin.bookings.storePlacement', $booking->id) }}"
            x-on:submit="submitForm($event)" class="space-y-5">@csrf
            <section class="p-6 bg-white border rounded-2xl border-slate-200">
                <h2 class="text-lg font-semibold">1. Goods Check</h2>
                <p class="mt-1 text-sm text-slate-500">Count and verify received goods.</p>
                <div class="grid gap-4 mt-5 sm:grid-cols-3">
                    <div class="p-4 rounded-xl bg-slate-50">
                        <p class="text-xs text-slate-500">Ordered Quantity</p>
                        <p class="mt-2 text-2xl font-bold">{{ number_format($expected) }}</p>
                        <p class="text-xs text-slate-500">{{ $unit }}</p>
                    </div>
                    <div><label class="text-sm font-semibold">Received Quantity<input x-model="actual"
                                x-on:input="quantityChanged()" type="number" min="0" step="1" required
                                class="w-full px-4 py-3 mt-2 border rounded-xl border-slate-300"
                                placeholder="Enter actual quantity"></label><button type="button"
                            x-on:click="matchBookingQuantity()"
                            class="px-4 py-2 mt-2 text-sm font-semibold rounded-lg bg-emerald-50 text-emerald-800">✓
                            Quantity Matches Order</button></div>
                    <div class="p-4 rounded-xl bg-slate-50">
                        <p class="text-xs text-slate-500">Difference</p>
                        <p class="mt-2 text-2xl font-bold"
                            x-text="quantityDifference === null ? '—' : formatNumber(quantityDifference)"></p>
                    </div>
                </div>
                <p x-show="quantityMatches" x-cloak class="p-3 mt-4 text-sm rounded-lg bg-emerald-50 text-emerald-800">
                    Received quantity matches the order.</p>
                <p x-show="mismatch" x-cloak class="p-3 mt-4 text-sm rounded-lg bg-amber-50 text-amber-800">Quantity does not match. Resolve the difference before receiving; partial receipt is not supported.
                </p>
                <p class="mt-4 text-sm text-slate-500">Product: <strong>{{ $product?->product_name }}</strong> ·
                    {{ $unit }}</p>
            </section>
            <section class="p-6 bg-white border rounded-2xl border-slate-200">
                <h2 class="text-lg font-semibold">2. Pallet Placement</h2>
                <p class="mt-1 text-sm text-slate-500">Enter units per pallet or the physical pallet count. Slots may contain several pallets and bookings. Verify physical capacity before receiving.</p>
                <div class="flex flex-wrap gap-5 mt-5 text-sm"><label class="flex items-center gap-2"><input type="radio"
                            x-model="planningMode" value="quantity" x-on:change="planningChanged()"> Units per Pallet</label><label class="flex items-center gap-2"><input type="radio" x-model="planningMode"
                            value="manual" x-on:change="planningChanged()"> Pallet Count</label></div>
                <div class="grid gap-4 mt-5 sm:grid-cols-3">
                    <div><template x-if="planningMode === 'quantity'"><label class="text-sm font-semibold">Units per Pallet<input type="number" min="1" step="1" x-model="perPallet"
                                    x-on:input="capacityChanged()"
                                    class="w-full p-3 mt-2 border rounded-xl border-slate-300"
                                    placeholder="Enter units per pallet"></label></template><template
                            x-if="planningMode === 'manual'"><label class="text-sm font-semibold">Physical Pallet Count<input type="number" min="1" step="1" x-model="manualPalletCount"
                                    x-on:input="planningChanged()"
                                    class="w-full p-3 mt-2 border rounded-xl border-slate-300"
                                    placeholder="Enter pallet count"></label></template></div>
                    <div class="p-4 rounded-xl bg-slate-50">
                        <p class="text-xs text-slate-500">Pallet Count Planned</p>
                        <p class="mt-1 text-2xl font-bold" x-text="needed"></p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50">
                        <p class="text-xs text-slate-500">Warehouse Locations</p>
                        <p class="mt-1 text-2xl font-bold">{{ $slots->count() }}</p>
                    </div>
                </div>
                <p x-show="slots.length === 0" x-cloak
                    class="p-4 mt-4 text-sm rounded-lg bg-amber-50 text-amber-800">
                    No warehouse locations exist. Add locations in Warehouse before receiving.
                </p>
                <div class="flex flex-wrap items-end gap-3 mt-5"><label class="text-sm font-semibold">Preferred Warehouse Line<select x-model="preferredLine" x-on:change="clearAllocation()"
                            class="mt-2 block min-w-[180px] rounded-lg border border-slate-300 p-3">
                            <option value="">All lines</option><template x-for="line in availableLines"
                                :key="line">
                                <option :value="line" x-text="'Line '+line"></option>
                            </template>
                        </select></label><button type="button" x-on:click="suggest()" :disabled="!canSuggest"
                        class="px-5 py-3 text-sm font-semibold text-white rounded-xl bg-slate-900 disabled:opacity-40">Assign Locations</button><button type="button" x-show="allocation.length" x-cloak
                        x-on:click="clearAllocation()" class="px-5 py-3 text-sm border rounded-xl">Reset Locations</button></div>
                <p class="mt-3 text-xs text-slate-500">A slot may hold multiple products and orders. Verify physical capacity, safe stacking and product compatibility according to site procedures.</p>
                <div x-show="allocation.length" x-cloak class="mt-5 max-h-[470px] space-y-2 overflow-auto"><template
                        x-for="(row,index) in allocation" :key="index">
                        <div class="grid gap-3 rounded-lg border p-3 sm:grid-cols-[110px_1fr_2fr] sm:items-center"><span
                                class="text-sm font-semibold" x-text="'Pallet '+(index+1)"></span><span class="text-sm"
                                x-text="formatNumber(row.qty)+' '+unit"></span><select x-model="row.key"
                                x-on:change="locationChanged()" class="p-2 border rounded-lg border-slate-300" x-init="$nextTick(() => $el.value = row.key)" required>
                                <option value="">Select location (occupied slots allowed)</option><template x-for="slot in slots"
                                    :key="slot.id">
                                    <option :value="locationKey(slot)" :selected="locationKey(slot) === row.key" 
                                        x-text="locationLabel(slot)"></option>
                                </template>
                            </select><input type="hidden" name="pallet_qty[]" value="1"><input type="hidden"
                                name="product_names[]" value="{{ $product?->product_name ?? 'Product' }}"><input
                                type="hidden" name="lines[]" :value="row.key ? row.key.split('|')[0] : ''"><input
                                type="hidden" name="petaks[]" :value="row.key ? row.key.split('|')[1] : ''"></div>
                    </template></div>
                <input type="hidden" name="planning_mode" :value="planningMode"><input type="hidden"
                    name="manual_pallet_count" :value="planningMode === 'manual' ? needed : ''"><input type="hidden"
                    name="total_qty" :value="expected"><input type="hidden" name="per_pallet"
                    :value="capacity">
            </section>
            <section class="p-6 bg-white border rounded-2xl border-slate-200">
                <h2 class="text-lg font-semibold">3. Staff and Confirmation</h2>
                <div class="grid gap-4 mt-4 sm:grid-cols-2"><label class="text-sm font-semibold">Warehouse PIC<select name="pic_warehouse" x-model="warehousePic" x-on:change="staffChanged()"
                            required class="w-full p-3 mt-2 border rounded-xl border-slate-300">
                            <option value="">Select Warehouse PIC</option>
                            @foreach ($warehousePics as $pic)
                                <option value="{{ $pic->name }}">{{ $pic->name }}</option>
                            @endforeach
                        </select>
                    </label><label class="text-sm font-semibold">Porter<select name="porter_name"
                            x-model="porter" x-on:change="staffChanged()" required
                            class="w-full p-3 mt-2 border rounded-xl border-slate-300">
                            <option value="">Select Porter</option>
                            @foreach ($porters as $porter)
                                <option value="{{ $porter->name }}">{{ $porter->name }}</option>
                            @endforeach
                        </select></label></div>
                <div class="p-4 mt-5 text-sm rounded-lg bg-slate-50"><span
                        x-text="'Quantity: '+formatNumber(expected)"></span> · <span x-text="'Pallet: '+needed"></span> ·
                    <span x-text="'Allocations: '+allocation.length"></span>
                </div><label class="flex items-start gap-2 mt-5 text-sm"><input type="checkbox" x-model="confirmed"
                        class="mt-1"> I verified quantities, physical capacity, selected locations and staff details.</label>
                <div class="flex flex-wrap justify-between gap-3 mt-6"><a class="px-5 py-3 text-sm border rounded-xl"
                        href="{{ route('admin.workspace.show', ['booking' => $booking, 'stage' => 'receiving']) }}">Cancel</a><button
                        type="submit" :disabled="!ready"
                        class="px-5 py-3 text-sm font-semibold text-white bg-blue-600 rounded-xl disabled:opacity-40"
                        x-text="submitting ? 'Saving...' : 'Confirm Receiving'"></button></div>
            </section>
        </form>
    </div>
@endsection
