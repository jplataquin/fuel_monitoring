<?php

namespace App\Http\Controllers;

use App\Models\SubAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubAccountNoteController extends Controller
{
    /**
     * Display a listing of the notes for a sub account.
     */
    public function index(SubAccount $sub_account): JsonResponse
    {
        $notes = $sub_account->notes()
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json($notes);
    }

    /**
     * Store a newly created note in storage.
     */
    public function store(Request $request, SubAccount $sub_account): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $note = $sub_account->notes()->create([
            'user_id' => $request->user()->id,
            'note' => $validated['note'],
        ]);

        return response()->json($note->load('user:id,name'), 201);
    }
}
