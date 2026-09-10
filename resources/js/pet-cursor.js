/**
 * Bone cursor + chasing dog.
 *
 * The native cursor is replaced by a bone that tracks the pointer, and a little
 * dog trails after it — running when the bone moves, easing into a sit when it
 * stops. Pointer-only: skipped on touch devices, on reduced-motion, and on
 * small screens, where the native cursor stays untouched.
 */

const DOG_SVG = `
<svg viewBox="-4 -4 136 96" aria-hidden="true" focusable="false">
  <g class="pc-dog__tail">
    <path d="M27 42c-9-4-14-12-12-21" />
  </g>
  <g class="pc-dog__leg pc-dog__leg--back-far"><rect x="30" y="52" width="10" height="26" rx="5" /></g>
  <g class="pc-dog__leg pc-dog__leg--front-far"><rect x="66" y="52" width="10" height="26" rx="5" /></g>
  <ellipse class="pc-dog__body" cx="55" cy="48" rx="31" ry="18" />
  <g class="pc-dog__leg pc-dog__leg--back"><rect x="42" y="52" width="11" height="27" rx="5.5" /></g>
  <g class="pc-dog__leg pc-dog__leg--front"><rect x="76" y="52" width="11" height="27" rx="5.5" /></g>
  <circle class="pc-dog__body" cx="93" cy="31" r="17" />
  <ellipse class="pc-dog__muzzle" cx="110" cy="39" rx="13" ry="9" />
  <circle class="pc-dog__nose" cx="120" cy="35" r="4" />
  <circle class="pc-dog__eye-white" cx="97" cy="26" r="4.6" />
  <circle class="pc-dog__eye" cx="98" cy="26" r="2.6" />
  <g class="pc-dog__ear">
    <path d="M85 17c-7-1-12 4-12 12s3 14 8 15c4 1 6-4 6-11z" />
  </g>
</svg>`;

const BONE_SVG = `
<svg viewBox="-3 -3 66 36" aria-hidden="true" focusable="false">
  <g class="pc-bone__outline">
    <circle cx="10" cy="9" r="8" /><circle cx="10" cy="21" r="8" />
    <circle cx="50" cy="9" r="8" /><circle cx="50" cy="21" r="8" />
    <rect x="8" y="7" width="44" height="16" rx="8" />
  </g>
  <g class="pc-bone__fill">
    <circle cx="10" cy="9" r="8" /><circle cx="10" cy="21" r="8" />
    <circle cx="50" cy="9" r="8" /><circle cx="50" cy="21" r="8" />
    <rect x="8" y="7" width="44" height="16" rx="8" />
  </g>
</svg>`;

/** Pointer sits over something you type into — give the native caret back. */
const wantsNativeCursor = (el) =>
    !!el?.closest?.('input:not([type=checkbox]):not([type=radio]):not([type=submit]), textarea, select, [contenteditable]');

const initPetCursor = () => {
    const fine = window.matchMedia('(pointer: fine)').matches;
    const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!fine || calm || window.innerWidth < 900 || document.body.classList.contains('no-pet-cursor')) {
        return;
    }

    const root = document.createElement('div');
    root.className = 'pc-root';
    root.innerHTML = `
        <div class="pc-dog" data-pc-dog>${DOG_SVG}</div>
        <div class="pc-bone" data-pc-bone>${BONE_SVG}</div>`;
    document.body.appendChild(root);
    document.body.classList.add('pet-cursor-on');

    const bone = root.querySelector('[data-pc-bone]');
    const dog = root.querySelector('[data-pc-dog]');

    // Everything starts off-screen centre so the first mouse move doesn't dart in.
    const pointer = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
    const boneAt = { ...pointer };
    const dogAt = { x: pointer.x - 160, y: pointer.y };

    let facing = 1;
    let boneTilt = 0;
    let running = false;
    let idleFrames = 0;
    let frame = null;

    const lerp = (from, to, amount) => from + (to - from) * amount;

    const tick = () => {
        // Bone: tight follow, tilting into the direction it is being dragged.
        const dxBone = pointer.x - boneAt.x;
        const dyBone = pointer.y - boneAt.y;
        boneAt.x = lerp(boneAt.x, pointer.x, 0.3);
        boneAt.y = lerp(boneAt.y, pointer.y, 0.3);
        boneTilt = lerp(boneTilt, Math.max(-32, Math.min(32, dxBone * 0.6 + dyBone * 0.25)), 0.12);
        bone.style.transform = `translate3d(${boneAt.x}px, ${boneAt.y}px, 0) translate(-50%, -50%) rotate(${boneTilt}deg)`;

        // Dog: chases, but stops a nose-length short of the bone.
        const dx = pointer.x - dogAt.x;
        const dy = pointer.y - dogAt.y;
        const distance = Math.hypot(dx, dy) || 1;
        const gap = 96;

        if (distance > gap) {
            const targetX = pointer.x - (dx / distance) * gap;
            const targetY = pointer.y - (dy / distance) * gap;
            dogAt.x = lerp(dogAt.x, targetX, 0.075);
            dogAt.y = lerp(dogAt.y, targetY, 0.075);
        }

        const speed = Math.hypot(pointer.x - boneAt.x, pointer.y - boneAt.y) + Math.max(0, distance - gap) * 0.08;

        if (speed > 1.2) {
            idleFrames = 0;
            if (!running) {
                running = true;
                dog.classList.add('is-running');
            }
            if (Math.abs(dx) > 12) {
                const next = dx < 0 ? -1 : 1;
                if (next !== facing) {
                    facing = next;
                }
            }
        } else if (running && ++idleFrames > 24) {
            running = false;
            dog.classList.remove('is-running');
        }

        dog.style.transform = `translate3d(${dogAt.x}px, ${dogAt.y}px, 0) translate(-50%, -50%) scaleX(${facing})`;

        frame = requestAnimationFrame(tick);
    };

    const onMove = (event) => {
        pointer.x = event.clientX;
        pointer.y = event.clientY;
        root.classList.add('is-awake');
        root.classList.toggle('is-typing', wantsNativeCursor(event.target));
        document.body.classList.toggle('pet-cursor-on', !wantsNativeCursor(event.target));
    };

    document.addEventListener('mousemove', onMove, { passive: true });
    document.addEventListener('mousedown', () => bone.classList.add('is-grabbed'));
    document.addEventListener('mouseup', () => bone.classList.remove('is-grabbed'));
    document.addEventListener('mouseleave', () => root.classList.remove('is-awake'));
    document.addEventListener('mouseenter', () => root.classList.add('is-awake'));

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            cancelAnimationFrame(frame);
        } else {
            frame = requestAnimationFrame(tick);
        }
    });

    frame = requestAnimationFrame(tick);
};

export default initPetCursor;
