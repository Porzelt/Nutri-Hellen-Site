<?php

use App\Livewire\LandingPage;
use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\LoginForm;
use App\Http\Middleware\CabecalhosPaciente;
use App\Livewire\Anamnese\Formulario;
use App\Livewire\Admin\Convites;
use App\Livewire\Admin\Dashboard;

Route::get('/', LandingPage::class)->name('home');

Route::get('/login', LoginForm::class)->name('login');

Route::view('/privacidade', 'privacidade')->name('privacidade');

// Mapa do site para o Google (só páginas públicas)
Route::get('/sitemap.xml', function () {
    $paginas = [route('home'), route('privacidade')];
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($paginas as $url) {
        $xml .= '  <url><loc>'.e($url).'</loc></url>'."\n";
    }

    return response($xml.'</urlset>'."\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

// Página do paciente (anamnese). O token tem 48 caracteres aleatórios.
Route::get('/anamnese/{token}', Formulario::class)
    ->where('token', '[A-Za-z0-9]{40,64}')
    ->middleware(['throttle:30,1', CabecalhosPaciente::class])
    ->name('anamnese');

// Rota protegida pelo middleware 'auth'
Route::get('/dashboard', Dashboard::class)
    ->middleware('auth')
    ->name('dashboard');

Route::get('/dashboard/convites', Convites::class)
    ->middleware('auth')
    ->name('admin.convites');