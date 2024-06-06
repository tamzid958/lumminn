<?php

namespace App\Filament\Pages\Auth;

use Afatmustafa\FilamentTurnstile\Forms\Components\Turnstile;
use Filament\Forms\Form;

class Login extends \Filament\Pages\Auth\Login
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                Turnstile::make('turnstile')
                    ->size('nomral')
                    ->language('en-US'),
            ])
            ->statePath('data');
    }
}
