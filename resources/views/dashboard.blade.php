<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financial Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white border-l-8 border-indigo-600 shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Perbelanjaan</div>
                    <div class="mt-3 text-3xl font-black text-gray-900">RM {{ number_format($totalAmount, 2) }}</div>
                    <div class="mt-2 text-sm text-gray-500 font-medium">{{ request()->has('month') && request('month') ? date('F', mktime(0, 0, 0, request('month'), 1)) : 'Sepanjang Masa' }}</div>
                </div>

                <div class="bg-white border-l-8 border-green-500 shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Bil. Transaksi</div>
                    <div class="mt-3 text-3xl font-black text-gray-900">{{ $expenses->count() }}</div>
                    <div class="mt-2 text-sm text-gray-500 font-medium">{{ request()->has('month') && request('month') ? 'Rekod bulan ' . date('F', mktime(0, 0, 0, request('month'), 1)) : 'Jumlah keseluruhan' }}</div>
                </div>

                <div class="bg-indigo-600 shadow-sm sm:rounded-lg p-6 text-white">
                    <div class="text-xs font-bold text-indigo-100 uppercase tracking-widest">Tips Bijak</div>
                    <div class="mt-3 text-lg font-bold">Jejak perbelanjaan</div>
                    <div class="mt-1 text-xs text-indigo-100">Simpan resit untuk audit bulanan yang lebih mudah.</div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
                <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-2 md:grid-cols-5 gap-4 items-end">
                    
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Bulan</label>
                        <select name="month" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Kategori</label>
                        <select name="category_id" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Susun Ikut</label>
                        <select name="sort_by" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="spent_at" {{ request('sort_by') == 'spent_at' ? 'selected' : '' }}>Tarikh</option>
                            <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>Jumlah (RM)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Turutan</label>
                        <select name="sort_order" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Terbaru/Besar</option>
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Lama/Kecil</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" 
                                class="flex-1 bg-gray-900 text-white px-6 py-2.5 rounded-xl font-bold text-xs hover:bg-black transition">
                                Filter
                        </button>
                        
                        <a href="{{ route('dashboard') }}" 
                        class="flex-1 text-center px-6 py-2.5 bg-gray-100 text-gray-500 rounded-xl font-bold text-xs hover:bg-gray-200 transition">
                        Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold">Rekod Transaksi</h3>
                            <x-primary-button
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'add-expense')"
                            >
                                {{ __('+ Tambah Belanja') }}
                            </x-primary-button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 text-sm uppercase">
                                    <th class="p-4 border-b text-left">Tarikh</th>
                                    <th class="p-4 border-b text-left">Perkara</th>
                                    <th class="p-4 border-b text-left">Kategori</th>
                                    <th class="p-4 border-b text-right">Jumlah</th>
                                    <th class="p-4 border-b text-center">Gambar</th>
                                    <th class="p-4 border-b text-center">Tindakan</th> </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($expenses as $item)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 text-sm text-gray-600">{{ $item->spent_at->format('d M Y') }}</td>
                                    
                                    <td class="p-4">
                                        <div class="font-medium text-gray-900">{{ $item->title }}</div>
                                        @if($item->description)
                                            <div class="text-xs text-gray-400 font-normal italic leading-tight mt-1">
                                                {{ $item->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold text-white" 
                                            style="background-color: {{ $item->category->color }}">
                                            {{ $item->category->name }}
                                        </span>
                                    </td>

                                    <td class="p-4 text-right font-bold text-red-600">RM {{ number_format($item->amount, 2) }}</td>
                                    
                                    <td class="p-4 text-center">
                                        @if($item->attachment)
                                            <button type="button" 
                                                    onclick="openImage('{{ asset('storage/' . $item->attachment) }}')" 
                                                    class="inline-flex items-center text-indigo-600 hover:text-indigo-900 text-xs font-bold">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                LIHAT
                                            </button>
                                        @else
                                            <span class="text-gray-300 text-xs">-</span>
                                        @endif
                                    </td>

                                    <td class="p-4 text-center">
                                        <div class="flex justify-center gap-3">
                                            <button type="button" 
                                                x-data=""
                                                x-on:click.prevent="$dispatch('open-modal', 'edit-expense'); $dispatch('set-edit-data', {{ $item->toJson() }})"
                                                class="text-indigo-500 hover:text-indigo-700 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>

                                            <form id="delete-form-{{ $item->id }}" method="POST" action="{{ route('expenses.destroy', $item->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="confirmDelete({{ $item->id }})" class="text-red-500 hover:text-red-700 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="p-10 text-center text-gray-400">
                                        Tiada rekod dijumpai.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

<x-modal name="add-expense" focusable>
    <form method="post" action="{{ route('expenses.store') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">Tambah Belanja Baru</h2>

            <div class="mt-4">
                <x-input-label for="title" value="Perkara" />
                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" placeholder="cth: Nasi Kandar" required />
            </div>

            <div class="mt-4">
                <x-input-label for="amount" value="Jumlah (RM)" />
                <x-text-input id="amount" name="amount" type="number" step="0.01" class="mt-1 block w-full" required />
            </div>

            <div class="mt-4">
                <x-input-label for="category_id" value="Kategori" />
                <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <x-input-label for="spent_at" value="Tarikh" />
                <x-text-input id="spent_at" name="spent_at" type="date" class="mt-1 block w-full" value="{{ date('Y-m-d') }}" required />
            </div>

            <div class="mt-4">
                <x-input-label for="description" value="Nota / Butiran" />
                <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3" placeholder="cth: Air RM10, Kambing RM30..."></textarea>
            </div>

            <div class="mt-4">
                <x-input-label for="attachment" value="Gambar Resit (Jika ada)" />
                <input type="file" name="attachment" id="attachment" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="ms-3">Simpan</x-primary-button>
            </div>
    </form>
</x-modal>

<x-modal name="edit-expense" focusable>
    <form id="edit-form" method="post" action="" enctype="multipart/form-data" class="p-6" 
          x-data="{ actionUrl: '' }" 
          x-on:set-edit-data.window="
            actionUrl = '/expenses/' + $event.detail.id;
            $el.action = actionUrl;
            $el.querySelector('#edit_title').value = $event.detail.title;
            $el.querySelector('#edit_amount').value = $event.detail.amount;
            $el.querySelector('#edit_category_id').value = $event.detail.category_id;
            $el.querySelector('#edit_spent_at').value = $event.detail.spent_at.split('T')[0];
            $el.querySelector('#edit_description').value = $event.detail.description || '';
          ">
        @csrf
        @method('PATCH')

        <h2 class="text-lg font-medium text-gray-900">Kemaskini Belanja</h2>

        <div class="mt-4">
            <x-input-label for="edit_title" value="Perkara" />
            <x-text-input id="edit_title" name="title" type="text" class="mt-1 block w-full" required />
        </div>

        <div class="mt-4">
            <x-input-label for="edit_amount" value="Jumlah (RM)" />
            <x-text-input id="edit_amount" name="amount" type="number" step="0.01" class="mt-1 block w-full" required />
        </div>

        <div class="mt-4">
            <x-input-label for="edit_category_id" value="Kategori" />
            <select name="category_id" id="edit_category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-4">
            <x-input-label for="edit_spent_at" value="Tarikh" />
            <x-text-input id="edit_spent_at" name="spent_at" type="date" class="mt-1 block w-full" required />
        </div>

        <div class="mt-4">
            <x-input-label for="edit_description" value="Nota / Butiran" />
            <textarea name="description" id="edit_description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
        </div>

        <div class="mt-4">
            <x-input-label for="edit_attachment" value="Tukar Gambar Resit (Biar kosong jika tak mahu tukar)" />
            <input type="file" name="attachment" id="edit_attachment" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
            <p class="text-xs text-gray-400 mt-1">*Muat naik gambar baru akan memadam gambar lama.</p>
        </div>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
            <x-primary-button class="ms-3">Simpan Perubahan</x-primary-button>
        </div>
    </form>
</x-modal>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Betul ke nak padam?',
            text: "Data yang dipadam tak boleh dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', // warna merah tailwind
            cancelButtonColor: '#6b7280', // warna kelabu tailwind
            confirmButtonText: 'Ya, padam sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>

@if(session('success'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000
        });
    </script>
@endif

<x-modal name="view-image" maxWidth="lg" focusable>
    <div class="p-2">
        <img id="modal-image-src" src="" alt="Resit" class="w-full h-auto rounded-lg shadow-lg">
    </div>
</x-modal>

<script>
    function openImage(url) {
        document.getElementById('modal-image-src').src = url;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'view-image' }));
    }
</script>

</x-app-layout>