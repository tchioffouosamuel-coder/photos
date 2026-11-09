<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Classe;
use App\Services\CardGenerationService;

class GenerateCardsCommand extends Command
{
    protected $signature = 'cards:generate {classe : Class name} {--type=cards : Type of generation}';
    protected $description = 'Generate cards for a class via CLI';
    
    private $cardService;
    
    public function __construct(CardGenerationService $cardService)
    {
        parent::__construct();
        $this->cardService = $cardService;
    }
    
    public function handle()
    {
        $classeName = $this->argument('classe');
        $type = $this->option('type');
        
        $classe = Classe::where('nom', $classeName)->first();
        
        if (!$classe) {
            $this->error("Class '{$classeName}' not found!");
            return 1;
        }
        
        $this->info("Generating {$type} for {$classe->nom}...");
        
        try {
            $filePath = $this->cardService->generateCards($classe);
            $this->info("Generated successfully: {$filePath}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
}

?>