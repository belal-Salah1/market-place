import { onMounted, nextTick } from 'vue';
import gsap from 'gsap';

export function useGsap() {
    onMounted(() => {
        nextTick(() => {
            animatePage();
        });
    });
}

function animatePage() {
    const elements = document.querySelectorAll('[data-gsap]');

    elements.forEach((el) => {
        const htmlEl = el as HTMLElement;
        const type = htmlEl.dataset.gsap;
        const delay = parseFloat(htmlEl.dataset.gsapDelay || '0');
        const duration = parseFloat(htmlEl.dataset.gsapDuration || '0.7');

        const base = {
            duration,
            delay,
            ease: 'power3.out',
            onStart() {
                htmlEl.style.visibility = 'visible';
            },
        };

        switch (type) {
            case 'fade-up':
                gsap.fromTo(htmlEl, { opacity: 0, y: 30 }, { opacity: 1, y: 0, ...base });
                break;
            case 'fade-down':
                gsap.fromTo(htmlEl, { opacity: 0, y: -20 }, { opacity: 1, y: 0, ...base });
                break;
            case 'fade-in':
                gsap.fromTo(htmlEl, { opacity: 0 }, { opacity: 1, ...base });
                break;
            case 'slide-left':
                gsap.fromTo(htmlEl, { opacity: 0, x: -30 }, { opacity: 1, x: 0, ...base });
                break;
            case 'slide-right':
                gsap.fromTo(htmlEl, { opacity: 0, x: 30 }, { opacity: 1, x: 0, ...base });
                break;
            case 'scale-in':
                gsap.fromTo(htmlEl, { opacity: 0, scale: 0.85 }, { opacity: 1, scale: 1, ...base });
                break;
            case 'stagger': {
                const kids = htmlEl.children;
                Array.from(kids).forEach((c) => {
                    (c as HTMLElement).style.visibility = 'visible';
                });
                gsap.fromTo(
                    kids,
                    { opacity: 0, y: 25 },
                    { opacity: 1, y: 0, ...base, stagger: 0.1 },
                );
                break;
            }
        }
    });

    // Floating orbs
    document.querySelectorAll('[data-gsap-float]').forEach((el) => {
        const speed = el.getAttribute('data-gsap-float') === 'slow' ? 8 : 5;
        gsap.to(el, {
            y: -20,
            x: 10,
            rotation: 2,
            duration: speed,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
        });
    });
}
