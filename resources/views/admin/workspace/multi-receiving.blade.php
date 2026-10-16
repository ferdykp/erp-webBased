@extends('admin.layout.app')
@section('title', 'Receive Goods')
@section('content')
@php
    $products = $booking->products->map(fn($p) => ['id'=>$p->id, 'name'=>$p->product_name, 'quantity'=>(int)$p->quantity, 'unit'=>$p->unit]);
@endphp
<div class="mx-auto max-w-6xl space-y-6 pb-12" x-data="{
    products: @js($products), rows: @js($products->map(fn($p) => ['product_id'=>$p['id'], 'quantity'=>$p['quantity'], 'pallet_id'=>''])->values()),
    submitting: false,
    get balanced() { return this.products.every(p => this.rows.filter(r => r.product_id == p.id).reduce((sum,r) => sum + Number(r.quantity || 0),0) === p.quantity) && this.rows.every(r => r.pallet_id && Number.isSafeInteger(Number(r.quantity)) && Number(r.quantity)>0); }
}">
    <header><a href="{{ route('admin.workspace.show', $booking) }}" class="text-sm text-blue-700">← Back to Order</a><h1 class="mt-3 text-3xl font-bold">Receive Goods</h1><p class="mt-2 text-sm text-slate-500">{{ $booking->booking_code }} · {{ $booking->customer?->company_name }}</p></header>
    <p class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm">Verify each product and quantity. Shared slots are allowed. Check physical capacity and compatibility according to site procedures.</p>
    @if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <form method="POST" action="{{ route('admin.bookings.storePlacement', $booking->id) }}" class="space-y-5" @submit="if (!balanced || submitting) { $event.preventDefault(); } else { submitting = true; }">
        @csrf
        <section class="rounded-2xl border bg-white p-5 space-y-4"><h2 class="text-lg font-semibold">Product Allocation</h2>
            <template x-for="product in products" :key="product.id"><div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 p-4"><div><p class="font-semibold" x-text="product.name"></p><p class="text-sm" x-text="rows.filter(r => r.product_id == product.id).reduce((sum,r) => sum+Number(r.quantity || 0),0) + ' / ' + product.quantity + ' ' + product.unit"></p></div><button type="button" class="rounded-lg border bg-white px-3 py-2 text-sm" @click="rows.push({product_id:product.id,quantity:0,pallet_id:''})">Split Across Another Location</button></div></template>
            <template x-for="(row,index) in rows" :key="index"><div class="grid gap-3 rounded-xl border p-4 md:grid-cols-4">
                <label class="text-sm">Product<select x-model="row.product_id" :name="'allocations['+index+'][product_id]'" required class="mt-1 w-full rounded-lg border p-2"><template x-for="product in products" :key="product.id"><option :value="product.id" x-text="product.name+' ('+product.unit+')'"></option></template></select></label>
                <label class="text-sm">Quantity<input type="number" step="1" min="1" x-model.number="row.quantity" :name="'allocations['+index+'][quantity]'" required class="mt-1 w-full rounded-lg border p-2"></label>
                <label class="text-sm md:col-span-2">Warehouse Location<select x-model="row.pallet_id" :name="'allocations['+index+'][pallet_id]'" required class="mt-1 w-full rounded-lg border p-2"><option value="">Select location</option>@foreach($slots as $slot)<option value="{{ $slot->id }}">Line {{ $slot->line }} / Slot {{ $slot->slot_section }} · {{ $slot->contents->isEmpty() ? 'Empty' : $slot->contents->groupBy(fn($r) => $r->unit ?? 'unverified unit')->map(fn($rows,$unit) => $rows->sum('quantity').' '.$unit)->implode(', ') }}</option>@endforeach</select></label>
                <button type="button" @click="rows.splice(index,1)" class="text-left text-sm text-red-700">Remove Allocation</button>
            </div></template>
        </section>
        <section class="rounded-2xl border bg-white p-5"><h2 class="text-lg font-semibold">Staff and Verification</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="text-sm">Warehouse PIC<select name="pic_warehouse" required class="mt-1 w-full rounded-lg border p-3"><option value="">Select PIC</option>@foreach($warehousePics as $pic)<option>{{ $pic->name }}</option>@endforeach</select></label>
            <label class="text-sm">Porter<select name="porter_name" required class="mt-1 w-full rounded-lg border p-3"><option value="">Select Porter</option>@foreach($porters as $porter)<option>{{ $porter->name }}</option>@endforeach</select></label>
        </div><label class="mt-5 flex items-start gap-2 text-sm"><input type="checkbox" name="physical_verified" value="1" required> I checked all product quantities, locations, physical capacity and compatibility.</label>
        <p x-cloak x-show="!balanced" class="mt-3 text-sm text-amber-800">Allocate every product in full and select each location before confirming.</p>
        <button type="submit" :disabled="!balanced || submitting" class="mt-5 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white disabled:opacity-40" x-text="submitting ? 'Saving...' : 'Confirm Receiving'"></button></section>
    </form>
    <details class="rounded-2xl border bg-white p-5"><summary class="cursor-pointer font-semibold">Current Location Contents</summary><div class="mt-4 space-y-3">@foreach($slots as $slot)<div class="rounded-lg border p-3 text-sm"><strong>Line {{ $slot->line }} / Slot {{ $slot->slot_section }}</strong>@forelse($slot->contents as $content)<p class="mt-1">{{ $content->booking?->booking_code }} · {{ $content->product_name }} · {{ $content->quantity }} {{ $content->unit ?? 'unverified unit' }}</p>@empty<p class="mt-1 text-slate-500">Empty</p>@endforelse</div>@endforeach</div></details>
</div>
@endsection
