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
     */
    public function index()
    {
        // list all file details from mysql
        $files = File::all();
        return response()->json([
            // return an array/object of id, filename, uploader,date, category
            'files' => $files
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return response()->json(['message' => "file create endpoint"]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //save file details to mysql db
        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'uploader' => 'required|max:255',
            'category' => 'required|max:255',
            'date' => 'required|date',
        ]);

        // NAS disk
        $disk = Storage::disk('sftp');
        if ($validated) {
            //upload file to NAS
            $file = $request->file('file');
            // $path = 'PSTO-SDN-FMS/' . $file->getClientOriginalName();
            $filename = $file->getClientOriginalName();
            // $file_upload = $disk->put($path, file_get_contents($file));


            $file_details = File::create([
                'filename' => $filename,
                'uploader' => $request->input('uploader'),
                'category' => $request->input('category'),
                'date' => $request->input('date'),
            ]);
            // Return success response
            return response()->json([
                'message' => 'File uploaded successfully.',
                // 'file_path' => $uploadedFile, // SFTP file path
                'file_details' => $file_details, // Database record
            ]);
        }
    }

    public function show(File $file)
    {
        return response()->json(['file' => $file]);
    }

    public function update(Request $request, File $file)
    {
        // Validate the request inputs
        $request->validate([
            'file' => 'sometimes|file|max:10240', // File is optional during update
            'uploader' => 'required|max:255',
            'category' => 'required|max:255',
            'date' => 'required|date',
        ]);

        // Initialize the SFTP disk
        $disk = Storage::disk('sftp');

        if ($request->hasFile('file')) {
            // Get the uploaded file
            $uploadedFile = $request->file('file');
            $filename = $uploadedFile->getClientOriginalName();
            $path = 'PSTO-SDN-FMS/' . $filename;

            // Check if the file already exists on the NAS
            if ($disk->exists($path)) {
                return response()->json([
                    'message' => 'File already exists on the SFTP server!',
                ], 400);
            }

            // Upload the new file to the NAS
            $fileUploadSuccess = $disk->put($path, file_get_contents($uploadedFile));

            if (!$fileUploadSuccess) {
                return response()->json([
                    'message' => 'Failed to upload file to SFTP server.',
                ], 500);
            }

            // Delete the old file from the NAS if it exists
            $oldPath = 'PSTO-SDN-FMS/' . $file->filename;
            if ($disk->exists($oldPath)) {
                $disk->delete($oldPath);
            }

            // Update the file record in the database
            $file->filename = $filename;
        }

        // Update other file details in the database
        $file->uploader = $request->input('uploader');
        $file->category = $request->input('category');
        $file->date = $request->input('date');
        $file->save();

        // Return success response
        return response()->json([
            'message' => 'File details updated successfully!',
            'file' => $file,
        ]);
    }

    public function destroy(File $file)
    {
        $disk = Storage::disk('sftp');
        //get file name from file details in mysql
        $file_for_delete = 'PSTO-SDN-FMS/' . $file->filename;
        if ($disk->exists($file_for_delete)) {
            $disk->delete($file_for_delete);
        }
        // delete file details in mysql
        $file->delete();
        return response()->json(['message' => 'file deleted successfully']);
    }
}
