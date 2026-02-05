<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Material;
use App\Models\Stage;
use App\Models\Unit;
use Auth;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::guard('teacher')->user();

        $stages = Stage::whereHas('teachers', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->whereHas('schools', function ($query) use ($teacher) {
            $query->where('school_id', $teacher->school_id);
        })->with('materials')->get();
        return view('pages.teacher.teacher', compact('stages'));
    }

    public function showMaterials($stageId)
    {
        $teacher = Auth::guard('teacher')->user();

        $stage = Stage::where('id', $stageId)
            ->whereHas('teachers', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->whereHas('schools', function ($query) use ($teacher) {
                $query->where('school_id', $teacher->school_id);
            })
            ->with([
                'materials' => function ($query) use ($teacher) {
                    $query->whereHas('schools', function ($q) use ($teacher) {
                        $q->where('schools.id', $teacher->school_id);
                    });
                }
            ])
            ->firstOrFail();

        return view('pages.teacher.teacherTheme', compact('stage'));
    }

    // public function showUnits($materialId)
    // {
    //     // Fetch the material and filter units and chapters correctly
    //     $material = Material::with([
    //         'units' => function ($unitQuery) use ($materialId) {
    //             $unitQuery->with([
    //                 'chapters' => function ($chapterQuery) use ($materialId) {
    //                     $chapterQuery->where('material_id', $materialId);  // Filter by material_id
    //                 }
    //             ]);
    //         }
    //     ])->findOrFail($materialId);

    //     return view('pages.teacher.units', compact('material'));
    // }
    public function showUnits($materialId)
    {
        $teacher = Auth::guard('teacher')->user();

        $material = Material::where('id', $materialId)
            ->whereHas('schools', function ($q) use ($teacher) {
                $q->where('schools.id', $teacher->school_id);
            })
            ->whereHas('stage.teachers', function ($q) use ($teacher) {
                $q->where('teachers.id', $teacher->id);
            })
            ->with(['units.chapters'])
            ->firstOrFail();

        return view('pages.teacher.units', compact('material'));
    }


    public function showLessons($chapterId)
    {
        $teacher = Auth::guard('teacher')->user();

        $chapter = Chapter::with(['unit.material'])
            ->whereHas('unit.material.schools', function ($q) use ($teacher) {
                $q->where('schools.id', $teacher->school_id);
            })
            ->whereHas('unit.material.stage.teachers', function ($q) use ($teacher) {
                $q->where('teachers.id', $teacher->id);
            })
            ->with('lessons')
            ->findOrFail($chapterId);
        
        

        return view('pages.teacher.lessons', compact('chapter'));
    }
    public function changeName()
    {
        $validatedData = request()->validate([
            'username' => 'required|string|min:1',
        ]);

        $teacher = Auth::guard('teacher')->user();
        $teacher->username = request()->username;
        $teacher->save();
        return redirect()->back();
    }
}
