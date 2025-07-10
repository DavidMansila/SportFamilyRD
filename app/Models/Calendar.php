<?php
	namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time',
        'place',
        'price',
        'image', 
        'quantity', 
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'item_id')->where('item_type', 'event');
    }
}
