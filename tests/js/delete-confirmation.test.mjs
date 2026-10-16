import test from 'node:test';
import assert from 'node:assert/strict';
import { initializeDeleteConfirmation } from '../../resources/js/delete-confirmation.js';

function fixture() {
    let submit, close, opens=0, accepted=0;
    const nodes=new Map();
    const dialog={returnValue:'',querySelector(selector){if(!nodes.has(selector)) nodes.set(selector,{}); return nodes.get(selector);},
        addEventListener(name,fn){if(name==='close') close=fn;},showModal(){opens++;}};
    globalThis.document={createElement:tag=>tag==='dialog'?dialog:{},body:{append(){}},addEventListener(name,fn){if(name==='submit') submit=fn;}};
    globalThis.HTMLFormElement=class {
        dataset={}; isConnected=true; busy=false; confirmed=null;
        querySelector(selector){return selector==='[name="_method"]'?{value:'DELETE'}:this.confirmed;}
        removeAttribute(){} getAttribute(){return this.busy?'true':null;} append(input){this.confirmed=input;}
        requestSubmit(){const event=fire(this);if(!event.prevented) accepted++;}
    };
    function fire(form){const event={target:form,prevented:false,preventDefault(){this.prevented=true;},stopImmediatePropagation(){}};submit(event);return event;}
    initializeDeleteConfirmation();
    return {form:new HTMLFormElement(),fire,confirm(){dialog.returnValue='delete';close();},opens:()=>opens,accepted:()=>accepted};
}

test('delete confirmation applies once and is required again for a later submission',()=>{
    const f=fixture();
    assert.ok(f.fire(f.form).prevented); assert.equal(f.opens(),1);
    f.confirm(); assert.equal(f.accepted(),1); assert.equal(f.form.confirmed.value,'1');
    assert.ok(f.fire(f.form).prevented); assert.equal(f.opens(),2);
});

test('busy delete form cannot open another dialog or submit a duplicate request',()=>{
    const f=fixture(); f.form.busy=true;
    assert.ok(f.fire(f.form).prevented); assert.equal(f.opens(),0); assert.equal(f.accepted(),0);
});
