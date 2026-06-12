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
                                        <img src="{{ $jadwalKlien->getFotoCheckinUrl() }}" alt="Check-in" class="img-fluid rounded" style="max-height: 300px;" data-photo-state="saved" data-photo-type="checkin">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deletePhoto('checkin')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    @else
                                        <p class="text-muted mb-3">Belum ada foto check-in</p>
                                        <div id="checkinCameraStage" class="mb-3 d-none">
                                            <video id="checkinVideo" class="img-fluid rounded border w-100" style="max-height: 300px; background: #111;" autoplay playsinline muted></video>
                                            <img id="checkinPreview" class="img-fluid rounded d-none w-100" style="max-height: 300px;" alt="Preview check-in">
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                                            <button type="button" class="btn btn-outline-primary" id="checkinStartBtn" onclick="startCamera('checkin', this)">
                                                <i class="fas fa-video"></i> Nyalakan Kamera
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary d-none" id="checkinRetakeBtn" onclick="retakePhoto('checkin')">
                                                <i class="fas fa-redo"></i> Ambil Ulang
                                            </button>
                                            <button type="button" class="btn btn-primary d-none" id="checkinCaptureBtn" onclick="capturePhoto('checkin', this)">
                                                <i class="fas fa-camera"></i> Ambil Foto
                                            </button>
                                            <button type="button" class="btn btn-success d-none" id="checkinUploadBtn" onclick="uploadCapturedPhoto('checkin', this)">
                                                <i class="fas fa-cloud-upload-alt"></i> Upload Foto
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-3">Gunakan kamera handphone untuk bukti check-in. File dari galeri tidak digunakan.</small>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-2">Max 5MB, format: JPG, PNG, WebP</small>
                            </div>

                            <!-- Check-out Photo -->
                            <div class="col-md-6">
                                <label class="form-label"><strong>Foto Check-out</strong></label>
                                <div id="checkoutPhotoContainer" class="text-center" style="border: 2px dashed #ccc; padding: 40px; border-radius: 8px;">
                                    @if($jadwalKlien->foto_checkout)
                                        <img src="{{ $jadwalKlien->getFotoCheckoutUrl() }}" alt="Check-out" class="img-fluid rounded" style="max-height: 300px;" data-photo-state="saved" data-photo-type="checkout">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deletePhoto('checkout')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    @else
                                        <p class="text-muted mb-3">Belum ada foto check-out</p>
                                        <div id="checkoutCameraStage" class="mb-3 d-none">
                                            <video id="checkoutVideo" class="img-fluid rounded border w-100" style="max-height: 300px; background: #111;" autoplay playsinline muted></video>
                                            <img id="checkoutPreview" class="img-fluid rounded d-none w-100" style="max-height: 300px;" alt="Preview check-out">
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                                            <button type="button" class="btn btn-outline-primary" id="checkoutStartBtn" onclick="startCamera('checkout', this)">
                                                <i class="fas fa-video"></i> Nyalakan Kamera
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary d-none" id="checkoutRetakeBtn" onclick="retakePhoto('checkout')">
                                                <i class="fas fa-redo"></i> Ambil Ulang
                                            </button>
                                            <button type="button" class="btn btn-primary d-none" id="checkoutCaptureBtn" onclick="capturePhoto('checkout', this)">
                                                <i class="fas fa-camera"></i> Ambil Foto
                                            </button>
                                            <button type="button" class="btn btn-success d-none" id="checkoutUploadBtn" onclick="uploadCapturedPhoto('checkout', this)">
                                                <i class="fas fa-cloud-upload-alt"></i> Upload Foto
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-3">Gunakan kamera handphone untuk bukti check-out. File dari galeri tidak digunakan.</small>
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
                            <button type="button" class="btn btn-sm btn-danger w-100 mb-3" onclick="deleteSignature()">
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
                                <button type="button" class="btn btn-outline-info mt-4" onclick="getCheckoutLocation(this)">
                                    <i class="fas fa-location-arrow"></i> Ambil Lokasi Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mb-4">
                    <button type="button" onclick="submitVisitForm(this)" class="btn btn-success btn-lg w-100">
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
                    <p><strong>Kontak:</strong><br>{{ $jadwalKlien->klien->phone ?? '-' }}</p>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.5/signature_pad.umd.min.js"></script>

