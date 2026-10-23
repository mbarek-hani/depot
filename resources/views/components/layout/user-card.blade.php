@php
    $user = auth()->user();
@endphp

<div class="border-t border-stone-200 p-3 dark:border-white/5">
    <div class="flex items-center gap-3 rounded-xl bg-stone-50 p-2.5 dark:bg-white/5">
        <x-ui.avatar :name="$user->name" size="size-9" />
        <div class="min-w-0 flex-1">
            <p class="truncate text-xs font-bold text-stone-900 dark:text-white">{{ $user->name }}</p>
            <p class="truncate text-[10px] text-stone-500 dark:text-stone-400">{{ $user->role->label() }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rounded-lg p-1.5 text-stone-500 transition hover:bg-stone-200 hover:text-stone-900 dark:hover:bg-white/10 dark:hover:text-white" aria-label="تسجيل الخروج" title="تسجيل الخروج">
                <x-ui.icon name="logout" size="size-4.5" />
            </button>
        </form>
    </div>
</div>
