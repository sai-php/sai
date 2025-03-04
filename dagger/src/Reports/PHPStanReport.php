<?php

declare(strict_types=1);

namespace DaggerModule\Reports;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\File;
use GraphQL\Exception\QueryError;

use function Dagger\dag;

#[DaggerObject]
final class PHPStanReport
{
    public function __construct(private int $exitCode, private string $stdout, private string $stderr)
    {
    }

    #[DaggerFunction]
    #[Doc('Returns the report from PHPStan')]
    public function report(): string
    {
        if ($this->exitCode !== 0) {
            throw new QueryError(['errors' => [[
                'message' => 'problem(s) found during static analysis',
                'extensions' => [
                    'exitCode' => $this->exitCode,
                    'stdout' => $this->stdout,
                    'stderr' => $this->stderr,
                ]]]]);
        }

        return $this->stdout;
    }

    #[DaggerFunction]
    #[Doc('Returns the report from PHPStan as a File')]
    public function logFile(): File
    {
        return dag()->directory()->withNewFile('logfile', $this->stdout)->file('logfile');
    }
}