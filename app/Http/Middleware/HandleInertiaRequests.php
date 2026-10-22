<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->getAttribute('avatar_url'),
                    'profile_photo_url' => $user->getAttribute('profile_photo_url'),
                ] : null,
            ],
            'seo' => [
                'title' => 'Jepretin — Abadikan Setiap Momen',
                'description' => 'Kumpulkan foto candid dari tamu undangan melalui QR Code. Semua momen tersimpan otomatis di Jepretin.',
                'image' => secure_asset('banner/banner-1.webp'),
                'url' => secure_url($request->getPathInfo()),
            ],
        ];
    }
}
