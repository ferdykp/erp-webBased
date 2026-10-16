<?php
namespace Tests\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;

/** Apply incremental migrations and roll back test transactions; never drop a database. */
trait NonDestructiveDatabase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        if (!(($connection === 'sqlite' && $database === ':memory:')
            || ($connection === 'mysql' && in_array($database, ['beamcustom_codex_ux_20261016', 'beamcustom_codex_ux_20261016_regression'], true)))) {
            throw new \RuntimeException('Tests require the isolated SQLite memory database or the dedicated BeamCustom UX schema.');
        }
    }

    protected function migrateDatabases(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    }
}
