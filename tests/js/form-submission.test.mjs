import test from 'node:test';
import assert from 'node:assert/strict';
import { initializeFormSubmissions } from '../../resources/js/form-submission.js';

function setup() {
    let submit, pageshow;
    globalThis.document = { addEventListener: (name, callback) => { if(name==='submit') submit=callback; } };
    globalThis.window = { addEventListener: (name, callback) => { if(name==='pageshow') pageshow=callback; } };
    globalThis.HTMLFormElement = class {
        method = 'post'; target = ''; attributes = {}; buttons = [];
        getAttribute(key) { return key === 'method' ? this.method : this.attributes[key]; }
        setAttribute(key,value) { this.attributes[key]=value; }
        removeAttribute(key) { delete this.attributes[key]; }
        querySelectorAll() { return this.buttons; }
    };
    initializeFormSubmissions();
    const form=new HTMLFormElement();
    const event=() => ({target:form,defaultPrevented:false,preventDefault() { this.defaultPrevented=true; }});
    return { form, event, submit: e=>submit(e), pageshow: ()=>pageshow() };
}
const flush=()=>new Promise(resolve=>queueMicrotask(resolve));

test('ordinary form rejects duplicate submission and resets when returning through browser history', async () => {
    const {form,event,submit,pageshow}=setup();
    submit(event()); await flush();
    assert.equal(form.attributes['aria-busy'],'true');
    const second=event(); submit(second); assert.equal(second.defaultPrevented,true);
    pageshow(); assert.equal(form.attributes['aria-busy'],undefined);
    const third=event(); submit(third); assert.equal(third.defaultPrevented,false);
});

test('AJAX cancellation and dialog submissions never leave the navigation guard locked', async () => {
    const {form,event,submit}=setup();
    const cancelled=event(); submit(cancelled); cancelled.preventDefault(); await flush();
    assert.equal(form.attributes['aria-busy'],undefined);
    form.method='dialog'; submit(event()); await flush();
    assert.equal(form.attributes['aria-busy'],undefined);
});
