<?php

namespace App\Http\Controllers;

use App\Services\NavigationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShortcutController extends Controller
{
    public function toggle(Request $request, NavigationService $nav): JsonResponse
    {
        $key = $request->input('key', '');
        $user = auth()->user();
        $pinned = $nav->toggleShortcut($user, $key);

        return response()->json(['pinned' => $pinned]);
    }
}
