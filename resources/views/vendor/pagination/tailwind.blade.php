@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            @if ($paginator->firstItem())
                {{ __('Showing') }} <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() }}</span>
                {{ __('to') }} <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
                {{ __('of') }} <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $paginator->total() }}</span> {{ __('results') }}
            @else
                {{ __('Showing') }} 0 {{ __('results') }}
            @endif
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300 dark:border-slate-700 dark:text-slate-600">
                    @include('partials.icon', ['name' => 'left', 'class' => 'h-4 w-4'])
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:text-white">
                    @include('partials.icon', ['name' => 'left', 'class' => 'h-4 w-4'])
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="flex h-9 min-w-9 items-center justify-center px-2 text-sm text-slate-500 dark:text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="flex h-9 min-w-9 items-center justify-center rounded-lg bg-blue-600 px-2 text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-2 text-sm font-medium text-slate-600 transition hover:border-blue-300 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-blue-700 dark:hover:text-blue-400">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:text-white">
                    @include('partials.icon', ['name' => 'right', 'class' => 'h-4 w-4'])
                </a>
            @else
                <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300 dark:border-slate-700 dark:text-slate-600">
                    @include('partials.icon', ['name' => 'right', 'class' => 'h-4 w-4'])
                </span>
            @endif
        </div>
    </nav>
@endif
