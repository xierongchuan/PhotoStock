<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PhotoStock') }} | Dashboard</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Your images</h1>
                <p class="text-muted mb-0">Загрузка доступна только для PNG и JPEG до 5 MB.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="user-email" class="text-muted small"></span>
                <button id="logout-button" class="btn btn-outline-secondary">Logout</button>
            </div>
        </div>

        <div id="dashboard-alert" class="alert d-none" role="alert"></div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form id="upload-form" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label for="image" class="form-label">Choose image</label>
                        <input id="image" name="image" type="file" class="form-control" accept=".png,.jpg,.jpeg" required>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" type="submit">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="images-table">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="preview-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5 mb-0">Preview</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="preview-image" src="" alt="Preview" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.9.0/dist/axios.min.js"></script>
    <script>
        const tokenKey = 'photostock_token';
        const userKey = 'photostock_user';
        const token = localStorage.getItem(tokenKey);
        const previewModal = new bootstrap.Modal(document.getElementById('preview-modal'));
        const previewImage = document.getElementById('preview-image');
        const alertBox = document.getElementById('dashboard-alert');
        const tableBody = document.getElementById('images-table');
        let currentPreviewUrl = null;

        if (!token) {
            window.location.href = '{{ route('login') }}';
        }

        axios.defaults.headers.common.Authorization = `Bearer ${token}`;

        const storedUser = localStorage.getItem(userKey);
        if (storedUser) {
            try {
                document.getElementById('user-email').textContent = JSON.parse(storedUser).email || '';
            } catch (error) {
                localStorage.removeItem(userKey);
            }
        }

        function showAlert(message, type = 'danger') {
            alertBox.textContent = message;
            alertBox.className = `alert alert-${type}`;
        }

        function clearAlert() {
            alertBox.className = 'alert d-none';
            alertBox.textContent = '';
        }

        function formatSize(bytes) {
            if (bytes < 1024) return `${bytes} B`;
            if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
            return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
        }

        function ensureAuthorized(error) {
            if (error?.response?.status === 401) {
                localStorage.removeItem(tokenKey);
                localStorage.removeItem(userKey);
                window.location.href = '{{ route('login') }}';
                return true;
            }

            return false;
        }

        function renderRows(images) {
            if (!images.length) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No images uploaded yet.</td></tr>';
                return;
            }

            tableBody.innerHTML = images.map((image) => `
                <tr>
                    <td>${image.original_name}</td>
                    <td>${image.mime_type}</td>
                    <td>${formatSize(image.size_bytes)}</td>
                    <td>${new Date(image.created_at).toLocaleString()}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-2" data-action="preview" data-id="${image.id}">View</button>
                        <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${image.id}">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        async function fetchUser() {
            try {
                const response = await axios.get('/api/user');
                localStorage.setItem(userKey, JSON.stringify(response.data));
                document.getElementById('user-email').textContent = response.data.email;
            } catch (error) {
                ensureAuthorized(error);
            }
        }

        async function fetchImages() {
            try {
                clearAlert();
                const response = await axios.get('/api/images');
                renderRows(response.data.data);
            } catch (error) {
                if (!ensureAuthorized(error)) {
                    showAlert('Unable to load images.');
                }
            }
        }

        document.getElementById('upload-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(event.target);
            const selectedFile = formData.get('image');

            if (selectedFile instanceof File && selectedFile.size > 5 * 1024 * 1024) {
                showAlert('Image size must not exceed 5 MB.');
                return;
            }

            try {
                clearAlert();
                await axios.post('/api/images', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                event.target.reset();
                showAlert('Image uploaded successfully.', 'success');
                fetchImages();
            } catch (error) {
                if (!ensureAuthorized(error)) {
                    showAlert(error?.response?.data?.message || Object.values(error?.response?.data?.errors || {}).flat()[0] || 'Upload failed.');
                }
            }
        });

        tableBody.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-action]');

            if (!button) {
                return;
            }

            const imageId = button.dataset.id;

            if (button.dataset.action === 'preview') {
                try {
                    clearAlert();
                    const response = await axios.get(`/api/images/${imageId}`, {
                        responseType: 'blob',
                    });
                    if (currentPreviewUrl) {
                        URL.revokeObjectURL(currentPreviewUrl);
                    }
                    currentPreviewUrl = URL.createObjectURL(response.data);
                    previewImage.src = currentPreviewUrl;
                    previewModal.show();
                } catch (error) {
                    if (!ensureAuthorized(error)) {
                        showAlert('Preview failed.');
                    }
                }
                return;
            }

            if (button.dataset.action === 'delete') {
                try {
                    clearAlert();
                    await axios.delete(`/api/images/${imageId}`);
                    showAlert('Image deleted successfully.', 'success');
                    fetchImages();
                } catch (error) {
                    if (!ensureAuthorized(error)) {
                        showAlert('Delete failed.');
                    }
                }
            }
        });

        document.getElementById('logout-button').addEventListener('click', async () => {
            try {
                await axios.post('/api/logout');
            } catch (error) {
                ensureAuthorized(error);
            } finally {
                localStorage.removeItem(tokenKey);
                localStorage.removeItem(userKey);
                window.location.href = '{{ route('login') }}';
            }
        });

        fetchUser();
        fetchImages();
    </script>
</body>
</html>
