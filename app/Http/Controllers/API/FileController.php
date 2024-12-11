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

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'files' => 'required|array', // Expect an array of files
            'files.*' => 'file|mimes:jpg,png,pdf,docx|max:10240', // Validate each file
            'uploader' => 'required|string|max:255',
            'category' => 'required|string|in:SETUP,GIA,Others', // Replace with actual categories
            'date' => 'required|date_format:m/d/Y',
        ]);

        try {
            // Handle multiple file uploads using SFTP disk
            $filePaths = [];
            foreach ($request->file('files') as $file) {
                // Store each file in the SFTP disk
                $filePath = $file->store('sftp'); // Adjust the directory as needed
                $filePaths[] = $filePath;
            }

            // Create a new record in the database for each uploaded file
            foreach ($filePaths as $filePath) {
                $record = new File(); // Replace with your actual model name
                $record->file_path = $filePath;
                $record->uploader = $validatedData['uploader'];
                $record->category = $validatedData['category'];
                $record->date = $validatedData['date'];
                $record->save();
            }

            return response()->json([
                'message' => 'Files uploaded successfully!',
                'data' => $filePaths, // Return the file paths for the uploaded files
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to upload data.',
                'error' => $e->getMessage()
            ], 500);
        }
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
