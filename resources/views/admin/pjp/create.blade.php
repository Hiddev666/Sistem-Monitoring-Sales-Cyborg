@extends('layouts.app')

@section('title', 'Buat Jadwal Kunjungan')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-plus-circle"></i> Buat Jadwal Kunjungan Baru</h2>
        </div>
    </div>

    @include('components.alerts')

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.pjp.store') }}" method="POST">
                        @csrf

                        <!-- User/Sales Selection -->
                        <div class="mb-4">
                            <label for="user_id" class="form-label">
                                <strong>Sales/Karyawan</strong> <span class="text-danger">*</span>
                            </label>
                            <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Sales --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                            @selected(old('user_id') == $user->id)>
                                        {{ $user->name }} ({{ $user->wilayah?->nama_wilayah ?? 'Belum ada wilayah' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Tanggal -->
                        <div class="mb-4">
                            <label for="tanggal" class="form-label">
                                <strong>Tanggal Kunjungan</strong> <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal" id="tanggal" 
                                   class="form-control @error('tanggal') is-invalid @enderror"
                                   value="{{ old('tanggal', now()->addDay()->toDateString()) }}"
                                   min="{{ now()->toDateString() }}"
                                   required>
                            @error('tanggal')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-4">
                            <label for="keterangan" class="form-label">Keterangan/Catatan</label>
                            <textarea name="keterangan" id="keterangan" 
                                      class="form-control @error('keterangan') is-invalid @enderror"
                                      rows="3" placeholder="Misal: Kunjungan klien area timur">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Klien Selection -->
                        <div class="mb-4">
                            <label for="klien" class="form-label">
                                <strong>Pilih Klien untuk Dikunjungi</strong> <span class="text-danger">*</span>
                            </label>
                            <small class="text-muted d-block mb-2">Pilih klien dalam urutan yang akan dikunjungi</small>
                            
                            <div id="klienContainer" class="mb-3">
                                @if(old('klien'))
                                    @foreach(old('klien') as $index => $klienId)
                                        <div class="klien-item mb-2 p-3 bg-light rounded d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-primary">{{ $index + 1 }}</span>&nbsp;
                                                <strong id="klien-name-{{ $index }}">
                                                    {{ $klien->find($klienId)?->nama_klien ?? 'Klien' }}
                                                </strong>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger remove-klien">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="klien[]" value="{{ $klienId }}">
                                    @endforeach
                                @endif
                            </div>

                            <div class="input-group">
                                <select id="klienSelect" class="form-select">
                                    <option value="">-- Tambah Klien --</option>
                                    @foreach($klien as $k)
                                        <option value="{{ $k->id }}" data-name="{{ $k->nama_klien }}">
                                            {{ $k->nama_klien }} ({{ $k->alamat }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" id="addKlienBtn">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>

                            @error('klien')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                            <a href="{{ route('admin.pjp.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Buat Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Panduan</h6>
                </div>
                <div class="card-body small">
                    <h6>Langkah-langkah:</h6>
                    <ol>
                        <li>Pilih sales/karyawan yang akan melakukan kunjungan</li>
                        <li>Tentukan tanggal kunjungan</li>
                        <li>Tambahkan klien dalam urutan yang akan dikunjungi</li>
                        <li>Simpan jadwal</li>
                    </ol>

                    <hr>
                    <h6>Tips:</h6>
                    <ul>
                        <li>Urutan klien penting untuk efisiensi rute</li>
                        <li>Pertimbangkan lokasi geografis klien</li>
                        <li>Minimal 1 klien per jadwal</li>
                        <li>Jadwal dapat diedit sebelum perjalanan dimulai</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let klienCount = {{ count(old('klien', [])) }};

document.getElementById('addKlienBtn').addEventListener('click', function() {
    const select = document.getElementById('klienSelect');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!select.value) {
        alert('Silahkan pilih klien');
        return;
    }

    // Check if already added
    const hiddenInputs = document.querySelectorAll('input[name="klien[]"]');
    for (let input of hiddenInputs) {
        if (input.value == select.value) {
            alert('Klien sudah ditambahkan');
            return;
        }
    }

    klienCount++;

    const item = document.createElement('div');
    item.className = 'klien-item mb-2 p-3 bg-light rounded d-flex justify-content-between align-items-center';
    item.innerHTML = `
        <div>
            <span class="badge bg-primary">${klienCount}</span>&nbsp;
            <strong>${selectedOption.dataset.name}</strong>
        </div>
        <button type="button" class="btn btn-sm btn-danger remove-klien">
            <i class="fas fa-trash"></i>
        </button>
    `;

    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'klien[]';
    hidden.value = select.value;

    document.getElementById('klienContainer').appendChild(item);
    document.getElementById('klienContainer').appendChild(hidden);

    select.value = '';
    updateBadges();
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-klien')) {
        e.preventDefault();
        const item = e.target.closest('.klien-item');
        const nextHidden = item.nextElementSibling;
        item.remove();
        if (nextHidden && nextHidden.name === 'klien[]') {
            nextHidden.remove();
        }
        updateBadges();
    }
});

function updateBadges() {
    const items = document.querySelectorAll('.klien-item');
    items.forEach((item, index) => {
        const badge = item.querySelector('.badge');
        badge.textContent = index + 1;
    });
    klienCount = items.length;
}
</script>
@endpush

@endsection
