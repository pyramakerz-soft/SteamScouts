<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Fetch all themes (materials) and units for filters.
        $themes = \App\Models\Material::all();


        // Apply filters based on request.
        $query = Lesson::query()->with('chapter.unit.material');

        if ($request->filled('theme_id')) {
            $units = \App\Models\Unit::where('material_id', $request->theme_id)->get();
            $query->whereHas('chapter.unit.material', function ($q) use ($request) {
                $q->where('id', $request->theme_id);
            });
        } else {
            $units = \App\Models\Unit::all();
        }

        if ($request->filled('unit_id')) {
            $query->whereHas('chapter.unit', function ($q) use ($request) {
                $q->where('id', $request->unit_id);
            });
        }

        $lessons = $query->paginate(30);

        return view('admin.lessons.index', compact('lessons', 'themes', 'units'));
        // $lessons = Lesson::with('chapter.material')->get();
        // return view('admin.lessons.index', compact('lessons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $chapters = Chapter::with('material.stage')->get();
        return view('admin.lessons.create', compact('chapters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'chapter_id' => 'required|exists:chapters,id',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif',
            'is_active' => 'nullable|boolean',
            'file_path' => 'required',
        ]);

        // $file = $request->file('file_path');
        // $isZip = $file->getClientOriginalExtension() === 'zip';
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('lessons', 'public');
        }

        // if ($isZip) {
        //     $filePath = $file->store('ebooks', 'public');

        //     $extractPath = storage_path('app/public/ebooks/' . pathinfo($filePath, PATHINFO_FILENAME));

        //     $zip = new \ZipArchive;
        //     if ($zip->open(storage_path('app/public/' . $filePath)) === TRUE) {
        //         $zip->extractTo($extractPath);
        //         $zip->close();

        //         $filePath = 'ebooks/' . pathinfo($filePath, PATHINFO_FILENAME);

        //         if (file_exists(public_path('storage/' . $filePath . '/index.html'))) {
        //             $lesson = Lesson::create([
        //                 'title' => $request->title,
        //                 'chapter_id' => $request->chapter_id,
        //                 'image' => $imagePath,
        //                 'is_active' => $request->is_active ?? 0,
        //                 'file_path' => $filePath,
        //             ]);
        //             return redirect()->back();
        //         }
        //     } else {
        //         return back()->withErrors(['file_path' => 'Failed to extract the zip file.']);
        //     }
        // } else {
        //     $filePath = $file->store('ebooks', 'public');
        // }


        Lesson::create([
            'title' => $request->title,
            'chapter_id' => $request->chapter_id,
            'image' => $imagePath,
            'is_active' => $request->is_active ?? 0,
            'file_path' => $request->file_path,
        ]);

        return redirect()->route('lessons.index')->with('success', 'Lesson created successfully.');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    public function viewEbook(Lesson $lesson)
    {
        $relativePath = 'storage/' . $lesson->file_path . '/index.html';

        $fileUrl = asset($relativePath);

        \Log::info('Looking for index.html at: ' . $fileUrl);

        if (file_exists(public_path($relativePath))) {
            return redirect($fileUrl);
        } else {
            return abort(404, 'Index file not found.');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lesson = Lesson::findOrFail($id);
        $chapters = Chapter::with('material.stage')->get();
        return view('admin.lessons.edit', compact('lesson', 'chapters'));
    }


    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     $lesson = Lesson::findOrFail($id);

    //     dd($request);
    //     $request->validate([
    //         'title' => 'required|string|max:255',
    //         'chapter_id' => 'required|exists:chapters,id',
    //         'image' => 'required|mimes:jpeg,png,jpg,gif',
    //         'file_path' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,html,txt,zip|max:10240',

    //         'is_active' => 'nullable|boolean',
    //     ]);

    //     if ($request->hasFile('image')) {
    //         $imagePath = $request->file('image')->store('lessons', 'public');
    //         $lesson->image = $imagePath;
    //     }
    //     if ($request->hasFile('file_path')) {
    //         $filePath = $request->file('file_path')->store('ebooks', 'public');
    //         $lesson->file_path = $filePath;
    //     }

    //     $lesson->update([
    //         'title' => $request->title,
    //         'chapter_id' => $request->chapter_id,
    //         'is_active' => $request->is_active ?? 0,
    //     ]);

    //     return redirect()->route('lessons.index')->with('success', 'Lesson updated successfully.');
    // }

    public function update(Request $request, string $id)
    {
        $lesson = Lesson::findOrFail($id);

        // Validate the request. The image is not required on update.
        $request->validate([
            'title' => 'required|string|max:255',
            'chapter_id' => 'required|exists:chapters,id',

            'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048', // Image is not required
            'file_path' => 'required',

            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('lessons', 'public');
            $lesson->image = $imagePath;
        }

        if ($request->hasFile('file_path')) {
            $filePath = $request->file('file_path')->store('ebooks', 'public');
            $lesson->file_path = $filePath;
        }

        if ($request->hasFile('teacher_file_path')) {
            $teacherFilePath = $request->file('teacher_file_path')->store('teacher_ebooks', 'public');
            $lesson->teacher_file_path = $teacherFilePath;
        }

        if($request->hasFile('video_file_path')) {
            $file = $request->file('video_file_path');
            $basePath = public_path('lesson_videos');

            if (!File::exists($basePath)) 
                File::makeDirectory($basePath, 0777, true, true);

            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = 'lesson_videos/' . $fileName;

            $file->move($basePath, $fileName);
            $lesson->video_file_path = $filePath;
        }
        
        $lesson->update([
            'title' => $request->title,
            'chapter_id' => $request->chapter_id,
            'file_path' => $request->file_path,
            'video_url' => $request->video_url,
            'teacher_file_path' => $request->teacher_file_path,
            'is_active' => $request->is_active ?? 0,
        ]);

        return redirect()->route('lessons.index')->with('success', 'Lesson updated successfully.');
    }
    


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->delete();

        return redirect()->route('lessons.index')->with('success', 'Lesson deleted successfully.');
    }
}
