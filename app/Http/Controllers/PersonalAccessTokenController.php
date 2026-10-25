<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class PersonalAccessTokenController extends Controller
{
    /**
     * Display a listing of personal access tokens.
     */
    public function index(): View
    {
        $tokens = PersonalAccessToken::with('tokenable')
            ->latest()
            ->paginate(15);

        $users = User::orderBy('name')->get();

        return view('settings.mcp-tokens.index', compact('tokens', 'users'));
    }

    /**
     * Generate a new personal access token.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $user = $request->filled('user_id')
            ? User::findOrFail($request->user_id)
            : $request->user();

        $token = $user->createToken($request->name);

        return redirect()->route('mcp-tokens.index')
            ->with('plainTextToken', $token->plainTextToken)
            ->with('tokenName', $request->name)
            ->with('status', "Personal access token '{$request->name}' generated successfully.");
    }

    /**
     * Revoke (delete) a personal access token.
     */
    public function destroy(PersonalAccessToken $token): RedirectResponse
    {
        $tokenName = $token->name;
        $token->delete();

        return redirect()->route('mcp-tokens.index')
            ->with('status', "Token '{$tokenName}' was revoked successfully.");
    }
}
