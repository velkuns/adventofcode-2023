<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Script;

use Application\PuzzleInterface;
use Eureka\Component\Console\AbstractScript;
use Eureka\Component\Console\Color\Bit8StandardColor;
use Eureka\Component\Console\Option\Option;
use Eureka\Component\Console\Option\Options;
use Eureka\Component\Console\Help;
use Eureka\Component\Console\Style\Style;

class Solve extends AbstractScript
{
    private string $dataDir;

    public function __construct()
    {
        $this->setExecutable();
        $this->setDescription('Solver');

        $this->initOptions(
            (new Options())
                ->add(
                    new Option(
                        shortName:   'd',
                        longName:    'day',
                        description: 'Day to solve',
                        mandatory:   true,
                        hasArgument: true,
                        default:     (int) date('d'),
                    )
                )
                ->add(
                    new Option(
                        shortName:   'e',
                        longName:    'example',
                        description: 'Example Only',
                    )
                )
                ->add(
                    new Option(
                        shortName:   'f',
                        longName:    'functional',
                        description: 'Activate Solve in functional programming style',
                        default:     false,
                    )
                )
                ->add(
                    new Option(
                        shortName:   's',
                        longName:    'skip-empty-lines',
                        description: 'Remove empty lines from inputs',
                        default:     false,
                    )
                )
                ->add(
                    new Option(
                        longName:    'render',
                        description: 'Use render script instead of solver script (if present)',
                        default:     false,
                    )
                )
        );

        $this->dataDir = (string) realpath(__DIR__ . '/../data');
    }

    public function help(): void
    {
        (new Help(self::class, $this->declaredOptions(), $this->output(), $this->options()))
            ->display()
        ;
    }

    public function run(): void
    {
        $day   = (int) $this->options()->value('d', 'day');
        $class = "\Application\Day\PuzzleDay$day";

        /** @var PuzzleInterface $solver */
        $solver = new $class();

        $this->output()->writeln('');
        $this->solveExamples($day, $solver);

        $this->solvePuzzle($day, $solver);
        $this->output()->writeln('');

    }

    private function solvePuzzle(int $day, PuzzleInterface $solver): void
    {
        if ($this->options()->value('e', 'example') === true) {
            return;
        }

        $file = "$this->dataDir/day-$day/inputs.txt";

        if (!file_exists($file)) {
            throw new \RuntimeException("No file for day $day!");
        }

        $white  = (new Style($this->options()))->bold();
        $yellow = (new Style())->color(Bit8StandardColor::Yellow);
        $cyan   = (new Style())->color(Bit8StandardColor::Cyan);
        $red    = (new Style())->color(Bit8StandardColor::Red);

        $doSolveFunctional = (bool) $this->options()->value('f', 'functional');
        $functionalSuffix  = $doSolveFunctional ? '(FUNCTIONAL)' : '';
        $line              = \str_repeat('-', 22);

        /** @var array<string> $inputs */
        $inputs = (array) \file($file);
        $inputs = \array_map(\trim(...), $inputs); // remove trailing chars
        $inputs = $this->cleanEmptyLines($inputs); // remove trailing chars

        $this->output()->writeln($white->apply("$line OUTPUT $functionalSuffix $line"));

        $timeOnePart   = -\microtime(true);
        $solvePartOne  = \str_pad($solver->solve(1, $inputs, $doSolveFunctional), 10);
        $timePartOne   = '[' . \round($timeOnePart + \microtime(true), 5) . 's]';
        $memoryPartOne = '[' . \round(\memory_get_peak_usage() / 1024 / 1024, 1) . 'MB]';
        $this->output()->writeln(
            $yellow->apply('*') . '  : ' .
            $cyan->apply($solvePartOne) . ' - ' .
            $red->apply($timePartOne) . ' - ' .
            $yellow->apply($memoryPartOne)
        );

        $timePartTwo   = -microtime(true);
        $solvePartTwo  = \str_pad($solver->solve(2, $inputs, $doSolveFunctional), 10);
        $timePartTwo   = '[' . round($timePartTwo + microtime(true), 5) . 's]';
        $memoryPartTwo = '[' . round(memory_get_peak_usage() / 1024 / 1024, 1) . 'MB]';

        $this->output()->writeln(
            $yellow->apply('**') . ' : ' .
            $cyan->apply($solvePartTwo) . ' - ' .
            $red->apply($timePartTwo) . ' - ' .
            $yellow->apply($memoryPartTwo)
        );
    }

    private function solveExamples(int $day, PuzzleInterface $solver): void
    {
        $white  = (new Style($this->options()))->bold();
        $yellow = (new Style())->color(Bit8StandardColor::Yellow);
        $cyan   = (new Style())->color(Bit8StandardColor::Cyan);

        $doSolveFunctional = (bool) $this->options()->value('f', 'functional');
        $functionalSuffix  = $doSolveFunctional ? '(FUNCTIONAL)' : '';
        $line              = str_repeat('-', 21);

        $this->output()->writeln($white->apply("$line EXAMPLES $functionalSuffix $line"));
        foreach ([1, 2] as $part) {
            $examples = $this->getExamples($day, $part);
            foreach ($examples as $data) {
                foreach ($data as $expected => $inputs) {
                    $inputs = $this->cleanEmptyLines($inputs);
                    $answer = $solver->solve($part, $inputs, $doSolveFunctional);

                    $stars = \str_pad(\str_repeat('*', $part), 2);
                    $this->output()->writeln(
                        $yellow->apply($stars . ' : ') . $cyan->apply($answer) . ' - expected: ' . $expected
                    );
                }
            }
        }
    }

    /**
     * @return list<array<string|int|float, list<string>>>
     */
    private function getExamples(int $day, int $part): array
    {
        $filesInputs   = \glob("$this->dataDir/day-$day/inputs-part-$part-example-*.txt");
        $filesExpected = \glob("$this->dataDir/day-$day/inputs-part-$part-expected-*.txt");

        if (empty($filesInputs) || empty($filesExpected) || count($filesInputs) !== count($filesExpected)) {
            return [];
        }

        $examples = [];
        for ($f = 0; $f < \count($filesInputs); $f++) {
            $expected = \trim((string) \file_get_contents($filesExpected[$f]));
            if (\ctype_digit($expected)) {
                $expected = (int) $expected;
            } elseif (\is_numeric($expected)) {
                $expected = (float) $expected;
            }

            /** @var array<string> $inputs */
            $inputs     = (array) \file($filesInputs[$f]);
            $inputs     = \array_map(\trim(...), $inputs);
            $examples[] = [$expected => $inputs];
        }

        return $examples;
    }

    /**
     * @param list<string> $inputs
     * @return list<string>
     */
    private function cleanEmptyLines(array $inputs): array
    {
        if ($this->options()->value('skip-empty-lines', 's') === false) {
            return $inputs;
        }

        return \array_filter($inputs, fn(string $input) => $input !== '');
    }
}
