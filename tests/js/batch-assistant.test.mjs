import test from 'node:test';
import assert from 'node:assert/strict';
import batchAssistant from '../../resources/js/batch-assistant.js';
test('one click allocates all remaining goods and requires specification verification', () => {
    const plan = batchAssistant(121);
    plan.useSingleBatch();
    assert.deepEqual(plan.quantities, [121]);
    assert.equal(plan.ready, false);
    plan.verified = true;
    assert.equal(plan.ready, true);
});
test('calculation splits the remainder exactly and invalidates previous confirmation', () => {
    const plan = batchAssistant(121);
    plan.limit = 50; plan.verified = true; plan.generate();
    assert.deepEqual(plan.quantities, [50,50,21]);
    assert.equal(plan.allocated, 121);
    assert.equal(plan.verified, false);
});
