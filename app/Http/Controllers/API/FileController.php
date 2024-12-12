<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $disk = Storage::disk('sftp');
            $files = $disk->allFiles(); // List all files on the SFTP server

            // Transform filenames into structured data
            $structuredFiles = array_map(function ($file, $index) {
                return [
                    'id' => $index + 1, // Assign a unique ID
                    'name' => str_replace('PSTO-SDN-FMS/', '', $file), // Remove prefix
                    'uploadedBy' => 'Unknown', // Placeholder for uploadedBy
                    'date' => now()->format('m/d/Y'), // Placeholder date
                    'category' => 'General', // Placeholder category
                ];
            }, $files, array_keys($files));

            return response()->json([
                'files' => $structuredFiles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function store(Request $request)
    {
        // Validate the incoming request to make sure a file is present
        $validated = $request->validate([
            'file' => 'required|file', // Adjust validation as needed
        ]);

        // Ensure that 'file' exists in the request
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Use the sftp disk to store the file
            $path = $file->store('sftp');  // Store in 'files' directory on SFTP

            // Handle other fields from the request if necessary
            $uploader = $request->input('uploader');
            $category = $request->input('category');
            $date = $request->date('date');

            // Store the file details in the database
            File::create([
                'filename' => $path,
                'uploader' => $uploader,
                'category' => $category,
                'date' => $date,
            ]);

            return response()->json(['message' => 'File uploaded successfully'], 201);
        }
        return response()->json(['error' => 'No file provided'], 400);
    }

    public function show($id)
    {
        // Return a single file's details
        return response()->json(['message' => "Details of file ID: $id"]);
    }

    public function update(Request $request, $id)
    {
        // Update file details
        return response()->json(['message' => "File ID: $id updated successfully"]);
    }

    public function destroy($id)
    {
        // Delete a file
        return response()->json(['message' => "File ID: $id deleted successfully"]);
    }
}
