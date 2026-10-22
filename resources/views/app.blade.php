<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $seo = data_get($page ?? [], 'props.seo', []);
            $seoTitle = data_get($seo, 'title', 'Jepretin — Abadikan Setiap Momen');
            $seoDescription = data_get($seo, 'description', 'Kumpulkan foto candid dari tamu undangan melalui QR Code. Semua momen tersimpan otomatis di Jepretin.');
            $seoImage = data_get($seo, 'image', secure_asset('banner/banner-1.webp'));
            $seoUrl = data_get($seo, 'url', secure_url(request()->getPathInfo()));
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-inertia::head>
            <title inertia>{{ $seoTitle }}</title>
            <meta data-inertia="description" name="description" content="{{ $seoDescription }}">
            <meta data-inertia="og:type" property="og:type" content="website">
            <meta data-inertia="og:title" property="og:title" content="{{ $seoTitle }}">
            <meta data-inertia="og:description" property="og:description" content="{{ $seoDescription }}">
            <meta data-inertia="og:image" property="og:image" content="{{ $seoImage }}">
            <meta data-inertia="og:url" property="og:url" content="{{ $seoUrl }}">
            <meta data-inertia="twitter:card" name="twitter:card" content="summary_large_image">
            <meta data-inertia="twitter:title" name="twitter:title" content="{{ $seoTitle }}">
            <meta data-inertia="twitter:description" name="twitter:description" content="{{ $seoDescription }}">
            <meta data-inertia="twitter:image" name="twitter:image" content="{{ $seoImage }}">
        </x-inertia::head>
    </head>
    <body>
        <x-inertia::app />
    </body>
</html>
