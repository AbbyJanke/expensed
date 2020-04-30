<?php

namespace AbbyJanke\Expensed\App\Models;

use App\User;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'incomes';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['entry_date', 'amount', 'comments', 'currency_id', 'category_id', 'added_by_id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['entry_date', 'created_at', 'updated_at'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($entry) {
            $entry->added_by_id = backpack_user()->id;
        });
    }

    public function getCategories()
    {
        return $this->category()->where('type', 'income')->orWhere('type', 'other');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * What currency was the income received in.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * What kind of income was it?
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * What currency was the income received in.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function added_by()
    {
        return $this->belongsTo(config('backpack.base.user_model_fqn'));
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = str_replace(',', '', substr($value, 2));
    }
}
