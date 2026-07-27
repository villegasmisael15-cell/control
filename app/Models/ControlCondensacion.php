<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlCondensacion extends Model
{
    use HasFactory;

    protected $table = 'control_condensaciones';

    protected $fillable = [
        'semana',
        'fecha', 
        'agropark',
    ];
}