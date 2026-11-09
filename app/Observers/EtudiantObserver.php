<?php
namespace App\Observers;

use App\Models\Etudiant;

class EtudiantObserver
{
    public function created(Etudiant $etudiant)
    {
        $this->updateClasseEffectifs($etudiant);
    }
    
    public function updated(Etudiant $etudiant)
    {
        $this->updateClasseEffectifs($etudiant);
    }
    
    public function deleted(Etudiant $etudiant)
    {
        $this->updateClasseEffectifs($etudiant);
    }
    
    private function updateClasseEffectifs(Etudiant $etudiant)
    {
        if ($etudiant->classe) {
            $etudiant->classe->updateEffectifs();
        }
    }
}
?>