<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WishlistToggleController;
use Illuminate\Support\Facades\Route;

// ----------------------------------------------------------------
// BOUTIQUE (espace public)
// ----------------------------------------------------------------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/produits', [ProductController::class, 'index'])->name('products.index');
Route::get('/produits/{slug}', [ProductController::class, 'show'])->name('products.show');

// Recherche
Route::get('/recherche', [SearchController::class, 'index'])->name('search');
Route::get('/api/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

// Panier
Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
Route::post('/panier', [CartController::class, 'store'])->name('cart.store');
Route::patch('/panier/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/panier/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

// Tunnel d'achat
Route::prefix('commande')->name('checkout.')->group(function () {
    Route::get('/livraison', [CheckoutController::class, 'shipping'])->name('shipping');
    Route::post('/livraison', [CheckoutController::class, 'storeShipping'])->name('shipping.store');
    Route::get('/paiement', [CheckoutController::class, 'payment'])->name('payment');
    Route::post('/paiement', [CheckoutController::class, 'storePayment'])->name('payment.store');
    Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('confirmation');
    Route::get('/confirmation/{order}/statut', [CheckoutController::class, 'checkPaymentStatus'])->name('payment.status');
    Route::get('/paiement/{order}/succes', [CheckoutController::class, 'confirmation'])->name('payment.success');
    Route::get('/paiement/{order}/echec', [CheckoutController::class, 'payment'])->name('payment.failed');
    Route::post('/paiement/webhook', [CheckoutController::class, 'checkPaymentStatus'])->name('payment.webhook');
});

// ----------------------------------------------------------------
// AUTHENTIFICATION
// ----------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/inscription', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisteredUserController::class, 'store']);

    Route::get('/connexion', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/connexion', [AuthenticatedSessionController::class, 'store']);

    Route::get('/mot-de-passe-oublie', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [NewPasswordController::class, 'store'])->name('password.store');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Profil client
    Route::get('/mon-compte', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/mon-compte', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/mon-compte/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/mes-commandes', [ProfileController::class, 'orders'])->name('orders.index');
    Route::get('/mes-commandes/{order}', [ProfileController::class, 'orderShow'])->name('orders.show');
    Route::get('/mes-commandes/{order}/facture', [ProfileController::class, 'orderInvoice'])->name('orders.invoice');
    Route::get('/ma-fidelite', [ProfileController::class, 'loyalty'])->name('profile.loyalty');
    Route::get('/ma-liste-de-souhaits', [ProfileController::class, 'wishlist'])->name('wishlist.index');
    Route::post('/ma-liste-de-souhaits/{product}', [WishlistToggleController::class, 'store'])->name('wishlist.toggle');

    // Carnet d'adresses
    Route::resource('adresses', AddressController::class)->except(['create', 'edit', 'show']);
});

require __DIR__.'/admin.php';
