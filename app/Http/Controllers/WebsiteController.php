<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebsiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Website::query();

        if ($user->hasRole('master')) {
            // See all
        } elseif ($user->hasRole('admin')) {
            // See all for now, or filter by creator if needed
        } elseif ($user->hasRole('client')) {
            // See own websites
            $query->where('client_id', $user->clientProfile->id ?? 0);
        } else {
            // Other users see all or none? Let's say all for now if they have access to the route
        }

        $websites = $query->with('client')->latest()->get();

        return view('websites.index', compact('websites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::orderBy('company_name')->get();
        return view('websites.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'domain_name' => 'nullable|string|max:255',
            'domain_expiry_date' => 'nullable|date',
            'ssl_expiry_date' => 'nullable|date',
            'hosting_provider' => 'nullable|string|max:255',
            'server_ip' => 'nullable|string|max:45',
            'php_version' => 'nullable|string|max:20',
            'cms' => 'nullable|string|max:50',
            'admin_url' => 'nullable|url|max:255',
            'admin_username' => 'nullable|string|max:100',
            'admin_password' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Website::create($validated);

        return redirect()->route('websites.index')->with('success', 'Website added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Website $website)
    {
        return view('websites.show', compact('website'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Website $website)
    {
        $clients = Client::orderBy('company_name')->get();
        return view('websites.edit', compact('website', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Website $website)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'domain_name' => 'nullable|string|max:255',
            'domain_expiry_date' => 'nullable|date',
            'ssl_expiry_date' => 'nullable|date',
            'hosting_provider' => 'nullable|string|max:255',
            'server_ip' => 'nullable|string|max:45',
            'php_version' => 'nullable|string|max:20',
            'cms' => 'nullable|string|max:50',
            'admin_url' => 'nullable|url|max:255',
            'admin_username' => 'nullable|string|max:100',
            'admin_password' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $website->update($validated);

        return redirect()->route('websites.index')->with('success', 'Website updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Website $website)
    {
        $website->delete();
        return redirect()->route('websites.index')->with('success', 'Website deleted successfully.');
    }
}
