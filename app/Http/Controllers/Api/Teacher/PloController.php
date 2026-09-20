<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Plo;
use Illuminate\Http\Request;

class PloController extends Controller
{
    /**
     * Read-only list of active PLOs, for the CLO form's PLO dropdown.
     * Teachers don't manage PLOs themselves.
     */
    public function index(Request $request)
    {
        $plos = Plo::query()
            ->where('status', true)
            ->orderBy('title')
            ->get(['id', 'code', 'title']);

        return response()->json(['data' => $plos]);
    }
}
