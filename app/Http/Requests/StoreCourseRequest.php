<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'feature_video' => 'nullable|file|mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo,video/x-flv,video/webm|max:102400', // Max 100MB
            
            // Module validation
            'modules' => 'required|array|min:1',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            
            // Content validation
            'modules.*.contents' => 'required|array|min:1',
            'modules.*.contents.*.title' => 'required|string|max:255',
            'modules.*.contents.*.type' => 'required|in:text,image,video,link,document',
            'modules.*.contents.*.content' => 'nullable|string',
            'modules.*.contents.*.file' => 'nullable|file|max:51200', // Max 50MB
            'modules.*.contents.*.url' => 'nullable|url',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Course title is required.',
            'description.required' => 'Course description is required.',
            'category.required' => 'Course category is required.',
            'feature_video.mimetypes' => 'Feature video must be a valid video file (mp4, mpeg, mov, avi, flv, webm).',
            'feature_video.max' => 'Feature video must not exceed 100MB.',
            
            'modules.required' => 'At least one module is required.',
            'modules.*.title.required' => 'Module title is required.',
            
            'modules.*.contents.required' => 'Each module must have at least one content item.',
            'modules.*.contents.*.title.required' => 'Content title is required.',
            'modules.*.contents.*.type.required' => 'Content type is required.',
            'modules.*.contents.*.type.in' => 'Content type must be one of: text, image, video, link, document.',
            'modules.*.contents.*.file.max' => 'File size must not exceed 50MB.',
            'modules.*.contents.*.url.url' => 'Please enter a valid URL.',
        ];
    }
}
