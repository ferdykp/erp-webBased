import assert from 'node:assert/strict';
import test from 'node:test';
import receivingAssistant from '../../resources/js/receiving-assistant.js';

function setup(expected, locations = 10) {
    const data = receivingAssistant();
    data.$el = {dataset:{expected:String(expected),unit:'boxes',slots:JSON.stringify(Array.from({length:locations},(_,i)=>({id:i+1,line:'A',slot:String(i+1)})))}};
    data.init();
    data.matchBookingQuantity();
    data.warehousePic='PIC';data.porter='Porter';
    return data;
}
test('manual mode allocates uneven quantities exactly',()=>{
    const d=setup(121);d.planningMode='manual';d.manualPalletCount='4';d.suggest();
    assert.deepEqual(d.allocation.map(r=>r.qty),[31,30,30,30]);
    assert.equal(d.valid,true);
    assert.equal(d.allocation.reduce((s,r)=>s+r.qty,0),121);
});
test('quantity mode uses approved per pallet limit',()=>{
    const d=setup(121);d.perPallet='50';d.suggest();
    assert.deepEqual(d.allocation.map(r=>r.qty),[50,50,21]);
    assert.equal(d.valid,true);
});
test('shared locations accept several pallet allocations without quantity loss',()=>{
    const d=setup(121,2);d.perPallet='50';d.suggest();
    assert.equal(d.allocation.length,3);
    assert.equal(d.valid,true);
    assert.equal(d.allocation.reduce((sum,row)=>sum+row.qty,0),121);
    const d2=setup(121);d2.perPallet='50';d2.suggest();d2.allocation[1].key=d2.allocation[0].key;
    assert.equal(d2.valid,true);
});
test('verification and changed inputs invalidate confirmations',()=>{
    const d=setup(100);d.perPallet='50';d.suggest();d.confirmed=true;
    assert.equal(d.ready,true);
    d.capacityChanged();assert.equal(d.ready,false);
    d.actual='99';d.quantityChanged();assert.equal(d.mismatch,true);
});
