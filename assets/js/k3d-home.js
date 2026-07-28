(function () {
    var items = document.querySelectorAll('[data-k3d-animate]');
    if (!items.length) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        items.forEach(function (el) {
            el.classList.add('k3d-in-view');
        });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('k3d-in-view');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    items.forEach(function (el) {
        observer.observe(el);
    });
})();
