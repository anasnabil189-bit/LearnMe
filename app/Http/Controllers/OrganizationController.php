<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\OrganizationCode;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the organizations.
     */
    public function index()
    {
        $organizations = Organization::withCount('users')->get();
        return view('admin.organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new organization.
     */
    public function create()
    {
        return view('admin.organizations.create');
    }

    /**
     * Store a newly created organization in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organizations',
            'type' => 'required|in:school,company,university,center',
            'allowed_domains' => 'nullable|string|max:1000',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'max_users' => 'nullable|integer|min:1',
            'subscription_plan' => 'nullable|string|max:255',
        ]);

        $organization = Organization::create($request->all());

        return redirect()->route('admin.organizations.show', $organization->id)
                         ->with('success', 'Organization created successfully! You can now generate invite codes.');
    }

    /**
     * Display the specified organization.
     */
    public function show(Organization $organization)
    {
        $organization->load(['users.userLanguages', 'codes']);
        
        $totalXp = $organization->users->sum(function($user) {
            return $user->userLanguages->sum('learning_xp');
        });
        
        $userCount = $organization->users->count();
        $averageXp = $userCount > 0 ? round($totalXp / $userCount) : 0;
        
        $topPerformers = $organization->users()
            ->with('userLanguages')
            ->get()
            ->sortByDesc(function($user) {
                return $user->userLanguages->sum('learning_xp');
            })
            ->take(5);

        return view('admin.organizations.show', compact('organization', 'averageXp', 'topPerformers'));
    }

    /**
     * Remove the specified organization from storage.
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();
        return redirect()->route('admin.organizations.index')->with('success', 'Organization deleted successfully.');
    }

    /**
     * Generate a code for the organization.
     */
    public function generateCode(Request $request, Organization $organization)
    {
        $request->validate([
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        // Generate a random string that is unique
        do {
            $codeStr = strtoupper(Str::random(8));
        } while (OrganizationCode::where('code', $codeStr)->exists());

        $organization->codes()->create([
            'code' => "ORG-{$codeStr}",
            'usage_limit' => $request->usage_limit,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('admin.organizations.show', $organization)->with('success', 'Invite code generated successfully.');
    }

    /**
     * Toggle the active status of an organization code.
     */
    public function toggleCodeStatus(OrganizationCode $code)
    {
        $code->update([
            'is_active' => !$code->is_active
        ]);

        return redirect()->back()->with('success', 'Code status updated successfully.');
    }

    /**
     * Delete an organization code.
     */
    public function destroyCode(OrganizationCode $code)
    {
        $code->delete();
        return redirect()->back()->with('success', 'Invite code deleted successfully.');
    }
}
