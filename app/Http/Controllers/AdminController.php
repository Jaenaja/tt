<?php
// app/Http/Controllers/AdminController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // หน้าจัดการ User
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    // สร้าง User ใหม่
    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,general',
        ]);

        try {
            User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'สร้างผู้ใช้สำเร็จ'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 400);
        }
    }

    // แก้ไข User
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,general',
            'is_active' => 'required|boolean',
        ]);

        try {
            $data = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'role' => $validated['role'],
                'is_active' => $validated['is_active'],
            ];

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขผู้ใช้สำเร็จ'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 400);
        }
    }

    // ลบ User
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบตัวเองได้'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบผู้ใช้สำเร็จ'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 400);
        }
    }

    // หน้า Config
    public function config()
    {
        $configs = [
            'rate_2_top' => Config::get('rate_2_top', 90),
            'rate_2_bottom' => Config::get('rate_2_bottom', 90),
            'rate_3_top' => Config::get('rate_3_top', 900),
            'rate_3_toad' => Config::get('rate_3_toad', 120),
        ];

        return view('admin.config', compact('configs'));
    }

    // อัพเดท Config
    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'rate_2_top' => 'required|numeric|min:0',
            'rate_2_bottom' => 'required|numeric|min:0',
            'rate_3_top' => 'required|numeric|min:0',
            'rate_3_toad' => 'required|numeric|min:0',
        ]);

        try {
            Config::set('rate_2_top', $validated['rate_2_top'], '2 ตัวบน');
            Config::set('rate_2_bottom', $validated['rate_2_bottom'], '2 ตัวล่าง');
            Config::set('rate_3_top', $validated['rate_3_top'], '3 ตัวบน');
            Config::set('rate_3_toad', $validated['rate_3_toad'], '3 ตัวโต๊ด');

            return response()->json([
                'success' => true,
                'message' => 'บันทึกอัตราจ่ายสำเร็จ'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 400);
        }
    }
}