<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Schema};

/** Snapshots follow existing foreign keys; no relationships or constraints are changed. */
class AdminRecordHistory
{
    private ?array $references = null;

    private function references(): array
    {
        if ($this->references !== null) return $this->references;
        $this->references = [];
        foreach (Schema::getTableListing(null, false) as $table) {
            foreach (Schema::getForeignKeys($table) as $foreign) {
                if (count($foreign['columns']) !== 1 || count($foreign['foreign_columns']) !== 1) continue;
                $this->references[$foreign['foreign_table']][] = ['table'=>$table,'column'=>$foreign['columns'][0],'parent'=>$foreign['foreign_columns'][0]];
            }
        }
        return $this->references;
    }

    public function snapshot(Model $model): array
    {
        $seen = [];
        return $this->capture($model->getTable(), $model->getKey(), $seen);
    }

    private function capture(string $table, int|string $id, array &$seen): array
    {
        $key = $table.':'.$id;
        if (isset($seen[$key])) return ['reference'=>$key];
        $seen[$key] = true;
        $row = DB::table($table)->where('id',$id)->first();
        if (!$row) return ['table'=>$table,'id'=>$id,'deleted'=>true];
        $data = (array) $row;
        foreach (array_keys($data) as $column) {
            if (preg_match('/password|token|secret/i',$column)) unset($data[$column]);
        }
        $children = [];
        foreach ($this->references()[$table] ?? [] as $ref) {
            $value = $row->{$ref['parent']} ?? null;
            if ($value === null) continue;
            foreach (DB::table($ref['table'])->where($ref['column'],$value)->orderBy('id')->get() as $child) {
                $children[] = $this->capture($ref['table'],$child->id,$seen);
            }
        }
        // Historical warehouse placement uses coordinates instead of a slot foreign key.
        if ($table === 'pallets') {
            foreach (DB::table('placement_details')->where('line',$row->line)->where('slot_section',$row->slot_section)->get() as $child) {
                $children[] = $this->capture('placement_details',$child->id,$seen);
            }
        }
        return ['table'=>$table,'id'=>$id,'data'=>$data,'children'=>$children];
    }

    public function delete(Model $model): void
    {
        throw new \LogicException('Permanent deletion is disabled. Use recoverable Trash.');
    }
}
