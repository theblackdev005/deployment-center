<?php

namespace App\Services;

use App\Models\Deployment;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DeploymentRunner
{
    public function run(Deployment $deployment): void
    {
        $deployment->loadMissing(['project', 'domain.server']);
        $deployment->update(['status' => 'running', 'started_at' => now(), 'log' => null]);
        $workspace = storage_path('app/private/deployments/'.$deployment->id);

        try {
            $this->prepareWorkspace($workspace);
            $source = $workspace.'/source';
            $archive = $workspace.'/release.tar.gz';

            $this->record($deployment, 'Téléchargement du projet depuis GitHub.');
            $this->runProcess([
                'git', 'clone', '--depth', '1', '--single-branch', '--branch',
                $deployment->project->branch,
                $deployment->project->repository_url,
                $source,
            ], dirname($source));

            $this->prepareDependencies($deployment, $source);
            $this->removeLocalConfiguration($source);
            $this->createArchive($deployment, $source, $archive);
            $this->transfer($deployment, $archive);
            $this->verifyWebsite($deployment);

            $deployment->update([
                'status' => 'succeeded',
                'finished_at' => now(),
                'commit_hash' => $this->readCommitHash($source),
            ]);
            $this->record($deployment, 'Déploiement terminé avec succès.');
        } catch (Throwable $exception) {
            $deployment->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);
            $this->record($deployment, 'Échec : '.$exception->getMessage());

            throw $exception;
        } finally {
            $this->deleteDirectory($workspace);
        }
    }

    private function prepareDependencies(Deployment $deployment, string $source): void
    {
        if (is_file($source.'/composer.json')) {
            $this->record($deployment, 'Installation des dépendances PHP.');

            if (! is_file($source.'/.env') && is_file($source.'/.env.example')) {
                copy($source.'/.env.example', $source.'/.env');
            }

            $this->runProcess([
                'composer', 'install', '--no-dev', '--prefer-dist', '--optimize-autoloader', '--no-interaction',
            ], $source, 900);
        }

        if (is_file($source.'/package.json')) {
            $this->record($deployment, 'Compilation des fichiers du site.');
            $installCommand = is_file($source.'/package-lock.json') ? ['npm', 'ci'] : ['npm', 'install'];
            $this->runProcess($installCommand, $source, 900);
            $this->runProcess(['npm', 'run', 'build'], $source, 900);
        }
    }

    private function transfer(Deployment $deployment, string $archive): void
    {
        $server = $deployment->domain->server;
        $encryptedKey = Storage::disk('local')->get($server->ssh_key_path);

        if (! $encryptedKey) {
            throw new RuntimeException('La clé SSH du serveur est introuvable.');
        }

        $privateKey = PublicKeyLoader::loadPrivateKey(Crypt::decryptString($encryptedKey));
        $sftp = new SFTP($server->host, $server->port, 20);

        if (! $sftp->login($server->username, $privateKey)) {
            throw new RuntimeException('La connexion sécurisée au serveur a échoué.');
        }

        $this->assertHostFingerprint($server->fingerprint, $sftp->getServerPublicHostKey());

        $target = $deployment->domain->document_root;
        $parent = dirname($target);
        $files = $sftp->nlist($target);

        if ($files === false) {
            throw new RuntimeException('Le domaine est introuvable sur cet hébergement.');
        }

        $existingFiles = array_values(array_diff($files, ['.', '..']));
        $isHostingerPlaceholder = count($existingFiles) === 1
            && in_array('default.php', $existingFiles, true);

        if ($existingFiles && ! $isHostingerPlaceholder && ! $deployment->domain->is_installed) {
            throw new RuntimeException('Ce domaine contient déjà un site. Aucune modification n’a été effectuée.');
        }

        $remoteBase = '/home/'.$server->username.'/.deploy-center';
        $remoteArchive = $remoteBase.'/uploads/deployment-'.$deployment->id.'.tar.gz';
        $releasePath = $remoteBase.'/releases/deployment-'.$deployment->id;
        $backupPath = $remoteBase.'/backups/'.$deployment->domain->name.'/'.now()->format('Ymd-His').'.tar.gz';
        $ssh = new SSH2($server->host, $server->port, 20);

        if (! $ssh->login($server->username, $privateKey)) {
            throw new RuntimeException('Impossible de préparer le dossier de publication.');
        }

        $this->record($deployment, 'Envoi sécurisé de l’archive.');
        $prepareCommand = 'mkdir -p '.escapeshellarg(dirname($remoteArchive)).' '.escapeshellarg($releasePath).' '.escapeshellarg(dirname($backupPath));
        $ssh->exec($prepareCommand);

        if (! $sftp->put($remoteArchive, $archive, SFTP::SOURCE_LOCAL_FILE)) {
            throw new RuntimeException('Le transfert de l’archive a échoué.');
        }

        $this->record($deployment, 'Installation du projet sur le domaine.');
        $backupCommand = $existingFiles && ! $isHostingerPlaceholder
            ? 'tar -czf '.escapeshellarg($backupPath).' -C '.escapeshellarg($target).' . && '
            : '';

        if ($backupCommand) {
            $this->record($deployment, 'Sauvegarde de la version précédente : '.$backupPath);
        }

        $publishCommand = $backupCommand
            .'tar -xzf '.escapeshellarg($remoteArchive).' -C '.escapeshellarg($releasePath)
            .' && test -f '.escapeshellarg($releasePath.'/artisan')
            .' && test -f '.escapeshellarg($releasePath.'/public/index.php')
            .' && rm -f '.escapeshellarg($target.'/default.php')
            .' && cp -a '.escapeshellarg($releasePath.'/.').' '.escapeshellarg($target.'/')
            .' && mkdir -p '.escapeshellarg($target.'/storage/framework/cache/data').' '.escapeshellarg($target.'/storage/framework/sessions').' '.escapeshellarg($target.'/storage/framework/views').' '.escapeshellarg($target.'/storage/logs').' '.escapeshellarg($target.'/bootstrap/cache')
            .' && chmod -R u+rwX '.escapeshellarg($target.'/storage').' '.escapeshellarg($target.'/bootstrap/cache')
            .' && rm -rf '.escapeshellarg($releasePath).' '.escapeshellarg($remoteArchive);

        $output = $ssh->exec($publishCommand);

        $exitStatus = $ssh->getExitStatus();

        if ($exitStatus !== null && $exitStatus !== 0) {
            throw new RuntimeException('L’installation distante a échoué. '.$this->cleanOutput($output));
        }

        $deployment->update(['release_path' => $target]);
    }

    private function verifyWebsite(Deployment $deployment): void
    {
        $this->record($deployment, 'Vérification du domaine.');
        $response = Http::timeout(20)->withOptions(['allow_redirects' => false])->get('https://'.$deployment->domain->name);

        if ($response->serverError()) {
            throw new RuntimeException('Le domaine répond avec une erreur après le déploiement.');
        }

        $deployment->domain->update([
            'status' => 'online',
            'is_installed' => true,
            'last_checked_at' => now(),
        ]);
    }

    private function createArchive(Deployment $deployment, string $source, string $archive): void
    {
        $this->record($deployment, 'Création de l’archive de déploiement.');
        $excludedPaths = array_unique(array_merge([
            '.git', '.env', '.env.backup', '.env.production', '.DS_Store', 'node_modules',
            'storage/logs/*', 'storage/framework/sessions/*', 'storage/framework/views/*',
            'storage/app/installed.lock', 'storage/app/installation/config-backup.env',
            'storage/app/theme-settings.json', 'storage/app/loan-requests/*',
            'public/assets/images/branding/*',
        ], $deployment->project->excluded_paths ?? []));

        $command = ['tar', '-czf', $archive];

        foreach ($excludedPaths as $path) {
            $command[] = '--exclude='.$path;
        }

        array_push($command, '-C', $source, '.');
        $this->runProcess($command, dirname($source), 900, ['COPYFILE_DISABLE' => '1']);
    }

    private function removeLocalConfiguration(string $source): void
    {
        foreach (glob($source.'/.env*') ?: [] as $envFile) {
            if (basename($envFile) !== '.env.example') {
                @unlink($envFile);
            }
        }
    }

    private function runProcess(array $command, string $workingDirectory, int $timeout = 300, array $environment = []): void
    {
        $process = new Process($command, $workingDirectory, $environment ?: null, null, $timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException($this->cleanOutput($process->getErrorOutput() ?: $process->getOutput()));
        }
    }

    private function readCommitHash(string $source): ?string
    {
        $process = new Process(['git', 'rev-parse', 'HEAD'], $source);
        $process->run();

        return $process->isSuccessful() ? trim($process->getOutput()) : null;
    }

    private function assertHostFingerprint(?string $expected, $hostKey): void
    {
        if (! $expected || ! $hostKey) {
            return;
        }

        $actual = 'SHA256:'.base64_encode(hash('sha256', $hostKey, true));

        if (! hash_equals($expected, $actual)) {
            throw new RuntimeException('L’identité du serveur SSH a changé. Connexion arrêtée par sécurité.');
        }
    }

    private function prepareWorkspace(string $workspace): void
    {
        $this->deleteDirectory($workspace);

        if (! mkdir($workspace, 0700, true) && ! is_dir($workspace)) {
            throw new RuntimeException('Impossible de préparer le dossier temporaire.');
        }
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
        }

        @rmdir($directory);
    }

    private function record(Deployment $deployment, string $message): void
    {
        $deployment->update(['log' => trim(($deployment->log ? $deployment->log."\n" : '').'['.now()->format('H:i:s').'] '.$message)]);
    }

    private function cleanOutput(string $output): string
    {
        return mb_substr(trim(preg_replace('/\s+/', ' ', $output) ?: ''), 0, 500);
    }
}
