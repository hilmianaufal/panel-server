<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Controller;
use App\Models\DeployProject;
use Illuminate\Http\Request;

class DeployController extends Controller
{
    public function index()
    {
        $projects = DeployProject::latest()->get();

        return view('admin.deploy.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'repository' => ['required', 'string', 'max:500'],
            'branch' => ['required', 'string', 'max:100'],
            'project_path' => ['required', 'string', 'max:500'],
        ]);

        $project = DeployProject::create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($project)
            ->withProperties($validated)
            ->log('Deploy project ditambahkan');

        return back()->with('success', 'Project deploy berhasil ditambahkan.');
    }

    public function destroy(DeployProject $project)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($project)
            ->withProperties([
                'name' => $project->name,
                'repository' => $project->repository,
            ])
            ->log('Deploy project dihapus');

        $project->delete();

        return back()->with('success', 'Project deploy berhasil dihapus.');
    }

    public function deploy(DeployProject $project): RedirectResponse
{
    if (PHP_OS_FAMILY !== 'Linux') {
        return back()->with('error', 'Deploy hanya bisa dijalankan di Ubuntu/Linux Server.');
    }

    if (! is_dir($project->project_path)) {
        return back()->with('error', 'Project path tidak ditemukan: ' . $project->project_path);
    }

    $commands = [
        "cd {$project->project_path} && git pull origin {$project->branch}",
        "cd {$project->project_path} && composer install --no-dev --optimize-autoloader",
        "cd {$project->project_path} && php artisan migrate --force",
        "cd {$project->project_path} && php artisan optimize",
    ];

    $logs = [];

    foreach ($commands as $command) {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run();

        $logs[] = '$ ' . $command;
        $logs[] = $process->getOutput();
        $logs[] = $process->getErrorOutput();

        if (! $process->isSuccessful()) {
            return back()->with('error', implode("\n", $logs));
        }
    }

    $project->update([
        'last_deployed_at' => now(),
    ]);

    activity()
        ->causedBy(auth()->user())
        ->performedOn($project)
        ->withProperties([
            'project' => $project->name,
            'branch' => $project->branch,
        ])
        ->log('Project dideploy');

    return back()->with('success', "Deploy berhasil.\n\n" . implode("\n", $logs));
}
}