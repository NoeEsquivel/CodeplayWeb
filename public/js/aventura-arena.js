(() => {
    'use strict';
    let speed = '1';
    let soundOn = true;
    let audioContext = null;
    let continueResolve = null;
    const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
    const pause = async ms => { const end = performance.now() + (reduced() ? Math.min(ms,120) : ms/Number(speed)); while(performance.now()<end) await new Promise(r=>setTimeout(r,40)); };
    function audio() {
        audioContext ||= new (window.AudioContext||window.webkitAudioContext)();
        if(audioContext.state==='suspended') audioContext.resume();
        return audioContext;
    }
    function oscillator(ctx, type, start, end, volume, duration, delay=0) {
        const sound=ctx.createOscillator(), gain=ctx.createGain(), now=ctx.currentTime+delay;
        sound.type=type; sound.frequency.setValueAtTime(Math.max(1,start),now); sound.frequency.exponentialRampToValueAtTime(Math.max(1,end),now+duration);
        gain.gain.setValueAtTime(.0001,now); gain.gain.exponentialRampToValueAtTime(volume,now+.012); gain.gain.exponentialRampToValueAtTime(.0001,now+duration);
        sound.connect(gain).connect(ctx.destination); sound.start(now); sound.stop(now+duration+.02);
    }
    function noise(ctx, high, low, volume, duration, delay=0) {
        const frames=Math.max(1,Math.floor(ctx.sampleRate*duration));
        const buffer=ctx.createBuffer(1,frames,ctx.sampleRate), data=buffer.getChannelData(0);
        for(let i=0;i<frames;i++) data[i]=Math.random()*2-1;
        const source=ctx.createBufferSource(), filter=ctx.createBiquadFilter(), gain=ctx.createGain(), now=ctx.currentTime+delay;
        source.buffer=buffer; filter.type='bandpass'; filter.Q.value=.8; filter.frequency.setValueAtTime(high,now); filter.frequency.exponentialRampToValueAtTime(Math.max(80,low),now+duration);
        gain.gain.setValueAtTime(.0001,now); gain.gain.exponentialRampToValueAtTime(volume,now+.008); gain.gain.exponentialRampToValueAtTime(.0001,now+duration);
        source.connect(filter).connect(gain).connect(ctx.destination); source.start(now);
    }
    function tone(kind) {
        if(!soundOn) return;
        try {
            const ctx=audio();
            if(kind==='atacar') { // Corte de espada: aire rápido, seguido por un impacto corto.
                noise(ctx,1700,320,.055,.13); oscillator(ctx,'triangle',740,120,.035,.12,.025); oscillator(ctx,'square',150,62,.045,.09,.115); return;
            }
            if(kind==='ataque') { // Golpe enemigo: más pesado y menos brillante.
                noise(ctx,680,110,.06,.16); oscillator(ctx,'sawtooth',180,48,.045,.15,.035); return;
            }
            if(kind==='hechizo') { oscillator(ctx,'sine',420,960,.045,.24); oscillator(ctx,'triangle',720,1420,.025,.2,.07); return; }
            if(kind==='pocion') { oscillator(ctx,'sine',520,760,.04,.19); oscillator(ctx,'sine',660,980,.03,.16,.08); return; }
            if(kind==='defender') { oscillator(ctx,'triangle',280,430,.04,.18); oscillator(ctx,'sine',410,560,.025,.2,.05); return; }
            if(kind==='runas') { oscillator(ctx,'sine',220,780,.045,.38); noise(ctx,1200,700,.018,.24,.08); return; }
            if(kind==='victoria') { oscillator(ctx,'triangle',520,880,.045,.26); oscillator(ctx,'triangle',660,1180,.04,.35,.13); return; }
            if(kind==='derrota') { oscillator(ctx,'sawtooth',170,58,.04,.38); return; }
            oscillator(ctx,'triangle',260,220,.025,.12);
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
        if(button.hasAttribute('data-sound-toggle') || button.hasAttribute('data-combat-action')) { try { audio(); } catch(_) {} }
        if(button.hasAttribute('data-sound-toggle')) {
            soundOn=!soundOn;
            button.setAttribute('aria-pressed',String(soundOn));
            button.textContent=soundOn?'🔊 Sonido':'🔇 Silencio';
            document.dispatchEvent(new CustomEvent('codeplay:combat-sound',{detail:{enabled:soundOn}}));
            return;
        }
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
    function animate(actor,kind) {
        const fighter=actor?.querySelector('.fighter'); if(!fighter)return;
        fighter.classList.remove('strike','enemy-strike','magic','hit','restore','guard','damage-flash','heal-flash');
        void fighter.offsetWidth; fighter.classList.add(kind);
        if(kind==='hit') { fighter.classList.add('damage-flash'); setTimeout(()=>fighter.classList.remove('damage-flash'),950); }
        if(kind==='restore') { fighter.classList.add('heal-flash'); setTimeout(()=>fighter.classList.remove('heal-flash'),1100); }
    }
    function setHeroSprite(player, pose) {
        const sprite = player?.querySelector('[data-hero-sprite]');
        if (!sprite || sprite.dataset.heroPose === pose) return;
        const source = sprite.dataset[`hero${pose[0].toUpperCase()}${pose.slice(1)}`];
        if (!source) return;
        sprite.dataset.heroPose = pose;
        sprite.src = source;
    }
    function restoreHeroSprite(player, vida, vidaMaxima) {
        setHeroSprite(player, vida > 0 && vida / Math.max(1, vidaMaxima) < .25 ? 'wounded' : 'default');
    }
    function slash(arena,target,enemy=false) {
        if(!arena||!target) return;
        const bounds=target.getBoundingClientRect(), stage=arena.getBoundingClientRect();
        const effect=document.createElement('span'); effect.className='slash-effect'+(enemy?' enemy-slash':'');
        effect.style.left=(bounds.left-stage.left+bounds.width/2)+'px'; effect.style.top=(bounds.top-stage.top+bounds.height/2+(enemy?0:50))+'px';
        arena.querySelector('.arena-fx').append(effect); setTimeout(()=>effect.classList.add('is-settled'),390); return effect;
    }
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
    function aura(arena, actor, type) {
        if (!arena || !actor) return null;
        const bounds=actor.getBoundingClientRect(),stage=arena.getBoundingClientRect();
        const effect=document.createElement('span'); effect.className=`combat-aura ${type}`;
        effect.style.left=(bounds.left-stage.left+bounds.width/2)+'px';
        effect.style.top=(bounds.top-stage.top+bounds.height/2)+'px';
        if (type==='level-up') effect.innerHTML='<i>✦</i><i>✧</i><i>✦</i><b>NIVEL ↑</b>';
        else effect.innerHTML='<i></i><i></i><i></i><i></i><i></i>';
        arena.querySelector('.arena-fx').append(effect); setTimeout(()=>effect.remove(),1050); return effect;
    }
    async function play(root,sequence,nextRoot) {
        const theatre=root.querySelector('[data-theatre]'); if(!theatre||!sequence?.length)return;
        continueResolve=null;theatre.classList.add('is-playing');theatre.setAttribute('aria-busy','true');
        const hint=theatre.querySelector('[data-continue-hint]');
        hint.hidden=false;
        const arena=theatre.querySelector('.arena');
        for(const event of sequence) {
            const visualEffects=[];
            const actor=theatre.querySelector(`[data-actor="${event.actor}"]`);
            const narration = theatre.querySelector('.battle-narration');
            narration.textContent=event.texto||'El turno continúa.';
            narration.classList.toggle('is-correct', event.answer_feedback === 'correct');
            narration.classList.toggle('is-incorrect', event.answer_feedback === 'incorrect');
            // La narración puede avanzar desde que aparece. No debe quedar
            // bloqueada por el tiempo que el retrato temporal permanezca visible.
            const continueTurn = waitForContinue();
            arena.dataset.effect=event.tipo;
            tone(event.tipo);
            const player=theatre.querySelector('[data-actor="jugador"]');
            const heroSprite = player.querySelector('[data-hero-sprite]');
            const enemyHitIndex=event.enemigos.findIndex((enemy,index)=>enemy.vida<Number(theatre.querySelector(`[data-actor="enemigo-${index}"]`)?.dataset.hp));
            const playerWasHit=event.actor.startsWith('enemigo-') && event.vida<Number(player.dataset.hp);
            const playerStruck=event.actor==='jugador' && enemyHitIndex>=0;
            const heroAction = event.actor === 'jugador' && ['atacar','defender','pocion'].includes(event.tipo) ? event.tipo : null;
            const heroPoseEndsAt = heroAction && heroAction !== 'defender'
                ? performance.now() + (4200 / Number(speed))
                : null;
            if (heroAction) setHeroSprite(player, heroAction === 'atacar' ? 'attack' : heroAction === 'defender' ? 'defense' : 'potion');
            animate(actor,event.tipo==='hechizo'?'magic':event.tipo==='defender'?'guard':event.tipo==='pocion'?'restore':playerWasHit?'enemy-strike':'strike');
            if(playerStruck) {
                const i=enemyHitIndex;
                if(i>=0) visualEffects.push(slash(arena,theatre.querySelector(`[data-actor="enemigo-${i}"]`)));
            } else if(playerWasHit) visualEffects.push(slash(arena,player,true));
            if(event.tipo==='hechizo') {
                const i=event.enemigos.findIndex((enemy,i)=>enemy.vida<Number(theatre.querySelector(`[data-actor="enemigo-${i}"]`)?.dataset.hp));
                if(i>=0)projectile(arena,actor,theatre.querySelector(`[data-actor="enemigo-${i}"]`));
            }
            // Conserva el efecto de golpe, pero permite continuar en cuanto el
            // jugador lo decida. El sprite temporal sigue en pantalla aparte.
            await Promise.race([pause(650), continueTurn]);
            const hpLabel=theatre.querySelector('[data-player-hp]'),manaLabel=theatre.querySelector('[data-player-mana]');
            const maxHp=Number(hpLabel.textContent.split('/')[1]),maxMana=Number(manaLabel.textContent.split('/')[1]);
            const delta=event.vida-Number(player.dataset.hp);number(arena,player,delta);if(delta)animate(player,delta>0?'restore':'hit');
            if(delta>0) visualEffects.push(aura(arena,player,'heal-aura'));
            if(delta<0) visualEffects.push(aura(arena,player,'impact-aura'));
            const nextLevel=Number(nextRoot?.querySelector('[data-actor="jugador"]')?.dataset.level || 0);
            if(nextLevel>Number(player.dataset.level||0)) {
                player.dataset.level=String(nextLevel);
                const levelLabel=theatre.querySelector('[data-player-level]'); if(levelLabel) levelLabel.textContent=`Nv. ${nextLevel}`;
                visualEffects.push(aura(arena,player,'level-up'));
            }
            player.dataset.hp=event.vida;
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
                if(change<0) visualEffects.push(aura(arena,target,'impact-aura'));
                if(change>0) visualEffects.push(aura(arena,target,'heal-aura'));
                target.dataset.hp=enemy.vida;target.classList.toggle('is-fallen',enemy.vida<=0);
                target.querySelector('[data-hp-label]').textContent=`${enemy.vida} / ${enemy.max}`;
                target.querySelector('[data-hp-bar]').style.width=(100*enemy.vida/enemy.max)+'%';
            });
            theatre.querySelector('.opponents').style.setProperty('--opponents',event.enemigos.length);
            let restoreSpriteTimer = null;
            if (heroPoseEndsAt) {
                const remaining = Math.max(0, heroPoseEndsAt - performance.now());
                restoreSpriteTimer = window.setTimeout(() => restoreHeroSprite(player, event.vida, maxHp), remaining);
            }
            await continueTurn;
            // Al continuar, el siguiente evento puede ser un ataque enemigo.
            // Restablecemos aquí para que nunca conserve el sprite anterior.
            if (restoreSpriteTimer !== null) window.clearTimeout(restoreSpriteTimer);
            if (heroAction === 'atacar' || heroAction === 'pocion' || (event.actor.startsWith('enemigo-') && heroSprite?.dataset.heroPose === 'defense')) restoreHeroSprite(player,event.vida,maxHp);
            narration.classList.remove('is-correct', 'is-incorrect');
            visualEffects.forEach(effect=>effect?.remove());
        }
        continueResolve=null;theatre.removeAttribute('aria-busy');theatre.classList.remove('is-playing');hint.hidden=true;
    }
    window.CodePlayArena={init,play};
})();
