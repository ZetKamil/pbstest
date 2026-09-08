<?php
namespace App\Filament\Pages;
use BackedEnum; // nodig voor typed navigation properties
use Filament\Actions\Action; // actions voor callouts
use Filament\Forms\Components\Placeholder; // placeholder component
use Filament\Forms\Components\Textarea; // textarea veld
use Filament\Forms\Components\TextInput; // tekstinput veld
use Filament\Forms\Concerns\InteractsWithForms; // form interactie mogelijk maken
use Filament\Forms\Contracts\HasForms; // page kan forms gebruiken
use Filament\Pages\Page; // basisclass voor custom Filament page
use Filament\Schemas\Components\Callout; // opvallend infoblok
use Filament\Schemas\Components\EmptyState; // lege toestand / onboarding blok
use Filament\Schemas\Components\Fieldset; // visuele grouping van velden
use Filament\Schemas\Components\Flex; // flex layout
use Filament\Schemas\Components\Grid; // grid layout
use Filament\Schemas\Components\Icon; // los icoon in schema
use Filament\Schemas\Components\Section; // section layout
use Filament\Schemas\Components\Tabs; // tabs container
use Filament\Schemas\Components\Tabs\Tab; // individuele tab
use Filament\Schemas\Components\Text; // vrije tekst in schema
use Filament\Schemas\Components\UnorderedList; // bullet list
use Filament\Schemas\Components\Wizard; // wizard container
use Filament\Schemas\Components\Wizard\Step; // wizard stap
use Filament\Schemas\Schema; // schema object
use Filament\Support\Enums\Alignment; // uitlijning voor actions
use Filament\Support\Enums\FontWeight; // font dikte
use Filament\Support\Enums\TextSize; // tekstgrootte
use Filament\Support\Icons\Heroicon; // heroicons
use UnitEnum; // nodig voor typed navigation group
class LayoutShowcase extends Page implements HasForms
{
    use InteractsWithForms; // activeert Filament forms op deze page
    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack; // icoon in de sidebar
    protected static ?string $navigationLabel = 'Layout showcase'; // label in de navigatie
    protected static string|UnitEnum|null $navigationGroup = 'Demo'; // groepeert deze pagina in de sidebar
    protected static ?int $navigationSort = 99; // zet deze pagina lager in de groep
    protected string $view = 'filament.pages.layout-showcase'; // blade-view van deze page
    public ?array $data = []; // state van het schema / form
    public function mount(): void
    {
        $this->form->fill([
            'intro_title' => 'Filament 5 schema showcase', // voorbeeldtitel
            'intro_subtitle' => 'Visuele demonstratie van layouts, callouts,
empty states en prime components.', // voorbeeldsubtitel
            'grid_name' => 'Tom Demo', // demo naam
            'grid_email' => 'tom@example.com', // demo email
            'grid_status' => 'Actief', // demo status
            'aside_title' => 'Rate limiting', // voorbeeld instelling
            'aside_value' => '100 requests per minuut', // voorbeeld waarde
            'tab_title' => 'Dummy titel', // voorbeeld titel in tab
            'tab_excerpt' => 'Dit is een voorbeeld van inhoud in een tab.', // voorbeeld samenvatting
            'tab_notes' => 'Hier kun je extra notities plaatsen.', // voorbeeld notities
'wizard_order' => 'Bestelling #2026-001', // voorbeeld order
'wizard_delivery' => 'Levering binnen 2 werkdagen', // voorbeeld levering
'wizard_billing' => 'Facturatie via overschrijving', // voorbeeld facturatie
'flex_left' => 'Hoofdinhoud links', // voorbeeld linkerkolom
'flex_right' => 'Ondersteunende info rechts.', // voorbeeld rechterkolom
'fieldset_name' => 'Tom Demo', // voorbeeld fieldset naam
'fieldset_email' => 'tom@example.com', // voorbeeld fieldset email
]);
}
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Intro') // eerste visuele sectie
                ->description('Deze pagina combineert meerdere schemamogelijkheden uit Filament 5 in één visuele showcase.') // uitleg boven de section
    ->schema([
        Grid::make([
            'default' => 1, // mobiel = 1 kolom
            'lg' => 2, // groter scherm = 2 kolommen
        ])
            ->schema([
                TextInput::make('intro_title')
                    ->label('Titel van de demo'), // label boven het veld
TextInput::make('intro_subtitle')
    ->label('Subtitel van de demo'), // label boven het veld
]),
Callout::make('Waarom deze pagina bestaat') // opvallend uitlegblok
        ->description('Gebruik deze page om cursisten
snel te tonen hoe schema-layouts in Filament eruitzien zonder dat je telkens
een volledige resource hoeft te bouwen.') // uitleg in de callout
        ->info() // blauwe / info-stijl
        ->footer([
            Text::make('Tip: vergelijk deze componenten
later met echte resource forms.')
                ->color('gray'), // subtiele footertekst
        ]),
])
->columns(1), // section zelf in 1 kolom renderen
Section::make('1. Layouts: grid, columns, columnSpan en
flex') // layout demonstratie
->description('De layouts-docs tonen dat schema’s
responsieve kolommen gebruiken via columns(), column spans en componenten
zoals Grid en Flex.') // uitleg
->schema([
    Grid::make([
        'default' => 1, // mobiel
        'md' => 2, // tablet
        'xl' => 4, // desktop
    ])
        ->schema([
            TextInput::make('grid_name')
                ->label('Naam') // label boven veld
                ->columnSpan([
                    'default' => 1, // standaard 1 kolom  breed
'xl' => 2, // op xl 2 kolommen breed
]),
TextInput::make('grid_email')
    ->label('E-mail') // label boven veld
    ->columnSpan([
        'default' => 1, // standaard 1 kolom breed
'xl' => 2, // op xl 2 kolommen breed
]),
TextInput::make('grid_status')
    ->label('Status'), // label boven veld
Placeholder::make('grid_hint')
    ->label('Uitleg') // label boven placeholder
    ->content('Dit blok toont hoe kolommen en
columnSpan samen een responsieve layout vormen.'), // statische uitleg
]),
Flex::make([
    Textarea::make('flex_left')
->label('Linker blok') // label links
    ->rows(4), // zichtbare hoogte
Textarea::make('flex_right')
    ->label('Rechter blok') // label rechts
    ->rows(4), // zichtbare hoogte
]),
]),
Section::make('2. Section met aside()') // aside section demo
->description('Sections kunnen ook hun heading en
description links plaatsen, met de inhoud rechts in een card.') // uitleg
->aside() // maakt er een aside-layout van
->schema([
    TextInput::make('aside_title')
        ->label('Instelling'), // label van instelling
    TextInput::make('aside_value')
        ->label('Waarde'), // label van waarde
])
    ->columns(2), // inhoud in 2 kolommen
Section::make('3. Fieldset') // fieldset demo
->description('Een fieldset groepeert samenhangende
velden binnen een afgebakend blok.') // uitleg
->schema([
    Fieldset::make('Contactgegevens') // fieldset titel
    ->schema([
        TextInput::make('fieldset_name')
            ->label('Naam') // label
            ->default('Tom Demo'), // demo waarde
        TextInput::make('fieldset_email')
            ->label('E-mail') // label
            ->default('tom@example.com'), // demo waarde
    ])
        ->columns(2), // 2 kolommen binnen fieldset
]),
Section::make('4. Tabs') // tabs demo
->description('Tabs zijn geschikt om langere inhoud op te
delen. In de docs zie je ook mogelijkheden zoals badges, activeTab(),
vertical() en persistence.') // uitleg
->schema([
    Tabs::make('Tabs demo') // tabs container
    ->id('layout-showcase-tabs') // unieke ID voor tab persistence
        ->activeTab(2) // tweede tab standaard actief
        ->persistTab() // onthoudt actieve tab
        ->tabs([
            Tab::make('Content') // eerste tab
            ->badge(3) // badge getal
            ->badgeColor('primary') // badge kleur
            ->schema([
TextInput::make('tab_title')
    ->label('Titel'), // veld in tab
]),
Tab::make('Samenvatting') // tweede tab
->badge(1) // badge getal
->badgeColor('info') // badge kleur
->schema([
    Textarea::make('tab_excerpt')
        ->label('Samenvatting') // veld in tab 2
->rows(3), // hoogte
]),
Tab::make('Notities') // derde tab
->badge(7) // badge getal
->badgeColor('warning') // badge kleur
->schema([
    Textarea::make('tab_notes')
        ->label('Notities') // veld in tab 3
->rows(5), // hoogte
]),
]),
]),
Section::make('5. Wizard') // wizard demo
->description('Wizards tonen een lineaire, stapsgewijze
flow. De docs tonen onder meer step descriptions, icons, startOnStep() en
skippable().') // uitleg
->schema([
    Wizard::make([
        Step::make('Order') // eerste wizardstap
        ->description('Basis van de bestelling') // beschrijving van stap
        ->icon(Heroicon::OutlinedRectangleStack) // icoon van stap
        ->schema([
            TextInput::make('wizard_order')
                ->label('Bestelling'), // veld in stap 1
]),
Step::make('Delivery') // tweede wizardstap
->description('Leveringsinformatie') // beschrijving van stap
        ->icon(Heroicon::OutlinedTruck) // icoon van stap
    ->schema([
        TextInput::make('wizard_delivery')
            ->label('Levering'), // veld in stap 2
    ]),
Step::make('Billing') // derde wizardstap
->description('Facturatiegegevens') // beschrijving van stap
        ->icon(Heroicon::OutlinedDocumentText) // icoon van stap
        ->schema([
            TextInput::make('wizard_billing')
                ->label('Facturatie'), // veld in stap 3
]),
])
->startOnStep(1) // start op stap 1
    ->skippable(), // gebruiker mag stappen overslaan
]),
Section::make('6. Callouts') // callouts demo
->description('Callouts zijn bedoeld om belangrijke
informatie, waarschuwingen of acties op een opvallende manier te tonen.') // uitleg
    ->schema([
        Grid::make([
            'default' => 1, // mobiel 1 kolom
            'xl' => 2, // desktop 2 kolommen
        ])
            ->schema([
                Callout::make('Nieuwe versie beschikbaar') // info callout
        ->description('Er is een nieuwe release
beschikbaar voor je adminomgeving.') // tekst
        ->info(), // info-stijl
Callout::make('Publicatie geslaagd') // success callout
        ->description('De post is succesvol
gepubliceerd.') // tekst
        ->success(), // success-stijl
Callout::make('Let op bij verwijderen') // warning callout
        ->description('Een force delete
verwijdert ook gekoppelde media definitief.') // tekst
        ->warning() // warning-stijl
        ->color(null), // laat standaard warning kleur los
Callout::make('Upgrade nodig') // callout met actions
    ->description('Je proefperiode loopt
binnenkort af.') // tekst
    ->warning() // warning-stijl
    ->actions([
        Action::make('upgrade')
            ->label('Upgrade') // primaire actie
    ->button(), // als knop tonen
Action::make('compare')
    ->label('Vergelijk plannen'), // secundaire actie
])
->footerActionsAlignment(Alignment::End),
// actions rechts uitlijnen
]),
]),
Section::make('7. Empty state') // empty state demo
->description('Een empty state communiceert dat er nog
geen inhoud is en begeleidt de gebruiker naar een volgende actie.') // uitleg
->schema([
    EmptyState::make('Nog geen statistieken beschikbaar')
// titel van lege staat
        ->description('Zodra er gegevens zijn, kun je
hier dashboards of rapporten tonen.') // begeleidende tekst
        ->icon(Heroicon::OutlinedRectangleStack) // icoon
        ->footer([
            Text::make('Dit is een typische plek voor
onboarding of eerste instructies.')
                ->color('gray'), // subtiele footertekst
        ]),
]),
Section::make('8. Prime components') // prime components demo
->description('Prime components zijn elementaire schemabouwstenen voor arbitraire inhoud, zoals tekst, iconen en lijsten.') // uitleg
    ->schema([
        Text::make('Prime components helpen je om uitleg of
begeleiding rechtstreeks in een schema te tonen.')
            ->size(TextSize::Large) // grotere tekst
            ->weight(FontWeight::Bold), // vetgedrukt
        Text::make('Ze zijn nuttig wanneer je geen klassiek
formulier- of detailveld wilt, maar vrije inhoud.')
            ->color('gray'), // subtiele grijze tekst
        Grid::make([
            'default' => 1, // mobiel 1 kolom
            'md' => 2, // groter scherm 2 kolommen
        ])
            ->schema([
                Icon::make(Heroicon::OutlinedLightBulb)
                    ->color('warning'), // opvallend geel icoon
UnorderedList::make([
    'Text voor begeleidende uitleg', // lijstitem 1
'Icon voor visuele nadruk', // lijstitem2
'Unordered list voor korte opsommingen',
// lijstitem 3
]),
]),
]),
])
->statePath('data'); // alle state opslaan in $data
}
    public function getTitle(): string
    {
        return 'Filament layout showcase'; // paginatitel bovenaan
    }
}
