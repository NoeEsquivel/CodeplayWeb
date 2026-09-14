const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/js/aventura-victory.js'),'utf8');
function fixture({blocked=false, seen=false}={}) {
    const video = new EventTarget();
    Object.assign(video,{currentTime:0,duration:27.05,ended:false,error:null,calls:0,play(){this.calls++;return blocked?Promise.reject(Error('blocked')):Promise.resolve();},pause(){},load(){this.error=null;}});
    const retry = new EventTarget();retry.hidden=true;retry.focus=()=>{};
    const status={textContent:''}, epilogue={hidden:true,inert:true};
    const scene={hidden:false,dataset:{run:'42'},querySelector:s=>s==='video'?video:s==='[data-victory-status]'?status:retry};
    const saved=new Map(seen?[['codeplay:victory-cinematic:42','complete']]:[]), timers=new Map();let id=0;
    new Function('document','sessionStorage','setTimeout','clearTimeout',source)(
        {getElementById:id=>id==='victoryCinematic'?scene:{focus(){}},querySelector:()=>epilogue},
        {getItem:k=>saved.get(k),setItem:(k,v)=>saved.set(k,v)},fn=>{timers.set(++id,fn);return id;},id=>timers.delete(id));
    return {video,retry,epilogue,scene,saved,timers,unblock:()=>{blocked=false;}};
}
test('epilogue remains hidden until the complete movie ends',()=>{const f=fixture();assert.equal(f.video.muted,false);for(const t of [1,20,26]){f.video.currentTime=t;f.video.dispatchEvent(new Event('timeupdate'));assert.equal(f.epilogue.hidden,true);}f.video.currentTime=27.05;f.video.ended=true;f.video.dispatchEvent(new Event('ended'));assert.equal(f.epilogue.hidden,false);assert.equal(f.scene.hidden,true);assert.equal(f.saved.size,1);});
test('blocked autoplay offers retry without exposing epilogue',async()=>{const f=fixture({blocked:true});await Promise.resolve();assert.equal(f.retry.hidden,false);assert.equal(f.epilogue.hidden,true);assert.equal(f.saved.size,0);f.unblock();f.retry.dispatchEvent(new Event('click'));assert.equal(f.video.calls,2);});
test('premature end and stalled playback never count as victory movie completion',()=>{const f=fixture();f.video.currentTime=2;f.video.ended=true;f.video.dispatchEvent(new Event('ended'));assert.equal(f.epilogue.hidden,true);assert.equal(f.saved.size,0);f.retry.dispatchEvent(new Event('click'));[...f.timers.values()][0]();assert.equal(f.retry.hidden,false);assert.equal(f.epilogue.hidden,true);});
test('already viewed movie does not repeat on reload',()=>{const f=fixture({seen:true});assert.equal(f.video.calls,0);assert.equal(f.epilogue.hidden,false);});
