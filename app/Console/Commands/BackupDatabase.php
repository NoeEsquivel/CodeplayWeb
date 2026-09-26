<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'codeplay:backup {--keep=10 : Cantidad de respaldos que se conservarán}';

    protected $description = 'Crea y verifica un respaldo local de la base de datos de CodePlay';

    public function handle(): int
    {
        $keep = max(1, (int) $this->option('keep'));
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if (!is_string($database) || $database === '' || $database === ':memory:') {
            $this->error('La base de datos configurada no se puede respaldar como archivo.');
            return self::FAILURE;
        }

        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);
        $safeDatabase = preg_replace('/[^A-Za-z0-9_-]+/', '-', pathinfo($database, PATHINFO_FILENAME)) ?: 'codeplay';
        $stamp = now()->format('Y-m-d_H-i-s');

        try {
            $path = $connection === 'sqlite'
                ? $this->backupSqlite($database, $directory, $safeDatabase, $stamp)
                : $this->backupMysql($connection, $database, $directory, $safeDatabase, $stamp);

            clearstatcache(true, $path);
            if (!is_file($path) || filesize($path) === 0) {
                throw new RuntimeException('El archivo generado está vacío.');
            }

            $hash = hash_file('sha256', $path);
            File::put($path.'.sha256', $hash.'  '.basename($path).PHP_EOL);
            $this->removeOldBackups($directory, $keep);

            $this->info('Respaldo creado y verificado.');
            $this->line('Archivo: '.$path);
            $this->line('Tamaño: '.number_format((int) filesize($path)).' bytes');
            $this->line('SHA-256: '.$hash);
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('No se pudo crear el respaldo: '.$exception->getMessage());
            return self::FAILURE;
        }
    }

    private function backupSqlite(string $database, string $directory, string $safeDatabase, string $stamp): string
    {
        if (!is_file($database)) {
            throw new RuntimeException('No se encontró el archivo SQLite configurado.');
        }

        $path = $directory.DIRECTORY_SEPARATOR.$safeDatabase.'_'.$stamp.'.sqlite';
        if (!File::copy($database, $path)) {
            throw new RuntimeException('No se pudo copiar la base SQLite.');
        }
        return $path;
    }

    private function backupMysql(string $connection, string $database, string $directory, string $safeDatabase, string $stamp): string
    {
        if (!in_array($connection, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('El motor '.$connection.' todavía no está soportado por este comando.');
        }

        $config = config("database.connections.{$connection}");
        $binary = $this->findMysqlDump();
        $path = $directory.DIRECTORY_SEPARATOR.$safeDatabase.'_'.$stamp.'.sql';
        $process = new Process([
            $binary,
            '--host='.(string) ($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? '3306'),
            '--user='.(string) ($config['username'] ?? ''),
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
            '--result-file='.$path,
            $database,
        ], base_path(), ['MYSQL_PWD' => (string) ($config['password'] ?? '')]);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            File::delete($path);
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'mysqldump terminó con error.');
        }
        return $path;
    }

    private function findMysqlDump(): string
    {
        $configured = env('DB_DUMP_BINARY');
        $candidates = array_filter([
            is_string($configured) ? $configured : null,
            'E:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'E:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) return $candidate;
        }

        return 'mysqldump';
    }

    private function removeOldBackups(string $directory, int $keep): void
    {
        $files = collect(File::files($directory))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['sql', 'sqlite'], true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        foreach ($files->slice($keep) as $file) {
            File::delete($file->getPathname(), $file->getPathname().'.sha256');
        }
    }
}
