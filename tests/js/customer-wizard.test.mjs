import test from 'node:test';
import assert from 'node:assert/strict';
import customerWizard from '../../resources/js/customer-wizard.js';

test('invalid hidden-step field selects its step before focusing and reporting validity', () => {
    const wizard = customerWizard('openEdit');
    let prevented = false, focused = false, reported = false;
    const invalid = { willValidate: true, checkValidity: () => false,
        closest: () => ({ dataset: { wizardStep: '2' } }),
        focus: () => { focused = true; assert.equal(wizard.step, 2); }, reportValidity: () => { reported = true; } };
    wizard.$refs = { wizardForm: { elements: [invalid] } };
    wizard.$nextTick = callback => callback();
    wizard.validateSubmission({ preventDefault: () => { prevented = true; } });
    assert.ok(prevented && focused && reported);
});

test('current step validation prevents advancing and opening a wizard resets its step', () => {
    const wizard = customerWizard('openCreate');
    wizard.focusStep = () => {};
    let watches;
    wizard.$watch = (flag, callback) => { assert.equal(flag,'openCreate'); watches=callback; };
    wizard.init();
    let valid = false;
    const password = { value: 'previous-customer-password' };
    const reason = { value: 'Previous account reason' }, confirmation = { type: 'checkbox', checked: true };
    wizard.$refs = { wizardForm: { querySelectorAll: () => [password, reason, confirmation], querySelector: () => ({ querySelectorAll: () => [{ checkValidity: () => valid, reportValidity() {} }] }) } };
    wizard.validateAndNext(); assert.equal(wizard.step,1);
    valid = true; wizard.validateAndNext(); assert.equal(wizard.step,2);
    wizard.validateAndNext(); assert.equal(wizard.step,2);
    watches(false); assert.equal(wizard.step,2);
    assert.equal(password.value,'');
    assert.equal(reason.value,'');
    assert.equal(confirmation.checked,false);
    watches(true); assert.equal(wizard.step,1);
});
