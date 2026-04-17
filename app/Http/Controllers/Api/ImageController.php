<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function show(Request $request, string $path)
    {
        if (!Storage::disk('local')->exists($path)) abort(404);
        return response(Storage::disk('local')->get($path), 200)
            ->header('Content-Type', Storage::disk('local')->mimeType($path));
    }
}
