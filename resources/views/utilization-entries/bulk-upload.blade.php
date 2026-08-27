<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb mb-0 text-uppercase small fw-bold tracking-widest">
                        <li class="breadcrumb-item"><a href="{{ route('assets.index') }}" class="text-secondary text-decoration-none hover-text-primary">Fleet</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assets.show', $asset) }}" class="text-secondary text-decoration-none hover-text-primary">{{ $asset->fleet_no }}</a></li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">Bulk Upload</li>
                    </ol>
                </nav>
                <h2 class="h2 fw-bold text-light mb-0">
                    Bulk Upload Utilization: {{ $asset->fleet_no }}
                </h2>
            </div>
            <div>
                <a href="{{ route('assets.show', $asset) }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 d-inline-flex align-items-center">
                    <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Asset
                </a>
            </div>
        </div>
    </x-slot>

    <div class="container-xl py-5">
        <!-- Notification Area -->
        <div id="alert-container"></div>

        <!-- Upload Area (Top, Full Width) -->
        <div class="card bg-dark border-secondary border-opacity-25 mb-4">
            <div class="card-body p-4 p-md-5">
                <h4 class="h5 fw-bold text-light mb-4">Upload Spreadsheet File</h4>
                
                <form id="bulk-preview-form" enctype="multipart/form-data">
                    @csrf
                    <div class="border border-dashed border-secondary border-opacity-50 bg-secondary bg-opacity-5 rounded-4 p-5 text-center mb-4 cursor-pointer hover-bg-secondary-10 transition-all position-relative" id="dropzone">
                        <input type="file" id="file" name="file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept=".xlsx,.xls,.csv" required>
                        <svg class="text-secondary mb-3" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                        </svg>
                        <h5 class="h6 fw-bold text-light mb-1" id="file-label">Drag and drop spreadsheet here, or click to browse</h5>
                        <p class="text-secondary small mb-0">Accepts Excel (.xlsx, .xls) and CSV up to 50 rows</p>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="submit" id="preview-btn" class="btn btn-primary rounded-pill px-5 py-3 fw-bold text-uppercase tracking-widest shadow-sm">
                            <span>Preview Entries</span>
                            <div id="preview-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions & Guidelines (Below, Multi-Column) -->
        <div class="row g-4 mb-5">
            <!-- Column 1: Expected Columns Schema -->
            <div class="col-lg-8">
                <div class="card bg-dark border-secondary border-opacity-25 h-100">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                            <h4 class="h5 fw-bold text-light mb-0 d-flex align-items-center">
                                <span class="bg-primary p-1 rounded-circle me-2" style="width: 8px; height: 8px;"></span>
                                Expected Spreadsheet Columns (Headers)
                            </h4>
                            <a href="{{ route('assets.utilization-entries.bulk-template', $asset) }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold text-uppercase tracking-wider py-2 px-3 d-flex align-items-center justify-content-center" style="font-size: 0.75rem;">
                                <svg class="me-2" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download Excel Template
                            </a>
                        </div>
                        
                        <div class="row g-4">
                            <div class="col-md-4">
                                <small class="text-primary text-uppercase fw-semibold tracking-wider d-block mb-2" style="font-size: 0.75rem;">1. Core Metadata</small>
                                <ul class="list-group list-group-flush bg-transparent border-0 ps-0" style="font-size: 0.75rem;">
                                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-2">
                                        <strong>Date</strong>: YYYY-MM-DD format
                                    </li>
                                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-2">
                                        <strong>Start & End Time</strong>: HH:MM format
                                    </li>
                                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-2">
                                        <strong>Personnel In-Charge</strong>: Name of operator
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-4">
                                <small class="text-primary text-uppercase fw-semibold tracking-wider d-block mb-2" style="font-size: 0.75rem;">2. Allocation & Method</small>
                                <ul class="list-group list-group-flush bg-transparent border-0 ps-0" style="font-size: 0.75rem;">
                                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-2">
                                        <strong>Charged To</strong>: Active Account name
                                    </li>
                                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-2">
                                        <strong>Sub Account</strong>: Sub-account name
                                    </li>
                                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-2">
                                        <strong>Calculation Type</strong>: <em>Kilometer</em>, <em>Hour</em>, etc.
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-4">
                                <small class="text-primary text-uppercase fw-semibold tracking-wider d-block mb-2" style="font-size: 0.75rem;">3. Readings & Particulars</small>
                                <ul class="list-group list-group-flush bg-transparent border-0 ps-0" style="font-size: 0.75rem;">
                                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-2">
                                        <strong>Start & End Reading</strong>: Numeric values
                                    </li>
                                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-2">
                                        <strong>Actual Hours</strong>: Decimal hours
                                    </li>
                                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-2">
                                        <strong>Unbudgeted & Particulars</strong>: Task details
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Upload Guidelines & Reading Increments -->
            <div class="col-lg-4">
                <div class="card bg-dark border-secondary border-opacity-25 h-100 d-flex flex-column justify-content-between">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h4 class="h5 fw-bold text-light mb-3 d-flex align-items-center">
                                <span class="bg-primary p-1 rounded-circle me-2" style="width: 8px; height: 8px;"></span>
                                Upload Guidelines
                            </h4>
                            <p class="text-secondary small mb-4">
                                Prepare your Excel file (`.xlsx`, `.xls`) or `.csv` according to the template schemas. Maximum capacity is <strong>50 log entries</strong> per upload batch.
                            </p>
                        </div>
                        
                        <div class="bg-primary bg-opacity-10 border border-primary border-opacity-20 p-3 rounded-3 text-primary small">
                            <h6 class="fw-bold mb-1">💡 Reading Increments Notice</h6>
                            Our sequential validator checks that:
                            <ul class="mb-0 ps-3 mt-1" style="font-size: 0.75rem;">
                                <li>The start reading of row N is &ge; the end reading of row N-1 (or current readings).</li>
                                <li>Date & Start Time of row N cannot be earlier than End Time of row N-1.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Results Container -->
        <div id="preview-container" class="d-none">
            <div class="card bg-dark border-secondary border-opacity-25 mb-4">
                <div class="card-header bg-secondary bg-opacity-10 py-4 px-4 border-bottom border-secondary border-opacity-25 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <div>
                        <h3 class="h5 fw-bold text-light mb-1">Tabular Preview</h3>
                        <p class="text-secondary small mb-0">Check for errors before submitting. All errors must be resolved.</p>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded-pill px-3 py-2 text-sm text-light d-flex align-items-center gap-2">
                            <span>Total Rows:</span>
                            <span class="fw-bold" id="stats-total">0</span>
                        </div>
                        <div class="bg-danger bg-opacity-10 border border-danger border-opacity-20 rounded-pill px-3 py-2 text-sm text-danger d-flex align-items-center gap-2" id="stats-errors-container">
                            <span>Errors:</span>
                            <span class="fw-bold" id="stats-errors">0</span>
                        </div>
                        <div class="bg-success bg-opacity-10 border border-success border-opacity-20 rounded-pill px-3 py-2 text-sm text-success d-none align-items-center gap-2" id="stats-clean-container">
                            <i class="bi bi-check-circle"></i>
                            <span class="fw-bold">All Rows Valid</span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-dark table-hover align-middle mb-0" style="font-size: 0.75rem;">
                        <thead class="sticky-top bg-dark text-secondary text-uppercase small tracking-wider" style="z-index: 5;">
                            <tr>
                                <th class="py-3 px-4" style="width: 60px;">#</th>
                                <th class="py-3">Date</th>
                                <th class="py-3">Times</th>
                                <th class="py-3">Personnel</th>
                                <th class="py-3">Charged To</th>
                                <th class="py-3">Sub Account</th>
                                <th class="py-3">Type</th>
                                <th class="py-3 text-end">Start / End</th>
                                <th class="py-3 text-end">Qty/Hrs</th>
                                <th class="py-3 px-4" style="width: 250px;">Status / Errors</th>
                            </tr>
                        </thead>
                        <tbody id="preview-table-body">
                            <!-- Dynamic rows appended via JS -->
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-secondary bg-opacity-5 py-4 px-4 border-top border-secondary border-opacity-25 d-flex justify-content-end gap-3">
                    <button type="button" id="clear-btn" class="btn btn-outline-secondary rounded-pill px-4 py-2 text-uppercase small tracking-widest fw-bold">
                        Clear File
                    </button>
                    <button type="button" id="submit-upload-btn" class="btn btn-success rounded-pill px-5 py-3 fw-bold text-uppercase tracking-widest shadow-sm" disabled>
                        <span>Submit Bulk Upload</span>
                        <div id="submit-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles for interactive elements -->
    <style>
        .cursor-pointer {
            cursor: pointer;
        }
        .border-dashed {
            border-style: dashed !important;
        }
        .hover-bg-secondary-10:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
    </style>

    <!-- AJAX JS LOGIC -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file');
            const fileLabel = document.getElementById('file-label');
            const dropzone = document.getElementById('dropzone');
            const previewForm = document.getElementById('bulk-preview-form');
            const previewBtn = document.getElementById('preview-btn');
            const previewSpinner = document.getElementById('preview-spinner');
            const previewContainer = document.getElementById('preview-container');
            const previewTableBody = document.getElementById('preview-table-body');
            const clearBtn = document.getElementById('clear-btn');
            const submitUploadBtn = document.getElementById('submit-upload-btn');
            const submitSpinner = document.getElementById('submit-spinner');
            const alertContainer = document.getElementById('alert-container');

            const statsTotal = document.getElementById('stats-total');
            const statsErrors = document.getElementById('stats-errors');
            const statsErrorsContainer = document.getElementById('stats-errors-container');
            const statsCleanContainer = document.getElementById('stats-clean-container');

            let parsedRowsGlobal = [];

            // File select update label
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    fileLabel.innerHTML = `<span class="text-primary fw-bold">${fileInput.files[0].name}</span> selected`;
                } else {
                    fileLabel.innerText = "Drag and drop file here, or click to browse";
                }
            });

            // Clear button
            clearBtn.addEventListener('click', function() {
                fileInput.value = '';
                fileLabel.innerText = "Drag and drop file here, or click to browse";
                previewContainer.classList.add('d-none');
                parsedRowsGlobal = [];
                submitUploadBtn.disabled = true;
                showAlert('success', 'File cleared. You can upload another file.');
            });

            // Bulk preview submission via sequential chunk uploads
            previewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!fileInput.files.length) {
                    showAlert('danger', 'Please select an Excel or CSV file.');
                    return;
                }

                const file = fileInput.files[0];
                const chunkSize = 256 * 1024; // 256KB chunks
                const totalChunks = Math.ceil(file.size / chunkSize);
                const fileId = Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                previewBtn.disabled = true;
                previewSpinner.classList.remove('d-none');
                alertContainer.innerHTML = '';

                let chunkIndex = 0;

                function uploadNextChunk() {
                    const start = chunkIndex * chunkSize;
                    const end = Math.min(start + chunkSize, file.size);
                    const chunk = file.slice(start, end);

                    const formData = new FormData();
                    formData.append('file_chunk', chunk);
                    formData.append('chunk_index', chunkIndex);
                    formData.append('total_chunks', totalChunks);
                    formData.append('file_name', file.name);
                    formData.append('file_id', fileId);
                    formData.append('_token', '{{ csrf_token() }}');

                    // Progress indicator
                    const progressPercent = Math.round(((chunkIndex) / totalChunks) * 100);
                    previewBtn.querySelector('span').innerText = `Uploading chunk ${chunkIndex + 1}/${totalChunks} (${progressPercent}%)`;

                    fetch("{{ route('assets.utilization-entries.bulk-upload-chunk', $asset) }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(result => {
                        if (result.status !== 200) {
                            previewBtn.disabled = false;
                            previewBtn.querySelector('span').innerText = 'Preview Entries';
                            previewSpinner.classList.add('d-none');
                            showAlert('danger', result.body.error || result.body.message || 'Chunk upload failed.');
                            return;
                        }

                        if (chunkIndex < totalChunks - 1) {
                            // Upload next chunk
                            chunkIndex++;
                            uploadNextChunk();
                        } else {
                            // Final chunk completed! Handle response
                            previewBtn.disabled = false;
                            previewBtn.querySelector('span').innerText = 'Preview Entries';
                            previewSpinner.classList.add('d-none');
                            
                            if (result.body.error) {
                                showAlert('danger', result.body.error);
                            } else {
                                renderPreview(result.body.rows, result.body.has_errors, result.body.total_rows);
                            }
                        }
                    })
                    .catch(error => {
                        previewBtn.disabled = false;
                        previewBtn.querySelector('span').innerText = 'Preview Entries';
                        previewSpinner.classList.add('d-none');
                        showAlert('danger', 'System error occurred during chunk upload: ' + error.message);
                    });
                }

                // Start chunked upload
                uploadNextChunk();
            });

            // Submit Final batch upload
            submitUploadBtn.addEventListener('click', function() {
                if (submitUploadBtn.disabled) return;

                submitUploadBtn.disabled = true;
                submitSpinner.classList.remove('d-none');
                alertContainer.innerHTML = '';

                fetch("{{ route('assets.utilization-entries.bulk-store', $asset) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        rows: parsedRowsGlobal
                    })
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(result => {
                    submitSpinner.classList.add('d-none');

                    if (result.status === 200) {
                        // Success redirect back to asset logs with success message
                        window.location.href = "{{ route('assets.show', $asset) }}";
                    } else {
                        submitUploadBtn.disabled = false;
                        showAlert('danger', result.body.error || result.body.message || 'Failed to submit entries.');
                        
                        if (result.body.errors) {
                            // If backend validation rejected it, re-render the preview table with errors
                            renderPreview(result.body.errors, true, result.body.errors.length);
                        }
                    }
                })
                .catch(error => {
                    submitUploadBtn.disabled = false;
                    submitSpinner.classList.add('d-none');
                    showAlert('danger', 'System error occurred during submit: ' + error.message);
                });
            });

            function renderPreview(rows, hasErrors, totalRows) {
                previewTableBody.innerHTML = '';
                parsedRowsGlobal = rows;
                
                let errorCount = 0;

                rows.forEach(row => {
                    const tr = document.createElement('tr');
                    
                    if (row.has_errors) {
                        tr.classList.add('table-danger', 'bg-danger', 'bg-opacity-10', 'border-danger');
                        errorCount++;
                    }

                    let readingsStr = 'N/A';
                    if (row.calculation_type === 'Kilometer Reading') {
                        readingsStr = `${numberFormat(row.start_kilometer_reading)} KM<br><span class="text-secondary">&rarr; ${numberFormat(row.end_kilometer_reading)} KM</span>`;
                    } else if (row.calculation_type === 'Hour Reading') {
                        readingsStr = `${numberFormat(row.start_hour_reading)} HR<br><span class="text-secondary">&rarr; ${numberFormat(row.end_hour_reading)} HR</span>`;
                    }

                    let quantityStr = 'N/A';
                    if (row.calculation_type === 'Actual Hours') {
                        quantityStr = `${numberFormat(row.actual_hours)} HRS`;
                    } else if (row.calculation_type === 'Timeframe') {
                        quantityStr = 'Timeframe';
                    }

                    let statusCell = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Valid</span>';
                    if (row.has_errors) {
                        let errorsList = row.errors.map(err => `<li class="mb-1">${err}</li>`).join('');
                        statusCell = `<ul class="text-danger ps-3 mb-0" style="font-size: 0.75rem;">${errorsList}</ul>`;
                    }

                    tr.innerHTML = `
                        <td class="py-3 px-4 font-monospace text-secondary">${row.index}</td>
                        <td class="py-3">${row.date || '<span class="text-danger">Blank</span>'}</td>
                        <td class="py-3 font-monospace">${row.start_time || 'N/A'} - ${row.end_time || 'N/A'}</td>
                        <td class="py-3 text-light">${escapeHtml(row.driver_operator_name || 'Blank')}</td>
                        <td class="py-3">${escapeHtml(row.chargeable_account || 'Blank')}</td>
                        <td class="py-3">${row.unbudgeted ? '<span class="badge bg-warning text-dark">Unbudgeted</span>' : escapeHtml(row.sub_account || 'Blank')}</td>
                        <td class="py-3 text-secondary small">${row.calculation_type || 'Blank'}</td>
                        <td class="py-3 text-end font-monospace">${readingsStr}</td>
                        <td class="py-3 text-end font-monospace">${quantityStr}</td>
                        <td class="py-3 px-4">${statusCell}</td>
                    `;
                    
                    previewTableBody.appendChild(tr);
                });

                statsTotal.innerText = totalRows;
                statsErrors.innerText = errorCount;

                if (errorCount > 0) {
                    statsErrorsContainer.classList.remove('d-none');
                    statsCleanContainer.classList.add('d-none');
                    submitUploadBtn.disabled = true;
                    showAlert('warning', `Please resolve all ${errorCount} validation error(s) in your spreadsheet before submitting.`);
                } else {
                    statsErrorsContainer.classList.add('d-none');
                    statsCleanContainer.classList.remove('d-none');
                    statsCleanContainer.classList.add('d-flex');
                    submitUploadBtn.disabled = false;
                    showAlert('success', 'File parsed successfully with no errors! You can now submit the upload.');
                }

                previewContainer.classList.remove('d-none');
                
                // Scroll to preview
                previewContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function showAlert(type, message) {
                const icon = type === 'success' 
                    ? '<svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                    : type === 'warning'
                    ? '<svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>'
                    : '<svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';

                alertContainer.innerHTML = `
                    <div class="alert alert-${type} bg-${type} bg-opacity-10 border-${type} border-opacity-20 text-${type} d-flex align-items-center mb-4 rounded-3" role="alert">
                        ${icon}
                        <div class="fw-bold small">${message}</div>
                    </div>
                `;
            }

            function numberFormat(val) {
                if (val === null || val === undefined || isNaN(val)) return '0.00';
                return parseFloat(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function escapeHtml(text) {
                if (!text) return '';
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
    </script>
</x-app-layout>