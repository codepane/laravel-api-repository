<?php

namespace Codepane\LaravelAPIRepository\Console;

use Illuminate\Console\GeneratorCommand;

class MakeRepositoryCommand extends GeneratorCommand
{
    protected $name = 'make:repository';

    protected $description = 'Create a new repository class';

    protected function getStub()
    {
        return __DIR__ . '/stubs/Repository.php.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Repositories';
    }

    public function handle()
    {
        parent::handle();

        $this->GenerateRepository();
    }

    protected function GenerateRepository()
    {
        // Get the fully qualified class name (FQN)
        $class = $this->qualifyClass($this->getNameInput());

        // get the destination path, based on the default namespace
        $path = $this->getPath($class);

        $stubContents = file_get_contents($path);

        $inputName = array_reverse(explode('/', $this->getNameInput()))[0];
        
        $modelName = str_replace(['Repository', 'repository'], '', $inputName);
        $modelImport = "use App\Models\\" . $modelName . ";";

        $stubContents = str_replace(
            ['{{modelImport}}', '{{modelName}}', '{{modelVariable}}'], 
            [$modelImport, $modelName, strtolower($modelName)], 
            $stubContents
        );

        // dd($stubContents);

        // Update the file content with additional data (regular expressions)

        file_put_contents($path, $stubContents);
    }

}
