<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{


    /**
     * Delete an attachment
     */
    public function destroy(Attachment $attachment)
    {
        // Authorization will be handled by the parent model's policy
        // For now, we'll check if the user can access the attachable model
        
        $attachment->delete(); // This will also delete the file via the model's boot method

        return response()->json([
            'message' => 'Attachment deleted successfully'
        ]);
    }

    /**
     * Download an attachment
     */
    public function download(Attachment $attachment)
    {
        // Authorization check would go here
        
        if (!Storage::exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::download($attachment->file_path, $attachment->original_name);
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}