<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PhotoStock') }} | Auth</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row g-4">
                            <div class="col-md-5">
                                <h1 class="h3 mb-3">PhotoStock MVP</h1>
                                <p class="text-muted mb-0">
                                    Простая авторизация и управление изображениями через API.
                                </p>
                            </div>
                            <div class="col-md-7">
                                <div id="auth-alert" class="alert d-none" role="alert"></div>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <h2 class="h5 mb-3">Login</h2>
                                        <form id="login-form">
                                            <div class="mb-3">
                                                <label for="login-email" class="form-label">Email</label>
                                                <input id="login-email" name="email" type="email" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="login-password" class="form-label">Password</label>
                                                <input id="login-password" name="password" type="password" class="form-control" required>
                                            </div>
                                            <button class="btn btn-primary w-100" type="submit">Login</button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <h2 class="h5 mb-3">Register</h2>
                                        <form id="register-form">
                                            <div class="mb-3">
                                                <label for="register-name" class="form-label">Name</label>
                                                <input id="register-name" name="name" type="text" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="register-email" class="form-label">Email</label>
                                                <input id="register-email" name="email" type="email" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="register-password" class="form-label">Password</label>
                                                <input id="register-password" name="password" type="password" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="register-password-confirmation" class="form-label">Confirm password</label>
                                                <input id="register-password-confirmation" name="password_confirmation" type="password" class="form-control" required>
                                            </div>
                                            <button class="btn btn-outline-primary w-100" type="submit">Register</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios@1.9.0/dist/axios.min.js"></script>
    <script>
        const tokenKey = 'photostock_token';
        const userKey = 'photostock_user';

        if (localStorage.getItem(tokenKey)) {
            window.location.href = '{{ route('dashboard') }}';
        }

        const alertBox = document.getElementById('auth-alert');

        function showAlert(message, type = 'danger') {
            alertBox.textContent = message;
            alertBox.className = `alert alert-${type}`;
        }

        function handleSuccess(user, token) {
            localStorage.setItem(tokenKey, token);
            localStorage.setItem(userKey, JSON.stringify(user));
            window.location.href = '{{ route('dashboard') }}';
        }

        function extractError(error) {
            return error?.response?.data?.message
                || Object.values(error?.response?.data?.errors || {}).flat()[0]
                || 'Something went wrong.';
        }

        document.getElementById('login-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = new FormData(event.target);

            try {
                const response = await axios.post('/api/login', Object.fromEntries(form.entries()));
                handleSuccess(response.data.data.user, response.data.data.token);
            } catch (error) {
                showAlert(extractError(error));
            }
        });

        document.getElementById('register-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = new FormData(event.target);

            try {
                const response = await axios.post('/api/register', Object.fromEntries(form.entries()));
                handleSuccess(response.data.data.user, response.data.data.token);
            } catch (error) {
                showAlert(extractError(error));
            }
        });
    </script>
</body>
</html>
