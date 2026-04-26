<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $pendingSchools = School::with('teachers') // Admin user is in teachers/linked relation
            ->withCount(['teachers', 'grades', 'students'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $approvedSchools = School::withCount(['teachers', 'grades', 'students'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);

        return view('admin.schools.index', compact('pendingSchools', 'approvedSchools'));
    }

    public function approve(School $school)
    {
        $school->update(['status' => 'approved']);
        return redirect()->route('admin.schools.index')->with('success', 'School approved successfully.');
    }

    public function reject(School $school)
    {
        // Rejection marks as rejected, as per plan.
        $school->update(['status' => 'rejected']);
        
        // Optionally, if the user requested deletion in the conversation, we could delete here. 
        // Plan says: "I plan to mark as rejected, with possibility of deletion".
        // Let's keep it as is for now unless they manual delete it later via standard destroy.
        
        return redirect()->route('admin.schools.index')->with('warning', 'School registration rejected.');
    }

    public function show(School $school)
    {
        $school->load(['teachers', 'grades', 'students']);
        return view('admin.schools.show', compact('school'));
    }

    public function edit(School $school)
    {
        $adminUser = \App\Models\User::where('type', 'school')->where('school_id', $school->id)->first();
        return view('admin.schools.edit', compact('school', 'adminUser'));
    }

    public function update(Request $request, School $school)
    {
        $adminUser = \App\Models\User::where('type', 'school')->where('school_id', $school->id)->first();
        $userId = $adminUser ? $adminUser->id : null;

        $request->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'required|string|unique:schools,code,' . $school->id . '|max:50',
            'email'              => 'required|email|unique:users,email,' . $userId,
            'subscription_start' => 'nullable|date',
            'subscription_end'   => 'nullable|date|after_or_equal:subscription_start',
            'annual_subscription_fee' => 'required|numeric|min:0',
            'student_limit'            => 'required|integer|min:1',
        ], [
            'email.unique' => 'Admin email is already in use. Please choose another.'
        ]);

        $school->update($request->only('name', 'code', 'subscription_start', 'subscription_end', 'annual_subscription_fee', 'student_limit'));

        if ($adminUser) {
            $adminUser->update([
                'name'  => $request->name . ' (Admin)',
                'email' => $request->email,
            ]);
        }

        return redirect()->route('admin.schools.index')->with('success', 'School updated successfully.');
    }

    public function destroy(School $school)
    {
        $school->delete();
        return redirect()->route('admin.schools.index')->with('success', 'School deleted successfully.');
    }
}
