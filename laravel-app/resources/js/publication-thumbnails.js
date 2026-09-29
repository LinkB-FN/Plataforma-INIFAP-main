let pdfLibrary;
const pending = [];
let active = 0;

// Limit PDF work to two visible cards at a time.
function enqueue(task) {
    pending.push(task);
    drain();
}

function drain() {
    while (active < 2 && pending.length) {
        active++;
        Promise.resolve().then(pending.shift()).finally(() => {
            active--;
            drain();
        });
    }
}

async function renderPdf(img) {
    let loadingTask;
    try {
        const url = new URL(img.dataset.pdfUrl, window.location.href);
        // Remote documents may require authentication or disallow CORS.
        if (url.origin !== window.location.origin) return;
        pdfLibrary ??= Promise.all([
            import('pdfjs-dist/build/pdf.mjs'),
            import('pdfjs-dist/build/pdf.worker.min.mjs?url'),
        ]).then(([pdfjs, worker]) => {
            pdfjs.GlobalWorkerOptions.workerSrc = worker.default;
            return pdfjs;
        });
        const pdfjs = await pdfLibrary;
        loadingTask = pdfjs.getDocument({ url: url.href, isEvalSupported: false });
        // Encrypted documents keep their placeholder instead of waiting for a password.
        loadingTask.onPassword = () => { void loadingTask.destroy(); };
        const pdf = await loadingTask.promise;
        const page = await pdf.getPage(1);
        const original = page.getViewport({ scale: 1 });
        const viewport = page.getViewport({ scale: Math.min(600 / original.width, 800 / original.height) });
        const canvas = document.createElement('canvas');
        canvas.width = Math.ceil(viewport.width);
        canvas.height = Math.ceil(viewport.height);
        await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
        img.src = canvas.toDataURL('image/jpeg', 0.85);
    } catch {
        // The publication remains accessible even if its preview cannot be rendered.
    } finally {
        await loadingTask?.destroy();
    }
}

function initialize(img) {
    const youtubeId = img.dataset.youtubeId;
    const alternatives = youtubeId
        ? ['hqdefault', 'mqdefault', 'default'].map(size => `https://i.ytimg.com/vi/${youtubeId}/${size}.jpg`)
        : [];
    if (img.dataset.cover) alternatives.push(img.dataset.cover);
    if (img.dataset.fileImage && img.dataset.fileImage !== img.getAttribute('src')) {
        alternatives.push(img.dataset.fileImage);
    }
    alternatives.push(img.dataset.fallback);
    let pdfStarted = false;
    const preview = () => {
        if (!img.dataset.pdfUrl || pdfStarted) return;
        pdfStarted = true;
        enqueue(() => renderPdf(img));
    };
    const next = () => {
        const candidate = alternatives.shift();
        if (candidate && candidate !== img.getAttribute('src')) img.src = candidate;
        else if (alternatives.length) next();
        if (!alternatives.length) preview();
    };
    const loaded = () => {
        // YouTube sometimes returns a small placeholder with HTTP 200.
        const highResolutionYoutube = /\/(maxresdefault|hqdefault|mqdefault)\.jpg$/.test(img.src);
        if (youtubeId && highResolutionYoutube && img.naturalWidth <= 120) {
            next();
            return;
        }
        img.classList.toggle('img-small', img.naturalHeight > 0 && img.naturalHeight < 400);
    };
    img.addEventListener('error', next);
    img.addEventListener('load', loaded);
    if (img.complete) {
        if (img.naturalWidth) loaded();
        else next();
    }
    if (img.dataset.needsPreview === 'true') {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(entries => {
                if (entries.some(entry => entry.isIntersecting)) {
                    observer.disconnect();
                    preview();
                }
            }, { rootMargin: '200px' });
            observer.observe(img);
        } else preview();
    }
}

document.querySelectorAll('img[data-thumbnail]').forEach(initialize);
