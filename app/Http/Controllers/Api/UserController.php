<?php

namespace App\Http\Controllers\Api;

use App\Models\Penduduk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:user,email',
                'password' => 'required|min:8',
                'role' => 'required|in:penduduk,admin,petugas',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = new User();
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role = $request->role;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'user' => $user
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create user',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateUserPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update password',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            // Ambil ID pengguna dari token atau session
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Validasi input dari request
            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:user,email,' . $userId,
                'no_hp' => 'required|string|max:15',
                'alamat' => 'required|string',
                'jenis_kelamin' => 'required|in:laki-laki,perempuan',
                'tanggal_lahir' => 'required|date',
                'kebangsaan' => 'required|string|max:100',
                'pekerjaan' => 'required|string|max:100',
                'status_nikah' => 'required|string',
                'nik' => 'required|string|max:16',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Ambil pengguna dari database
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Update data email di tabel user
            $user->email = $request->email;
            if (!$user->save()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to save user data',
                ], 500);
            }

            // Memeriksa apakah data penduduk sudah ada
            $penduduk = Penduduk::where('id_user', $userId)->first();

            if ($penduduk) {
                // Update data penduduk jika sudah ada
                $penduduk->update([
                    'nama_lengkap' => $request->nama_lengkap,
                    'email' => $request->email,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'kebangsaan' => $request->kebangsaan,
                    'pekerjaan' => $request->pekerjaan,
                    'status_nikah' => $request->status_nikah,
                    'nik' => $request->nik,
                ]);
            } else {
                // Buat data penduduk baru jika belum ada
                $penduduk = Penduduk::create([
                    'id_user' => $userId,
                    'nama_lengkap' => $request->nama_lengkap,
                    'email' => $request->email,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'kebangsaan' => $request->kebangsaan,
                    'pekerjaan' => $request->pekerjaan,
                    'status_nikah' => $request->status_nikah,
                    'nik' => $request->nik,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'User data updated successfully',
                'user' => $user,
                'penduduk' => $penduduk
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getUserFromToken(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user) {

                $penduduk = Penduduk::where('id_user', $user->id)->first();

                return response()->json([
                    'status' => true,
                    'message' => 'User fetched successfully',
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'penduduk' => $penduduk,
                    ]
                ], 200);
            } else {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch user data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
