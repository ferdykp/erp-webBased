<?php
namespace App\Models\Concerns;

use App\Services\AdminRecordHistory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/** Normal deletes retain the original row, relationships and evidence files. */
trait Recoverable
{
    use SoftDeletes;

    public static function bootRecoverable(): void
    {
        static::deleting(function ($model) {
            if ($model instanceof \App\Models\DosimeterDetail || $model instanceof \App\Models\ProductTestDosimeter) {
                $column=$model instanceof \App\Models\DosimeterDetail ? 'tablet_number' : 'sequence';
                // Reserve an archival index; active numbering can safely reuse 1..N.
                $model->{$column}=1000000000 + $model->id;
                $model->saveQuietly();
            }
        });
        static::forceDeleting(fn () => throw new \LogicException('Permanent deletion is disabled. Use Trash and restore.'));
        static::restored(function ($model) {
            if ($model instanceof \App\Models\Customer) {
                $user=\App\Models\User::withTrashed()->find($model->user_id);
                if ($user?->trashed()) $user->restore();
                DB::table('trash_entries')->where('model_type',\App\Models\User::class)->where('record_id',$model->user_id)->whereNull('restored_at')->update(['restored_at'=>now(),'restored_by'=>auth('admin')->id(),'updated_at'=>now()]);
            }
        });
        static::deleted(function ($model) {
            DB::table('trash_entries')->insert([
                'model_type'=>get_class($model), 'record_id'=>$model->getKey(),
                'label'=>(string) ($model->booking_code ?? $model->company_name ?? $model->name ?? ('Record #'.$model->getKey())),
                'deleted_by'=>auth('admin')->id(),
                'snapshot'=>json_encode(app(AdminRecordHistory::class)->snapshot($model), JSON_THROW_ON_ERROR),
                'created_at'=>now(), 'updated_at'=>now(),
            ]);
        });
    }
}
