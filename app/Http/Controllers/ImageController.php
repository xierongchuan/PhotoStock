<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $images = auth()->user()
            ->images()
            ->latest()
            ->get();

        return ImageResource::collection($images);
    }

    public function store(StoreImageRequest $request): JsonResponse
    {
        $file = $request->file('image');
        $path = $file->store('images', 'local');

        $image = $request->user()->images()->create([
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully.',
            'data' => new ImageResource($image),
        ], Response::HTTP_CREATED);
    }

    public function show(Image $image): BinaryFileResponse
    {
        $this->authorize('view', $image);

        return response()->file(
            Storage::disk('local')->path($image->storage_path),
            ['Content-Type' => $image->mime_type]
        );
    }

    public function destroy(Image $image): JsonResponse
    {
        $this->authorize('delete', $image);

        Storage::disk('local')->delete($image->storage_path);
        $image->delete();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
