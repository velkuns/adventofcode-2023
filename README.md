# Advent Of Code - 2023


## Install

```bash
$ composer install
```

## Add New Puzzle Solver
Do add new Solver for a day, you need to create a new Class `PuzzleDay{Number}.php` in `src/Day/`.
You can copy / paste the `src/Day/PuzzleDay0.php` and rename it to have base class puzzle day solver.

For the input, the data are in `data/` directory. You can also use `data/day-0` as template inputs files.
- `data/day-{d}/inputs.txt`: main input for the puzzle day.
- `data/day-{d}/inputs-part-{p}-example-{n}.txt`: Examples inputs. You can try to verify your solver with example
- `data/day-{d}/inputs-part-{p}-expected-{n}.txt`: Expected results. Here, you can put the expected result for each example.


## Solve puzzle day

### Command
```bash
Use    : bin/aoc solve [OPTION]...
OPTIONS:
  -d ARG, --day=ARG                     Day to solve - MANDATORY
  -e,     --example                     Example Only
  -f,     --functional                  Activate Solve in functional programming style
  -s,     --skip-empty-lines            Remove empty lines from inputs

```

### Example
#### Run base solver
By default, we just use one base solver.

```bash
$ bin/aoc solve --day=1   
------------------------------------------ EXAMPLES ------------------------------------------
*  :  24000 - expected: 24000
** :  45000 - expected: 45000
------------------------------------------- OUTPUT -------------------------------------------
*  : 100000 - [0s] - [2.2MB]
** : 200000 - [0s] - [2.2MB]
```

#### Run functional solver
But you can also try to add solver in functional programmation, by using `velkuns/pipeline` for example
(or any custom / tiers code).
In this case, you'll need to override `partOneFunctional()` && `partTwoFunctional()` method and put you code here.
Then run the commande with `--functional` option.

It is quite challenging for some part, but for the first days, it very pleasant to also try functional programming :)

```bash
$ bin/aoc solve --day=1 --functional
------------------------------------------ EXAMPLES  (FUNCTIONAL) ------------------------------------------
*  :  24000 - expected: 24000
** :  45000 - expected: 45000
------------------------------------------ OUTPUT  (FUNCTIONAL) ------------------------------------------
*  : 100000 - [0s] - [2.2MB]
** : 200000 - [0s] - [2.2MB]
```
