<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::whereIn('type', ['admin', 'manager'])->latest()->paginate(10);
        return view('admin.staff.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.staff.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'type'     => 'required|in:admin,manager',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'type'     => $request->type,
            'subscription_tier' => null, // Admins/Managers don't have tiers
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff account created successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting the main seeder admin if needed, but for now just general check
        $user->delete();
        return redirect()->route('admin.staff.index')->with('success', 'Admin account removed successfully.');
    }
}
