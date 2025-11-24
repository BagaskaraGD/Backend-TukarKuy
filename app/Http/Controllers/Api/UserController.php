<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $User = User::latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'List Data User',
            'data'    => $User
        ], 200);
    }
    public function show(string $id)
    {
        $User = User::find($id);

        if (!$User) {
            return response()->json([
                'success' => false,
                'message' => 'User Not Found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Data User',
            'data'    => $User
        ], 200);
    }
    public function update(Request $request, string $id)
    {
        $User = User::find($id);

        if (!$User) {
            return response()->json([
                'success' => false,
                'message' => 'User Not Found',
            ], 404);
        }

        // Check ownership
        if ($User->id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'name'        => 'required',
            'no_wa' => 'required|string',
            'alamat'       => 'required|string',
            'foto_profil' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($request->hasFile('foto_profil')) {
            // Delete old foto_profil
            if ($User->foto_profil) {
                Storage::disk('public')->delete($User->foto_profil);
            }
            $User->foto_profil = $request->file('foto_profil')->store('User', 'public');
        }

        $User->update([
            'name'        => $request->name,
            'no_wa' => $request->no_wa,
            'alamat'       => $request->alamat,
            'foto_profil' => $User->foto_profil
            
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User Updated Successfully',
            'data'    => $User
        ], 200);
    }
}