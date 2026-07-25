<?php

namespace App\Filament\TenantAdmin\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Actions\Action;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;

class WebsiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-globe-alt';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public function getTitle(): string
    {
        return 'Website Settings';
    }
    
    protected string $view = 'filament.tenant-admin.pages.website-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = tenant();
        $this->form->fill([
            'name' => $tenant->name,
            'tagline' => $tenant->tagline,
            'about_text' => $tenant->about_text,
            'theme_color' => $tenant->theme_color ?? '#0ea5e9',
            'default_locale' => $tenant->default_locale ?? 'en',
            'sections' => $tenant->sections ?? [
                ['type' => 'hero', 'is_visible' => true],
                ['type' => 'about', 'is_visible' => true],
                ['type' => 'doctors', 'is_visible' => true],
                ['type' => 'services', 'is_visible' => true],
                ['type' => 'chambers', 'is_visible' => true],
                ['type' => 'features', 'is_visible' => true],
                ['type' => 'faq', 'is_visible' => true],
                ['type' => 'contact', 'is_visible' => true],
            ],
            'social_links' => $tenant->social_links ?? [],
            'faq' => $tenant->faq ?? [],
            'features' => $tenant->features ?? [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Identity & Theme')
                    ->schema([
                        Components\TextInput::make('name')->required()->label('Clinic Name'),
                        Components\TextInput::make('tagline')->label('Tagline'),
                        Components\Textarea::make('about_text')->label('About Us')->rows(4),
                        Components\ColorPicker::make('theme_color')->label('Theme Colour'),
                        Components\Select::make('default_locale')
                            ->label('Default Language')
                            ->options([
                                'en' => 'English',
                                'bn' => 'Bengali (বাংলা)',
                            ]),
                    ])->columns(2),
                Components\Section::make('Page Sections (Order & Visibility)')
                    ->schema([
                        Components\Repeater::make('sections')
                            ->schema([
                                Components\Select::make('type')->options([
                                    'hero' => 'Hero',
                                    'about' => 'About Us',
                                    'doctors' => 'Doctors',
                                    'services' => 'Services & Lab Tests',
                                    'chambers' => 'Locations & Hours',
                                    'features' => 'Why Choose Us',
                                    'faq' => 'FAQ',
                                    'contact' => 'Contact Us',
                                ])->required()->disableOptionWhenSelectedInSiblingRepeaters(),
                                Components\Toggle::make('is_visible')->default(true)->label('Visible'),
                            ])
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                    ]),
                Components\Section::make('Features / Why Choose Us')
                    ->schema([
                        Components\Repeater::make('features')
                            ->schema([
                                Components\TextInput::make('title')->required(),
                                Components\Textarea::make('description'),
                            ])
                    ]),
                Components\Section::make('FAQ')
                    ->schema([
                        Components\Repeater::make('faq')
                            ->schema([
                                Components\TextInput::make('question')->required(),
                                Components\Textarea::make('answer')->required(),
                            ])
                    ]),
                Components\Section::make('Social Links')
                    ->schema([
                        Components\Repeater::make('social_links')
                            ->schema([
                                Components\Select::make('platform')->options([
                                    'facebook' => 'Facebook',
                                    'twitter' => 'Twitter/X',
                                    'instagram' => 'Instagram',
                                    'linkedin' => 'LinkedIn',
                                    'youtube' => 'YouTube',
                                ])->required(),
                                Components\TextInput::make('url')->url()->required(),
                            ])->columns(2)
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $tenant = tenant();
            $tenant->update($data);
            
            Notification::make()
                ->success()
                ->title('Settings saved successfully.')
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}
