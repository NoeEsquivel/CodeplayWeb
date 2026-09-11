(() => {
    'use strict';
    let speed = '1';
    let soundOn = true;
    let audioContext = null;
    let continueResolve = null;
    const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
    const pause = async ms => { const end = performance.now() + (reduced() ? Math.min(ms,120) : ms/Number(speed)); while(performance.now()<end) await new Promise(r=>setTimeout(r,40)); };
    function tone(kind) {
        if(!soundOn) return;
        try {
            audioContext ||= new (window.AudioContext||window.webkitAudioContext)();
            if(audioContext.state==='suspended') audioContext.resume();
            const notes={atacar:220,hechizo:520,pocion:660,defender:330,victoria:880,derrota:110};
            const oscillator=audioContext.createOscillator(), gain=audioContext.createGain();
            oscillator.type=kind==='hechizo'?'sine':'triangle'; oscillator.frequency.value=notes[kind]||260;
            gain.gain.setValueAtTime(.0001,audioContext.currentTime); gain.gain.exponentialRampToValueAtTime(.07,audioContext.currentTime+.02); gain.gain.exponentialRampToValueAtTime(.0001,audioContext.currentTime+(kind==='victoria'? .45:.18));
            oscillator.connect(gain).connect(audioContext.destination); oscillator.start(); oscillator.stop(audioContext.currentTime+(kind==='victoria'? .5:.22));
        } catch(_) { /* El combate sigue siendo completamente jugable sin audio. */ }
    }
    function waitForContinue() { return new Promise(resolve => { continueResolve=resolve; }); }
    function init(root) {
        root.querySelectorAll('.battle-resources section, .arena-toolbox section').forEach(el=>{el.hidden=true;});
        const select=root.querySelector('[data-playback-speed]'); if(select) select.value=speed;
        const radio=root.querySelector('[name="enemy-target"]:checked');
        root.querySelectorAll('[data-arena-target]').forEach(el=>el.setAttribute('aria-pressed',String(el.dataset.arenaTarget===radio?.value)));
    }
    document.addEventListener('change',e=>{
        if(e.target.matches('[data-playback-speed]')) speed=e.target.value;
        if(e.target.matches('[name="enemy-target"]')) document.querySelectorAll('[data-arena-target]').forEach(el=>el.setAttribute('aria-pressed',String(el.dataset.arenaTarget===e.target.value)));
    });
    document.addEventListener('click',e=>{
        const zone=e.target.closest('[data-continue-zone]');
        if(zone && continueResolve) { const resolve=continueResolve; continueResolve=null; resolve(); return; }
        const button=e.target.closest('button'); if(!button || button.disabled) return;
        if(button.hasAttribute('data-sound-toggle')) { soundOn=!soundOn; button.setAttribute('aria-pressed',String(soundOn)); button.textContent=soundOn?'🔊 Sonido':'🔇 Silencio'; return; }
        if(button.dataset.arenaTarget!==undefined) {
            const radio=document.querySelector(`[name="enemy-target"][value="${Number(button.dataset.arenaTarget)}"]`);
            if(radio && !radio.disabled) {radio.checked=true;radio.dispatchEvent(new Event('change',{bubbles:true}));}
        }
        if(button.dataset.panel) {
            const panel=document.getElementById(button.dataset.panel); if(!panel)return;
            const hidden=panel.hidden;document.querySelectorAll('.battle-resources section, .arena-toolbox section').forEach(el=>el.hidden=true);
            panel.hidden=!hidden;button.setAttribute('aria-expanded',String(hidden));if(hidden)panel.scrollIntoView({block:'nearest',behavior:reduced()?'instant':'smooth'});
        }
    });
    document.addEventListener('keydown',e=>{
        if((e.key==='Enter'||e.key===' ') && e.target.closest('[data-continue-zone]') && continueResolve) {
            e.preventDefault(); const resolve=continueResolve; continueResolve=null; resolve();
        }
    });
    function animate(actor,kind) {const fighter=actor?.querySelector('.fighter');if(!fighter)return;fighter.classList.remove('strike','magic','hit','restore','guard');void fighter.offsetWidth;fighter.classList.add(kind);}
    function projectile(arena,from,to) {
        if(!from||!to||reduced())return;
        const a=from.getBoundingClientRect(),b=to.getBoundingClientRect(),c=arena.getBoundingClientRect();
        const orb=document.createElement('span');orb.className='spell-orb';orb.textContent='✧';
        const x=a.left+a.width/2-c.left,y=a.top+a.height/2-c.top;
        orb.style.left=x+'px';orb.style.top=y+'px';arena.querySelector('.arena-fx').append(orb);
        orb.animate([{transform:'translate(0,0) scale(.6)',opacity:1},{transform:`translate(${b.left+b.width/2-c.left-x}px,${b.top+b.height/2-c.top-y}px) scale(1.4)`,opacity:0}],{duration:420/Number(speed),easing:'ease-in'}).finished.finally(()=>orb.remove());
    }
    function number(arena,actor,delta) {
        if(!delta||!actor)return;
        const a=actor.getBoundingClientRect(),b=arena.getBoundingClientRect();
        const el=document.createElement('span');el.className='combat-number'+(delta>0?' heal':'');el.textContent=(delta>0?'+':'')+delta;
        el.style.left=(a.left-b.left+a.width/2)+'px';el.style.top=(a.top-b.top+a.height/2)+'px';arena.querySelector('.arena-fx').append(el);setTimeout(()=>el.remove(),1000);
    }
    async function play(root,sequence,nextRoot) {
        const theatre=root.querySelector('[data-theatre]'); if(!theatre||!sequence?.length)return;
        continueResolve=null;theatre.classList.add('is-playing');theatre.setAttribute('aria-busy','true');
        const hint=theatre.querySelector('[data-continue-hint]');
        hint.hidden=false;
        const arena=theatre.querySelector('.arena');
        for(const event of sequence) {
            const actor=theatre.querySelector(`[data-actor="${event.actor}"]`);
            theatre.querySelector('.battle-narration').textContent=event.texto||'El turno continúa.';
            arena.dataset.effect=event.tipo;
            tone(event.tipo);
            animate(actor,event.tipo==='hechizo'?'magic':event.tipo==='defender'?'guard':event.tipo==='pocion'?'restore':'strike');
            if(event.tipo==='hechizo') {
                const i=event.enemigos.findIndex((enemy,i)=>enemy.vida<Number(theatre.querySelector(`[data-actor="enemigo-${i}"]`)?.dataset.hp));
                if(i>=0)projectile(arena,actor,theatre.querySelector(`[data-actor="enemigo-${i}"]`));
            }
            await pause(650);
            const player=theatre.querySelector('[data-actor="jugador"]');
            const hpLabel=theatre.querySelector('[data-player-hp]'),manaLabel=theatre.querySelector('[data-player-mana]');
            const maxHp=Number(hpLabel.textContent.split('/')[1]),maxMana=Number(manaLabel.textContent.split('/')[1]);
            const delta=event.vida-Number(player.dataset.hp);number(arena,player,delta);if(delta)animate(player,delta>0?'restore':'hit');player.dataset.hp=event.vida;
            player.classList.toggle('is-fallen',event.vida<=0);
            hpLabel.textContent=`${event.vida} / ${maxHp}`;manaLabel.textContent=`${event.mana} / ${maxMana}`;
            theatre.querySelector('[data-player-bar]').style.width=Math.max(0,Math.min(100,100*event.vida/maxHp))+'%';
            theatre.querySelector('[data-mana-bar]').style.width=Math.max(0,Math.min(100,100*event.mana/maxMana))+'%';
            event.enemigos.forEach((enemy,i)=>{
                let target=theatre.querySelector(`[data-actor="enemigo-${i}"]`);
                if(!target && nextRoot) {
                    const template=nextRoot.querySelector(`[data-actor="enemigo-${i}"]`);if(template){target=template.cloneNode(true);target.disabled=true;target.dataset.hp=enemy.vida;theatre.querySelector('.opponents').append(target);}
                }
                if(!target)return;
                const change=enemy.vida-Number(target.dataset.hp);number(arena,target,change);if(change)animate(target,change>0?'restore':'hit');
                target.dataset.hp=enemy.vida;target.classList.toggle('is-fallen',enemy.vida<=0);
                target.querySelector('[data-hp-label]').textContent=`${enemy.vida} / ${enemy.max}`;
                target.querySelector('[data-hp-bar]').style.width=(100*enemy.vida/enemy.max)+'%';
            });
            theatre.querySelector('.opponents').style.setProperty('--opponents',event.enemigos.length);
            await waitForContinue();
        }
        continueResolve=null;theatre.removeAttribute('aria-busy');theatre.classList.remove('is-playing');hint.hidden=true;
    }
    window.CodePlayArena={init,play};
})();
