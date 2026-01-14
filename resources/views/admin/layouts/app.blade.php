@php($title = trim($__env->yieldContent('title')))

<x-layouts.app :title="$title !== '' ? $title : null">
    @yield('content')
</x-layouts.app>
