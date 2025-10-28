<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Repositories\CourseRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class CourseController extends Controller
{
    protected CourseRepository $courseRepository;

    /**
     * Create a new controller instance.
     *
     * @param CourseRepository $courseRepository
     */
    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Display a listing of courses.
     *
     * @return View
     */
    public function index(): View
    {
        try {
            $courses = $this->courseRepository->getAllWithRelations();
            return view('courses.index', compact('courses'));
        } catch (Exception $e) {
            return view('courses.index', [
                'courses' => collect(),
                'error' => 'Unable to load courses. Please try again later.'
            ]);
        }
    }

    /**
     * Show the form for creating a new course.
     *
     * @return View
     */
    public function create(): View
    {
        return view('courses.create');
    }

    /**
     * Store a newly created course in storage.
     *
     * @param StoreCourseRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCourseRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Handle feature video file
            if ($request->hasFile('feature_video')) {
                $data['feature_video'] = $request->file('feature_video');
            }

            // Handle content files
            if (isset($data['modules'])) {
                foreach ($data['modules'] as $moduleIndex => $module) {
                    if (isset($module['contents'])) {
                        foreach ($module['contents'] as $contentIndex => $content) {
                            if ($request->hasFile("modules.{$moduleIndex}.contents.{$contentIndex}.file")) {
                                $data['modules'][$moduleIndex]['contents'][$contentIndex]['file'] = 
                                    $request->file("modules.{$moduleIndex}.contents.{$contentIndex}.file");
                            }
                        }
                    }
                }
            }

            $course = $this->courseRepository->create($data);

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Course created successfully!',
                    'redirect' => route('courses.show', $course),
                    'course' => $course
                ], 201);
            }

            return redirect()
                ->route('courses.show', $course)
                ->with('success', 'Course created successfully!');

        } catch (Exception $e) {
            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create course: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create course: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified course.
     *
     * @param int $id
     * @return View|RedirectResponse
     */
    public function show(int $id): View|RedirectResponse
    {
        try {
            $course = $this->courseRepository->findWithRelations($id);
            
            if (!$course) {
                return redirect()
                    ->route('courses.index')
                    ->with('error', 'Course not found.');
            }

            return view('courses.show', compact('course'));

        } catch (Exception $e) {
            return redirect()
                ->route('courses.index')
                ->with('error', 'Unable to load course details.');
        }
    }

    /**
     * Remove the specified course from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $course = $this->courseRepository->findWithRelations($id);
            
            if (!$course) {
                return redirect()
                    ->route('courses.index')
                    ->with('error', 'Course not found.');
            }

            $this->courseRepository->delete($course);

            return redirect()
                ->route('courses.index')
                ->with('success', 'Course deleted successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete course: ' . $e->getMessage());
        }
    }
}
