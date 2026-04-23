<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SellerKycImageController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Seller $seller, string $type): Response
    {
        if (! in_array($type, ['front', 'back'])) {
            abort(404);
        }

        $path = $type === 'front' ? $seller->cccd_front_image : $seller->cccd_back_image;
        if (! $path) {
            abort(404);
        }

        $disk = config('filesystems.default');
        if (! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        Log::info('Admin accessed KYC image', [
            'admin_id'    => auth()->guard('admin')->id(),
            'admin_email' => auth()->guard('admin')->user()?->email,
            'seller_id'   => $seller->id,
            'image_type'  => $type,
            'path'        => $path,
        ]);

        $fileContent = Storage::disk($disk)->get($path);

        /** @var string $mimeType */
        $mimeType = Storage::disk($disk)->mimeType($path);

        return response($fileContent)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'max-age=3600');
    }
}
