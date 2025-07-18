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
use Z3d0X\FilamentFabricator\Models\Page;
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
        return $form
            ->schema([
                // Campi esistenti
                TextInput::make('title')
                    ->required()
                    ->label(__('filament-fabricator::page-resource.labels.title')),
                
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label(__('filament-fabricator::page-resource.labels.slug')),
                
                // AGGIUNGI QUESTI CAMPI MANCANTI:
                TextInput::make('sottotitolo')
                    ->label('Sottotitolo')
                    ->default(''),
                    
                TextInput::make('meta')
                    ->label('Meta')
                    ->default(''),
                    
                TextInput::make('categoria')
                    ->label('Categoria')
                    ->default(''),
                    
                TextInput::make('tag')
                    ->label('Tag')
                    ->default(''),
                    
                DateTimePicker::make('published_at')
                    ->label('Data di pubblicazione')
                    ->default(now()),
                    
                FileUpload::make('immagine_evidenza')
                    ->label('Immagine in evidenza')
                    ->image()
                    ->directory('pages/featured'),
                    
                FileUpload::make('immagine_verticale')
                    ->label('Immagine verticale')
                    ->image()
                    ->directory('pages/vertical')
                    ->nullable(),
                    
                Select::make('layout')
                    ->options(FilamentFabricator::getLayouts())
                    ->required()
                    ->default('default')
                    ->label(__('filament-fabricator::page-resource.labels.layout')),
                    
                Select::make('parent_id')
                    ->options(Page::pluck('title', 'id'))
                    ->searchable()
                    ->label(__('filament-fabricator::page-resource.labels.parent')),
                    
                Toggle::make('is_published')
                    ->label('Pubblicato')
                    ->default(false),
                    
                Toggle::make('is_evidence')
                    ->label('In evidenza')
                    ->default(false),
                    
                // Page Builder (campo esistente)
                PageBuilder::make('blocks')
                    ->label(__('filament-fabricator::page-resource.labels.blocks'))
                    ->blocks(FilamentFabricator::getPageBlocks()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('ID'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('title')
                    ->label(__('Titolo'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label(__('Data di pubblicazione'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'Non pubblicato'),

                TextColumn::make('created_at')
                    ->label(__('Data di creazione'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'Non disponibile'),

                TextColumn::make('updated_at')
                    ->label(__('Ultima modifica'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'Non disponibile'),


                TextColumn::make('url')
                    ->label(__('filament-fabricator::page-resource.labels.url'))
                    ->toggleable()
                    ->getStateUsing(fn (?PageContract $record) => FilamentFabricator::getPageUrlFromId($record->id) ?: null)
                    ->url(fn (?PageContract $record) => FilamentFabricator::getPageUrlFromId($record->id) ?: null, true)
                    ->visible(fn () => (bool) config('filament-fabricator.routing.enabled', true)),

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
                    ->visible(fn () => (bool) config('filament-fabricator.routing.enabled', true)),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('is_published')
                    ->label('Stato')
                    ->options([
                        '1' => 'Pubblicati',
                        '0' => 'Non pubblicati',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            '1' => $query->where('is_published', true),
                            '0' => $query->where('is_published', false),
                            default => $query,
                        };
                    }),
            ]);
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
