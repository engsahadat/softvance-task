<?php

namespace App\Repositories;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class CourseRepository
{
    /**
     * Get all courses with their modules and contents.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithRelations()
    {
        return Course::with(['modules.contents'])->latest()->get();
    }

    /**
     * Find a course by ID with its modules and contents.
     *
     * @param int $id
     * @return Course|null
     */
    public function findWithRelations(int $id): ?Course
    {
        return Course::with(['modules.contents'])->find($id);
    }

    /**
     * Create a new course with modules and contents.
     *
     * @param array $data
     * @return Course
     * @throws Exception
     */
    public function create(array $data): Course
    {
        try {
            DB::beginTransaction();

            // Handle feature video upload
            if (isset($data['feature_video'])) {
                $data['feature_video'] = $data['feature_video']->store('videos/features', 'public');
            }

            // Create course
            $course = Course::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'category' => $data['category'],
                'feature_video' => $data['feature_video'] ?? null,
            ]);

            // Create modules and contents
            if (isset($data['modules'])) {
                foreach ($data['modules'] as $moduleIndex => $moduleData) {
                    $module = $course->modules()->create([
                        'title' => $moduleData['title'],
                        'description' => $moduleData['description'] ?? null,
                        'order' => $moduleIndex,
                    ]);

                    // Create contents for the module
                    if (isset($moduleData['contents'])) {
                        foreach ($moduleData['contents'] as $contentIndex => $contentData) {
                            $filePath = null;

                            // Handle file upload for content
                            if (isset($contentData['file'])) {
                                $filePath = $contentData['file']->store('content_files', 'public');
                            }

                            $module->contents()->create([
                                'title' => $contentData['title'],
                                'type' => $contentData['type'],
                                'content' => $contentData['content'] ?? null,
                                'file_path' => $filePath,
                                'url' => $contentData['url'] ?? null,
                                'order' => $contentIndex,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return $course->load(['modules.contents']);

        } catch (Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded files if transaction fails
            if (isset($data['feature_video']) && is_string($data['feature_video'])) {
                Storage::disk('public')->delete($data['feature_video']);
            }

            throw $e;
        }
    }

    /**
     * Delete a course and its associated files.
     *
     * @param Course $course
     * @return bool
     * @throws Exception
     */
    public function delete(Course $course): bool
    {
        try {
            DB::beginTransaction();

            // Delete feature video
            if ($course->feature_video) {
                Storage::disk('public')->delete($course->feature_video);
            }

            // Delete content files
            foreach ($course->modules as $module) {
                foreach ($module->contents as $content) {
                    if ($content->file_path) {
                        Storage::disk('public')->delete($content->file_path);
                    }
                }
            }

            $course->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
