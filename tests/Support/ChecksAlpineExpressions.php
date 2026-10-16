<?php
namespace Tests\Support;

trait ChecksAlpineExpressions
{
    private function assertAlpineExpressionsParse(string $html, string $page): void
    {
        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $expressions = [];
        foreach ($document->getElementsByTagName('*') as $element) {
            foreach ($element->attributes as $attribute) {
                $name = $attribute->name;
                $value = trim($attribute->value);
                if ($value === '') continue;
                if (str_starts_with($name, 'x-model')) $body = "({$value}) = value;";
                elseif (str_starts_with($name, '@') || str_starts_with($name, 'x-on:') || in_array($name, ['x-init', 'x-effect'])) $body = $value;
                elseif (str_starts_with($name, ':') || str_starts_with($name, 'x-bind:') || in_array($name, ['x-data', 'x-show', 'x-text', 'x-html'])) $body = "return ({$value});";
                else continue;
                $expressions[] = ['attribute' => $name, 'body' => $body];
            }
        }
        $script = <<<'JS'
const chunks=[]; for await (const chunk of process.stdin) chunks.push(chunk);
const AsyncFunction=Object.getPrototypeOf(async function(){}).constructor;
for (const expression of JSON.parse(Buffer.concat(chunks).toString())) {
    try { new AsyncFunction('$data','$event','value',`with($data){${expression.body}\n}`); }
    catch(error) { console.error(`${expression.attribute}: ${error.message}\n${expression.body}`); process.exitCode=1; }
}
JS;
        $process = new \Symfony\Component\Process\Process(['node', '--input-type=module', '-e', $script]);
        $process->setInput(json_encode($expressions, JSON_THROW_ON_ERROR));
        $process->run();
        $this->assertTrue($process->isSuccessful(), $page.' Alpine directive syntax: '.$process->getErrorOutput());
    }
}
