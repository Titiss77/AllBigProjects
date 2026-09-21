<?php namespace App\Models;

use CodeIgniter\Model;

class CountdownModel extends Model
{
    protected $table = 'alldates'; // Ou 'allDates' selon le nom de votre table
    protected $primaryKey = 'id';
    
    // Ajout de 'last_active'
    protected $allowedFields = ['countdown_date', 'past_date', 'user_token', 'last_active'];
}