<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Recente posts
        </x-slot>
        <x-slot name="description">
            De laatst aangemaakte posts in het systeem.
        </x-slot>

        <div class="flex flex-col gap-4 mt-4">
            @forelse ($this->getRecentPosts() as $post)
                <div class="flex items-center justify-between p-4 bg-white ring-1 ring-gray-200 rounded-xl shadow-sm">
                    <div>
                        <p class="text-sm font-semibold">
                            {{ $post->title }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Auteur: {{ $post->user?->name ?? 'Onbekend' }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @if ($post->is_published)
                            <x-filament::badge color="success">
                                Gepubliceerd
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="gray">
                                Draft
                            </x-filament::badge>
                        @endif
                        <p class="text-xs text-gray-500">
                            {{ $post->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="p-6 text-sm text-center text-gray-500 border border-dashed rounded-xl border-gray-300">
                    Er zijn nog geen posts beschikbaar.
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
