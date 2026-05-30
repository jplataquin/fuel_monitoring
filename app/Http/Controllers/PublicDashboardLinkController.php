<?php

namespace App\Http\Controllers;

use App\Models\PublicDashboardLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PublicDashboardLinkController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403);
        }

        $links = PublicDashboardLink::with('creator')->latest()->get();
        return view('public-dashboard-links.index', compact('links'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        PublicDashboardLink::create([
            'slug' => Str::random(32),
            'name' => $request->name,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('public-dashboard-links.index')->with('status', 'Public dashboard link generated successfully.');
    }

    public function toggleStatus(PublicDashboardLink $link)
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403);
        }

        $link->update([
            'is_active' => !$link->is_active,
        ]);

        return redirect()->route('public-dashboard-links.index')->with('status', 'Link status updated.');
    }

    public function destroy(PublicDashboardLink $link)
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403);
        }

        $link->delete();

        return redirect()->route('public-dashboard-links.index')->with('status', 'Public dashboard link deleted.');
    }
}
