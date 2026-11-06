<?php

use App\Livewire\LandingPage;
use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Admin\Convites;
use App\Livewire\Admin\Dashboard;

Route::get('/', LandingPage::class)->name('home');

Route::get('/login', LoginForm::class)->name('login');

// Rota protegida pelo middleware 'auth'
Route::get('/dashboard', Dashboard::class)
    ->middleware('auth')
    ->name('dashboard');

Route::get('/dashboard/convites', Convites::class)
    ->middleware('auth')
    ->name('admin.convites');