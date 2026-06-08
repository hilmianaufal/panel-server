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

        $deployUser = env('DEPLOY_USER', 'hilmidev');

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

        if (! file_exists($projectPath . '/.env')) {
            $commands[] = "cd {$safeProjectPath} && cp .env.example .env";
        }

        if ($project->auto_database) {
            $dbName = $project->db_name;
            $dbUser = $project->db_username;
            $dbPass = $project->db_password;

            $mysqlUser = config('database.connections.mysql.username');
            $mysqlPass = config('database.connections.mysql.password');

            $safeMysqlUser = escapeshellarg($mysqlUser);
            $safeMysqlPass = $mysqlPass ? '-p' . escapeshellarg($mysqlPass) : '';

            $safeDbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);
            $safeDbUser = preg_replace('/[^a-zA-Z0-9_]/', '', $dbUser);
            $safeDbPass = str_replace("'", "\\'", $dbPass);

            $commands[] = "mysql -u {$safeMysqlUser} {$safeMysqlPass} -e \"CREATE DATABASE IF NOT EXISTS `{$safeDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci\"";
            $commands[] = "mysql -u {$safeMysqlUser} {$safeMysqlPass} -e \"CREATE USER IF NOT EXISTS '{$safeDbUser}'@'localhost' IDENTIFIED BY '{$safeDbPass}'\"";
            $commands[] = "mysql -u {$safeMysqlUser} {$safeMysqlPass} -e \"GRANT ALL PRIVILEGES ON `{$safeDbName}`.* TO '{$safeDbUser}'@'localhost'\"";
            $commands[] = "mysql -u {$safeMysqlUser} {$safeMysqlPass} -e \"FLUSH PRIVILEGES\"";

            $commands[] = "cd {$safeProjectPath} && sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env";
            $commands[] = "cd {$safeProjectPath} && sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env";
            $commands[] = "cd {$safeProjectPath} && sed -i 's/^DB_PORT=.*/DB_PORT=3306/' .env";
            $commands[] = "cd {$safeProjectPath} && sed -i 's/^DB_DATABASE=.*/DB_DATABASE={$safeDbName}/' .env";
            $commands[] = "cd {$safeProjectPath} && sed -i 's/^DB_USERNAME=.*/DB_USERNAME={$safeDbUser}/' .env";
            $commands[] = "cd {$safeProjectPath} && sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD={$safeDbPass}/' .env";
        }

        $commands[] = "cd {$safeProjectPath} && php artisan key:generate --force";

        if ($project->auto_database) {
            $commands[] = "cd {$safeProjectPath} && php artisan migrate --force";
        }

        $commands[] = "cd {$safeProjectPath} && php artisan optimize";

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