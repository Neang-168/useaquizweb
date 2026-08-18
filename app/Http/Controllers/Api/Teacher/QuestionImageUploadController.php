<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionImageUploadController extends Controller
{
    /**
     * Stage an image for a question/option/matching-pair element. The
     * question save endpoints move the file out of tmp/ once the question
     * is actually persisted; anything left behind is swept by the
     * question-images:prune-tmp scheduled command.
     */
    public function store(Request $request)
    {
        // 'mimes' and 'image' both inspect the file's real content (via
        // fileinfo/getimagesize), not the client-supplied name or header.
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $extension = strtolower($request->file('image')->extension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $token = Str::uuid()->toString().'.'.$extension;

        $request->file('image')->storeAs('tmp', $token, 'public');

        return response()->json([
            'token' => $token,
            'url' => Storage::disk('public')->url('tmp/'.$token),
        ], 201);
    }
}
