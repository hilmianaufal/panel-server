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

    $projectPath = rtrim($project->project_path, '/');
    $parentPath = dirname($projectPath);

    $deployUser = env('DEPLOY_USER', 'www-data');

    $safeProjectPath = escapeshellarg($projectPath);
    $safeParentPath = escapeshellarg($parentPath);
    $safeRepository = escapeshellarg($project->repository);
    $safeBranch = escapeshellarg($project->branch);

    $commands = [];

    if (! is_dir($projectPath)) {
        $commands[] = "sudo mkdir -p {$safeParentPath}";
        $commands[] = "sudo chown -R {$deployUser}:www-data {$safeParentPath}";
        $commands[] = "git clone -b {$safeBranch} {$safeRepository} {$safeProjectPath}";
    } else {
        $commands[] = "cd {$safeProjectPath} && git config --global --add safe.directory {$safeProjectPath}";
        $commands[] = "cd {$safeProjectPath} && git pull origin {$safeBranch}";
    }

    $commands[] = "sudo chown -R {$deployUser}:www-data {$safeProjectPath}";
    $commands[] = "sudo find {$safeProjectPath} -type d -exec chmod 775 {} \\;";
    $commands[] = "sudo find {$safeProjectPath} -type f -exec chmod 664 {} \\;";
    $commands[] = "cd {$safeProjectPath} && composer install --no-dev --optimize-autoloader";
    $commands[] = "cd {$safeProjectPath} && php artisan migrate --force";
    $commands[] = "cd {$safeProjectPath} && php artisan optimize";

    $logs = [];

    foreach ($commands as $command) {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(600);
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
            'repository' => $project->repository,
            'branch' => $project->branch,
            'path' => $projectPath,
        ])
        ->log('Project dideploy');

    return back()->with('success', "Deploy berhasil.\n\n" . implode("\n", $logs));
}
}