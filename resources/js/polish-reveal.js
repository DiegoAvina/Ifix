/**
 * Efecto "pulir la pintura" del hero de /nosotros.
 *
 * Debajo hay una foto opaca (filtros CSS + marcas de swirl). Encima, un
 * <canvas> dibuja la misma foto pulida solo donde ya pasó el pad: una
 * máscara que se va pintando con el cursor (o el dedo, arrastrando en
 * horizontal). Al llegar a cierto porcentaje, se completa sola.
 *
 * Uso: x-data="polishReveal({ src: '/images/foto.jpg' })" con x-ref="canvas".
 */
const GRID_W = 48;
const GRID_H = 24;
const FINISH_AT = 0.42;

export default function polishReveal({ src }) {
    return {
        progress: 0,
        done: false,
        padVisible: false,
        padX: 0,
        padY: 0,

        init() {
            this.canvas = this.$refs.canvas;
            this.ctx = this.canvas.getContext('2d');
            this.mask = document.createElement('canvas');
            this.mctx = this.mask.getContext('2d');
            this.grid = new Uint8Array(GRID_W * GRID_H);
            this.last = null;
            this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            this.img = new Image();
            this.img.decoding = 'async';
            this.img.onload = () => {
                this.resize();
                this.reduced ? this.finish(true) : this.intro();
            };
            this.img.src = src;

            this.observer = new ResizeObserver(() => this.img.complete && this.resize());
            this.observer.observe(this.$el);
        },

        destroy() {
            this.observer?.disconnect();
            cancelAnimationFrame(this.introFrame);
            cancelAnimationFrame(this.finishFrame);
        },

        resize() {
            const rect = this.$el.getBoundingClientRect();
            this.dpr = Math.min(window.devicePixelRatio || 1, 2);
            this.w = rect.width;
            this.h = rect.height;

            // Conserva lo ya pulido al cambiar de tamaño.
            const previous = document.createElement('canvas');
            previous.width = this.mask.width || 1;
            previous.height = this.mask.height || 1;
            previous.getContext('2d').drawImage(this.mask, 0, 0);

            for (const c of [this.canvas, this.mask]) {
                c.width = Math.round(this.w * this.dpr);
                c.height = Math.round(this.h * this.dpr);
            }

            this.mctx.drawImage(previous, 0, 0, this.mask.width, this.mask.height);

            if (this.done) {
                this.mctx.fillStyle = '#000';
                this.mctx.fillRect(0, 0, this.mask.width, this.mask.height);
            }

            this.render();
        },

        radius() {
            return Math.max(46, Math.min(110, this.w * 0.065));
        },

        dab(x, y) {
            const r = this.radius() * this.dpr;
            const cx = x * this.dpr;
            const cy = y * this.dpr;
            const g = this.mctx.createRadialGradient(cx, cy, 0, cx, cy, r);
            g.addColorStop(0, 'rgba(0,0,0,1)');
            g.addColorStop(0.55, 'rgba(0,0,0,.9)');
            g.addColorStop(1, 'rgba(0,0,0,0)');
            this.mctx.fillStyle = g;
            this.mctx.beginPath();
            this.mctx.arc(cx, cy, r, 0, Math.PI * 2);
            this.mctx.fill();

            // Avance: celdas de una cuadrícula gruesa tocadas por el pad.
            const cellW = this.w / GRID_W;
            const cellH = this.h / GRID_H;
            const rr = this.radius() * 0.7;
            for (let gx = Math.max(0, Math.floor((x - rr) / cellW)); gx <= Math.min(GRID_W - 1, Math.floor((x + rr) / cellW)); gx++) {
                for (let gy = Math.max(0, Math.floor((y - rr) / cellH)); gy <= Math.min(GRID_H - 1, Math.floor((y + rr) / cellH)); gy++) {
                    this.grid[gy * GRID_W + gx] = 1;
                }
            }
        },

        stroke(x, y) {
            // Interpola entre el punto anterior y el actual: trazo continuo aunque el mouse vaya rápido.
            const from = this.last ?? { x, y };
            const dist = Math.hypot(x - from.x, y - from.y);
            const steps = Math.max(1, Math.ceil(dist / (this.radius() * 0.3)));
            for (let i = 1; i <= steps; i++) {
                this.dab(from.x + ((x - from.x) * i) / steps, from.y + ((y - from.y) * i) / steps);
            }
            this.last = { x, y };

            const touched = this.grid.reduce((sum, cell) => sum + cell, 0);
            this.progress = Math.min(100, Math.round((touched / this.grid.length / FINISH_AT) * 100));
            this.render();

            if (this.progress >= 100) {
                this.finish();
            }
        },

        render() {
            if (!this.w) return;
            const { ctx, canvas, img } = this;
            const scale = Math.max(canvas.width / img.naturalWidth, canvas.height / img.naturalHeight);
            const dw = img.naturalWidth * scale;
            const dh = img.naturalHeight * scale;

            ctx.globalCompositeOperation = 'source-over';
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // Igual que object-cover + object-position: 70% center de la foto opaca.
            ctx.drawImage(img, (canvas.width - dw) * 0.7, (canvas.height - dh) / 2, dw, dh);
            ctx.globalCompositeOperation = 'destination-in';
            ctx.drawImage(this.mask, 0, 0);
            ctx.globalCompositeOperation = 'source-over';
        },

        point(event) {
            const rect = this.$el.getBoundingClientRect();
            return { x: event.clientX - rect.left, y: event.clientY - rect.top };
        },

        move(event) {
            if (this.done) return;
            this.stopIntro();
            const { x, y } = this.point(event);
            this.padX = x;
            this.padY = y;
            this.padVisible = true;
            this.stroke(x, y);
        },

        leave() {
            this.padVisible = false;
            this.last = null;
        },

        stopIntro() {
            if (this.introFrame) {
                cancelAnimationFrame(this.introFrame);
                this.introFrame = null;
                this.last = null;
            }
        },

        // Demostración: el pad pule una franja sobre el cofre para que se entienda el juego.
        intro() {
            const start = performance.now();
            const duration = 2600;
            const tick = (now) => {
                const t = Math.min(1, (now - start) / duration);
                const x = this.w * (0.5 + 0.42 * t);
                const y = this.h * (0.62 + 0.14 * Math.sin(t * Math.PI * 4));
                this.padX = x;
                this.padY = y;
                this.padVisible = true;
                this.stroke(x, y);

                if (t < 1 && !this.done) {
                    this.introFrame = requestAnimationFrame(tick);
                } else {
                    this.introFrame = null;
                    this.padVisible = false;
                    this.last = null;
                }
            };
            this.introFrame = requestAnimationFrame(tick);
        },

        finish(instant = false) {
            if (this.done) return;
            this.done = true;
            this.progress = 100;
            this.padVisible = false;
            this.mctx.fillStyle = '#000';

            if (instant) {
                this.mctx.fillRect(0, 0, this.mask.width, this.mask.height);
                this.render();
                return;
            }

            const start = performance.now();
            const tick = (now) => {
                const t = Math.min(1, (now - start) / 900);
                this.mctx.globalAlpha = 0.12 + t * 0.3;
                this.mctx.fillRect(0, 0, this.mask.width, this.mask.height);
                this.mctx.globalAlpha = 1;
                this.render();
                if (t < 1) this.finishFrame = requestAnimationFrame(tick);
                else {
                    this.mctx.fillRect(0, 0, this.mask.width, this.mask.height);
                    this.render();
                }
            };
            this.finishFrame = requestAnimationFrame(tick);
        },
    };
}
