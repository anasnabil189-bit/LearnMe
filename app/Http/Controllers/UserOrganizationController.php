<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganizationCode;

class UserOrganizationController extends Controller
{
    /**
     * Join an organization via an invite code.
     */
    public function join(Request $request)
    {
        $request->validate([
            'organization_code' => 'required|string|max:255',
        ]);

        $codeStr = $request->input('organization_code');
        $user = auth()->user();

        // 1. Find the code
        $orgCode = OrganizationCode::where('code', $codeStr)->first();

        if (!$orgCode) {
            return redirect()->back()->with('error', 'Invalid organization code.');
        }

        // 1.5 Check if code is active
        if (!$orgCode->is_active) {
            return redirect()->back()->with('error', 'This invite code has been deactivated by the administrator.');
        }

        // 2. Check expiration
        if ($orgCode->expires_at && $orgCode->expires_at->isPast()) {
            return redirect()->back()->with('error', 'This organization code has expired.');
        }

        // 3. Check usage limits
        if ($orgCode->usage_limit !== null && $orgCode->used_count >= $orgCode->usage_limit) {
            return redirect()->back()->with('error', 'This organization code has reached its usage limit.');
        }

        $organization = $orgCode->organization;

        // 4. Domain Verification
        if ($organization->allowed_domains) {
            $userEmail = $user->email;
            $userDomain = substr(strrchr($userEmail, "@"), 1);
            $allowedDomains = array_map('trim', explode(',', $organization->allowed_domains));
            
            if (!in_array($userDomain, $allowedDomains)) {
                $domainList = implode(', ', $allowedDomains);
                return redirect()->back()->with('error', "هذا الكود مخصص لطلاب نطاقات البريد التالية: {$domainList}. يرجى التسجيل ببريدك الرسمي.");
            }
        }

        // 5. Check if user already joined this organization
        if ($user->organizations()->where('organization_id', $organization->id)->exists()) {
            return redirect()->back()->with('info', "You are already a member of {$organization->name}.");
        }

        // 6. Check organization max users limit
        if ($organization->max_users !== null) {
            $currentUsers = $organization->users()->count();
            if ($currentUsers >= $organization->max_users) {
                return redirect()->back()->with('error', "{$organization->name} has reached its maximum user capacity.");
            }
        }

        // Attach user
        $user->organizations()->attach($organization->id, [
            'role' => 'student',
            'joined_at' => now(),
        ]);

        // Increment usage count
        $orgCode->increment('used_count');

        return redirect()->back()->with('success', "You have successfully joined {$organization->name}!");
    }
}
