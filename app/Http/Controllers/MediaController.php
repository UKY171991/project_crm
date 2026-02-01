<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function storeImage(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB
        ]);

        return $this->handleUpload($request, $project, 'image', 'images');
    }

    public function storeVideo(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|mimes:mp4,mov,qt|max:102400', // 100MB
        ]);

        return $this->handleUpload($request, $project, 'video', 'videos');
    }

    private function handleUpload($request, $project, $type, $folderName)
    {
        $file = $request->file('file');
        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        
        // Path: public/projects/{id}/{folderName}/
        $path = $file->storeAs(
            "public/projects/{$project->id}/{$folderName}", 
            $fileName
        );

        $media = MediaFile::create([
            'project_id' => $project->id,
            'file_type' => $type,
            'file_path' => $path,
            'file_name' => $fileName,
            'size_kb' => round($file->getSize() / 1024),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true, 
            'path' => Storage::url($path),
            'id' => $media->id
        ]);
    }
}
