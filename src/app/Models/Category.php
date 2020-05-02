<?php

namespace AbbyJanke\Expensed\App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
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
    protected $table = 'categories';

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
    protected $fillable = ['name'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function countEntries()
    {
        $incomeCount = $this->income()->count();
        $expenseCount = $this->expenses()->count();
        return $incomeCount + $expenseCount;
    }

    public function yearTotal($year = null)
    {
        if(is_null($year)) {
            $year = date('Y');
        }

        $incomeCount = $this->income()->whereYear('entry_date', $year)->count();
        $expenseCount = $this->expenses()->whereYear('entry_date', $year)->count();
        return $incomeCount + $expenseCount;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * What kind of income was it?
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function income()
    {
        return $this->hasMany(Income::class, 'category_id');
    }

    /**
     * What kind of income was it?
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
