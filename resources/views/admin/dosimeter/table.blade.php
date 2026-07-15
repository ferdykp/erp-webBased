<div class="overflow-x-auto border border-gray-200 rounded-lg">
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase border-b border-gray-200 bg-gray-50">
            <tr>
                <th class="px-6 py-4 font-bold">Booking Code</th>
                <th class="px-6 py-4 font-bold">Customer Name</th>
                <th class="px-6 py-4 font-bold">Product</th>
                <th class="px-6 py-4 font-bold">Created Date</th>
                <th class="px-6 py-4 font-bold text-center">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($bookings as $item)
                <tr class="transition-colors hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                        {{ $item->booking_code }}
                    </td>
                    <td class="px-6 py-4 text-gray-700">
                        {{ $item->customer->company_name ?? ($item->customer->name ?? '-') }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->products->first()?->product_name ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-xs whitespace-nowrap">
                        {{ $item->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <a href="{{ route('admin.dosimeter.show', $item->id) }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                            <i class="fa-solid fa-pen-to-square"></i> Input Absorbance
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-sm text-center text-gray-400">
                        <i class="block mb-2 text-2xl fa-solid fa-inbox"></i>
                        No booking data found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 pagination-container">
    {{ $bookings->appends(request()->query())->links() }}
</div>
