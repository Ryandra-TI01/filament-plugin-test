<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->rule(Password::defaults()),

                        // Forms\Components\Select::make('roles')
                        //     ->relationship('roles', 'name')
                        //     ->multiple()
                        //     ->preload()
                        //     ->searchable()
                        //     ->required()
                        //     ->columnSpanFull(),

                        // Forms\Components\Toggle::make('is_active')
                        //     ->label('Active')
                        //     ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Info')
                    ->schema([
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At'),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable(),

                // Tables\Columns\BadgeColumn::make('roles.name')
                //     ->label('Roles')
                //     ->sortable()
                //     ->colors([
                //         'primary',
                //         'success' => fn ($state): bool => in_array($state, ['admin', 'super_admin']),
                //         'warning' => fn ($state): bool => $state === 'user',
                //     ]),

                // Tables\Columns\IconColumn::make('is_active')
                //     ->boolean()
                //     ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->label('Filter by Role'),

                Tables\Filters\Filter::make('is_active')
                    ->label('Active')
                    ->query(fn ($query) => $query->where('is_active', true)),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Kalau pakai plugin Filament Impersonate:
                // Tables\Actions\Action::make('impersonate')
                //     ->label('Login as User')
                //     ->url(fn (User $record): string => route('impersonate', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }

    // public static function getEloquentQuery()
    // {
    //     // kalau mau, batasi hanya super_admin yg bisa lihat semua
    //     return parent::getEloquentQuery()
    //         ->when(auth()->user()->cannot('viewAllUsers', User::class), function ($query) {
    //             $query->whereKey(auth()->id());
    //         });
    // }

    // public static function canAccess(): bool
    // {
    //     return auth()->user()?->hasAnyRole(['admin', 'super_admin']);
    // }
}
