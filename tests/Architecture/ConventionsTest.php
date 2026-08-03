<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;

/**
 * Conventions from PROJECT_PLAN §8.4. These exist so the rules survive contact
 * with a deadline: reviewers forget, CI does not.
 */
arch('controllers are final and extend the base controller')
    ->expect('App\Http\Controllers')
    ->toExtend(Controller::class)
    ->toBeFinal()
    ->ignoring(Controller::class);

arch('form requests are final')
    ->expect('App\Http\Requests')
    ->toBeFinal();

arch('jobs are final and queued')
    ->expect('App\Jobs')
    ->toBeFinal()
    ->toImplement(ShouldQueue::class);

arch('console commands are final')
    ->expect('App\Console\Commands')
    ->toBeFinal()
    ->toExtend(Command::class);

arch('middleware is final')
    ->expect('App\Http\Middleware')
    ->toBeFinal();

it('keeps Eloquent models out of the HTTP and console layers', function (): void {
    $offenders = [];

    foreach (['Http', 'Console', 'Jobs', 'Providers'] as $layer) {
        $path = app_path($layer);

        if (! is_dir($path)) {
            continue;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $source = (string) file_get_contents($file->getPathname());

            if (preg_match('#^\s*(final\s+)?class\s+\w+\s+extends\s+Model#m', $source) === 1) {
                $offenders[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }
    }

    expect($offenders)->toBe([]);
});

it('confirms the Model class is the one being guarded against', function (): void {
    // Guards the regex above against a rename of Eloquent's base class.
    expect(class_exists(Model::class))->toBeTrue();
});
