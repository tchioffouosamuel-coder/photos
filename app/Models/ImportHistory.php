<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    protected $table = 'import_history';
    protected $fillable = [
        'classe_id', 'file_name', 'total_rows', 'imported_rows', 
        'failed_rows', 'status', 'error_log', 'imported_by'
    ];
    
    protected $casts = [
        'error_log' => 'array'
    ];
    
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
}