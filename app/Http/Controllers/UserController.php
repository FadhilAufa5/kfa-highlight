<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::latest()->paginate(10);

        return Inertia::render('users/index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('users/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        try {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            \Log::error('User creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        try {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'] ?? $user->is_active,
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            \Log::error('User update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->withErrors(['error' => 'You cannot delete your own account!']);
        }

        try {
            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('User deletion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }
}
