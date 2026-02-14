<!-- resources/views/admin/users.blade.php -->
<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>จัดการผู้ใช้ - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        * {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb & Header -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center mb-4">
                <nav class="text-sm text-slate-600 dark:text-slate-400">
                    <a href="{{ route('dashboard') }}"
                        class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                    <span class="mx-2">›</span>
                    <span class="text-slate-900 dark:text-white font-semibold">จัดการผู้ใช้งาน</span>
                </nav>
                <button id="themeToggle"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                    <span
                        class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                </button>
            </div>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">👥 จัดการผู้ใช้งาน</h1>
                </div>
                <div class="text-right">
                    <div
                        class="transition-all duration-300 inline-flex items-center gap-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full px-4 py-2 mb-2">
                        <span class="text-sm text-slate-900 dark:text-white font-medium">{{ Auth::user()->name }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">ออกจากระบบ</button>
                    </form>
                </div>
            </div>
        </div>

        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center mb-6">
                <button onclick="openCreateModal()"
                    class="bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded-lg transition-all">
                    ➕ เพิ่มผู้ใช้
                </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">ชื่อ</th>
                            <th class="px-4 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">ชื่อผู้ใช้</th>
                            <th class="px-4 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">สิทธิ์</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">สถานะ</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">สร้างเมื่อ
                            </th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        @foreach($users as $user)
                            <tr class="transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <td class="px-4 py-3 text-slate-900 dark:text-white font-semibold">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-slate-800 dark:text-slate-300">{{ $user->username }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block px-3 py-1 rounded-md text-xs font-bold {{ $user->role === 'admin' ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300' : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300' }}">
                                        {{ $user->role === 'admin' ? 'ผู้ดูแลระบบ' : 'พนักงาน' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-block px-3 py-1 rounded-md text-xs font-bold {{ $user->is_active ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                                        {{ $user->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-slate-600 dark:text-slate-400">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        onclick="openEditModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->username }}', '{{ $user->role }}', {{ $user->is_active ? 'true' : 'false' }})"
                                        class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 mr-2 transition-colors">แก้ไข</button>
                                    @if($user->id !== Auth::id())
                                        <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">ลบ</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal สร้างผู้ใช้ -->
    <div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">เพิ่มผู้ใช้ใหม่</h2>
            <form id="createForm" class="space-y-4">
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">ชื่อ *</label>
                    <input type="text" name="name" required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">ชื่อผู้ใช้ *</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">รหัสผ่าน *</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">สิทธิ์ *</label>
                    <select name="role" required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                        <option value="general">พนักงาน</option>
                        <option value="admin">ผู้ดูแลระบบ</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-4">
                    <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-2 rounded-lg transition-all">บันทึก</button>
                    <button type="button" onclick="closeCreateModal()"
                        class="flex-1 bg-slate-300 dark:bg-slate-700 hover:bg-slate-400 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold py-2 rounded-lg transition-all">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal แก้ไขผู้ใช้ -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">แก้ไขผู้ใช้</h2>
            <form id="editForm" class="space-y-4">
                <input type="hidden" name="user_id" id="editUserId">
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">ชื่อ *</label>
                    <input type="text" name="name" id="editName" required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">ชื่อผู้ใช้ *</label>
                    <input type="text" name="username" id="editUsername" required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">รหัสผ่านใหม่
                        (เว้นว่างถ้าไม่เปลี่ยน)</label>
                    <input type="password" name="password" id="editPassword"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">สิทธิ์ *</label>
                    <select name="role" id="editRole" required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                        <option value="general">พนักงาน</option>
                        <option value="admin">ผู้ดูแลระบบ</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">สถานะ *</label>
                    <select name="is_active" id="editActive" required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                        <option value="1">ใช้งาน</option>
                        <option value="0">ปิดใช้งาน</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-4">
                    <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-2 rounded-lg transition-all">บันทึก</button>
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 bg-slate-300 dark:bg-slate-700 hover:bg-slate-400 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold py-2 rounded-lg transition-all">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        themeToggle.addEventListener('click', () => {
            if (htmlElement.classList.contains('dark')) {
                htmlElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                htmlElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        });

        // === ORIGINAL LOGIC - DO NOT MODIFY ===
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
            document.getElementById('createForm').reset();
        }

        function openEditModal(id, name, username, role, isActive) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editUsername').value = username;
            document.getElementById('editRole').value = role;
            document.getElementById('editActive').value = isActive ? '1' : '0';
            document.getElementById('editPassword').value = '';
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        document.getElementById('createForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const response = await fetch('{{ route("admin.users.create") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: data.message, timer: 1500 });
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'ERROR', text: data.message });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'เกิดข้อผิดพลาด' });
            }
        });

        document.getElementById('editForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const userId = formData.get('user_id');

            try {
                const response = await fetch(`/admin/users/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-HTTP-Method-Override': 'PUT',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: data.message, timer: 1500 });
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'ERROR', text: data.message });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'เกิดข้อผิดพลาด' });
            }
        });

        async function deleteUser(id, name) {
            const result = await Swal.fire({
                title: 'ยืนยันการลบ?',
                text: `ต้องการลบผู้ใช้ "${name}" หรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/admin/users/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        await Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: data.message, timer: 1500 });
                        location.reload();
                    } else {
                        Swal.fire({ icon: 'error', title: 'ERROR', text: data.message });
                    }
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'ERROR', text: 'เกิดข้อผิดพลาด' });
                }
            }
        }
    </script>
</body>

</html>