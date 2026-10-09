/**
 * 投稿記事一覧の非同期ページネーション
 *
 * functions-pagenation.php が出力する window.wpPagenation（ajaxUrl / action）を使って
 * 10件ずつ投稿記事を取得し、.pagenation 内の一覧とページ番号を書き換える。
 */
(function () {
    'use strict';

    var config = window.wpPagenation;

    // WordPress外（表示見本の index.php など）では何もしない
    if (!config || !config.ajaxUrl) {
        return;
    }

    document.querySelectorAll('.pagenation').forEach(init);

    function init(root) {
        var list = root.querySelector('.pagenation__list');
        var nav = root.querySelector('.pagenation__nav');
        if (!list || !nav) {
            return;
        }

        var current = 1;
        var total = parseInt(root.dataset.totalPages, 10) || 1;
        var requestId = 0;

        renderNav();

        nav.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-page]');
            if (!button || button.disabled) {
                return;
            }
            var page = parseInt(button.dataset.page, 10);
            if (page !== current) {
                load(page);
            }
        });

        function load(page) {
            var id = ++requestId;
            var url = new URL(config.ajaxUrl, window.location.href);
            url.searchParams.set('action', config.action);
            url.searchParams.set('page', page);

            setMessage('');
            root.classList.add('is-loading');
            list.setAttribute('aria-busy', 'true');

            fetch(url.toString(), { credentials: 'same-origin' })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function (json) {
                    if (id !== requestId) {
                        return;
                    }
                    if (!json || !json.success) {
                        throw new Error('Invalid response');
                    }
                    current = json.data.page;
                    total = json.data.totalPages;
                    renderList(json.data.items);
                    renderNav();
                    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
                })
                .catch(function () {
                    if (id === requestId) {
                        setMessage('記事の読み込みに失敗しました。時間をおいて再度お試しください。');
                    }
                })
                .then(function () {
                    if (id === requestId) {
                        root.classList.remove('is-loading');
                        list.removeAttribute('aria-busy');
                    }
                });
        }

        function renderList(items) {
            var fragment = document.createDocumentFragment();

            items.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'pagenation__item';

                var thumb = document.createElement('div');
                thumb.className = 'pagenation__thumb';
                var img = document.createElement('img');
                img.src = item.thumb;
                img.alt = '';
                thumb.appendChild(img);

                var content = document.createElement('div');
                content.className = 'pagenation__content';

                var time = document.createElement('time');
                time.className = 'pagenation__date';
                time.dateTime = item.datetime;
                time.textContent = item.date;

                var title = document.createElement('h3');
                title.className = 'pagenation__title';
                title.textContent = item.title;

                var body = document.createElement('p');
                body.className = 'pagenation__body';
                body.textContent = item.body;
                if (item.more) {
                    var more = document.createElement('a');
                    more.className = 'pagenation__more';
                    more.href = item.url;
                    more.textContent = '...詳しく読む';
                    body.appendChild(more);
                }

                content.appendChild(time);
                content.appendChild(title);
                content.appendChild(body);
                li.appendChild(thumb);
                li.appendChild(content);
                fragment.appendChild(li);
            });

            list.textContent = '';
            list.appendChild(fragment);
        }

        function renderNav() {
            nav.textContent = '';
            nav.hidden = total <= 1;
            if (total <= 1) {
                return;
            }

            nav.appendChild(createButton('前へ', current - 1, 'pagenation__page pagenation__page--prev', current <= 1));

            pageNumbers(current, total).forEach(function (page) {
                if (page === null) {
                    var ellipsis = document.createElement('span');
                    ellipsis.className = 'pagenation__ellipsis';
                    ellipsis.textContent = '…';
                    nav.appendChild(ellipsis);
                    return;
                }
                var button = createButton(String(page), page, 'pagenation__page', false);
                button.setAttribute('aria-label', page + 'ページ目');
                if (page === current) {
                    button.classList.add('is-current');
                    button.setAttribute('aria-current', 'page');
                }
                nav.appendChild(button);
            });

            nav.appendChild(createButton('次へ', current + 1, 'pagenation__page pagenation__page--next', current >= total));
        }

        function setMessage(text) {
            var message = root.querySelector('.pagenation__message');
            if (!text) {
                if (message) {
                    message.remove();
                }
                return;
            }
            if (!message) {
                message = document.createElement('p');
                message.className = 'pagenation__message';
                message.setAttribute('role', 'alert');
                root.insertBefore(message, nav);
            }
            message.textContent = text;
        }
    }

    function createButton(label, page, className, disabled) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.dataset.page = page;
        button.textContent = label;
        button.disabled = disabled;
        return button;
    }

    // 表示するページ番号の並び（null は省略記号）。先頭・末尾・現在ページの前後1件を表示する
    function pageNumbers(current, total) {
        var pages = [];
        var last = 0;
        for (var page = 1; page <= total; page++) {
            if (page === 1 || page === total || Math.abs(page - current) <= 1) {
                if (page - last > 1) {
                    pages.push(null);
                }
                pages.push(page);
                last = page;
            }
        }
        return pages;
    }
})();
