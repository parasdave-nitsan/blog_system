(function () {
    "use strict";

    function initBlogCards() {
        const cards = document.querySelectorAll(".js-blog-card");

        if (!cards.length) {
            return;
        }

        // Respect users who prefer reduced motion.
        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            cards.forEach(function (card) {
                card.classList.add("is-visible");
            });

            return;
        }

        // Reveal cards as they enter the viewport.
        if ("IntersectionObserver" in window) {
            const observer = new IntersectionObserver(
                function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        entry.target.classList.add("is-visible");
                        observer.unobserve(entry.target);
                    });
                },
                {
                    threshold: 0.12,
                    rootMargin: "0px 0px -40px 0px"
                }
            );

            cards.forEach(function (card) {
                observer.observe(card);
            });
        } else {
            cards.forEach(function (card) {
                card.classList.add("is-visible");
            });
        }

        // Slight mouse-follow effect on desktop.
        if (window.matchMedia("(pointer: fine)").matches) {
            cards.forEach(function (card) {
                card.addEventListener("mousemove", function (event) {
                    const rect = card.getBoundingClientRect();

                    const x =
                        (event.clientX - rect.left) / rect.width - 0.5;

                    const y =
                        (event.clientY - rect.top) / rect.height - 0.5;

                    card.style.transform =
                        "translateY(-8px) perspective(900px) " +
                        "rotateX(" + (-y * 2) + "deg) " +
                        "rotateY(" + (x * 2) + "deg)";
                });

                card.addEventListener("mouseleave", function () {
                    card.style.transform = "";
                });
            });
        }
    }

    document.addEventListener('click', function (event) {
        if (!event.target.matches('.ajax-pagination-link')) {
            return;
        }

        event.preventDefault();
        const url = event.target.getAttribute('href');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-store'
        })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContainer = doc.querySelector('#reload-container');

                document.querySelector('#reload-container').replaceWith(newContainer);
                history.pushState({}, '', url);
            })
            .catch(error => console.error('Error loading pagination:', error));
    });

    document.getElementById('blog-filter-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const category = document.getElementById('blog-filter-category').value;
        const sortBy = document.getElementById('blog-filter-sort').value;
        const direction = document.getElementById('blog-filter-direction').value;

        window.location.href = '/' + category + '/' + sortBy + '/' + direction + '/page-1';
    });


    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initBlogCards);
    } else {
        initBlogCards();
    }

})();