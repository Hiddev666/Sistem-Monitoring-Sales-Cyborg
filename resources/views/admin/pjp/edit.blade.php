@extends('layouts.app')

@section('title', 'Edit Jadwal Kunjungan')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-edit"></i> Edit Jadwal Kunjungan</h2>
        </div>
    </div>

    @include('components.alerts')

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.pjp.update', $jadwal) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- User/Sales Selection -->
                        <div class="mb-4">
                            <label for="user_id" class="form-label">
                                <strong>Sales/Karyawan</strong> <span class="text-danger">*</span>
                            </label>
                            <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Sales --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                            @selected($jadwal->user_id === $user->id)>
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
                                   value="{{ old('tanggal', $jadwal->tanggal->toDateString()) }}"
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
                                      rows="3" placeholder="Misal: Kunjungan klien area timur">{{ old('keterangan', $jadwal->keterangan) }}</textarea>
                            @error('keterangan')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Status Info -->
                        <div class="alert alert-info">
                            <strong>Status Jadwal:</strong> 
                            <span class="badge bg-{{ $jadwal->status === 'pending' ? 'warning text-dark' : ($jadwal->status === 'aktif' ? 'info' : 'success') }}">
                                {{ ucfirst($jadwal->status) }}
                            </span>
                            @if($jadwal->status !== 'pending')
                                <p class="mb-0 mt-2">
                                    <small>Perjalanan telah {{ $jadwal->status === 'aktif' ? 'dimulai' : 'selesai' }}. Perubahan klien mungkin tidak berpengaruh pada data yang sudah tercatat.</small>
                                </p>
                            @endif
                        </div>

                        <!-- Klien Selection -->
                        <div class="mb-4">
                            <label for="klien" class="form-label">
                                <strong>Pilih Klien untuk Dikunjungi</strong> <span class="text-danger">*</span>
                            </label>
                            <small class="text-muted d-block mb-2">Pilih klien dalam urutan yang akan dikunjungi</small>
                            
                            <div id="klienContainer" class="mb-3">
                                @foreach($jadwal->jadwalKlien()->ordered()->get() as $index => $jk)
                                    <div class="klien-item mb-2 p-3 bg-light rounded d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-primary">{{ $index + 1 }}</span>&nbsp;
                                            <strong>{{ $jk->klien->nama_klien }}</strong>
                                            @if($jk->status === 'completed')
                                                <span class="badge bg-success ms-2">
                                                    <i class="fas fa-check"></i> Selesai
                                                </span>
                                            @endif
                                        </div>
                                        @if($jadwal->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-danger remove-klien">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <span class="text-muted small">Tidak dapat dihapus</span>
                                        @endif
                                    </div>
                                    <input type="hidden" name="klien[]" value="{{ $jk->klien_id }}">
                                @endforeach
                            </div>

                            @if($jadwal->status === 'pending')
                                <div class="input-group">
                                    <select id="klienSelect" class="form-select">
                                        <option value="">-- Tambah Klien --</option>
                                        @foreach($klien as $k)
                                            @if(!in_array($k->id, $selectedKlien))
                                                <option value="{{ $k->id }}" data-name="{{ $k->nama_klien }}">
                                                    {{ $k->nama_klien }} ({{ $k->alamat }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" id="addKlienBtn">
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </div>
                            @endif

                            @error('klien')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                            <a href="{{ route('admin.pjp.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary" @if($jadwal->status !== 'pending') disabled @endif>
                                <i class="fas fa-save"></i> Simpan Perubahan
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
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Detail Jadwal</h6>
                </div>
                <div class="card-body small">
                    <p><strong>Dibuat:</strong> {{ $jadwal->created_at->format('d M Y H:i') }}</p>
                    <p><strong>Oleh:</strong> {{ $jadwal->creator->name }}</p>
                    <p><strong>Total Klien:</strong> {{ $jadwal->getTotalKlienCount() }}</p>
                    <p><strong>Selesai:</strong> {{ $jadwal->getCompletedKlienCount() }} / {{ $jadwal->getTotalKlienCount() }}</p>
                    
                    @if($jadwal->status === 'aktif')
                        <p><strong>Mulai:</strong> {{ $jadwal->waktu_mulai }}</p>
                    @elseif($jadwal->status === 'selesai')
                        <p><strong>Mulai:</strong> {{ $jadwal->waktu_mulai }}</p>
                        <p><strong>Selesai:</strong> {{ $jadwal->waktu_selesai }}</p>
                    @endif

                    <hr>
                    <h6>Status Klien:</h6>
                    <ul class="mb-0">
                        @foreach($jadwal->jadwalKlien()->ordered()->get() as $jk)
                            <li>
                                {{ $jk->klien->nama_klien }}
                                @if($jk->status === 'completed')
                                    <span class="badge bg-success">✓</span>
                                @elseif($jk->status === 'active')
                                    <span class="badge bg-info">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let klienCount = {{ $jadwal->getTotalKlienCount() }};
const canEdit = {{ $jadwal->status === 'pending' ? 'true' : 'false' }};

if (canEdit) {
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
}

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
