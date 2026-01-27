<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Stage;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function theme()
    {
        $userAuth = auth()->guard('student')->user();

        if ($userAuth) {
            $materials = Material::whereHas('materialSchools', function ($query) use ($userAuth) {
                $query->where('school_id', $userAuth->school_id);
            })->where('stage_id', $userAuth->stage_id)->get();
            return view('pages.student.theme.index', compact('materials', 'userAuth'));
        } else {
            // If the user is not logged in, redirect to login
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
    }

    public function curriculum()
    {
        $userAuth = auth()->guard('student')->user();

        if ($userAuth) {
            $materials = Material::whereHas('materialSchools', function ($query) use ($userAuth) {
                $query->where('school_id', $userAuth->school_id);
            })
                ->where('stage_id', $userAuth->stage_id)
                ->with(['units.chapters.lessons'])
                ->get();

            return view('pages.student.curriculum.index', compact('materials', 'userAuth'));
        }

        return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
    }
    public function index()
    {
        // $student = auth()->guard('student')->user();

        // if ($student) {
        //     $materials = Material::whereHas('materialSchools', function ($query) use ($student) {
        //         $query->where('school_id', $student->school_id);
        //     })
        //         ->where('stage_id', $student->stage_id)
        //         ->get();

        //     // Eager load units for performance
        //     $materials->load('units');

        //     return view('pages.student.dashboard.index', compact('materials', 'student'));
        // } else {
        //     return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        // }
        $userAuth = auth()->guard('student')->user();

        if ($userAuth) {
            $materials = Material::whereHas('materialSchools', function ($query) use ($userAuth) {
                $query->where('school_id', $userAuth->school_id);
            })->where('stage_id', $userAuth->stage_id)->get();
            return view('pages.student.theme.index', compact('materials', 'userAuth'));
        } else {
            // If the user is not logged in, redirect to login
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
    }
}
