@extends('layouts.app')

@section('title', 'All Courses - Course Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-book"></i> All Courses</h2>
    <a href="{{ route('courses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Course
    </a>
</div>

@if($courses->isEmpty())
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> No courses found. <a href="{{ route('courses.create') }}">Create your first course</a>
    </div>
@else
    <div class="row">
        @foreach($courses as $course)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    @php
                        $featurePath = storage_path('app/public/' . $course->feature_video);
                    @endphp
                    @if($course->feature_video && \Illuminate\Support\Facades\File::exists($featurePath))
                        <video class="card-img-top plyr" style="max-height: 200px; object-fit: cover;" controls>
                            <source src="{{ asset('storage/' . $course->feature_video) }}" type="{{ \Illuminate\Support\Facades\File::mimeType($featurePath) ?? 'video/mp4' }}">
                        </video>
                    @else
                        <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-graduation-cap fa-4x text-white"></i>
                        </div>
                    @endif
                    
                    <div class="card-body">
                        <h5 class="card-title">{{ $course->title }}</h5>
                        <p class="card-text text-muted">
                            <small><i class="fas fa-tag"></i> {{ $course->category }}</small>
                        </p>
                        <p class="card-text">{{ Str::limit($course->description, 100) }}</p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="badge bg-primary">
                                    <i class="fas fa-th"></i> {{ $course->modules->count() }} Modules
                                </span>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-file-alt"></i> {{ $course->modules->sum(fn($m) => $m->contents->count()) }} Contents
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <form action="{{ route('courses.destroy', $course) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this course?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // initialize Plyr for any video elements on this page
            if (typeof Plyr !== 'undefined') {
                Array.from(document.querySelectorAll('.plyr')).forEach(function(el) {
                    try { new Plyr(el); } catch(e) { console.warn('Plyr init error', e); }
                });
            }
        });
    </script>
@endif
@endsection
