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
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="text-6xl mb-4">🎰</div>
            <h1 class="text-3xl font-bold text-gray-800">ระบบหวย</h1>
            <p class="text-gray-600 mt-2">เข้าสู่ระบบ</p>
        </div>

        <form id="loginForm" class="space-y-6">
            @csrf
            <div>
                <label class="block text-gray-700 font-semibold mb-2">ชื่อผู้ใช้</label>
                <input type="text" name="username" id="username" required
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       placeholder="ชื่อผู้ใช้">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">รหัสผ่าน</label>
                <input type="password" name="password" id="password" required
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       placeholder="รหัสผ่าน">
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" 
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="remember" class="ml-2 text-gray-700">จดจำการเข้าสู่ระบบ</label>
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 rounded-lg transition shadow-lg">
                เข้าสู่ระบบ
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            <p>สำหรับผู้ดูแลระบบและพนักงาน</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route("login.post") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.href = data.redirect;
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
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                });
            }
        });
    </script>
</body>
</html>