<?php
namespace App\Filament\Resources\Posts\Schemas;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basisgegevens')
                    ->description('Hier vul je de basisinformatie van de blogpost in.')
                    ->schema([
Select::make('user_id')
    ->label('Auteur')
    ->relationship('user', 'name')
    ->required()
    ->searchable()
    ->preload()
    ->default(Auth::id())
    ->helperText('Kies wie als auteur aan deze post
gekoppeld is.')
    ->validationMessages([
        'required' => 'Kies een auteur voor deze
post.',
    ]),
TextInput::make('title')
    ->label('Titel')
    ->required()
    ->minLength(3)
    ->maxLength(255)
    ->live(onBlur: true)
    ->afterStateUpdated(function (Get $get, Set
                                      $set, ?string $old, ?string $state): void {
        if (($get('slug') ?? '') !==
            Str::slug((string) $old)) {
            return;
        }
        $set('slug', Str::slug((string) $state));
    })
    ->placeholder('Bijv. Laravel Filament geavanceerd
uitgelegd')
    ->helperText('Geef een duidelijke titel voor de
blogpost')
    ->validationMessages([
        'required' => 'Een titel is verplicht.',
        'min_length' => 'De titel moet minstens 3
karakters bevatten.',
        'max_length' => 'De titel mag maximaal 255
karakters bevatten.',
    ]),
TextInput::make('slug')
    ->label('Slug')
    ->required()
    ->maxLength(255)
    ->unique(ignoreRecord: true)
    ->dehydrateStateUsing(fn (?string $state, Get
                                      $get): string => filled($state)
        ? Str::slug($state)
        : Str::slug((string) $get('title')))
    ->helperText('Laat je dit leeg, dan wordt automatisch de titel gebruikt met koppeltekens.')
    ->validationMessages([
        'required' => 'De slug is verplicht.',
        'unique' => 'Deze slug bestaat al. Kies een andere slug.',
        'max_length' => 'De slug mag maximaal 255 karakters bevatten.',
    ]),
Textarea::make('excerpt')
    ->label('Samenvatting')
    ->rows(4)
    ->maxLength(500)
    ->columnSpanFull()
    ->helperText('Korte introtekst voor
overzichtspagina’s of teasers')
    ->validationMessages([
        'max_length' => 'De samenvatting mag maximaal 500 karakters bevatten.',
    ]),
])
->columns(2),
Section::make('Inhoud')
    ->description('Hier schrijf je de volledige inhoud van de
blogpost.')
    ->schema([
        RichEditor::make('body')
            ->label('Inhoud')
            ->required()
            ->toolbarButtons([
                'bold',
                'italic',
                'underline',
                'bulletList',
                'orderedList',
                'h2',
                'h3',
                'blockquote',
                'redo',
                'undo',
            ])
            ->extraInputAttributes([
                'style' => 'min-height: 400px;',
            ])
            ->columnSpanFull()
            ->helperText('Volledige inhoud van de blogpost')
            ->validationMessages([
                'required' => 'De inhoud van de post is verplicht.',
            ]),
    ])
    ->columns(1),
Section::make('Publicatie')
    ->description('Hier bepaal je de categorieën en de
publicatiestatus van de post.')
    ->schema([
        Select::make('categories')
->label('Categorieën')
        ->relationship('categories', 'name')
        ->multiple()
        ->preload()
        ->searchable()
        ->required()
        ->helperText('Kies één of meerdere categorieën')
        ->validationMessages([
            'required' => 'Kies minstens één categorie.',
        ]),
Toggle::make('is_published')
    ->label('Gepubliceerd')
    ->default(false)
    ->live()
    ->afterStateUpdated(function (Set $set, bool
                                      $state, Get $get): void {
        if ($state && blank($get('published_at'))) {
            $set('published_at', now()->format('Y-m-d H:i:s'));
        }
        if (! $state) {
            $set('published_at', null);
        }
    })
    ->helperText('Zet dit aan als de post gepubliceerd mag worden.'),
DateTimePicker::make('published_at')
    ->label('Publicatiedatum')
    ->visible(fn (Get $get): bool => (bool)
    $get('is_published'))
    ->required(fn (Get $get): bool => (bool)
    $get('is_published'))
    ->helperText('Kies wanneer deze post gepubliceerd is of wordt.')
    ->validationMessages([
        'required' => 'Kies een publicatiedatum zodra de post gepubliceerd is.',
    ]),
])
->columns(2),
Section::make('Featured image')
    ->description('Voeg hier de hoofdafbeelding van de
blogpost toe.')
    ->relationship('featuredImage')
    ->schema([
        FileUpload::make('file_path')
            ->label('Afbeelding')
            ->image()
            ->disk('public')
            ->directory('posts')
            ->visibility('public')
->imageEditor()
        ->imagePreviewHeight('220')
        ->panelLayout('integrated')
        ->panelAspectRatio('16:9')
        ->openable()
        ->downloadable()
        ->nullable()
        ->maxSize(2048)
        ->columnSpanFull()
            ->storeFileNamesIn('file_name')

        ->helperText('Upload hier de featured image van de post.')
        ->validationMessages([
            'image' => 'Het bestand moet een geldige afbeelding zijn.',
            'max_size' => 'De afbeelding mag maximaal 2 MB groot zijn.',
        ]),
TextInput::make('alt_text')
    ->label('Alt-tekst')
    ->maxLength(255)
    ->helperText('Beschrijf kort wat op de afbeelding te zien is')
    ->validationMessages([
        'max_length' => 'De alt-tekst mag maximaal 255 karakters bevatten.',
    ]),
Textarea::make('caption')
    ->label('Caption')
    ->rows(2)
    ->helperText('Optioneel onderschrift bij de
afbeelding'),
Hidden::make('disk')
    ->default('public'),
Hidden::make('is_featured')
    ->default(true),
])
->columns(2),
]);
}
}
