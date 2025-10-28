@extends('layouts.app')

@section('title', $course->title . ' - Course Management')

@section('content')
<div class="mb-4">
    <a href="{{ route('courses.index') }}" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Courses
    </a>
    
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h2><i class="fas fa-graduation-cap"></i> {{ $course->title }}</h2>
            <p class="text-muted">
                <i class="fas fa-tag"></i> {{ $course->category }} | 
                <i class="fas fa-calendar"></i> Created: {{ $course->created_at->format('M d, Y') }}
            </p>
        </div>
        <form action="{{ route('courses.destroy', $course) }}" method="POST" 
              onsubmit="return confirm('Are you sure you want to delete this course?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete Course
            </button>
        </form>
    </div>
</div>

<!-- Course Description -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Course Description</h5>
    </div>
    <div class="card-body">
        <p>{{ $course->description }}</p>
        
        @php
            $featurePath = storage_path('app/public/' . $course->feature_video);
        @endphp
                    @if($course->feature_video && \Illuminate\Support\Facades\File::exists($featurePath))
            <div class="mt-3">
                <h6>Feature Video:</h6>
                <video class="w-100 plyr" style="max-height: 400px; border-radius: 10px;" controls>
                    <source src="{{ asset('storage/' . $course->feature_video) }}" type="{{ \Illuminate\Support\Facades\File::mimeType($featurePath) ?? 'video/mp4' }}">
                    Your browser does not support the video tag.
                </video>
            </div>
        @endif
    </div>
</div>

<!-- Course Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary">{{ $course->modules->count() }}</h3>
                <p class="mb-0"><i class="fas fa-th-large"></i> Total Modules</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success">{{ $course->modules->sum(fn($m) => $m->contents->count()) }}</h3>
                <p class="mb-0"><i class="fas fa-file-alt"></i> Total Contents</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info">
                    {{ $course->modules->flatMap(fn($m) => $m->contents)->groupBy('type')->count() }}
                </h3>
                <p class="mb-0"><i class="fas fa-layer-group"></i> Content Types</p>
            </div>
        </div>
    </div>
</div>

<!-- Modules and Contents -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-list"></i> Course Modules & Contents</h5>
    </div>
    <div class="card-body">
        @forelse($course->modules as $moduleIndex => $module)
            <div class="module-card mb-4">
                <div class="module-header">
                    <h5 class="mb-1">
                        <i class="fas fa-cube"></i> Module {{ $moduleIndex + 1 }}: {{ $module->title }}
                    </h5>
                    @if($module->description)
                        <p class="mb-0 mt-2 opacity-75">{{ $module->description }}</p>
                    @endif
                </div>
                
                <div class="nested-content">
                    @forelse($module->contents as $contentIndex => $content)
                        <div class="content-card mb-3">
                            <div class="content-header d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-file"></i> Content {{ $contentIndex + 1 }}: {{ $content->title }}
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-{{ getContentIcon($content->type) }}"></i> {{ ucfirst($content->type) }}
                                </span>
                            </div>
                            
                            <div class="mt-3">
                                @switch($content->type)
                                    @case('text')
                                        <p>{{ $content->content }}</p>
                                        @break
                                    
                                    @case('image')
                                        @if($content->file_path)
                                            <img src="{{ asset('storage/' . $content->file_path) }}" 
                                                 alt="{{ $content->title }}" 
                                                 class="img-fluid rounded mb-2"
                                                 style="max-height: 300px;">
                                        @endif
                                        @if($content->content)
                                            <p class="mt-2">{{ $content->content }}</p>
                                        @endif
                                        @break
                                    
                                    @case('video')
                                        @if($content->file_path)
                                            @php $contentPath = storage_path('app/public/' . $content->file_path); @endphp
                                            @if(\Illuminate\Support\Facades\File::exists($contentPath))
                                                    <video class="w-100 rounded mb-2 plyr" style="max-height: 400px;" controls>
                                                        <source src="{{ asset('storage/' . $content->file_path) }}" type="{{ \Illuminate\Support\Facades\File::mimeType($contentPath) ?? 'video/mp4' }}">
                                                        Your browser does not support the video tag.
                                                    </video>
                                            @endif
                                        @endif
                                        @if($content->content)
                                            <p class="mt-2">{{ $content->content }}</p>
                                        @endif
                                        @break
                                    
                                    @case('link')
                                        <a href="{{ $content->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i> Visit Link
                                        </a>
                                        @if($content->content)
                                            <p class="mt-2">{{ $content->content }}</p>
                                        @endif
                                        @break
                                    
                                    @case('document')
                                        @if($content->file_path)
                                            <a href="{{ asset('storage/' . $content->file_path) }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-download"></i> Download Document
                                            </a>
                                        @endif
                                        @if($content->content)
                                            <p class="mt-2">{{ $content->content }}</p>
                                        @endif
                                        @break
                                @endswitch
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No contents in this module.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <p class="text-muted">No modules in this course.</p>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Plyr !== 'undefined') {
                Array.from(document.querySelectorAll('.plyr')).forEach(function(el) {
                    try { new Plyr(el); } catch(e) { console.warn('Plyr init error', e); }
                });
            }
        });
    </script>
@endsection

@php
function getContentIcon($type) {
    return match($type) {
        'text' => 'align-left',
        'image' => 'image',
        'video' => 'video',
        'link' => 'link',
        'document' => 'file-pdf',
        default => 'file'
    };
}
@endphp
