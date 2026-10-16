@extends('admin.layout.app')
@section('title', 'Assisted Batch Planning')
@section('content')
    <div class="max-w-5xl pb-10 mx-auto space-y-6" x-data="batchAssistant({{ $remaining }})">
        <header><a href="{{ route('admin.workspace.show', ['booking' => $booking, 'stage' => 'preparation']) }}"
                class="text-sm text-slate-500">← Back to order</a>
            <h1 class="mt-3 text-3xl font-bold">Assisted Batch Planning</h1>
            <p class="mt-2 text-sm text-slate-500">{{ $booking->booking_code }} · {{ $booking->customer?->company_name }}.
                Verify machine and irradiation parameters against an approved process specification.</p>
        </header>
        <nav aria-label="Product batch allocation" class="flex flex-wrap gap-2">@foreach($booking->products as $product)<a class="rounded-xl border px-4 py-2 text-sm {{ $product->id === $selectedProduct->id ? 'bg-blue-600 text-white' : 'bg-white' }}" href="{{ route('admin.smart.batch-plan', ['booking'=>$booking, 'product'=>$product->id]) }}">{{ $product->product_name }} · {{ $product->quantity }} {{ $product->unit }}</a>@endforeach</nav>
        <form method="POST" action="{{ route('admin.production.process') }}" class="space-y-5"
            @submit="if(!ready) $event.preventDefault()">
            @csrf<input type="hidden" name="booking_product_id" value="{{ $selectedProduct->id }}"><input type="hidden" name="booking_id" value="{{ $booking->id }}">
            <section class="p-6 bg-white border rounded-2xl">
                <h2 class="text-lg font-semibold">Quantity allocation</h2>
                <div class="grid gap-4 mt-4 sm:grid-cols-3">
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Remaining quantity</p>
                        <p class="mt-2 text-2xl font-bold">{{ $remaining }}</p>
                    </div><label class="text-sm">Units per batch<input min="1" type="number" x-model.number="limit"
                            class="w-full p-3 mt-2 border rounded-xl" required></label>
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Suggested batches</p>
                        <p class="mt-2 text-2xl font-bold" x-text="quantities.length"></p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-4">
                    <button type="button" @click="useSingleBatch()" :disabled="remaining <= 0"
                        class="inline-flex items-center gap-2 px-4 py-2.5
               text-sm font-semibold text-white bg-blue-600
               rounded-xl transition-colors hover:bg-blue-700
               disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-layer-group"></i>
                        Use All in 1 Batch
                    </button>

                    <button type="button" @click="generate()" :disabled="remaining <= 0"
                        class="inline-flex items-center gap-2 px-4 py-2.5
               text-sm font-semibold text-slate-700 bg-slate-100
               border border-slate-200 rounded-xl transition-colors
               hover:bg-slate-200 disabled:opacity-40">
                        <i class="fa-solid fa-calculator"></i>
                        Calculate Batch Plan
                    </button>
                </div>


                <div class="grid gap-3 mt-4 sm:grid-cols-2">
                    <template x-for="(q, i) in quantities" :key="i">
                        <div class="block p-4 text-sm bg-white border border-slate-200 rounded-xl">
                            <label class="block font-semibold text-slate-700" x-text="'Batch ' + (i + 1)">
                            </label>

                            <input type="number" min="1" step="1" x-model.number="quantities[i]"
                                name="batch_quantities[]" required
                                class="w-full p-3 mt-2 border border-slate-200 rounded-xl">
                        </div>
                    </template>
                </div>

                <div class="mt-4 text-sm" :class="allocated === remaining ? 'text-emerald-700' : 'text-amber-700'">
                    Allocated:
                    <strong x-text="allocated"></strong> / {{ $remaining }} · Unallocated: <strong
                        x-text="remaining-allocated"></strong>
                </div>
            </section>
            <section class="p-6 bg-white border rounded-2xl">
                <h2 class="text-lg font-semibold">Machine parameters</h2>
                <p class="mt-1 text-sm text-amber-800">Parameter values must be taken from an approved product/process
                    specification. This tool does not calculate a safe irradiation dose.</p>
                <div class="grid gap-4 mt-5 sm:grid-cols-2"><label class="text-sm">Production unit<select
                            name="production_line_id" required class="w-full p-3 mt-2 border rounded-xl">
                            <option value="">Select unit</option>
                            @foreach ($machines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                            @endforeach
                        </select></label><label class="text-sm">Target dose<input type="number" min="0.01"
                            step="any" name="target_dose" required
                            class="w-full p-3 mt-2 border rounded-xl"></label><label class="text-sm">Beam speed<input
                            type="number" min="0.01" step="any" name="beam_speed" required
                            class="w-full p-3 mt-2 border rounded-xl"></label>

                    <label class="block text-sm">
                        Loading Mode

                        <select name="loading_mode" id="loading_mode" required
                            class="w-full p-3 mt-2 bg-white border outline-none border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">

                            <option value="" disabled selected>
                                Select Loading Mode
                            </option>

                            <option value="single_side">
                                Single Side
                            </option>

                            <option value="double_side">
                                Double Side
                            </option>

                        </select>
                    </label>

                    <label class="text-sm">Frequency (if
                        applicable)<input type="number" min="0" step="any" name="freq"
                            class="w-full p-3 mt-2 border rounded-xl"></label><label class="text-sm">Scan gear (if
                        applicable)<input type="number" min="0" step="any" name="scan_gear"
                            class="w-full p-3 mt-2 border rounded-xl"></label>
                </div><label class="flex items-start gap-3 mt-5 text-sm"><input type="checkbox" x-model="verified"
                        class="mt-1"><span>I verified the quantity distribution, machine availability, and authorized
                        process parameters.</span></label><button type="submit" :disabled="!ready"
                    class="px-5 py-3 mt-5 font-semibold text-white bg-blue-600 rounded-xl disabled:opacity-40">Create
                    Batches</button>
            </section>
        </form>
    </div>
@endsection
