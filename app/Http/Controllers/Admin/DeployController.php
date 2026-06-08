<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeployProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

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
            'auto_database' => ['nullable', 'boolean'],
        ]);

        $validated['auto_database'] = $request->boolean('auto_database');

        if ($validated['auto_database']) {
            $dbName = Str::slug($validated['name'], '_');

            $validated['db_name'] = $dbName;
            $validated['db_username'] = $dbName;
            $validated['db_password'] = Str::random(20);
        }

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

    $deployUser = config('app.deploy_user', 'hilmidev');

    $safeProjectPath = escapeshellarg($projectPath);
    $safeParentPath = escapeshellarg($parentPath);
    $safeRepository = escapeshellarg($project->repository);
    $safeBranch = escapeshellarg($project->branch);

    $commands = [];

    if (! is_dir($projectPath)) {
        $commands[] = "sudo mkdir -p {$safeParentPath}";
        $commands[] = "sudo chown -R {$deployUser}:www-data {$safeParentPath}";
        $commands[] = "sudo -u {$deployUser} git clone -b {$safeBranch} {$safeRepository} {$safeProjectPath}";
    } else {
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} git config --global --add safe.directory {$safeProjectPath}";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} git pull origin {$safeBranch}";
    }

    $commands[] = "sudo chown -R {$deployUser}:www-data {$safeProjectPath}";
    $commands[] = "sudo find {$safeProjectPath} -type d -exec chmod 775 {} \\;";
    $commands[] = "sudo find {$safeProjectPath} -type f -exec chmod 664 {} \\;";

    $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} composer install --no-dev --optimize-autoloader --no-scripts";

    if (! file_exists($projectPath . '/.env')) {
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} cp .env.example .env";
    }

    if ($project->auto_database) {
        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $project->db_name);
        $dbUser = preg_replace('/[^a-zA-Z0-9_]/', '', $project->db_username);
        $dbPass = str_replace("'", "\\'", $project->db_password);

        $mysqlUser = config('app.mysql_admin_user', 'hilmi');
        $mysqlPass = config('app.mysql_admin_password');

        $mysqlLogin = "-u " . escapeshellarg($mysqlUser);

        if (! empty($mysqlPass)) {
            $mysqlLogin .= " -p" . escapeshellarg($mysqlPass);
        }

        $commands[] = "mysql {$mysqlLogin} -e \"CREATE DATABASE IF NOT EXISTS {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci\"";
        $commands[] = "mysql {$mysqlLogin} -e \"CREATE USER IF NOT EXISTS '{$dbUser}'@'localhost' IDENTIFIED BY '{$dbPass}'\"";
        $commands[] = "mysql {$mysqlLogin} -e \"GRANT ALL PRIVILEGES ON {$dbName}.* TO '{$dbUser}'@'localhost'\"";
        $commands[] = "mysql {$mysqlLogin} -e \"FLUSH PRIVILEGES\"";

        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sed -i 's/^DB_PORT=.*/DB_PORT=3306/' .env";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sed -i 's/^DB_DATABASE=.*/DB_DATABASE={$dbName}/' .env";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sed -i 's/^DB_USERNAME=.*/DB_USERNAME={$dbUser}/' .env";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD={$dbPass}/' .env";

        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sh -c \"grep -q '^DB_CONNECTION=' .env || echo 'DB_CONNECTION=mysql' >> .env\"";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sh -c \"grep -q '^DB_HOST=' .env || echo 'DB_HOST=127.0.0.1' >> .env\"";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sh -c \"grep -q '^DB_PORT=' .env || echo 'DB_PORT=3306' >> .env\"";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sh -c \"grep -q '^DB_DATABASE=' .env || echo 'DB_DATABASE={$dbName}' >> .env\"";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sh -c \"grep -q '^DB_USERNAME=' .env || echo 'DB_USERNAME={$dbUser}' >> .env\"";
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} sh -c \"grep -q '^DB_PASSWORD=' .env || echo 'DB_PASSWORD={$dbPass}' >> .env\"";
    }

    $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} php -r \"file_put_contents('.env', preg_replace('/^APP_KEY=.*/m', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), file_get_contents('.env')));\"";

    $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} php artisan package:discover --ansi";

    if ($project->auto_database) {
        $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} php artisan migrate --force";
    }

    $commands[] = "cd {$safeProjectPath} && sudo -u {$deployUser} php artisan optimize";

    $commands[] = "sudo chmod -R 775 {$safeProjectPath}/storage {$safeProjectPath}/bootstrap/cache";
    $commands[] = "sudo chown -R {$deployUser}:www-data {$safeProjectPath}";

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
            'auto_database' => $project->auto_database,
            'db_name' => $project->db_name,
        ])
        ->log('Project dideploy');

    return back()->with('success', "Deploy berhasil.\n\n" . implode("\n", $logs));
}
}