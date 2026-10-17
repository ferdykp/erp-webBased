<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Existing template records, exposed as models for transactional audit snapshots. */
class ProductionParameterTemplate extends Model
{
    protected $guarded = ['*'];
}
