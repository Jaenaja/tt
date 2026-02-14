<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>เข้าสู่ระบบ - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }
    </script>
</head>

<body
    class="bg-gradient-to-br from-blue-100 via-purple-100 to-pink-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen flex items-center justify-center p-4 transition-colors duration-200">
    <!-- Dark Mode Toggle (Fixed Position) -->
    <button onclick="toggleDarkMode()"
        class="fixed top-4 right-4 p-3 rounded-full bg-white/80 dark:bg-gray-800/80 shadow-lg hover:shadow-xl transition z-50">
        <svg id="theme-toggle-dark-icon" class="hidden w-6 h-6 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
        </svg>
        <svg id="theme-toggle-light-icon" class="hidden w-6 h-6 text-yellow-500" fill="currentColor"
            viewBox="0 0 20 20">
            <path
                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                fill-rule="evenodd" clip-rule="evenodd"></path>
        </svg>
    </button>

    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 transition-colors duration-200">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">🎰</div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-2">ระบบหวย</h1>
                <p class="text-gray-600 dark:text-gray-400">เข้าสู่ระบบเพื่อใช้งาน</p>
            </div>

            <form id="loginForm" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                        👤 ชื่อผู้ใช้
                    </label>
                    <input type="text" name="username" id="username" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="กรอกชื่อผู้ใช้">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                        🔒 รหัสผ่าน
                    </label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="กรอกรหัสผ่าน">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember"
                        class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700">
                    <label for="remember" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        จดจำการเข้าสู่ระบบ
                    </label>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-700 dark:to-purple-700 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-purple-700 transition shadow-lg hover:shadow-xl">
                    เข้าสู่ระบบ
                </button>
            </form>
        </div>

        <div class="text-center mt-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                © 2024 ระบบหวย - สงวนลิขสิทธิ์
            </p>
        </div>
    </div>

    <script>
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const darkIcon = document.getElementById('theme-toggle-dark-icon');
            const lightIcon = document.getElementById('theme-toggle-light-icon');
            if (document.documentElement.classList.contains('dark')) {
                darkIcon.classList.remove('hidden');
                lightIcon.classList.add('hidden');
            } else {
                darkIcon.classList.add('hidden');
                lightIcon.classList.remove('hidden');
            }
        }
        updateThemeIcon();

        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const response = await fetch('{{ route("login.post") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: formData.get('username'),
                        password: formData.get('password'),
                        remember: formData.has('remember')
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: data.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อระบบได้'
                });
            }
        });
    </script>
</body>

</html>