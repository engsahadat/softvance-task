@extends('layouts.app')

@section('title', 'Create Course - Course Management')

@section('content')
    <h2 class="mb-4"><i class="fas fa-plus-circle"></i> Create New Course</h2>

    <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data" id="courseForm">
        @csrf
        <!-- Course Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Course Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category" name="category" value="{{ old('category') }}">
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="description" name="description"
                        rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="feature_video" class="form-label">Feature Video</label>
                    <input type="file" class="form-control" id="feature_video" name="feature_video" accept="video/*">
                    <small class="text-muted">Max size: 100MB. Supported formats: MP4, MPEG, MOV, AVI, FLV, WEBM</small>
                    @error('feature_video')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <!-- Modules Section -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-th-large"></i> Course Modules</h5>
                <button type="button" class="btn btn-light btn-sm" id="addModule">
                    <i class="fas fa-plus"></i> Add Module
                </button>
            </div>
            <div class="card-body">
                <div id="modulesContainer">

                </div>
            </div>
        </div>
        <!-- Submit Button -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('courses.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                <i class="fas fa-save"></i> Create Course
            </button>
        </div>
        <!-- Progress Bar -->
        <div class="mt-3" id="uploadProgress" style="display: none;">
            <div class="progress" style="height: 25px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"
                    id="progressBar">0%</div>
            </div>
            <small class="text-muted mt-2 d-block" id="uploadStatus">Uploading files...</small>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        let moduleIndex = 0;

        // Add Module
        $('#addModule').on('click', function () {
            const $modulesContainer = $('#modulesContainer');
            const moduleHtml = `
            <div class="module-card" data-module-index="${moduleIndex}">
                <div class="module-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-cube"></i> Module ${moduleIndex + 1}</h5>
                    <button type="button" class="remove-btn" onclick="removeModule(${moduleIndex})">
                        <i class="fas fa-trash"></i> Remove Module
                    </button>
                </div>

                <div class="mb-3">
                    <label class="form-label">Module Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="modules[${moduleIndex}][title]">
                </div>

                <div class="mb-3">
                    <label class="form-label">Module Description</label>
                    <textarea class="form-control" name="modules[${moduleIndex}][description]" rows="2"></textarea>
                </div>

                <div class="nested-content">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6><i class="fas fa-file-alt"></i> Module Contents</h6>
                        <button type="button" class="add-btn btn-sm" onclick="addContent(${moduleIndex})">
                            <i class="fas fa-plus"></i> Add Content
                        </button>
                    </div>
                    <div class="contents-container" data-module-index="${moduleIndex}">

                    </div>
                </div>
            </div>
        `;

            $modulesContainer.append(moduleHtml);

            addContent(moduleIndex);
            moduleIndex++;
        });

        // Remove Module
        function removeModule(index) {
            if (confirm('Are you sure you want to remove this module and all its contents?')) {
                $(`[data-module-index="${index}"]`).remove();
            }
        }

        // Add Content
        function addContent(moduleIdx) {
            const $contentsContainer = $(`.contents-container[data-module-index="${moduleIdx}"]`);
            const contentCount = $contentsContainer.find('.content-card').length;

            const contentHtml = `
            <div class="content-card" data-content-id="${moduleIdx}-${contentCount}">
                <div class="content-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file"></i> Content ${contentCount + 1}</span>
                    <button type="button" class="remove-btn btn-sm" onclick="removeContent('${moduleIdx}-${contentCount}')">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Content Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="modules[${moduleIdx}][contents][${contentCount}][title]">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Content Type <span class="text-danger">*</span></label>
                        <select class="form-control content-type-select"
                                name="modules[${moduleIdx}][contents][${contentCount}][type]"
                                onchange="toggleContentFields(this, '${moduleIdx}', ${contentCount})">
                            <option value="">Select Type</option>
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            <option value="video">Video</option>
                            <option value="link">Link</option>
                            <option value="document">Document</option>
                        </select>
                    </div>
                </div>

                <div class="content-fields-${moduleIdx}-${contentCount}"></div>
            </div>
        `;

            $contentsContainer.append(contentHtml);
        }

        // Remove Content
        function removeContent(contentId) {
            const $content = $(`[data-content-id="${contentId}"]`);
            const $moduleContainer = $content.closest('.contents-container');
            const remaining = $moduleContainer.find('.content-card').length;

            if (remaining <= 1) {
                alert('Each module must have at least one content item.');
                return;
            }

            $content.remove();
        }

        // Toggle Content Fields
        function toggleContentFields(selectElement, moduleIdx, contentIdx) {
            const type = $(selectElement).val();
            const $fieldsContainer = $(`.content-fields-${moduleIdx}-${contentIdx}`);
            let fieldsHtml = '';

            switch (type) {
                case 'text':
                    fieldsHtml = `
                    <div class="mb-3">
                        <label class="form-label">Text Content</label>
                        <textarea class="form-control" name="modules[${moduleIdx}][contents][${contentIdx}][content]" rows="3"></textarea>
                    </div>
                `;
                    break;

                case 'image':
                case 'video':
                case 'document':
                    fieldsHtml = `
                    <div class="mb-3">
                        <label class="form-label">Upload ${type.charAt(0).toUpperCase() + type.slice(1)}</label>
                        <input type="file" class="form-control" name="modules[${moduleIdx}][contents][${contentIdx}][file]" accept="${getAcceptAttribute(type)}">
                        <small class="text-muted">Max size: 50MB</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="modules[${moduleIdx}][contents][${contentIdx}][content]" rows="2"></textarea>
                    </div>
                `;
                    break;

                case 'link':
                    fieldsHtml = `
                    <div class="mb-3">
                        <label class="form-label">URL</label>
                        <input type="url" class="form-control" name="modules[${moduleIdx}][contents][${contentIdx}][url]" placeholder="https://example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="modules[${moduleIdx}][contents][${contentIdx}][content]" rows="2"></textarea>
                    </div>
                `;
                    break;
            }

            $fieldsContainer.html(fieldsHtml);
        }

        // Get file accept attribute
        function getAcceptAttribute(type) {
            switch (type) {
                case 'image': return 'image/*';
                case 'video': return 'video/*';
                case 'document': return '.pdf,.doc,.docx,.txt,.ppt,.pptx,.xls,.xlsx';
                default: return '*';
            }
        }

        // Show alert message
        function showAlert(message, type) {
            const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
            $('#courseForm').prepend(alertHtml);
            $('html, body').animate({ scrollTop: $('#courseForm').offset().top - 100 }, 500);
        }
        function validateFeatureVideo() {
            const input = document.querySelector('input[name="feature_video"]');
            if (!input) return true;
            input.classList.remove('is-invalid');
            const prev = input.parentNode.querySelector('.invalid-feedback.ajax-error');
            if (prev) prev.remove();

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                const maxBytes = 100 * 1024 * 1024; // 100MB
                if (file.size > maxBytes) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback ajax-error';
                    feedback.innerHTML = 'Feature video must not exceed 100MB.';
                    input.classList.add('is-invalid');
                    input.parentNode.appendChild(feedback);
                    return false;
                }
            }

            return true;
        }

        // Attach change listener to feature_video input
        $(document).on('change', 'input[name="feature_video"]', function () {
            validateFeatureVideo();
        });
        // Utility: human readable bytes
        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Client-side validation for module/content file inputs (50MB max)
        function validateContentFileInput(input) {
            if (!input) return true;
            // remove previous error
            input.classList.remove('is-invalid');
            const prev = input.parentNode.querySelector('.invalid-feedback.ajax-error');
            if (prev) prev.remove();

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                const maxBytes = 50 * 1024 * 1024; // 50MB
                if (file.size > maxBytes) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback ajax-error';
                    feedback.innerHTML = `File is too large (${formatBytes(file.size)}). Maximum allowed is ${formatBytes(maxBytes)}.`;
                    input.classList.add('is-invalid');
                    input.parentNode.appendChild(feedback);
                    return false;
                }
            }
            return true;
        }

        // Validate all dynamic content file inputs; returns true if all valid
        function validateAllContentFiles() {
            let allValid = true;
            document.querySelectorAll('input[type="file"][name$="[file]"]').forEach(function(input) {
                if (!validateContentFileInput(input)) {
                    allValid = false;
                }
            });
            return allValid;
        }

        // delegated listener for dynamically added content file inputs
        $(document).on('change', 'input[type="file"][name$="[file]"]', function() {
            validateContentFileInput(this);
        });

