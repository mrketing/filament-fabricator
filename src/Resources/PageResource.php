<?php

namespace Z3d0X\FilamentFabricator\Resources;

use Closure;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;

use Filament\Forms\Components\TagsInput;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Forms\Components\PageBuilder;
use Z3d0X\FilamentFabricator\Models\Contracts\Page as PageContract;
use Z3d0X\FilamentFabricator\Resources\PageResource\Pages;


class PageResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $label = 'test';

    protected static ?string $navigationLabel = 'Archivio Articoli';
    protected static ?string $pluralModelLabel = 'Articoli';
    protected static ?string $recordTitleAttribute = 'title';

    public static function getModel(): string
    {
        return FilamentFabricator::getPageModel();
    }
    public static function form(Form $form): Form
    {

        // Nvan

        return $form
            ->columns(3)
            ->schema([
                Group::make()
                    ->schema([
                        Group::make()->schema(FilamentFabricator::getSchemaSlot('blocks.before')),

                        TextInput::make('title')->required()->maxLength(65)->label('Titolo')->reactive()->label('Titolo (massimo 6 5 caratteri spazi inclusi)')
                            ->afterStateUpdated(function (Closure $set, $state) {
                                $set('slug', Str::slug($state));
                            })->required(),

                        TextInput::make('titolo')->required()->maxLength(65)->label('Titolo originale')->reactive(),

                        TextInput::make('sottotitolo')->required(),

                        RichEditor::make('paragrafo')->label('Paragrafo principale'),

                        Select::make('categoria')
                            ->options([
                                'Prima squadra' => 'Prima squadra',
                                'Settore giovanile' => 'Settore giovanile',
                                'Eventi' => 'Eventi',
                                'Ticketing' => 'Ticketing',
                                'Merchandising' => 'Merchandising',
                                'Comunicati' => 'Comunicati ufficiali',
                                'Progetti speciali' => 'Progetti speciali',
                            ])->required(),


                        MarkdownEditor::make('meta')->required()->maxLength(155)->label('Meta description (massimo 155 caratteri spazi inclusi)')->toolbarButtons([]),

                        PageBuilder::make('blocks')
                            ->label(__('filament-fabricator::page-resource.labels.blocks')),

                        Group::make()->schema(FilamentFabricator::getSchemaSlot('blocks.after')),
                    ])
                    ->columnSpan(2),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Group::make()->schema(FilamentFabricator::getSchemaSlot('sidebar.before')),

                        Card::make()
                            ->schema([
                                // Placeholder::make('page_url')
                                //     ->visible(fn (?PageContract $record) => config('filament-fabricator.routing.enabled') && filled($record))
                                //     ->content(fn (?PageContract $record) => FilamentFabricator::getPageUrlFromId($record?->id)),

                                Select::make('autore')
                                    ->options([
                                        'Feralpisalò Media house' => 'Feralpisalò Media house',
                                    ])->disabled()->default('Feralpisalò Media house'),


                                DateTimePicker::make('published_at')->label('Data e ora di pubblicazione')->required(),

                                DateTimePicker::make('evidence_at')->label('Data e ora di evidenza'),

                                TagsInput::make('tag')->label('Tag')->separator(','),



                                // Hidden::make('is_slug_changed_manually')
                                //     ->default(false)
                                //     ->dehydrated(false),



                                // TextInput::make('slug')
                                //  ->label(__('filament-fabricator::page-resource.labels.slug'))
                                //   ->unique(ignoreRecord: true, callback: fn (Unique $rule, Closure $get) => $rule->where('parent_id', $get('parent_id'))),


                                TextInput::make('slug')
                                    ->label(__('filament-fabricator::page-resource.labels.slug'))
                                    ->unique(ignoreRecord: true, callback: fn (Unique $rule, Closure $get) => $rule->where('parent_id', $get('parent_id')))
                                    ->afterStateUpdated(function (Closure $set) {
                                        $set('is_slug_changed_manually', true);
                                    })
                                    ->rule(function ($state) {
                                        return function (string $attribute, $value, Closure $fail) use ($state) {
                                            if ($state !== '/' && (Str::startsWith($value, '/') || Str::endsWith($value, '/'))) {
                                                $fail(__('filament-fabricator::page-resource.errors.slug_starts_or_ends_with_slash'));
                                            }
                                        };
                                    })
                                    ->required(),
                                //     ->afterStateUpdated(function (Closure $set) {
                                //         $set('is_slug_changed_manually', true);
                                //     })
                                //     ->rule(function ($state) {
                                //         return function (string $attribute, $value, Closure $fail) use ($state) {
                                //             if ($state !== '/' && (Str::startsWith($value, '/') || Str::endsWith($value, '/'))) {
                                //                 $fail(__('filament-fabricator::page-resource.errors.slug_starts_or_ends_with_slash'));
                                //             }
                                //         };
                                //     })
                                //     ->required(),

                                Toggle::make('is_published')->label('Attivo'),
                                Toggle::make('is_evidence')->label('In Evidenza'),
                                
                                Select::make('layout')
                                    ->label(__('filament-fabricator::page-resource.labels.layout'))
                                    ->options(FilamentFabricator::getLayouts())
                                    ->default('default')
                                    ->required(),
                                    
                                Select::make('parent_id')
                                    ->label(__('filament-fabricator::page-resource.labels.parent'))
                                    ->options(function () {
                                        return FilamentFabricator::getPageModel()::query()
                                            ->whereNull('parent_id')
                                            ->pluck('title', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->placeholder(__('filament-fabricator::page-resource.labels.select_parent')),
                                    
                                FileUpload::make('immagine_evidenza')->image()->label('Immagine rilevanza (Risoluzione massima consigliata 1920x1080) ')->required()
                                    ->imageResizeTargetWidth('1920')
                                    ->imageResizeTargetHeight('1920')
                                    ->imageResizeMode('contain'),
                                FileUpload::make('immagine_verticale')->image()->label('Immagine verticale (Risoluzione massima consigliata 1080x1920) ')->required()
                                    ->imageResizeTargetWidth('1920')
                                    ->imageResizeTargetHeight('1920')
                                    ->imageResizeMode('contain'),
                            ]),

                        Group::make()->schema(FilamentFabricator::getSchemaSlot('sidebar.after')),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Titolo'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label(__('Data di publicazione'))
                    ->searchable()
                    ->sortable(),


                TextColumn::make('url')
                    ->label(__('filament-fabricator::page-resource.labels.url'))
                    ->toggleable()
                    ->getStateUsing(fn (?PageContract $record) => FilamentFabricator::getPageUrlFromId($record->id) ?: null)
                    ->url(fn (?PageContract $record) => FilamentFabricator::getPageUrlFromId($record->id) ?: null, true)
                    ->visible(config('filament-fabricator.routing.enabled', true)),

                TextColumn::make('categoria'),

                BooleanColumn::make('is_published')->searchable()->label('Attivo')->default(false),
                BooleanColumn::make('is_evidence')->searchable()->label('In Evidenza')->default(false),


                TextColumn::make('parent.title')
                    ->label(__('filament-fabricator::page-resource.labels.parent'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => $state ?? '-')
                    ->url(fn (?PageContract $record) => filled($record->parent_id) ? PageResource::getUrl('edit', ['record' => $record->parent_id]) : null),

            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('visit')
                    ->label(__('filament-fabricator::page-resource.actions.visit'))
                    ->url(fn (?PageContract $record) => FilamentFabricator::getPageUrlFromId($record->id, true) ?: null)
                    ->icon('heroicon-o-external-link')
                    ->openUrlInNewTab()
                    ->color('success')
                    ->visible(config('filament-fabricator.routing.enabled', true)),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
            ])
            ->defaultSort('published_at', 'desc');
        }

    public static function getLabel(): string
    {
        return __('filament-fabricator::page-resource.labels.page');
    }

    public static function getPluralLabel(): string
    {
        return __('filament-fabricator::page-resource.labels.pages');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'view' => Pages\ViewPage::route('/{record}'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
