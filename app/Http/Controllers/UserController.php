<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::when(Auth::user()->role !== 'administrator', function ($query) {
            $query->whereIn('role', ['data_logger', 'fuel_man', 'budgeteer']);
        })->get();

        return view('users.index', compact('users'));
    }

    public function createModerator(): View
    {
        return view('users.create', ['role' => 'moderator']);
    }

    public function createDataLogger(): View
    {
        return view('users.create', ['role' => 'data_logger']);
    }

    public function createFuelMan(): View
    {
        return view('users.create', ['role' => 'fuel_man']);
    }

    public function createBudgeteer(): View
    {
        return view('users.create', ['role' => 'budgeteer']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:moderator,data_logger,fuel_man,budgeteer',
            'password' => 'required|string|min:8',
        ]);

        if ($validated['role'] === 'moderator' && Auth::user()->role !== 'administrator') {
            abort(403, 'Only administrators can create moderators.');
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_temporary_password' => true,
        ]);

        return redirect()->route('users.index')->with('status', 'User created successfully with temporary password.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')->with('status', 'User soft deleted successfully.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
            'is_temporary_password' => true,
        ]);

        return redirect()->route('users.index')->with('status', 'Password reset successfully with temporary password.');
    }
}
