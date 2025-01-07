<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $files = File::select(['id', 'filename', 'uploader', 'date', 'category'])->get();

        return response()->json(['files' => $files]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        return response()->json(['message' => 'file create endpoint']);
    }

    /**
     * Get SFTP storage statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStorageStats()
    {
        $disk = Storage::disk('sftp');
        $files = $disk->allFiles('PSTO-SDN-FMS');
        $totalFiles = count($files);

        $totalSize = 0;
        foreach ($files as $file) {
            $totalSize += $disk->size($file);
        }

        // Convert bytes to GB
        $totalSizeGB = round($totalSize / (1024 * 1024 * 1024), 2);

        return response()->json([
            'total_files' => $totalFiles,
            'total_size_gb' => $totalSizeGB,
            'path' => 'PSTO-SDN-FMS'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240',
            'uploader' => 'required|max:255',
            'category' => 'required|max:255',
            'date' => 'required|date',
        ]);

        $disk = Storage::disk('sftp');

        if ($validated) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $path = 'PSTO-SDN-FMS/' . $filename;

            // Check if file already exists in storage
            if ($disk->exists($path)) {
                return response()->json([
                    'message' => 'File already exists on the SFTP server!'
                ], 400);
            }

            // Check if file details already exist in database
            $existingFile = File::where('filename', $filename)
                ->where('uploader', $request->input('uploader'))
                ->where('category', $request->input('category'))
                ->where('date', $request->input('date'))
                ->first();

            if ($existingFile) {
                return response()->json([
                    'message' => 'File details already exist in the system!'
                ], 400);
            }

            $fileUploadSuccess = $disk->put($path, file_get_contents($file));

            if (!$fileUploadSuccess) {
                return response()->json(['message' => 'Upload failed'], 500);
            }

            File::create([
                'filename' => $filename,
                'uploader' => $request->input('uploader'),
                'category' => $request->input('category'),
                'date' => $request->input('date'),
            ]);

            return response()->json(['message' => 'Upload successful']);
        }

        return response()->json(['message' => 'Upload failed'], 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(File $file)
    {
        return response()->json(['file' => $file]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, File $file)
    {
        $disk = Storage::disk('sftp');

        // Validate the incoming request
        $validated = $request->validate([
            'file' => 'nullable|file|max:10240', // New file is optional
            'uploader' => 'required|max:255',
            'category' => 'required|max:255',
            'date' => 'required|date',
        ]);

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $filename = $uploadedFile->getClientOriginalName();
            $path = 'PSTO-SDN-FMS/' . $filename;

            // Check if the new file exists on the SFTP server
            if ($disk->exists($path)) {
                return response()->json([
                    'message' => 'File already exists on the SFTP server!',
                ], 400);
            }

            // Upload the new file
            if (!$disk->put($path, file_get_contents($uploadedFile))) {
                return response()->json([
                    'message' => 'Failed to upload file to SFTP server.',
                ], 500);
            }

            // Delete the old file if it exists
            $oldPath = 'PSTO-SDN-FMS/' . $file->filename;
            if ($disk->exists($oldPath)) {
                $disk->delete($oldPath);
            }

            // Update the filename in the database
            $file->filename = $filename;
        }

        // Update other file details in the database
        $file->update([
            'uploader' => $validated['uploader'],
            'category' => $validated['category'],
            'date' => $validated['date'],
        ]);

        return response()->json([
            'message' => 'File details updated successfully!',
            'file' => $file,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(File $file)
    {
        $disk = Storage::disk('sftp');
        $filePath = 'PSTO-SDN-FMS/' . $file->filename;

        if ($disk->exists($filePath)) {
            $disk->delete($filePath);
        }

        $file->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
}
