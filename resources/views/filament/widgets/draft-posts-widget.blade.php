<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Drafts die nog aandacht vragen
        </x-slot>
        <x-slot name="description">
            Niet-gepubliceerde posts die nog afgewerkt of nagekeken kunnen worden.
        </x-slot>

        <div class="flex flex-col gap-4 mt-4">
            @forelse ($this->getDraftPosts() as $post)
                <div class="flex items-center justify-between p-4 rounded-xl shadow-sm" style="background-color: #fffbeb; border: 1px solid #fde68a;">
                    <div>
                        <p class="text-sm font-semibold">
                            {{ $post->title }}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            Auteur: {{ $post->user?->name ?? 'Onbekend' }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <x-filament::badge color="warning">
                            Draft
                        </x-filament::badge>
                        <p class="text-xs text-gray-500">
                            {{ $post->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="p-6 text-sm text-center text-gray-500 border border-dashed rounded-xl border-gray-300">
                    Er zijn momenteel geen drafts.
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
