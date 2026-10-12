<style>
    .tutorial-video-controls {
        display: grid;
        grid-template-columns: auto auto minmax(120px, 1fr) auto;
        gap: 12px;
        align-items: center;
        margin: -1rem 0 2rem;
        padding: 12px 14px;
        border: 1px solid rgba(0, 255, 157, .45);
        border-radius: 13px;
        background: rgba(2, 22, 28, .96);
        box-shadow: 0 10px 28px rgba(0, 0, 0, .3);
    }

    .tutorial-video-controls[hidden] { display: none !important; }

    .tutorial-video-seek-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 42px;
        padding: 8px 13px;
        border: 1px solid rgba(0, 255, 157, .65);
        border-radius: 10px;
        background: rgba(0, 255, 157, .1);
        color: #eafff8;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s, transform .2s;
    }

    .tutorial-video-seek-button:hover:not(:disabled) {
        background: rgba(0, 255, 157, .22);
        transform: translateY(-1px);
    }

    .tutorial-video-seek-button:disabled { opacity: .45; cursor: not-allowed; }

    .tutorial-video-timeline {
        width: 100%;
        height: 8px;
        accent-color: #00ff9d;
        cursor: pointer;
    }

    .tutorial-video-time {
        min-width: 100px;
        color: #cce7df;
        font-variant-numeric: tabular-nums;
        text-align: right;
    }

    @media (max-width: 620px) {
        .tutorial-video-controls { grid-template-columns: 1fr 1fr; }
        .tutorial-video-timeline { grid-column: 1 / -1; grid-row: 1; }
        .tutorial-video-time { grid-column: 1 / -1; grid-row: 2; min-width: 0; text-align: center; }
        .tutorial-video-seek-button { grid-row: 3; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const video = document.getElementById('tutorialVideo');
    if (!video || video.dataset.seekControlsReady === 'true') return;

    video.dataset.seekControlsReady = 'true';
    video.preload = 'metadata';

    const controls = document.createElement('div');
    controls.className = 'tutorial-video-controls';
    controls.hidden = video.style.display === 'none';
    controls.setAttribute('aria-label', 'Controles para avanzar y retroceder el video');
    controls.innerHTML = `
        <button type="button" class="tutorial-video-seek-button" data-skip="-10" title="Retroceder 10 segundos">
            <i class="fas fa-undo" aria-hidden="true"></i><span>10 s</span>
        </button>
        <button type="button" class="tutorial-video-seek-button" data-skip="10" title="Adelantar 10 segundos">
            <span>10 s</span><i class="fas fa-redo" aria-hidden="true"></i>
        </button>
        <input class="tutorial-video-timeline" type="range" min="0" max="0" value="0" step="0.1" aria-label="Posición del video">
        <span class="tutorial-video-time" aria-live="off">00:00 / 00:00</span>
    `;
    video.closest('.video-container').insertAdjacentElement('afterend', controls);

    const timeline = controls.querySelector('.tutorial-video-timeline');
    const timeLabel = controls.querySelector('.tutorial-video-time');
    const skipButtons = [...controls.querySelectorAll('[data-skip]')];

    const formatTime = seconds => {
        if (!Number.isFinite(seconds) || seconds < 0) return '00:00';
        const total = Math.floor(seconds);
        const hours = Math.floor(total / 3600);
        const minutes = Math.floor((total % 3600) / 60);
        const secs = total % 60;
        return hours > 0
            ? `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
            : `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    };

    const updateControls = () => {
        const duration = Number.isFinite(video.duration) ? video.duration : 0;
        timeline.max = duration;
        timeline.value = Math.min(video.currentTime || 0, duration || 0);
        timeline.disabled = duration <= 0;
        skipButtons.forEach(button => { button.disabled = duration <= 0; });
        timeLabel.textContent = `${formatTime(video.currentTime)} / ${formatTime(duration)}`;
    };

    const seekTo = seconds => {
        if (!Number.isFinite(video.duration) || video.duration <= 0) return;
        const destination = Math.max(0, Math.min(seconds, video.duration));
        video.currentTime = destination;
    };

    skipButtons.forEach(button => {
        button.addEventListener('click', () => seekTo(video.currentTime + Number(button.dataset.skip)));
    });

    timeline.addEventListener('input', () => seekTo(Number(timeline.value)));
    video.addEventListener('loadedmetadata', updateControls);
    video.addEventListener('durationchange', updateControls);
    video.addEventListener('timeupdate', updateControls);
    video.addEventListener('seeking', updateControls);
    video.addEventListener('play', () => { controls.hidden = false; updateControls(); });
    video.addEventListener('click', () => { controls.hidden = false; });

    updateControls();
});
</script>
