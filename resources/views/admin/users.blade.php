<!-- resources/views/admin/users.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>จัดการผู้ใช้</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับหน้าหลัก</a>
            <div class="text-sm text-gray-600">
                <span class="font-semibold">{{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline ml-3">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800">ออกจากระบบ</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">👥 จัดการผู้ใช้งาน</h1>
                <button onclick="openCreateModal()"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                    ➕ เพิ่มผู้ใช้
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">ชื่อ</th>
                            <th class="px-4 py-3 text-left">ชื่อผู้ใช้</th>
                            <th class="px-4 py-3 text-left">สิทธิ์</th>
                            <th class="px-4 py-3 text-center">สถานะ</th>
                            <th class="px-4 py-3 text-center">สร้างเมื่อ</th>
                            <th class="px-4 py-3 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->username }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 rounded text-sm {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $user->role === 'admin' ? 'ผู้ดูแลระบบ' : 'พนักงาน' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 rounded text-sm {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $user->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        onclick="openEditModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->username }}', '{{ $user->role }}', {{ $user->is_active ? 'true' : 'false' }})"
                                        class="text-blue-600 hover:text-blue-800 mr-2">แก้ไข</button>
                                    @if($user->id !== Auth::id())
                                        <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                            class="text-red-600 hover:text-red-800">ลบ</button>
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
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold mb-6">เพิ่มผู้ใช้ใหม่</h2>
            <form id="createForm" class="space-y-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">ชื่อ *</label>
                    <input type="text" name="name" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">ชื่อผู้ใช้ *</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">รหัสผ่าน *</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">สิทธิ์ *</label>
                    <select name="role" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="general">พนักงาน</option>
                        <option value="admin">ผู้ดูแลระบบ</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-4">
                    <button type="submit"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-lg">บันทึก</button>
                    <button type="button" onclick="closeCreateModal()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 rounded-lg">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal แก้ไขผู้ใช้ -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold mb-6">แก้ไขผู้ใช้</h2>
            <form id="editForm" class="space-y-4">
                <input type="hidden" name="user_id" id="editUserId">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">ชื่อ *</label>
                    <input type="text" name="name" id="editName" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">ชื่อผู้ใช้ *</label>
                    <input type="text" name="username" id="editUsername" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">รหัสผ่านใหม่ (เว้นว่างถ้าไม่เปลี่ยน)</label>
                    <input type="password" name="password" id="editPassword"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">สิทธิ์ *</label>
                    <select name="role" id="editRole" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="general">พนักงาน</option>
                        <option value="admin">ผู้ดูแลระบบ</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">สถานะ *</label>
                    <select name="is_active" id="editActive" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="1">ใช้งาน</option>
                        <option value="0">ปิดใช้งาน</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-4">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg">บันทึก</button>
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 rounded-lg">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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