@extends('admin.layout.app')

@section('title', 'Dosimeter Queue List')

@section('content')
    <div class="max-w-6xl p-6 mx-auto bg-white border shadow-md rounded-xl md:p-8 border-gray-150">

        {{-- Header & Search Section --}}
        <div class="flex flex-col gap-4 pb-4 mb-6 border-b border-gray-200 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dosimeter Task List</h1>
                <p class="mt-1 text-sm text-gray-500">Select a booking task below to input or update absorbance data.</p>
            </div>

            {{-- Input Pencarian AJAX --}}
            <div class="w-full md:w-80">
                <div class="relative">
                    <input type="text" id="search-input" value="{{ request('search') }}"
                        placeholder="Search booking, customer, product..."
                        class="w-full py-2.5 pl-4 pr-10 text-sm font-medium transition-all bg-gray-50 border border-gray-200 outline-none rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-blue-500">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass" id="search-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Container Tabel Utama (Akan Diperbarui oleh AJAX) --}}
        <div id="dosimeter-table-container">
            @include('admin.dosimeter.table')
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('search-input');
                const tableContainer = document.getElementById('dosimeter-table-container');
                const searchIcon = document.getElementById('search-icon');
                let debounceTimer;

                // Fungsi utama untuk mengambil data via AJAX Fetch
                function fetchDosimeterData(keyword = '', page = 1) {
                    if (searchIcon) {
                        searchIcon.className = 'fa-solid fa-spinner fa-spin text-blue-500';
                    }

                    const url = new URL("{{ route('admin.dosimeter.index') }}");
                    url.searchParams.append('search', keyword);
                    url.searchParams.append('page', page);

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.text();
                        })
                        .then(html => {
                            // Perbarui isi container tabel dengan HTML baru dari server
                            tableContainer.innerHTML = html;

                            if (searchIcon) {
                                searchIcon.className = 'fa-solid fa-magnifying-glass text-gray-400';
                            }

                            // Inisialisasi ulang event listener untuk pagination link yang baru di-render
                            bindPaginationLinks(keyword);
                        })
                        .catch(error => {
                            console.error('AJAX Error:', error);
                            if (searchIcon) {
                                searchIcon.className = 'fa-solid fa-magnifying-glass text-gray-400';
                            }
                        });
                }

                // Mengikat event click pada seluruh tag <a> di dalam pagination container
                function bindPaginationLinks(keyword) {
                    // PERBAIKAN: Selector menargetkan tag 'a' di dalam .pagination-container atau elemen nav bawaan Laravel
                    const paginationLinks = tableContainer.querySelectorAll('.pagination-container a, nav a');

                    paginationLinks.forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();

                            // Ambil query parameter 'page' dari atribut href link tersebut
                            try {
                                const urlObj = new URL(this.getAttribute('href'));
                                const page = urlObj.searchParams.get('page') || 1;
                                fetchDosimeterData(keyword, page);
                            } catch (err) {
                                // Fallback jika href berupa relative path bawaan server tertentu
                                const urlParams = new URLSearchParams(this.getAttribute('href').split(
                                    '?')[1]);
                                const page = urlParams.get('page') || 1;
                                fetchDosimeterData(keyword, page);
                            }
                        });
                    });
                }

                // Event Listener ketika pengguna mengetik (Debounce 400ms)
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const keyword = this.value;

                    debounceTimer = setTimeout(() => {
                        fetchDosimeterData(keyword, 1); // Pencarian baru selalu reset ke page 1
                    }, 400);
                });

                // Jalankan binding awal untuk halaman pertama saat pertama kali load
                bindPaginationLinks(searchInput.value);
            });
        </script>
    @endpush
@endsection
