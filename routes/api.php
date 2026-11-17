<?php

use App\Http\Controllers\Api\V1\ContactMessageController;
use App\Http\Controllers\Api\V1\DesignerController;
use App\Http\Controllers\Api\V1\DesignerGalleryController;
use App\Http\Controllers\Api\V1\MemberApplicationController;
use App\Http\Controllers\Api\V1\NavigationController;
use App\Http\Controllers\Api\V1\PressItemController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\StudioProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('navigation', NavigationController::class)->name('navigation');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');

    Route::get('designers', [DesignerController::class, 'index'])->name('designers.index');
    Route::get('designers/{designer:slug}', [DesignerController::class, 'show'])->name('designers.show');
    Route::get('designers/{designer:slug}/gallery', DesignerGalleryController::class)->name('designers.gallery');

    Route::get('press', [PressItemController::class, 'index'])->name('press.index');

    Route::get('studio', StudioProfileController::class)->name('studio');

    Route::post('contact-messages', [ContactMessageController::class, 'store'])
        ->middleware('throttle:contact')
        ->name('contact-messages.store');

    Route::post('member-applications', [MemberApplicationController::class, 'store'])
        ->middleware('throttle:applications')
        ->name('member-applications.store');
});
