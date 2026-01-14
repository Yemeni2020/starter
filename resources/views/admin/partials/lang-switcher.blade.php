@auth
    @php
        $activeLocale = $currentLocale ?? app()->getLocale();
    @endphp

    <div class="flex items-center gap-1 rounded-md border border-zinc-200 bg-white p-0.5 text-xs dark:border-zinc-700 dark:bg-zinc-900">
        <form method="POST" action="{{ route('lang.switch', 'ar') }}">
            @csrf
            <button type="submit" class="rounded px-2 py-1 font-semibold {{ $activeLocale === 'ar' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white' }}">
                AR
            </button>
        </form>

        <form method="POST" action="{{ route('lang.switch', 'en') }}">
            @csrf
            <button type="submit" class="rounded px-2 py-1 font-semibold {{ $activeLocale === 'en' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white' }}">
                EN
            </button>
        </form>
    </div>
@endauth
