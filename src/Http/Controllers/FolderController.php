<?php

namespace TomatoPHP\FilamentMediaManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use TomatoPHP\FilamentMediaManager\Services\FolderAccess;

class FolderController extends Controller
{
    public function index(Request $request)
    {
        $folders = FolderAccess::visibleTo($request->user());

        if ($request->has('search')) {
            $folders->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'data' => config('filament-media-manager.api.resources.folders')::collection($folders->paginate(10)),
        ], 200);
    }

    public function show(Request $request, int $id)
    {
        $folder = FolderAccess::visibleTo($request->user())->find($id);

        if (! $folder) {
            return response()->json(['message' => 'Not Found.'], 404);
        }

        if ($folder->is_protected) {
            $password = $request->header('X-Folder-Password') ?? $request->input('password');

            if (! FolderAccess::passwordMatches($folder, $password)) {
                return response()->json(['message' => 'This folder is password protected.'], 403);
            }
        }

        return response()->json([
            'data' => config('filament-media-manager.api.resources.folder')::make($folder),
        ], 200);
    }
}
