<?php
namespace App\Filament\Resources\Posts\Tables;
use App\Models\User; // nodig voor auteurfilter
use Filament\Actions\Action; // custom actions
use Filament\Actions\ActionGroup; // groepeert recordacties in een dropdown
use Filament\Actions\BulkActionGroup; // bulk acties groeperen
use Filament\Actions\DeleteAction; // soft delete actie per rij
use Filament\Actions\DeleteBulkAction; // bulk soft delete
use Filament\Actions\EditAction; // edit actie per rij
use Filament\Actions\ForceDeleteAction; // definitief verwijderen per rij
use Filament\Actions\ForceDeleteBulkAction; // bulk force delete
use Filament\Actions\RestoreAction; // herstellen per rij
use Filament\Actions\RestoreBulkAction; // bulk restore
use Filament\Notifications\Notification; // feedback na acties
use Filament\Support\Icons\Heroicon; // icoon voor action group
use Filament\Tables\Columns\ImageColumn; // afbeeldingskolom
use Filament\Tables\Columns\TextColumn; // tekstkolommen
use Filament\Tables\Filters\SelectFilter; // dropdown filters
use Filament\Tables\Filters\TrashedFilter; // filter voor soft deleted records
use Filament\Tables\Table; // tabel-object
use Illuminate\Database\Eloquent\Collection; // bulk records verwerken
class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc') // nieuwste posts standaard bovenaan tonen
        ->columns([
            ImageColumn::make('featuredImage.file_path')
                ->label('Afbeelding') // toon featured image in het overzicht
    ->disk('public') // haal afbeelding op via public disk
    ->square(), // vierkante thumbnail
TextColumn::make('id')
    ->label('ID') // record-ID
    ->sortable(), // sorteerbaar
TextColumn::make('title')
    ->label('Titel') // titel van de post
    ->searchable() // zoekbaar
->sortable() // sorteerbaar
    ->limit(40), // lange titels inkorten in de tabel
TextColumn::make('categories.name')
    ->label('Categorieën') // toon gekoppelde categorieën
    ->badge() // categorieën als badges tonen
    ->separator(', ') // meerdere categorieën gescheiden tonen
    ->toggleable(), // gebruiker mag kolom tonen/verbergen
TextColumn::make('user.name')
    ->label('Auteur') // naam van de auteur
    ->sortable() // sorteerbaar
    ->searchable(), // ook auteur doorzoekbaar maken
TextColumn::make('is_published')
    ->label('Status') // duidelijkere statuskolom
    ->badge() // badge-weergave
    ->formatStateUsing(fn (bool $state): string => $state ?
        'Gepubliceerd' : 'Draft') // tekst per toestand
    ->color(fn (bool $state): string => $state ? 'success' :
        'gray'), // kleur per toestand
TextColumn::make('slug')
    ->label('Slug') // URL-slug
    ->searchable() // zoekbaar
    ->toggleable(isToggledHiddenByDefault: true), // standaard verborgen
TextColumn::make('published_at')
    ->label('Publicatiedatum') // publicatiedatum
    ->dateTime('d/m/Y H:i') // netjes formatteren
    ->sortable()
    ->placeholder('Niet gepubliceerd'), // duidelijke placeholder
TextColumn::make('created_at')
    ->label('Aangemaakt')
    ->since() // relatieve tijd tonen
    ->sortable(),
TextColumn::make('deleted_at')
    ->label('Verwijderd op')
    ->dateTime('d/m/Y H:i')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true), // standaard verborgen
])
->filters([
        SelectFilter::make('is_published')
            ->label('Status') // filter op publicatiestatus
            ->options([
                1 => 'Gepubliceerd',
                0 => 'Draft',
            ]),
SelectFilter::make('user_id')
    ->label('Auteur') // filter op auteur
    ->options(
        User::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray()
    )
    ->searchable(), // auteur sneller vinden
SelectFilter::make('categories')
    ->label('Categorie') // filter op categorie
    ->relationship('categories', 'name') // filter via relatie
    ->searchable()
    ->preload(),
TrashedFilter::make(), // actieve, trashed of alle records
])
->recordActions([
        ActionGroup::make([
            EditAction::make()
                ->label('Bewerken'),
            Action::make('publish')
                ->label('Publiceren')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation() // extra veiligheid
                ->authorize(fn ($record): bool =>
                auth()->user()->can('publish', $record)) // policy check: mag deze user publiceren?
->visible(fn ($record): bool => !
    $record->is_published) // alleen tonen als de post nog niet gepubliceerd is
    ->action(function ($record): void {
        $record->update([
            'is_published' => true,
            'published_at' => $record->published_at ??
                now(),
        ]);
        Notification::make()
            ->title('Post gepubliceerd')
            ->body("De post '{$record->title}' is gepubliceerd.")
            ->success()
            ->send();
    }),
Action::make('unpublish')
    ->label('Depubliceren')
    ->icon(Heroicon::OutlinedXCircle)
    ->color('gray')
    ->requiresConfirmation() // extra veiligheid
->authorize(fn ($record): bool =>
    auth()->user()->can('unpublish', $record)) // policy check: mag deze user depubliceren?
->visible(fn ($record): bool => (bool)
    $record->is_published) // alleen tonen bij gepubliceerde posts
    ->action(function ($record): void {
        $record->update([
            'is_published' => false,
            'published_at' => null,
        ]);
        Notification::make()
            ->title('Post gedepubliceerd')
            ->body("De post '{$record->title}' staat nu opnieuw als draft.")
            ->success()
            ->send();
    }),
DeleteAction::make()
    ->label('Verwijderen'),
RestoreAction::make()
    ->label('Herstellen'),
ForceDeleteAction::make()
    ->label('Definitief verwijderen'),
])
->label('Acties') // tekst op de knop
    ->icon(Heroicon::OutlinedEllipsisVertical) // icoon met drie puntjes
        ->button(), // render als knop
])
->toolbarActions([
        BulkActionGroup::make([
            Action::make('bulkPublish')
                ->label('Publiceren')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->action(function (Collection $records): void {
                    $count = 0;
                    $records->each(function ($record) use (&$count):
                    void {
                        if (
                            auth()->user()->can('publish', $record)
                            &&
                            ! $record->is_published
                        ) {
                            $record->update([
                                'is_published' => true,
                                'published_at' =>
                                    $record->published_at ?? now(),
                            ]);
$count++;
}
                    });
                    Notification::make()
                        ->title('Bulk publicatie voltooid')
                        ->body("{$count} post(s) zijn gepubliceerd.")
                        ->success()
                        ->send();
                }),
            Action::make('bulkUnpublish')
                ->label('Depubliceren')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('gray')
                ->requiresConfirmation()
                ->action(function (Collection $records): void {
                    $count = 0;
                    $records->each(function ($record) use (&$count):
                    void {
                        if (
                            auth()->user()->can('unpublish', $record)
                            &&
                            $record->is_published
                        ) {
                            $record->update([
                                'is_published' => false,
                                'published_at' => null,
                            ]);
                            $count++;
                        }
                    });
                    Notification::make()
                        ->title('Bulk depublicatie voltooid')
                        ->body("{$count} post(s) zijn gedepubliceerd.")
                        ->success()
                        ->send();
                }),
            DeleteBulkAction::make(), // meerdere records soft deleten
RestoreBulkAction::make(), // meerdere trashed records herstellen
ForceDeleteBulkAction::make(), // meerdere records definitief verwijderen
]),
]);
}
}
