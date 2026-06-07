<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\DeployController;
use App\Http\Controllers\Admin\FileManagerController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SecurityLoginController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SslController;
use App\Http\Controllers\Admin\TunnelManagerController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    abort(404);
});

Route::get('/dashboard', function () {
    return redirect('/admin/dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])
        ->name('two-factor.challenge');

    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'verify'])
        ->name('two-factor.verify');

    Route::get('/admin/security-login/2fa', [TwoFactorController::class, 'setup'])
        ->name('admin.security-login.2fa');

    Route::post('/admin/security-login/2fa/enable', [TwoFactorController::class, 'enable'])
        ->name('admin.security-login.2fa.enable');

    Route::post('/admin/security-login/2fa/disable', [TwoFactorController::class, 'disable'])
        ->name('admin.security-login.2fa.disable');
});

Route::middleware(['auth', '2fa'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services/{service}/restart', [ServiceController::class, 'restart'])->name('services.restart');

    Route::get('/websites', [WebsiteController::class, 'index'])->name('websites.index');
    Route::post('/websites', [WebsiteController::class, 'store'])->name('websites.store');
    Route::get('/websites/{website}/edit', [WebsiteController::class, 'edit'])->name('websites.edit');
    Route::put('/websites/{website}', [WebsiteController::class, 'update'])->name('websites.update');
    Route::get('/websites/{website}/nginx-config', [WebsiteController::class, 'generateNginxConfig'])->name('websites.nginx-config');
    Route::post('/websites/{website}/deploy-nginx', [WebsiteController::class, 'deployNginxConfig'])->name('websites.deploy-nginx');
    Route::delete('/websites/{website}', [WebsiteController::class, 'destroy'])->name('websites.destroy');

    Route::get('/files', [FileManagerController::class, 'index'])->name('files.index');
    Route::get('/files/view', [FileManagerController::class, 'show'])->name('files.show');
    Route::get('/files/edit', [FileManagerController::class, 'edit'])->name('files.edit');
    Route::put('/files/update', [FileManagerController::class, 'update'])->name('files.update');
    Route::post('/files/create-folder', [FileManagerController::class, 'createFolder'])->name('files.create-folder');
    Route::post('/files/upload', [FileManagerController::class, 'upload'])->name('files.upload');
    Route::delete('/files/delete', [FileManagerController::class, 'destroy'])->name('files.destroy');

    Route::get('/databases', [DatabaseController::class, 'index'])->name('databases.index');
    Route::post('/databases', [DatabaseController::class, 'store'])->name('databases.store');
    Route::post('/databases/{database}/backup', [DatabaseController::class, 'backup'])->name('databases.backup');
    Route::delete('/databases/{database}', [DatabaseController::class, 'destroy'])->name('databases.destroy');

    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::post('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

    Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
    Route::get('/security-login', [SecurityLoginController::class, 'index'])->name('security-login.index');
    Route::get('/activities', [ActivityLogController::class, 'index'])->name('activities.index');
    Route::get('/deploy', [DeployController::class, 'index'])
    ->name('deploy.index');

    Route::get('/deploy', [DeployController::class, 'index'])->name('deploy.index');
    Route::post('/deploy', [DeployController::class, 'store'])->name('deploy.store');
    Route::delete('/deploy/{project}', [DeployController::class, 'destroy'])->name('deploy.destroy');

    Route::post('/deploy/{project}/run', [DeployController::class, 'deploy'])
    ->name('deploy.run');

    Route::get('/tunnels', [TunnelManagerController::class, 'index'])
    ->name('tunnels.index');

    Route::post('/tunnels', [TunnelManagerController::class, 'store'])
        ->name('tunnels.store');

     Route::post('/tunnels/test', [TunnelManagerController::class, 'test'])
    ->name('tunnels.test');

    Route::post('/tunnels/hostnames', [TunnelManagerController::class, 'addHostname'])
    ->name('tunnels.hostnames.store');

Route::delete('/tunnels/hostnames', [TunnelManagerController::class, 'deleteHostname'])
    ->name('tunnels.hostnames.delete');
    Route::get('/ssl', [SslController::class, 'index'])->name('ssl.index');

Route::post('/ssl/{website}/generate', [SslController::class, 'generate'])
    ->name('ssl.generate');

Route::post('/ssl/renew', [SslController::class, 'renew'])
    ->name('ssl.renew');


});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';