@extends('layouts.sales')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>
                <i class="fas fa-clipboard-list"></i> Form Kunjungan
                <span class="badge badge-info ms-2">{{ $jadwalKlien->klien->nama_klien ?? 'Unknown' }}</span>
            </h2>
            <p class="text-muted">
                <strong>Waktu Check-in:</strong> {{ $jadwalKlien->waktu_checkin ?? '-' }} |
                <strong>GPS:</strong> {{ $jadwalKlien->getGpsFormatted() ?? '-' }}
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <form id="visitForm" class="needs-validation">
                @csrf

                <!-- Photo Sections -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-camera"></i> Dokumentasi Foto</h5>
                    </div>
                    <div class="card-body">
                        <!-- Check-in Photo -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Foto Check-in</strong></label>
                                <div id="checkinPhotoContainer" class="text-center" style="border: 2px dashed #ccc; padding: 40px; border-radius: 8px;">
                                    @if($jadwalKlien->foto_checkin)
                                        <img src="{{ $jadwalKlien->getFotoCheckinUrl() }}" alt="Check-in" class="img-fluid rounded" style="max-height: 300px;">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deletePhoto('checkin')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    @else
                                        <p class="text-muted mb-3">Belum ada foto check-in</p>
                                        <input type="file" id="checkinPhotoInput" accept="image/*" class="d-none" onchange="handlePhotoSelect('checkin', this.files[0])">
                                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('checkinPhotoInput').click()">
                                            <i class="fas fa-camera"></i> Ambil Foto Check-in
                                        </button>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-2">Max 5MB, format: JPG, PNG, WebP</small>
                            </div>

                            <!-- Check-out Photo -->
                            <div class="col-md-6">
                                <label class="form-label"><strong>Foto Check-out</strong></label>
                                <div id="checkoutPhotoContainer" class="text-center" style="border: 2px dashed #ccc; padding: 40px; border-radius: 8px;">
                                    @if($jadwalKlien->foto_checkout)
                                        <img src="{{ $jadwalKlien->getFotoCheckoutUrl() }}" alt="Check-out" class="img-fluid rounded" style="max-height: 300px;">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deletePhoto('checkout')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    @else
                                        <p class="text-muted mb-3">Belum ada foto check-out</p>
                                        <input type="file" id="checkoutPhotoInput" accept="image/*" class="d-none" onchange="handlePhotoSelect('checkout', this.files[0])">
                                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('checkoutPhotoInput').click()">
                                            <i class="fas fa-camera"></i> Ambil Foto Check-out
                                        </button>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-2">Max 5MB, format: JPG, PNG, WebP</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visit Details -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Detail Kunjungan</h5>
                    </div>
                    <div class="card-body">
                        <!-- Hasil Tipe -->
                        <div class="mb-3">
                            <label for="hasilTipe" class="form-label"><strong>Hasil Kunjungan *</strong></label>
                            <select id="hasilTipe" name="hasil_tipe" class="form-select" required>
                                <option value="">-- Pilih Hasil --</option>
                                @foreach($hasilTipeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('hasil_tipe') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Pilih hasil kunjungan</div>
                        </div>

                        <!-- Nominal Transaksi -->
                        <div class="mb-3">
                            <label for="nominalTransaksi" class="form-label"><strong>Nominal Transaksi (Rp)</strong></label>
                            <input type="number" id="nominalTransaksi" name="nominal_transaksi" step="0.01" min="0" class="form-control" placeholder="0" value="{{ old('nominal_transaksi') }}">
                            <small class="text-muted">Isi jika ada transaksi</small>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="catatanKunjungan" class="form-label"><strong>Catatan Kunjungan *</strong></label>
                            <textarea id="catatanKunjungan" name="catatan_kunjungan" rows="4" class="form-control" placeholder="Tulis catatan kunjungan..." required>{{ old('catatan_kunjungan') }}</textarea>
                            <div class="form-text">Minimal 5 karakter</div>
                            <div class="invalid-feedback">Catatan kunjungan wajib diisi</div>
                        </div>
                    </div>
                </div>

                <!-- Signature Section -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-pen"></i> Tanda Tangan Digital</h5>
                    </div>
                    <div class="card-body">
                        @if($jadwalKlien->tanda_tangan)
                            <div class="text-center mb-3">
                                <img src="{{ $jadwalKlien->getTandaTanganUrl() }}" alt="Signature" class="img-fluid rounded" style="max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                            </div>
                            <button type="button" class="btn btn-sm btn-danger w-100 mb-3" onclick="clearSignature()">
                                <i class="fas fa-trash"></i> Hapus Tanda Tangan
                            </button>
                        @else
                            <canvas id="signaturePad" class="border rounded" style="border: 2px dashed #ccc; width: 100%; height: 200px; cursor: crosshair; background-color: #f9f9f9;"></canvas>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-warning" onclick="clearSignature()">
                                    <i class="fas fa-redo"></i> Bersihkan
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" onclick="saveSignature()">
                                    <i class="fas fa-check"></i> Simpan Tanda Tangan
                                </button>
                            </div>
                        @endif
                        <small class="text-muted d-block mt-2">Tanda tangan digital pelanggan</small>
                    </div>
                </div>

                <!-- GPS Checkout Info -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Lokasi Check-out</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Latitude</label>
                                <input type="number" id="latCheckout" name="lat_checkout" step="0.0000001" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitude</label>
                                <input type="number" id="lngCheckout" name="lng_checkout" step="0.0000001" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Akurasi GPS (m)</label>
                                <input type="number" id="accuracyCheckout" name="accuracy_checkout" step="0.01" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-info mt-4" onclick="getCheckoutLocation()">
                                    <i class="fas fa-location-arrow"></i> Ambil Lokasi Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mb-4">
                    <button type="button" onclick="submitVisitForm()" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-save"></i> Simpan & Selesaikan Kunjungan
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Klien Info -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Informasi Klien</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong><br>{{ $jadwalKlien->klien->nama_klien ?? '-' }}</p>
                    <p><strong>Kontak:</strong><br>{{ $jadwalKlien->klien->nomor_telepon ?? '-' }}</p>
                    <p><strong>Alamat:</strong><br>{{ $jadwalKlien->klien->alamat ?? '-' }}</p>
                    @if($jadwalKlien->klien->latitude && $jadwalKlien->klien->longitude)
                        <a href="https://maps.google.com/?q={{ $jadwalKlien->klien->latitude }},{{ $jadwalKlien->klien->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-map"></i> Buka di Google Maps
                        </a>
                    @endif
                </div>
            </div>

            <!-- Checklist -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Checklist Sebelum Submit</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="checkPhotos" disabled>
                        <label class="form-check-label" for="checkPhotos">
                            <i id="checkPhotosIcon" class="fas fa-times text-danger"></i> Kedua foto sudah diambil
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="checkResults" disabled>
                        <label class="form-check-label" for="checkResults">
                            <i id="checkResultsIcon" class="fas fa-times text-danger"></i> Hasil kunjungan dipilih
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="checkNotes" disabled>
                        <label class="form-check-label" for="checkNotes">
                            <i id="checkNotesIcon" class="fas fa-times text-danger"></i> Catatan diisi
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="checkSignature" disabled>
                        <label class="form-check-label" for="checkSignature">
                            <i id="checkSignatureIcon" class="fas fa-times text-danger"></i> Tanda tangan tersimpan
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkGPS" disabled>
                        <label class="form-check-label" for="checkGPS">
                            <i id="checkGPSIcon" class="fas fa-times text-danger"></i> Lokasi check-out terekam
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signature Pad Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.5/signature_pad.min.js"></script>

<script>
    let signaturePad = null;
    let jadwalKlienId = {{ $jadwalKlien->id }};

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize signature pad if not already signed
        @if(!$jadwalKlien->tanda_tangan)
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                const rect = canvas.getBoundingClientRect();
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
                signaturePad = new SignaturePad(canvas);
            }
        @endif

        // Load pre-filled GPS data if exists
        @if($jadwalKlien->lat_checkout && $jadwalKlien->lng_checkout)
            document.getElementById('latCheckout').value = {{ $jadwalKlien->lat_checkout }};
            document.getElementById('lngCheckout').value = {{ $jadwalKlien->lng_checkout }};
            document.getElementById('accuracyCheckout').value = {{ $jadwalKlien->accuracy_checkout ?? 0 }};
        @endif

        updateChecklist();
    });

    function handlePhotoSelect(type, file) {
        if (!file) return;

        const formData = new FormData();
        formData.append('photo', file);
        formData.append('type', type);

        const uploadButton = event.target;
        uploadButton.disabled = true;

        fetch(`/sales/pjp/klien/${jadwalKlienId}/upload-photo`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                const containerId = type === 'checkin' ? 'checkinPhotoContainer' : 'checkoutPhotoContainer';
                const container = document.getElementById(containerId);
                container.innerHTML = `
                    <img src="${data.photo.url}" alt="${type}" class="img-fluid rounded" style="max-height: 300px;">
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-danger" onclick="deletePhoto('${type}')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                `;
                updateChecklist();
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Gagal mengunggah foto');
        })
        .finally(() => {
            uploadButton.disabled = false;
        });
    }

    function deletePhoto(type) {
        fetch(`/sales/pjp/klien/${jadwalKlienId}/delete-photo`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ type: type })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                const containerId = type === 'checkin' ? 'checkinPhotoContainer' : 'checkoutPhotoContainer';
                const inputId = type === 'checkin' ? 'checkinPhotoInput' : 'checkoutPhotoInput';
                const container = document.getElementById(containerId);
                container.innerHTML = `
                    <p class="text-muted mb-3">Belum ada foto ${type === 'checkin' ? 'check-in' : 'check-out'}</p>
                    <input type="file" id="${inputId}" accept="image/*" class="d-none" onchange="handlePhotoSelect('${type}', this.files[0])">
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('${inputId}').click()">
                        <i class="fas fa-camera"></i> Ambil Foto ${type === 'checkin' ? 'Check-in' : 'Check-out'}
                    </button>
                `;
                updateChecklist();
            } else {
                showAlert('danger', data.message);
            }
        });
    }

    function saveSignature() {
        if (!signaturePad || signaturePad.isEmpty()) {
            showAlert('warning', 'Please draw a signature first');
            return;
        }

        const signatureData = signaturePad.toDataURL('image/png');

        fetch(`/sales/pjp/klien/${jadwalKlienId}/upload-signature`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ signature: signatureData })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                document.querySelector('[style*="cursor: crosshair"]')?.parentElement.innerHTML = `
                    <div class="text-center">
                        <img src="${data.signature.url}" alt="Signature" class="img-fluid rounded" style="max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger w-100 mt-3" onclick="clearSignature()">
                        <i class="fas fa-trash"></i> Hapus Tanda Tangan
                    </button>
                `;
                updateChecklist();
            } else {
                showAlert('danger', data.message);
            }
        });
    }

    function clearSignature() {
        if (signaturePad) {
            signaturePad.clear();
        }
    }

    function getCheckoutLocation() {
        if (!navigator.geolocation) {
            showAlert('danger', 'Geolocation is not supported by your browser');
            return;
        }

        const button = event.target;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengambil lokasi...';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('latCheckout').value = position.coords.latitude.toFixed(7);
                document.getElementById('lngCheckout').value = position.coords.longitude.toFixed(7);
                document.getElementById('accuracyCheckout').value = position.coords.accuracy.toFixed(2);
                updateChecklist();
                showAlert('success', 'Lokasi terekam');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-location-arrow"></i> Ambil Lokasi Sekarang';
            },
            function(error) {
                showAlert('danger', 'Tidak dapat mengakses GPS: ' + error.message);
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-location-arrow"></i> Ambil Lokasi Sekarang';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    function updateChecklist() {
        // Check photos
        const hasCheckInPhoto = document.querySelector('[src*="check-in"]') || 
                               document.querySelector('#checkinPhotoContainer img');
        const hasCheckOutPhoto = document.querySelector('[src*="check-out"]') || 
                                document.querySelector('#checkoutPhotoContainer img');
        
        updateChecklistItem('checkPhotos', hasCheckInPhoto && hasCheckOutPhoto);

        // Check results
        const resultsValue = document.getElementById('hasilTipe').value;
        updateChecklistItem('checkResults', resultsValue !== '');

        // Check notes
        const notesValue = document.getElementById('catatanKunjungan').value;
        updateChecklistItem('checkNotes', notesValue.length >= 5);

        // Check signature
        const hasSignature = document.querySelector('[src*="signature"]') || 
                           document.querySelector('.img-fluid[style*="150px"]');
        updateChecklistItem('checkSignature', hasSignature);

        // Check GPS
        const gpsLat = document.getElementById('latCheckout').value;
        const gpsLng = document.getElementById('lngCheckout').value;
        updateChecklistItem('checkGPS', gpsLat && gpsLng);
    }

    function updateChecklistItem(elementId, isComplete) {
        const checkbox = document.getElementById(elementId);
        const icon = document.getElementById(elementId + 'Icon');
        checkbox.checked = isComplete;
        icon.className = isComplete ? 'fas fa-check text-success' : 'fas fa-times text-danger';
    }

    function submitVisitForm() {
        // Final validation
        const latCheckout = document.getElementById('latCheckout').value;
        const lngCheckout = document.getElementById('lngCheckout').value;
        const accuracyCheckout = document.getElementById('accuracyCheckout').value;
        const catatanKunjungan = document.getElementById('catatanKunjungan').value;
        const hasilTipe = document.getElementById('hasilTipe').value;
        const nominalTransaksi = document.getElementById('nominalTransaksi').value;

        if (!latCheckout || !lngCheckout || !accuracyCheckout) {
            showAlert('danger', 'Lokasi check-out harus diambil');
            return;
        }

        if (!catatanKunjungan || catatanKunjungan.length < 5) {
            showAlert('danger', 'Catatan kunjungan minimal 5 karakter');
            return;
        }

        if (!hasilTipe) {
            showAlert('danger', 'Hasil kunjungan harus dipilih');
            return;
        }

        // Submit form
        const formData = {
            catatan_kunjungan: catatanKunjungan,
            hasil_tipe: hasilTipe,
            nominal_transaksi: nominalTransaksi || 0,
            lat_checkout: latCheckout,
            lng_checkout: lngCheckout,
            accuracy_checkout: accuracyCheckout,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        const submitButton = event.target;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        fetch(`/sales/pjp/klien/${jadwalKlienId}/submit-form`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => window.location.href = data.redirect, 1500);
            } else {
                showAlert('danger', data.message);
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-save"></i> Simpan & Selesaikan Kunjungan';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Gagal menyimpan form');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-save"></i> Simpan & Selesaikan Kunjungan';
        });
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row').firstChild);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    // Update checklist on input change
    document.getElementById('hasilTipe').addEventListener('change', updateChecklist);
    document.getElementById('catatanKunjungan').addEventListener('input', updateChecklist);
</script>
@endsection
