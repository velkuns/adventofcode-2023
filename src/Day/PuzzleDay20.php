<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day;

use Application\Day20\Enum\Pulse;
use Application\Day20\Module\Broadcast;
use Application\Day20\Module\Conjunction;
use Application\Day20\Module\FlipFlop;
use Application\Day20\Module\Module;
use Application\Day20\Signal;
use Application\Puzzle;

class PuzzleDay20 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        //~ Get modules
        $modules = $this->parseModules($inputs);

        //~ Init counters for pulses types
        $pulses  = [Pulse::Low->name => 0, Pulse::High->name => 0];

        //~ Press 1 000 times the button
        for ($i = 0; $i < 1_000; $i++) {

            /** @var \SplQueue<Signal> $signals */
            $signals = new \SplQueue();
            $signals->enqueue(new Signal(Pulse::Low, 'button', ['broadcaster']));

            //~ While we have signal to dispatch, run the loop
            while (!$signals->isEmpty()) {
                $signal = $signals->dequeue();

                //~ Count number of signal to dispatch and add it to counter
                $pulses[$signal->pulse->name] += $signal->count();

                //~ For each destination of signal, transmit the signal to the destination module & get response signal
                //~ from it. If we have a signal in output, enqueue it, otherwise, skip and continue
                foreach ($signal->destinations as $name) {
                    $newSignal = ($modules[$name] ?? null)?->receive($signal);
                    if ($newSignal !== null) {
                        $signals->enqueue($newSignal);
                    }
                }
            }
        }

        return (int) array_product($pulses);
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        //~ Get modules
        $modules = $this->parseModules($inputs);

        //~ Init counter for number of time the button will be pressed
        $pressed = 0;

        do {
            //~ Increase the counter for the number of times when the button is pressed
            $pressed++;

            //~ Reset the counter for the RX module have received a low pulse. We want to stop only when it received ONE
            //~ and ONLY ONE low pulse
            $countRxLow = 0;

            /** @var \SplQueue<Signal> $signals */
            $signals = new \SplQueue();
            $signals->enqueue(new Signal(Pulse::Low, 'button', ['broadcaster']));

            while (!$signals->isEmpty()) {
                $signal = $signals->dequeue();

                //~ For each destination of signal, transmit the signal to the destination module & get response signal
                //~ from it. If we have a signal in output, enqueue it, otherwise, skip and continue
                foreach ($signal->destinations as $name) {
                    //~ When 'rx' module received a low pulse, increase counter for rx low pulse
                    if ($name === 'rx' && $signal->pulse === Pulse::Low) {
                        $countRxLow++;
                    }

                    $newSignal = ($modules[$name] ?? null)?->receive($signal);
                    if ($newSignal !== null) {
                        $signals->enqueue($newSignal);
                    }
                }
            }

        } while($countRxLow !== 1); // Stop only when it received ONE low pulse.

        //~ Return the number of time when the button have been pressed
        return $pressed;
    }

    /**
     * @param list<string> $inputs
     * @return array<string, Module>
     */
    private function parseModules(array $inputs): array
    {
        $conjunctions = [];
        $modules      = [];
        foreach ($inputs as $line) {
            $module = $this->parseModule($line);
            if ($module instanceof Conjunction) {
                $conjunctions[$module->name] = $module;
            }

            $modules[$module->name] = $module;
        }

        foreach ($modules as $module) {
            foreach ($module->destinations as $name) {
                if (isset($conjunctions[$name])) {
                    $conjunctions[$name]->initMemory($module->name);
                }
            }
        }

        return $modules;
    }

    private function parseModule(string $line): Module
    {
        [$name, $destinations] = explode(' -> ', $line);
        $destinations          = explode(', ', $destinations);
        return match ($name[0]) {
            '%'     => new FlipFlop(substr($name, 1), $destinations),
            '&'     => new Conjunction(substr($name, 1), $destinations),
            default => new Broadcast($name, $destinations),
        };
    }
}
