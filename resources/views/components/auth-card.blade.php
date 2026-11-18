@props(['title', 'description' => ''])
<x-site-layout :title="$title"><section class="store-wrap blade-page"><div class="blade-auth blade-panel"><p class="eyebrow">REFUGIO OUTDOOR</p><h1>{{ $title }}</h1><p class="page-note mb-5">{{ $description }}</p>@if(session('status'))<p class="blade-status" role="status">{{ session('status') }}</p>@endif{{ $slot }}</div></section></x-site-layout>
