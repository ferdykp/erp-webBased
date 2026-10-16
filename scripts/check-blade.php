<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$compiler = $app->make('blade.compiler');
$failed = 0; $count = 0;
foreach (Symfony\Component\Finder\Finder::create()->files()->in(__DIR__.'/../resources/views')->name('*.blade.php') as $file) {
    $compiled = $compiler->compileString($file->getContents());
    $temp = tempnam(sys_get_temp_dir(), 'beam-view-');
    file_put_contents($temp, $compiled);
    exec(PHP_BINARY.' -l '.escapeshellarg($temp).' 2>&1', $output, $status);
    if ($status) { echo $file->getRelativePathname().': '.implode("\n",$output)."\n"; ++$failed; }
    unlink($temp); $output = []; ++$count;
}
echo "$count views checked, $failed errors\n";
exit($failed ? 1 : 0);
