<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class S3UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $path = Storage::disk('s3')->putFile(
            'uploads',
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diupload ke AWS S3',
            'path' => $path,
        ], 200);
    }
}