<script>
    let signaturePad = null;
    let jadwalKlienId = {{ $jadwalKlien->id }};
    let hasSignature = {{ $jadwalKlien->tanda_tangan ? 'true' : 'false' }};
    const uploadPhotoUrlTemplate = @json(route('sales.pjp.upload-photo', ['jadwalKlien' => '__JADWAL_KLIEN__']));
    const deletePhotoUrlTemplate = @json(route('sales.pjp.delete-photo', ['jadwalKlien' => '__JADWAL_KLIEN__']));
    const uploadSignatureUrlTemplate = @json(route('sales.pjp.upload-signature', ['jadwalKlien' => '__JADWAL_KLIEN__']));
    const submitFormUrlTemplate = @json(route('sales.pjp.submit-form', ['jadwalKlien' => '__JADWAL_KLIEN__']));
    const uploadPhotoUrl = uploadPhotoUrlTemplate.replace('__JADWAL_KLIEN__', jadwalKlienId);
    const deletePhotoUrl = deletePhotoUrlTemplate.replace('__JADWAL_KLIEN__', jadwalKlienId);
    const uploadSignatureUrl = uploadSignatureUrlTemplate.replace('__JADWAL_KLIEN__', jadwalKlienId);
    const submitFormUrl = submitFormUrlTemplate.replace('__JADWAL_KLIEN__', jadwalKlienId);

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize signature pad if not already signed
        @if(!$jadwalKlien->tanda_tangan)
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                const rect = canvas.getBoundingClientRect();
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
                if (typeof SignaturePad !== 'undefined') {
                    signaturePad = new SignaturePad(canvas);
                } else {
                    showAlert('danger', 'Library tanda tangan gagal dimuat. Muat ulang halaman.');
                }
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

    const cameraState = {
        checkin: { stream: null, blob: null, previewUrl: null },
        checkout: { stream: null, blob: null, previewUrl: null },
    };

    function getCameraElements(type) {
        return {
            stage: document.getElementById(`${type}CameraStage`),
            video: document.getElementById(`${type}Video`),
            preview: document.getElementById(`${type}Preview`),
            startBtn: document.getElementById(`${type}StartBtn`),
            captureBtn: document.getElementById(`${type}CaptureBtn`),
            uploadBtn: document.getElementById(`${type}UploadBtn`),
            retakeBtn: document.getElementById(`${type}RetakeBtn`),
        };
    }

    function stopCamera(type) {
        const state = cameraState[type];
        if (state?.stream) {
            state.stream.getTracks().forEach(track => track.stop());
            state.stream = null;
        }
    }

    function resetCaptureState(type) {
        const state = cameraState[type];
        if (state?.previewUrl) {
            URL.revokeObjectURL(state.previewUrl);
        }
        state.blob = null;
        state.previewUrl = null;
    }

    async function startCamera(type, button) {
        const elements = getCameraElements(type);
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showAlert('danger', 'Browser Anda tidak mendukung kamera. Gunakan perangkat yang memiliki kamera aktif.');
            return;
        }

        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membuka kamera...';

        try {
            stopCamera(type);
            resetCaptureState(type);

            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' }
                },
                audio: false
            });

            cameraState[type].stream = stream;
            elements.stage?.classList.remove('d-none');
            if (elements.video) {
                elements.video.srcObject = stream;
                elements.video.classList.remove('d-none');
            }
            elements.preview?.classList.add('d-none');
            elements.startBtn?.classList.add('d-none');
            elements.captureBtn?.classList.remove('d-none');
            elements.retakeBtn?.classList.add('d-none');
            elements.uploadBtn?.classList.add('d-none');
        } catch (error) {
            showAlert('danger', 'Tidak dapat mengakses kamera: ' + error.message);
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-video"></i> Nyalakan Kamera';
        }
    }

    function capturePhoto(type, button) {
        const state = cameraState[type];
        const elements = getCameraElements(type);

        if (!state?.stream || !elements.video) {
            showAlert('danger', 'Kamera belum aktif. Nyalakan kamera terlebih dahulu.');
            return;
        }

        if (elements.video.readyState < 2) {
            showAlert('warning', 'Kamera masih memuat. Tunggu sebentar lalu coba lagi.');
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = elements.video.videoWidth;
        canvas.height = elements.video.videoHeight;
        const context = canvas.getContext('2d');
        context.drawImage(elements.video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            if (!blob) {
                showAlert('danger', 'Gagal mengambil foto dari kamera.');
                return;
            }

            stopCamera(type);
            resetCaptureState(type);
            state.blob = blob;
            state.previewUrl = URL.createObjectURL(blob);

            if (elements.video) {
                elements.video.classList.add('d-none');
            }
            if (elements.preview) {
                elements.preview.src = state.previewUrl;
                elements.preview.classList.remove('d-none');
            }
            elements.captureBtn?.classList.add('d-none');
            elements.uploadBtn?.classList.remove('d-none');
            elements.retakeBtn?.classList.remove('d-none');

            showAlert('success', 'Foto berhasil diambil dari kamera. Silakan upload.');
        }, 'image/jpeg', 0.92);

        button.disabled = false;
    }

    function retakePhoto(type) {
        const state = cameraState[type];
        const elements = getCameraElements(type);

        resetCaptureState(type);
        if (elements.preview) {
            elements.preview.classList.add('d-none');
            elements.preview.removeAttribute('src');
        }
        elements.uploadBtn?.classList.add('d-none');
        elements.retakeBtn?.classList.add('d-none');
        elements.captureBtn?.classList.remove('d-none');

        const startButton = elements.startBtn;
        if (startButton) {
            startButton.classList.remove('d-none');
            startButton.disabled = false;
        }

        if (elements.stage) {
            elements.stage.classList.remove('d-none');
        }

        if (state?.stream) {
            stopCamera(type);
        }
    }

    function uploadCapturedPhoto(type, button) {
        const state = cameraState[type];

        if (!state?.blob) {
            showAlert('danger', 'Ambil foto dari kamera terlebih dahulu.');
            return;
        }

        const file = new File([state.blob], `${type}-camera-${Date.now()}.jpg`, { type: 'image/jpeg' });
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('type', type);
        formData.append('capture_source', 'camera');

        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengunggah...';

        fetch(uploadPhotoUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json().catch(() => ({
                success: false,
                message: `Upload gagal dengan status ${response.status}`
            }));

            if (!response.ok) {
                throw data;
            }

            return data;
        })
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                const containerId = type === 'checkin' ? 'checkinPhotoContainer' : 'checkoutPhotoContainer';
                const container = document.getElementById(containerId);
                container.innerHTML = `
                    <img src="${data.photo.url}" alt="${type}" class="img-fluid rounded" style="max-height: 300px;" data-photo-state="saved" data-photo-type="${type}">
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-danger" onclick="deletePhoto('${type}')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                `;
                resetCaptureState(type);
                updateChecklist();
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message || 'Gagal mengunggah foto');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload Foto';
        });
    }

    function deletePhoto(type) {
        fetch(deletePhotoUrl, {
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
                const container = document.getElementById(containerId);
                container.innerHTML = getEmptyCameraTemplate(type);
                stopCamera(type);
                resetCaptureState(type);
                updateChecklist();
            } else {
                showAlert('danger', data.message);
            }
        });
    }

    function getEmptyCameraTemplate(type) {
        const label = type === 'checkin' ? 'check-in' : 'check-out';
        const displayLabel = type === 'checkin' ? 'Check-in' : 'Check-out';

        return `
            <p class="text-muted mb-3">Belum ada foto ${label}</p>
            <div id="${type}CameraStage" class="mb-3 d-none">
                <video id="${type}Video" class="img-fluid rounded border w-100" style="max-height: 300px; background: #111;" autoplay playsinline muted></video>
                <img id="${type}Preview" class="img-fluid rounded d-none w-100" style="max-height: 300px;" alt="Preview ${label}">
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <button type="button" class="btn btn-outline-primary" id="${type}StartBtn" onclick="startCamera('${type}', this)">
                    <i class="fas fa-video"></i> Nyalakan Kamera
                </button>
                <button type="button" class="btn btn-outline-secondary d-none" id="${type}RetakeBtn" onclick="retakePhoto('${type}')">
                    <i class="fas fa-redo"></i> Ambil Ulang
                </button>
                <button type="button" class="btn btn-primary d-none" id="${type}CaptureBtn" onclick="capturePhoto('${type}', this)">
                    <i class="fas fa-camera"></i> Ambil Foto
                </button>
                <button type="button" class="btn btn-success d-none" id="${type}UploadBtn" onclick="uploadCapturedPhoto('${type}', this)">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Foto
                </button>
            </div>
            <small class="text-muted d-block mt-3">Gunakan kamera handphone untuk bukti ${label}. File dari galeri tidak digunakan.</small>
        `;
    }

    function saveSignature() {
        if (!signaturePad || signaturePad.isEmpty()) {
            showAlert('warning', 'Please draw a signature first');
            return;
        }

        const signatureData = signaturePad.toDataURL('image/png');

        fetch(uploadSignatureUrl, {
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
                hasSignature = true;
                const signatureCanvas = document.querySelector('[style*="cursor: crosshair"]');
                if (signatureCanvas && signatureCanvas.parentElement) {
                    signatureCanvas.parentElement.innerHTML = `
                        <div class="text-center">
                            <img src="${data.signature.url}" alt="Signature" class="img-fluid rounded" style="max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                        </div>
                        <button type="button" class="btn btn-sm btn-danger w-100 mt-3" onclick="deleteSignature()">
                            <i class="fas fa-trash"></i> Hapus Tanda Tangan
                        </button>
                    `;
                }
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

    function deleteSignature() {
        fetch(deletePhotoUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ type: 'signature' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => window.location.reload(), 700);
            } else {
                showAlert('danger', data.message);
            }
        });
    }

    function getCheckoutLocation(button) {
        if (!navigator.geolocation) {
            showAlert('danger', 'Geolocation is not supported by your browser');
            return;
        }

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
        const hasCheckInPhoto = !!document.querySelector('#checkinPhotoContainer img[data-photo-state="saved"]');
        const hasCheckOutPhoto = !!document.querySelector('#checkoutPhotoContainer img[data-photo-state="saved"]');
        
        updateChecklistItem('checkPhotos', hasCheckInPhoto && hasCheckOutPhoto);

        // Check results
        const resultsValue = document.getElementById('hasilTipe').value;
        updateChecklistItem('checkResults', resultsValue !== '');

        // Check notes
        const notesValue = document.getElementById('catatanKunjungan').value;
        updateChecklistItem('checkNotes', notesValue.length >= 5);

        // Check signature
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

    function submitVisitForm(submitButton) {
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

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        fetch(submitFormUrl, {
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
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
        }
        setTimeout(() => alertDiv.remove(), 5000);
    }

    // Update checklist on input change
    document.getElementById('hasilTipe').addEventListener('change', updateChecklist);
    document.getElementById('catatanKunjungan').addEventListener('input', updateChecklist);

    window.startCamera = startCamera;
    window.capturePhoto = capturePhoto;
    window.retakePhoto = retakePhoto;
    window.uploadCapturedPhoto = uploadCapturedPhoto;
    window.deletePhoto = deletePhoto;
    window.saveSignature = saveSignature;
    window.clearSignature = clearSignature;
    window.deleteSignature = deleteSignature;
    window.getCheckoutLocation = getCheckoutLocation;
    window.submitVisitForm = submitVisitForm;
</script>
@endsection