// Parse PHP-style sizes like "210M" into bytes
function phpSizeToBytes(sizeStr) {
    if (!sizeStr) return 0;
    const s = sizeStr.trim().toUpperCase();
    const last = s.slice(-1);
    let num = parseFloat(s);
    if (isNaN(num)) return 0;
    switch (last) {
        case 'G': num *= 1024; // fallthrough
        case 'M': num *= 1024; // fallthrough
        case 'K': num *= 1024; break;
        default: break;
    }
    return Math.round(num);
}

// validate total upload size (sum of all selected files) against server post_max_size
function validateTotalUploadSize() {
    // get server post_max_size via blade
    const postMax = '{{ ini_get("post_max_size") }}' || '8M';
    const postMaxBytes = phpSizeToBytes(postMax);

    // sum sizes of all file inputs
    let total = 0;
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        if (input.files && input.files.length > 0) {
            for (let i = 0; i < input.files.length; i++) {
                total += input.files[i].size;
            }
        }
    });

    if (postMaxBytes > 0 && total > postMaxBytes) {
        // attach inline error to the first file input available
        const firstFile = document.querySelector('input[type="file"]');
        if (firstFile) {
            // remove previous
            firstFile.classList.add('is-invalid');
            const prev = firstFile.parentNode.querySelector('.invalid-feedback.ajax-error');
            if (prev) prev.remove();
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback ajax-error';
            feedback.innerHTML = 'Total selected files size (' + formatBytes(total) + ") exceeds server limit (post_max_size = " + postMax + '). Please reduce file sizes or upload fewer files.';
            firstFile.parentNode.appendChild(feedback);
            firstFile.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            // fallback: alert
            alert('Total upload size exceeds server limit. Please reduce files.');
        }
        return false;
    }

    return true;
}
        function displayErrors(errors) {
            document.querySelectorAll('.invalid-feedback.ajax-error').forEach(el => el.remove());
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            Object.keys(errors).forEach(function (key) {
                const messages = errors[key];
                const parts = key.split('.');
                let inputName = parts[0] || key;
                for (let i = 1; i < parts.length; i++) {
                    inputName += '[' + parts[i] + ']';
                }
                const selector = '[name="' + inputName + '"]';
                const el = document.querySelector(selector);

                if (el) {
                    el.classList.add('is-invalid');
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback ajax-error';
                    feedback.innerHTML = messages.join('<br>');
                    if (el.parentNode) {
                        if (el.nextSibling) {
                            el.parentNode.insertBefore(feedback, el.nextSibling);
                        } else {
                            el.parentNode.appendChild(feedback);
                        }
                    }
                } else {
                    console.warn('Validation for ' + key + ': ' + messages.join(' | '));
                }
            });
        }
        // Form Submit
        $('#courseForm').on('submit', function (e) {
            e.preventDefault();

            const $modules = $('.module-card');

            if ($modules.length === 0) {
                showAlert('Please add at least one module to the course.', 'danger');
                return false;
            }

            let valid = true;
            $modules.each(function (index) {
                const $contents = $(this).find('.content-card');
                if ($contents.length === 0) {
                    valid = false;
                    showAlert(`Module ${index + 1} must have at least one content item.`, 'danger');
                }
            });

            if (!valid) return false;

            // client-side checks for file sizes
            if (!validateFeatureVideo()) {
                return false;
            }

            if (!validateAllContentFiles()) {
                // do not proceed if any module content file is too large
                return false;
            }

            // client-side total POST size check against server's post_max_size
            if (!validateTotalUploadSize()) {
                return false;
            }

            const formData = new FormData(this);
            const $submitBtn = $('#submitBtn');

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Course...');
            $('#uploadProgress').show();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $('#progressBar').css('width', percentComplete + '%').text(percentComplete + '%');

                            if (percentComplete < 100) {
                                $('#uploadStatus').text('Uploading files... ' + percentComplete + '%');
                            } else {
                                $('#uploadStatus').text('Processing course data...');
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function (response) {
                    displayErrors({});
                    $('#progressBar').css('width', '100%').text('100%');
                    $('#uploadStatus').text('Course created successfully!');
                    showAlert('Course created successfully! Redirecting...', 'success');

                    setTimeout(function () {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.href = "{{ route('courses.index') }}";
                        }
                    }, 1500);
                },
                error: function (xhr) {
                    console.error('Error:', xhr);

                    let errorMessage = 'Failed to create course.';

                    if (xhr.status === 413 || xhr.status === 0) {
                        // attach inline post-size error to first file input instead of top alert
                        displayPostSizeExceededError(xhr);
                        return;
                    } else if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            displayErrors(errors);
                            $submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Create Course');
                            $('#uploadProgress').hide();
                            $('#progressBar').css('width', '0%').text('0%');
                            return;
                        }
                    } else if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.statusText) {
                        errorMessage = 'Error: ' + xhr.statusText;
                    }

                    showAlert(errorMessage, 'danger');
                    $submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Create Course');
                    $('#uploadProgress').hide();
                    $('#progressBar').css('width', '0%').text('0%');
                },
                timeout: 300000
            });
        });
        // Display an inline error when server rejects due to total post size (413)
        function displayPostSizeExceededError(xhr) {
            // If server provided max_size or current_size in JSON, use it
            let serverMax = null;
            if (xhr.responseJSON && xhr.responseJSON.max_size) {
                serverMax = xhr.responseJSON.max_size; // bytes
            }

            const postMax = '{{ ini_get("post_max_size") }}' || '8M';
            const postMaxBytes = phpSizeToBytes(postMax);

            // find first file input to attach error
            const firstFile = document.querySelector('input[type="file"]');
            if (firstFile) {
                firstFile.classList.add('is-invalid');
                const prev = firstFile.parentNode.querySelector('.invalid-feedback.ajax-error');
                if (prev) prev.remove();
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback ajax-error';
                const serverMaxText = serverMax ? formatBytes(serverMax) : postMax;
                feedback.innerHTML = 'The uploaded data is too large. Maximum allowed is ' + serverMaxText + '. Please reduce file sizes or upload fewer files.';
                firstFile.parentNode.appendChild(feedback);
                firstFile.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                // fallback
                showAlert('The uploaded files are too large. Please reduce file sizes or upload fewer files.', 'danger');
            }
            // re-enable submit UI
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Create Course');
            $('#uploadProgress').hide();
            $('#progressBar').css('width', '0%').text('0%');
        }
        $(document).ready(function () {
            $('#addModule').click();
        });
        const laravelErrors = @json($errors->messages());
        if (typeof laravelErrors !== 'undefined' && Object.keys(laravelErrors).length > 0) {
            displayErrors(laravelErrors);
            setTimeout(function () {
                const firstInvalid = document.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 300);
        }

    </script>
@endsection