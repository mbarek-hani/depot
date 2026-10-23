@props(['paginator'])

@if ($paginator->hasPages())
    @php
        $start = max(1, $paginator->currentPage() - 2);
        $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
        $pages = $paginator->getUrlRange($start, $end);
    @endphp

    <nav class="flex flex-col items-center justify-between gap-3 border-t border-stone-100 px-5 py-4 dark:border-night-800 sm:flex-row" aria-label="التنقل بين الصفحات">
        <p class="text-xs font-medium text-stone-500 dark:text-stone-400">
            عرض
            <span class="font-bold text-stone-800 dark:text-stone-200">{{ $paginator->firstItem() }}</span>
            إلى
            <span class="font-bold text-stone-800 dark:text-stone-200">{{ $paginator->lastItem() }}</span>
            من
            <span class="font-bold text-stone-800 dark:text-stone-200">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="flex size-9 items-center justify-center rounded-lg text-stone-300 dark:text-stone-600">
                    <x-ui.icon name="chevron-right" size="size-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex size-9 items-center justify-center rounded-lg border border-stone-200 text-stone-500 transition hover:border-brand-300 hover:text-stone-900 dark:border-night-800 dark:text-stone-400 dark:hover:border-brand-400/40 dark:hover:text-white" aria-label="الصفحة السابقة">
                    <x-ui.icon name="chevron-right" size="size-4" />
                </a>
            @endif

            @foreach ($pages as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="flex size-9 items-center justify-center rounded-lg bg-brand-400 text-sm font-bold text-night-950">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="flex size-9 items-center justify-center rounded-lg border border-transparent text-sm font-semibold text-stone-500 transition hover:border-stone-200 hover:bg-stone-50 hover:text-stone-900 dark:text-stone-400 dark:hover:border-night-800 dark:hover:bg-night-800 dark:hover:text-white">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex size-9 items-center justify-center rounded-lg border border-stone-200 text-stone-500 transition hover:border-brand-300 hover:text-stone-900 dark:border-night-800 dark:text-stone-400 dark:hover:border-brand-400/40 dark:hover:text-white" aria-label="الصفحة التالية">
                    <x-ui.icon name="chevron-left" size="size-4" />
                </a>
            @else
                <span class="flex size-9 items-center justify-center rounded-lg text-stone-300 dark:text-stone-600">
                    <x-ui.icon name="chevron-left" size="size-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
