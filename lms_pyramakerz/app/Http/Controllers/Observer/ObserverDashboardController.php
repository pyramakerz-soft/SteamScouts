<?php

namespace App\Http\Controllers\Observer;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\LessonResource;
use App\Models\Material;
use App\Models\MaterialResource;
use App\Models\Observation;
use App\Models\ObservationHeader;
use App\Models\ObservationHistory;
use App\Models\ObservationQuestion;
use App\Models\ObservationTemplate;
use App\Models\Observer;
use App\Models\School;
use App\Models\Stage;
use App\Models\Teacher;
use App\Models\TeacherResource;
use App\Models\Unit;
use Auth;
use Illuminate\Http\Request;
use DB;
use Imagick;

class ObserverDashboardController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());
        $observer = Auth::guard('observer')->user();
        // dd($observer);
        $teachers = Teacher::with('school')->withTrashed()->whereNull('alias_id')->get();
        $schools = School::all();
        $cities = School::distinct()->whereNotNull('city')->pluck('city');
        $observers = Observer::all();
        $stages = Stage::all();
        $templates = ObservationTemplate::all();
        // $query = Observation::where('observer_id', $observer->id);
        // $query = Observation::with(['school', 'subject', 'stage', 'teacher', 'observer', 'histories.observation_question'])
        //     ->where('observer_id', $observer->id);

        $query = Observation::with([
            'school',
            'subject',
            'stage',
            'observer',
            'histories.observation_question',
            'teacher' => function ($q) {
                $q->withTrashed();
            }
        ])->where('observer_id', $observer->id);

        if ($request->filled('teacher_id')) {
            $teacherIds = Teacher::withTrashed()->where('id', $request->teacher_id)
                ->orWhere('alias_id', $request->teacher_id)->pluck('id');
            $query->whereIn('teacher_id', $teacherIds);
        }
        if ($request->filled('school_id')) {
            $schoolIds = $request->school_id;
            if (in_array('all', $schoolIds)) {
            } else {
                $query->whereIn('school_id', $schoolIds);
            }
        }
        if ($request->filled('observer_id')) {
            $query->where('observer_id', $request->observer_id);
        }
        if ($request->filled('template_id')) {
            $query->where('observation_template_id', $request->template_id);
        }
        if ($request->filled('city')) {
            $query->whereHas('school', function ($query) use ($request) {
                $query->whereIn('city', $request->city);
            });
        }
        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }
        if ($request->filled('lesson_segment_filter')) {
            $query->whereJsonContains('lesson_segment', $request->lesson_segment_filter);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('activity', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('activity', '<=', $request->to_date);
        }

        if ($request->has('include_comments')) {
            $query->whereNotNull('note');
        }

        $observations = $query->get();


        return view('pages.observer.observer', compact('teachers', 'stages', 'cities', 'observers', 'schools', 'observer', 'observations', 'templates'));
    }
    public function exportObservations()
    {
        $observer = Auth::guard('observer')->user();

        $observations = Observation::with(['school', 'subject', 'stage', 'teacher', 'observer', 'histories.observation_question'])
            ->where('observer_id', $observer->id)
            ->get()
            ->map(function ($observation) {
                return [
                    'id' => $observation->id,
                    'name' => $observation->name,
                    'teacher_name' => $observation->teacher->username ?? 'N/A',
                    'coteacher_name' => $observation->coteacher_name ?? 'N/A',
                    'school' => $observation->school->name ?? 'N/A',
                    'city' => $observation->school->city ?? 'N/A',
                    'subject' => $observation->subject->title ?? $observation->subject->name ?? 'N/A',
                    'stage' => $observation->stage->name ?? 'N/A',
                    'activity' => $observation->activity,
                    'note' => $observation->note,
                    'questions' => $observation->histories->map(function ($history) {
                        $question = optional($history->observation_question);
                        return [
                            'name' => $question->question ?? 'N/A',
                            'avg_rating' => $history->rate ?? 0,
                            'max_rating' => $question->max_rate ?? 'N/A'
                        ];
                    })->toArray()
                ];
            });

        return response()->json($observations);
    }
    public function exportSingleObservation($id)
    {
        $observation = Observation::with([
            'school',
            'subject',
            'stage',
            'teacher',
            'observer',
            'histories.observation_question'
        ])->find($id);

        if (!$observation) {
            return response()->json(['error' => 'Observation not found'], 404);
        }

        return response()->json([
            'id' => $observation->id,
            'name' => $observation->name,
            'teacher_name' => optional($observation->teacher)->username ?? 'N/A',
            'coteacher_name' => $observation->coteacher_name ?? 'N/A',
            'school' => optional($observation->school)->name ?? 'N/A',
            'city' => optional($observation->school)->city ?? 'N/A',
            'subject' => optional($observation->subject)->title ?? optional($observation->subject)->name ?? 'N/A',
            'stage' => optional($observation->stage)->name ?? 'N/A',
            'activity' => $observation->activity,
            'note' => $observation->note,
            'questions' => $observation->histories->map(function ($history) {
                return [
                    'name' => optional($history->observation_question)->question ?? 'N/A',
                    'avg_rating' => $history->rate ?? 0,
                    'max_rating' => optional($history->observation_question)->max_rate ?? 'N/A'
                ];
            })->toArray()
        ]);
    }

    public function createObservation(Request $request)
    {
        $observer = Auth::guard('observer')->user();

        // Load templates with headers + questions
        $templates = ObservationTemplate::with('headers.questions')->get();

        if ($templates->isEmpty()) {
            return back()->with('error', 'No observation templates found. Please contact admin.');
        }

        // Force-select template → default to first
        $selectedTemplateId = $request->get('template_id', $templates->first()->id);

        $selectedTemplate = $templates->where('id', $selectedTemplateId)->first();

        // Data for right-side form
        $teachers = Teacher::with('school')->whereNull('alias_id')->get();
        $cities = School::distinct()->whereNotNull('city')->pluck('city');
        $grades = Stage::all();

        // Only headers belonging to the selected template
        $headers = $selectedTemplate ? $selectedTemplate->headers : collect();

        return view('pages.observer.create_observation', compact(
            'observer',
            'grades',
            'cities',
            'teachers',
            'templates',
            'selectedTemplateId',
            'headers'
        ));
    }

    public function getSchool($teacherId)
    {
        $schools = [];
        $teacher = Teacher::find($teacherId);
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }
        $school = School::find($teacher->school_id);
        $schools[] = $school;

        $teachers = Teacher::where('alias_id', $teacherId)->get();
        foreach ($teachers as $teacher) {
            $school = School::find($teacher->school_id);
            $schools[] = $school;
        }

        if (!$schools) {
            return response()->json(['error' => 'School not found'], 404);
        }
        // dd($schools);
        return response()->json($schools);
        // return response()->json($school);
    }
    public function getCoteachers($school_id)
    {
        $coteachers = Teacher::where('school_id', $school_id)

            ->get();

        return response()->json($coteachers);
    }
    public function getStages($teacherId)
    {
        $teacher = Teacher::find($teacherId);
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }
        $stages = DB::table('teacher_stage')
            ->where('teacher_id', $teacherId)
            ->join('stages', 'teacher_stage.stage_id', '=', 'stages.id')
            ->select('stages.id', 'stages.name')
            ->get();

        return response()->json($stages);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'observation_name' => 'required|string|max:255',
            'observer_id' => 'required|integer|exists:observers,id',
            'teacher_id' => 'required|integer|exists:teachers,id',
            'coteacher_id' => 'nullable|integer|exists:teachers,id',
            'grade_id' => 'required|integer|exists:stages,id',
            'school_id' => 'required|integer|exists:schools,id',
            'template_id' => 'required|integer|exists:observation_templates,id',
            'date' => 'required|date',
            'lesson_segment' => 'required|array',
            'lesson_segment.*' => 'string',
            'note' => 'nullable|string',
        ]);


        // ✅ Correct teacher lookup – prevents wrong "OR" issue
        $teacher = Teacher::where(function ($q) use ($request) {
            $q->where('id', $request->teacher_id)
                ->orWhere('alias_id', $request->teacher_id);
        })
            ->where('school_id', $request->school_id)
            ->firstOrFail();

        $coteacher = Teacher::find($request->coteacher_id);

        // ✅ Create Observation
        $observation = Observation::create([
            'name' => $request->observation_name,
            'observer_id' => $request->observer_id,
            'teacher_id' => $teacher->id,
            'teacher_name' => $teacher->name,
            'coteacher_id' => $coteacher->id ?? null,
            'coteacher_name' => $coteacher->name ?? null,
            'stage_id' => $request->grade_id,
            'school_id' => $request->school_id,
            'observation_template_id' => $request->template_id,
            'activity' => $request->date,
            'note' => $request->note,
            'lesson_segment' => json_encode($request->lesson_segment),
        ]);

        // ✅ Insert question answers
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'question-')) {
                $questionId = (int) str_replace('question-', '', $key);

                ObservationHistory::create([
                    'observation_id' => $observation->id,
                    'question_id' => $questionId,
                    'rate' => $value,
                ]);
            }
        }

        return redirect()
            ->route('observer.dashboard')
            ->with('success', 'Observation created successfully!');
    }

    public function destroy(string $id)
    {
        $observation = Observation::findOrFail($id);
        $observation->delete();
        return redirect()->route('observer.dashboard')->with('success', 'Observation deleted successfully.');
    }
    public function view(string $id)
    {
        $observer = Auth::guard('observer')->user();

        $observation = Observation::with([
            'template.headers.questions'  // Load template → headers → questions
        ])->findOrFail($id);

        // Get the answers for this observation, keyed by question_id for easy access
        $answers = ObservationHistory::where('observation_id', $id)
            ->get()
            ->keyBy('question_id');

        $template = $observation->template;
        $headers = $template ? $template->headers : collect();

        return view('pages.observer.view_observation', compact(
            'observer',
            'observation',
            'headers',
            'answers'
        ));
    }

    public function report(Request $request)
    {
        $Obs = Observation::whereNull('observation_template_id')->get();
        foreach ($Obs as $ob) {
            $ob->observation_template_id = 1;
            $ob->save();
        }

        $observer = Auth::guard('observer')->user();
        $teachers = Teacher::with('school')->whereNull('alias_id')->get();
        $schools = School::all();
        $observers = Observer::all();
        $stages = Stage::all();
        $headers = ObservationHeader::all();

        $templates = ObservationTemplate::with('headers.questions')->get();
        if ($templates->isEmpty()) {
            return back()->with('error', 'No templates found. Please create a template first.');
        }

        $selectedTemplateId = $request->get('template_id', $templates->first()->id);
        $selectedTemplate = ObservationTemplate::with('headers.questions')->findOrFail($selectedTemplateId);

        foreach ($selectedTemplate->headers as $header) {
            if (!isset($data[$header->id])) {
                $data[$header->id] = [
                    'header_id' => $header->id,
                    'name' => $header->header,
                    'questions' => [],
                ];
            }

            $headerQuestions = [];
            foreach ($header->questions as $question) {
                if (!isset($headerQuestions[$question->id])) {
                    $headerQuestions[$question->id] = [
                        'question_id' => $question->id,
                        'name' => $question->question,
                        'avg_rating' => 0,
                        'max_rating' => $question->max_rate,
                    ];
                }
            }
            $data[$header->id]['questions'] = $headerQuestions;
        }
        // dd($data);

        $query = Observation::where("observation_template_id", $selectedTemplateId)->where('observer_id', $observer->id);
        // if ($query->get()->count() == 0) {
        //     return redirect()->back()->with('error', 'No observations found for this observer');
        // }
        if ($request->filled('teacher_id')) {
            $teacherIds = Teacher::where('id', $request->teacher_id)
                ->orWhere('alias_id', $request->teacher_id)->pluck('id');
            $query->whereIn('teacher_id', $teacherIds);
        }

        if ($request->filled('school_id')) {
            $schoolIds = $request->school_id;
            if (in_array('all', $schoolIds)) {
            } else {
                $query->whereIn('school_id', $schoolIds);
            }
        }
        if ($request->filled('observer_id')) {
            $query->where('observer_id', $request->observer_id);
        }
        if ($request->filled('city')) {
            $query->whereHas('school', function ($query) use ($request) {
                $query->whereIn('city', $request->city);
            });
        }
        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }
        if ($request->filled('lesson_segment_filter')) {
            $query->whereJsonContains('lesson_segment', $request->lesson_segment_filter);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('activity', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('activity', '<=', $request->to_date);
        }

        if ($request->has('include_comments')) {
            $query->whereNotNull('note');
        }
        // if ($query->get()->count() == 0) {
        //     return redirect()->back()->with('error', 'No observations found for set filters');
        // }

        $observations = $query->pluck('id');

        $obsCount = $observations->count();


        // dd($obsCount);
        $histories = ObservationHistory::whereIn('observation_id', $observations)->get();
        // dd($history);
        foreach ($histories as $history) {
            $headerId = ObservationQuestion::find($history->question_id)->observation_header_id;
            $data[$headerId]['questions'][$history->question_id]['avg_rating'] += $history->rate;
        }
        if (isset($data)) {
            foreach ($data as $header) {
                foreach ($header['questions'] as $question) {
                    $total = $data[$header['header_id']]['questions'][$question['question_id']]['avg_rating'];
                    $data[$header['header_id']]['questions'][$question['question_id']]['avg_rating'] =
                        $obsCount > 0 ? round($total / $obsCount, 2) : 0;
                }
            }
            $overallComments = Observation::whereIn('id', $observations)
                ->whereNotNull('note')
                ->with('teacher')
                ->get(['note', 'teacher_id']);

            $cities = School::distinct()->whereNotNull('city')->pluck('city');
            return view('pages.observer.observation_report', compact('stages', 'cities', 'teachers', 'observer', 'observers', 'schools', 'headers', 'data', 'overallComments', 'templates', 'selectedTemplateId'));
        } else {
            $cities = School::distinct()->whereNotNull('city')->pluck('city');
            return view('pages.observer.observation_report', compact('stages', 'cities', 'teachers', 'observer', 'observers', 'schools', 'headers', 'templates', 'selectedTemplateId'));
        }
    }

    public function showTeacherResources(Request $request)
    {
        $selectedGrade = $request->get('grade');
        $selectedInstructor = $request->get('instructor');
        $selectedType = $request->get('type');
        $selectedTheme = $request->get('theme');

        $stages = Stage::all();
        $instructors = Teacher::with("school")->orderBy('name')->get();
        $themes = Material::orderBy('title')->get();

        $resourcesQuery = TeacherResource::with(['stage', 'material', 'teacher']);

        if ($selectedGrade) {
            $resourcesQuery->where('stage_id', $selectedGrade);
        }

        if ($selectedInstructor) {
            $resourcesQuery->where('teacher_id', $selectedInstructor);
        }

        if ($selectedType) {
            $resourcesQuery->where('type', $selectedType);
        }

        if ($selectedTheme) {
            $resourcesQuery->where('material_id', $selectedTheme);
        }

        $resources = $resourcesQuery->get();

        $groupedResources = $resources
            ->groupBy(fn($res) => $res->stage?->name ?? 'Other')
            ->map(
                fn($stageGroup) =>
                $stageGroup->groupBy(fn($res) => $res->material?->title ?? 'No Theme')
            );

        $types = TeacherResource::select('type')->distinct()->pluck('type');
        return view('pages.observer.teacher_resources', compact(
            'stages',
            'instructors',
            'themes',
            'selectedGrade',
            'selectedInstructor',
            'selectedType',
            'selectedTheme',
            'groupedResources',
            'types'
        ));
    }

    public function showAdminResources(Request $request)
    {
        $selectedGrade = $request->get('grade');
        $selectedTheme = $request->get('theme');
        $selectedType = $request->get('type');

        // Filters dropdown data
        $stages = Stage::orderBy('name')->get();
        $themes = Material::orderBy('title')->get();
        $types = LessonResource::select('type')->distinct()->pluck('type');

        // ✅ Lesson Resources Query
        $lessonResourcesQuery = LessonResource::with('lesson.chapter.unit.material');

        if ($selectedGrade) {
            $lessonResourcesQuery->whereHas('lesson.chapter.unit.material.stage', function ($q) use ($selectedGrade) {
                $q->where('id', $selectedGrade);
            });
        }

        if ($selectedTheme) {
            $lessonResourcesQuery->whereHas('lesson.chapter.unit.material', function ($q) use ($selectedTheme) {
                $q->where('id', $selectedTheme);
            });
        }

        if ($selectedType) {
            $lessonResourcesQuery->where('type', $selectedType);
        }

        $groupedLessons = $lessonResourcesQuery->get()->groupBy('lesson_id');

        // ✅ Theme Resources Query
        $materialsQuery = Material::with(['resources', 'stage']);

        if ($selectedGrade) {
            $materialsQuery->where('stage_id', $selectedGrade);
        }

        if ($selectedTheme) {
            $materialsQuery->where('id', $selectedTheme);
        }

        $themeStages = $materialsQuery->get()
            ->each(function ($material) use ($selectedType) {
                if ($selectedType) {
                    $material->setRelation(
                        'resources',
                        $material->resources->where('type', $selectedType)
                    );
                }
            })
            ->filter(fn($material) => $material->resources->isNotEmpty())
            ->groupBy(fn($material) => $material->stage->name);

        return view('pages.observer.admin_resources', compact(
            'groupedLessons',
            'themeStages',
            'stages',
            'themes',
            'types',
            'selectedGrade',
            'selectedTheme',
            'selectedType'
        ));
    }
    public function viewResource($id, $type)
    {
        if ($type === "teacher") {
            $resource = TeacherResource::findOrFail($id);
            $file = $resource->file_path;
        } elseif ($type === "lesson") {
            $resource = LessonResource::findOrFail($id);
            $file = $resource->path;
        } elseif ($type === "theme") {
            $resource = MaterialResource::findOrFail($id);
            $file = $resource->path;
        } else {
            abort(404, "Unknown resource type");
        }

        $fullPath = public_path($file);
        if (!file_exists($fullPath)) {
            abort(404, "File not found.");
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $name = pathinfo($file, PATHINFO_FILENAME) . '_' . $resource->id;

        $converted = [];
        $convertedType = null;

        // ---------- Save conversions inside public/converted/... ----------
        $outputDir = public_path('converted/' . $name);
        if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

        // ---------- Cloud Conversion ----------
        $cloudExts = ['pdf', 'ppt', 'pptx', 'doc', 'docx'];
        if (in_array($ext, $cloudExts)) {

            // If already converted -> load
            $existing = glob($outputDir . '/*.png');
            natsort($existing);
            if (count($existing) > 0) {
                foreach ($existing as $img) {
                    // Return as /converted/filename.png
                    $relative = str_replace(public_path(), '', $img);
                    $relative = str_replace('\\', '/', $relative);
                    $relative = ltrim($relative, '/');

                    $converted[] = $relative;
                }
                $convertedType = 'slides';
            } else {
                try {
                    $converter = new \App\Services\CloudConverter();
                    $files = $converter->convertToImages($fullPath, $ext);

                    if (!$files || !is_array($files)) {
                        throw new \RuntimeException("Cloud conversion failed for $file");
                    }

                    usort($files, function ($a, $b) {
                        preg_match('/(\d+)/', $a['FileName'] ?? '', $aMatch);
                        preg_match('/(\d+)/', $b['FileName'] ?? '', $bMatch);
                        return ((int)($aMatch[1] ?? 0)) <=> ((int)($bMatch[1] ?? 0));
                    });

                    foreach ($files as $index => $cloudFile) {
                        if (!isset($cloudFile['FileData'])) continue;

                        $imageData = base64_decode($cloudFile['FileData']);
                        if (!$imageData) continue;

                        $savePath = $outputDir . "/page_" . ($index + 1) . ".png";
                        file_put_contents($savePath, $imageData);

                        $relative = str_replace(public_path(), '', $savePath);
                        $relative = str_replace('\\', '/', $relative);   // convert \ to /
                        $relative = ltrim($relative, '/');               // remove leading slash

                        $converted[] = $relative;
                    }

                    $convertedType = 'slides';
                } catch (\Throwable $e) {
                    dd("Cloud conversion error: " . $e->getMessage());
                }
            }
        }

        // ---------- Direct images and videos ----------
        $directExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'webm'];
        if (in_array($ext, $directExts)) {
            return view('pages.observer.view', compact(
                'resource',
                'file',
                'ext',
                'converted',
                'convertedType'
            ));
        }

        return view('pages.observer.view', compact(
            'resource',
            'file',
            'ext',
            'converted',
            'convertedType'
        ));
    }
}
