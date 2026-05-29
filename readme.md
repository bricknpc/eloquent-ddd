# Eloquent DDD

![Logo](eloquent-ui-png-nobg-horizontal.png)

![Static Badge](https://img.shields.io/badge/bricknpc-eloquent--ddd-blue)
![GitHub License](https://img.shields.io/github/license/bricknpc/eloquent-ddd)
![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/bricknpc/eloquent-ddd/ci.yml)
![Codecov](https://img.shields.io/codecov/c/github/bricknpc/eloquent-ddd)
![GitHub commit activity](https://img.shields.io/github/commit-activity/t/bricknpc/eloquent-ddd)
![GitHub Release](https://img.shields.io/github/v/release/bricknpc/eloquent-ddd)

## Laravel helper for using DDD style architecture in your Laravel application.

## Installation

```bash
composer require bricknpc/eloquent-ddd
```

## Requirements

- PHP `^8.5`
- Laravel `^12.0`

## Usage

@todo

## Documentation

Documentation is available at [https://bricknpc.github.io/eloquent-ddd](https://bricknpc.github.io/eloquent-ddd).

## Local development

### Clone and install the project

This project has a simple docker setup for local development. To start local development, download the project
and start the docker container. You need to have Docker installed on your local machine for this.

First, clone the project.

```bash
git clone https://github.com/bricknpc/eloquent-ddd.git
cd eloquent-ddd
```

Up the docker container and install the dependencies.

```bash
docker compose up -d
docker compose exec php composer install
```

### Executing commands in the container

You can execute commands in the container using the exec option.

```bash
docker compose exec php <your command>
```

If you rather log in to the container and execute commands manually, you can use this:

```bash
docker compose exec php bash
```

### Stopping the container

```bash
docker compose down
```

### Documentation

When starting the docker container, the documentation site will automatically be started as well and will be available
on http://localhost:3000/eloquent-ddd. The documentation is built using [Docusaurus](https://docusaurus.io/). When
adding new features or making changes, please also update the documentation. Do not use separate pull requests to 
update the documentation and the code.

## Running tests

You can run the tests using the following command.

```bash
docker compose exec php composer test
```

### Test coverage

Test coverage must be at 100% before a pull request can be merged.

## Code quality tools

Eloquent DDD uses PHP CS Fixer and PHPStan to ensure a high-quality code base. We also use Deptrac to ensure correct 
architecture. You can run the tools locally using the following commands.

**PHP CS Fixer:**
```bash
docker compose exec php composer cs
```

**PHPStan:**
```bash
docker compose exec php composer ps
```

**Deptrac:**
```bash
docker compose exec php composer dt
```

## Community showcase

Are you using Eloquent DDD in your project? Let us know by opening a pull request to add your project to the
[community showcase](https://github.com/bricknpc/eloquent-ddd/blob/main/docs/src/pages/showcase.js). We love seeing
what people are building with Eloquent UI.

## Contributing

Pull requests are welcome. When creating a pull request, please include what you changed and why in the description of
the pull request. When fixing a bug, please include a test that reproduces the bug and describe how to test the bug
manually.

Before creating a pull request, please run the tests and code quality tools locally.

## MIT License

Copyright (c) 2025 BrickNPC

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.